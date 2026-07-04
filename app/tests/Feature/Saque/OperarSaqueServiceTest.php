<?php

namespace Tests\Feature\Saque;

use App\Domain\Saque\SaqueInvalidoException;
use App\Domain\Saque\SaqueService;
use App\Domain\Saque\SolicitarSaqueService;
use App\Domain\Saque\TransicaoInvalidaException;
use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Saque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Máquina de estados do saque no backoffice (STORY-017, ADR-005):
 * solicitado → em_analise → aprovado → pago; em_analise → rejeitado (estorno devolve o saldo).
 * Transições inválidas são barradas; rejeição estorna uma única vez (idempotência por estado).
 */
class OperarSaqueServiceTest extends TestCase
{
    use RefreshDatabase;

    private const CPF = '11144477735';

    private SaqueService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SaqueService::class);
    }

    /** Colaborador com saldo que já solicitou um saque de 800 (saldo reservado → 200). */
    private function saqueSolicitado(): array
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1000]);
        $saque = app(SolicitarSaqueService::class)->solicitar($user, 800, self::CPF, self::CPF);

        return [$user, $saque];
    }

    private function operador(): User
    {
        return User::factory()->create();
    }

    public function test_fluxo_feliz_ate_pago(): void
    {
        [, $saque] = $this->saqueSolicitado();
        $op = $this->operador();

        $saque = $this->service->assumir($saque, $op);
        $this->assertSame(Saque::STATUS_EM_ANALISE, $saque->status);
        $this->assertSame($op->id, $saque->processado_por);

        $saque = $this->service->aprovar($saque);
        $this->assertSame(Saque::STATUS_APROVADO, $saque->status);

        $saque = $this->service->pagar($saque, 'E2E-PIX-123');
        $this->assertSame(Saque::STATUS_PAGO, $saque->status);
        $this->assertSame('E2E-PIX-123', $saque->comprovante);
    }

    public function test_rejeitar_estorna_o_saldo(): void
    {
        [$user, $saque] = $this->saqueSolicitado();
        $saque = $this->service->assumir($saque, $this->operador());

        $saque = $this->service->rejeitar($saque, 'chave não confere');

        $this->assertSame(Saque::STATUS_REJEITADO, $saque->status);
        // Saldo volta para 1000 (200 + 800 estornado).
        $this->assertSame(1000, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
        $this->assertDatabaseHas('carteira_transacoes', [
            'saque_id' => $saque->id,
            'tipo' => CarteiraTransacao::TIPO_ESTORNO_SAQUE,
            'valor_centavos' => 800,
        ]);
    }

    public function test_reconciliacao_apos_estorno(): void
    {
        [$user, $saque] = $this->saqueSolicitado();
        $saque = $this->service->assumir($saque, $this->operador());
        $this->service->rejeitar($saque, null);

        $carteira = Carteira::where('user_id', $user->id)->firstOrFail();
        $soma = (int) DB::table('carteira_transacoes')->where('carteira_id', $carteira->id)->sum('valor_centavos');
        // -800 (débito) + 800 (estorno) = 0 no ledger deste teste; saldo = 1000.
        $this->assertSame(0, $soma);
        $this->assertSame(1000, $carteira->saldo_centavos);
    }

    public function test_nao_paga_pulando_a_analise(): void
    {
        [, $saque] = $this->saqueSolicitado(); // solicitado

        $this->expectException(TransicaoInvalidaException::class);
        $this->service->pagar($saque, 'E2E-PIX-123');
    }

    public function test_nao_aprova_direto_de_solicitado(): void
    {
        [, $saque] = $this->saqueSolicitado();

        $this->expectException(TransicaoInvalidaException::class);
        $this->service->aprovar($saque);
    }

    public function test_nao_assume_saque_ja_em_analise(): void
    {
        [, $saque] = $this->saqueSolicitado();
        $saque = $this->service->assumir($saque, $this->operador());

        $this->expectException(TransicaoInvalidaException::class);
        $this->service->assumir($saque, $this->operador());
    }

    public function test_rejeitar_duas_vezes_nao_estorna_de_novo(): void
    {
        [$user, $saque] = $this->saqueSolicitado();
        $saque = $this->service->assumir($saque, $this->operador());
        $saque = $this->service->rejeitar($saque, null);

        $this->expectException(TransicaoInvalidaException::class);
        try {
            $this->service->rejeitar($saque, null);
        } finally {
            $this->assertSame(1000, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
            $this->assertDatabaseCount('carteira_transacoes', 2); // débito + 1 estorno só
        }
    }

    public function test_pagar_exige_comprovante(): void
    {
        [, $saque] = $this->saqueSolicitado();
        $saque = $this->service->assumir($saque, $this->operador());
        $saque = $this->service->aprovar($saque);

        $this->expectException(SaqueInvalidoException::class);
        $this->service->pagar($saque, '   ');
    }
}
