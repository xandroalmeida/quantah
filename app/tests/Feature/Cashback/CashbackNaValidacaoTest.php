<?php

namespace Tests\Feature\Cashback;

use App\Domain\Cashback\CreditarCashbackService;
use App\Domain\Coleta\Events\CupomValidado;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\Sefaz\SefazSpFetcher;
use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\Support\Coleta\FakeSefazSpFetcher;
use Tests\TestCase;

/**
 * Amarra a coleta (EPIC-002) ao cashback (STORY-015): quando o cupom fica válido, o
 * evento de domínio `CupomValidado` dispara e o Colaborador que o coletou é creditado —
 * de forma assíncrona (fila), fora do request. Aqui a fila roda `sync` (inline).
 */
class CashbackNaValidacaoTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    private function comFetcher(FakeSefazSpFetcher $fake): void
    {
        $this->app->instance(SefazSpFetcher::class, $fake);
    }

    private function ingestao(): IngestaoCupomService
    {
        return $this->app->make(IngestaoCupomService::class);
    }

    /** Payload do portal com um valor específico (resto igual ao padrão de SP). */
    private function fakeComValor(string $valorReais): FakeSefazSpFetcher
    {
        return (new FakeSefazSpFetcher)->comPayload(
            ['valor_total' => $valorReais] + FakeSefazSpFetcher::payloadPadrao()
        );
    }

    public function test_cupom_validado_credita_o_coletor(): void
    {
        $this->comFetcher($this->fakeComValor('1000.00')); // 0,1% = 100 centavos
        $user = User::factory()->create();

        // Fluxo real: coleta autenticada → persiste pendente + atribui → job valida (sync).
        $this->ingestao()->capturar(self::CHAVE_SP, 'scan', $user->id);

        $cupom = Cupom::where('chave_acesso', self::CHAVE_SP)->firstOrFail();
        $this->assertSame(Cupom::STATUS_VALIDADO, $cupom->status);

        $carteira = Carteira::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(100, $carteira->saldo_centavos);
        $this->assertDatabaseHas('carteira_transacoes', [
            'cupom_id' => $cupom->id,
            'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
            'valor_centavos' => 100,
        ]);
    }

    public function test_ingestao_sem_usuario_valida_sem_creditar(): void
    {
        // Ingestão via CLI (`ingerir`, sem Colaborador) — o cupom vale, mas ninguém recebe.
        $this->comFetcher($this->fakeComValor('1000.00'));

        $this->ingestao()->ingerir(self::CHAVE_SP, 'scan');

        $this->assertSame(
            Cupom::STATUS_VALIDADO,
            Cupom::where('chave_acesso', self::CHAVE_SP)->firstOrFail()->status
        );
        $this->assertDatabaseCount('carteira_transacoes', 0);
        $this->assertDatabaseCount('carteiras', 0);
    }

    public function test_evento_cupom_validado_e_disparado_ao_validar(): void
    {
        Event::fake([CupomValidado::class]);
        $this->comFetcher($this->fakeComValor('1000.00'));

        $this->ingestao()->ingerir(self::CHAVE_SP, 'scan');

        $cupom = Cupom::where('chave_acesso', self::CHAVE_SP)->firstOrFail();
        Event::assertDispatched(
            CupomValidado::class,
            fn (CupomValidado $e) => $e->cupomId === $cupom->id
        );
    }

    public function test_cupom_rejeitado_nao_dispara_credito(): void
    {
        // Portal diz que o cupom não existe/está cancelado (negócio) → rejeitado, sem crédito.
        $this->comFetcher((new FakeSefazSpFetcher)->falharCom(
            \App\Domain\Coleta\Sefaz\SefazExtracaoException::negocio('cupom cancelado')
        ));
        $user = User::factory()->create();

        $this->ingestao()->capturar(self::CHAVE_SP, 'scan', $user->id);

        $this->assertSame(
            Cupom::STATUS_REJEITADO,
            Cupom::where('chave_acesso', self::CHAVE_SP)->firstOrFail()->status
        );
        $this->assertDatabaseCount('carteira_transacoes', 0);
    }

    public function test_reprocessamento_nao_duplica_credito(): void
    {
        $this->comFetcher($this->fakeComValor('1000.00'));
        $user = User::factory()->create();

        $this->ingestao()->capturar(self::CHAVE_SP, 'scan', $user->id);
        $cupom = Cupom::where('chave_acesso', self::CHAVE_SP)->firstOrFail();

        // Reprocessa a validação diretamente (idempotência do crédito é a garantia).
        app(CreditarCashbackService::class)->creditarPorCupom($cupom->fresh());

        $this->assertDatabaseCount('carteira_transacoes', 1);
        $this->assertSame(100, Carteira::where('user_id', $user->id)->firstOrFail()->saldo_centavos);
    }
}
