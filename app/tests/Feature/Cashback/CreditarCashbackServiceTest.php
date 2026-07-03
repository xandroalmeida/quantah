<?php

namespace Tests\Feature\Cashback;

use App\Domain\Cashback\CreditarCashbackService;
use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Crédito de cashback ao validar o cupom (STORY-015, ADR-005). Verifica o núcleo de
 * dinheiro: 0,1% em centavos, idempotência por cupom, no-op sem atribuição / cupom não
 * validado / crédito zero, e reconciliação saldo == SUM(ledger) + invariante saldo >= 0.
 */
class CreditarCashbackServiceTest extends TestCase
{
    use RefreshDatabase;

    private CreditarCashbackService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CreditarCashbackService::class);
    }

    /** Cria um cupom já validado com um dado valor em reais (decimal 12,2). */
    private function cupomValidado(string $valorReais, string $chave): Cupom
    {
        return Cupom::create([
            'chave_acesso' => $chave,
            'uf' => '35',
            'ano_mes' => '2601',
            'cnpj_emitente' => '12345678000195',
            'modelo' => '65',
            'numero' => 123456,
            'serie' => 1,
            'valor_total' => $valorReais,
            'status' => Cupom::STATUS_VALIDADO,
            'origem' => 'scan',
        ]);
    }

    private function atribuir(Cupom $cupom, User $user): void
    {
        CupomAtribuicao::create(['cupom_id' => $cupom->id, 'user_id' => $user->id]);
    }

    private const CHAVE_A = '35260112345678000195650010001234561000000019';

    private const CHAVE_B = '35260112345678000195650010001234561000000028';

    public function test_credita_a_carteira_do_coletor(): void
    {
        $user = User::factory()->create();
        $cupom = $this->cupomValidado('1000.00', self::CHAVE_A); // 0,1% = R$ 1,00 = 100 centavos
        $this->atribuir($cupom, $user);

        $transacao = $this->service->creditarPorCupom($cupom);

        $this->assertNotNull($transacao);
        $this->assertSame(100, $transacao->valor_centavos);
        $this->assertSame(CarteiraTransacao::TIPO_CREDITO_CASHBACK, $transacao->tipo);
        $this->assertSame($cupom->id, $transacao->cupom_id);

        $carteira = Carteira::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(100, $carteira->saldo_centavos);
        $this->assertDatabaseCount('carteira_transacoes', 1);
    }

    public function test_credito_e_idempotente_por_cupom(): void
    {
        $user = User::factory()->create();
        $cupom = $this->cupomValidado('1000.00', self::CHAVE_A);
        $this->atribuir($cupom, $user);

        $this->service->creditarPorCupom($cupom);
        $segunda = $this->service->creditarPorCupom($cupom); // reprocessamento não duplica

        $this->assertNull($segunda);
        $this->assertDatabaseCount('carteira_transacoes', 1);
        $this->assertSame(100, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
    }

    public function test_sem_atribuicao_nao_credita(): void
    {
        // Ex.: ingestão via CLI, sem Colaborador logado — o cupom vale, mas ninguém recebe.
        $cupom = $this->cupomValidado('1000.00', self::CHAVE_A);

        $this->assertNull($this->service->creditarPorCupom($cupom));
        $this->assertDatabaseCount('carteira_transacoes', 0);
        $this->assertDatabaseCount('carteiras', 0);
    }

    public function test_cupom_nao_validado_nao_credita(): void
    {
        $user = User::factory()->create();
        $cupom = $this->cupomValidado('1000.00', self::CHAVE_A);
        $cupom->update(['status' => Cupom::STATUS_PENDENTE]);
        $this->atribuir($cupom, $user);

        $this->assertNull($this->service->creditarPorCupom($cupom));
        $this->assertDatabaseCount('carteira_transacoes', 0);
    }

    public function test_credito_zero_nao_gera_transacao(): void
    {
        // R$ 4,99 → 0,1% = 0,499 centavo → 0. Cupom vale, mas não há centavo a creditar.
        $user = User::factory()->create();
        $cupom = $this->cupomValidado('4.99', self::CHAVE_A);
        $this->atribuir($cupom, $user);

        $this->assertNull($this->service->creditarPorCupom($cupom));
        $this->assertDatabaseCount('carteira_transacoes', 0);
    }

    public function test_reconciliacao_saldo_igual_soma_do_ledger(): void
    {
        $user = User::factory()->create();
        $a = $this->cupomValidado('1000.00', self::CHAVE_A); // 100
        $b = $this->cupomValidado('87.90', self::CHAVE_B);   // 9
        $this->atribuir($a, $user);
        $this->atribuir($b, $user);

        $this->service->creditarPorCupom($a);
        $this->service->creditarPorCupom($b);

        $carteira = Carteira::where('user_id', $user->id)->firstOrFail();
        $somaLedger = (int) DB::table('carteira_transacoes')
            ->where('carteira_id', $carteira->id)
            ->sum('valor_centavos');

        $this->assertSame(109, $carteira->saldo_centavos);
        $this->assertSame($somaLedger, $carteira->saldo_centavos);
    }

    public function test_saldo_negativo_e_barrado_pelo_banco(): void
    {
        // Invariante saldo >= 0 é a última linha de defesa (CHECK no banco), não só no app.
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 0]);

        $this->expectException(QueryException::class);
        DB::table('carteiras')->where('id', $carteira->id)->update(['saldo_centavos' => -1]);
    }
}
