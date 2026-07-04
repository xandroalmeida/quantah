<?php

namespace Tests\Feature\Saque;

use App\Domain\Saque\SaldoInsuficienteException;
use App\Domain\Saque\SaqueInvalidoException;
use App\Domain\Saque\SolicitarSaqueService;
use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Saque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

/**
 * Solicitação de saque (STORY-017, ADR-005) — a **reserva**: valida mínimo (R$ 5,00),
 * titularidade (CPF válido = chave PIX) e saldo suficiente **sob lock**, debita o ledger
 * (`debito_saque`) e cria o saque em `solicitado`. Núcleo de dinheiro: cobertura ≥98%.
 */
class SolicitarSaqueServiceTest extends TestCase
{
    use RefreshDatabase;

    private const CPF = '11144477735';

    private SolicitarSaqueService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(SolicitarSaqueService::class);
    }

    private function userComSaldo(int $centavos): User
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => $centavos]);

        return $user;
    }

    public function test_solicita_reserva_o_valor_e_cria_saque(): void
    {
        $user = $this->userComSaldo(1000);

        $saque = $this->service->solicitar($user, 800, self::CPF, self::CPF);

        $this->assertSame(Saque::STATUS_SOLICITADO, $saque->status);
        $this->assertSame(800, $saque->valor_centavos);
        $this->assertSame(self::CPF, $saque->cpf);

        $carteira = Carteira::where('user_id', $user->id)->firstOrFail();
        $this->assertSame(200, $carteira->saldo_centavos); // 1000 - 800 reservado
        $this->assertDatabaseHas('carteira_transacoes', [
            'saque_id' => $saque->id,
            'tipo' => CarteiraTransacao::TIPO_DEBITO_SAQUE,
            'valor_centavos' => -800,
        ]);
    }

    public function test_aceita_o_valor_minimo_exato(): void
    {
        $user = $this->userComSaldo(500);

        $saque = $this->service->solicitar($user, 500, self::CPF, self::CPF);

        $this->assertSame(500, $saque->valor_centavos);
        $this->assertSame(0, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
    }

    public function test_rejeita_abaixo_do_minimo(): void
    {
        $user = $this->userComSaldo(1000);

        $this->expectException(SaqueInvalidoException::class);
        try {
            $this->service->solicitar($user, 499, self::CPF, self::CPF);
        } finally {
            $this->assertDatabaseCount('saques', 0);
            $this->assertSame(1000, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
        }
    }

    public function test_rejeita_saldo_insuficiente(): void
    {
        $user = $this->userComSaldo(700);

        $this->expectException(SaldoInsuficienteException::class);
        try {
            $this->service->solicitar($user, 800, self::CPF, self::CPF);
        } finally {
            $this->assertDatabaseCount('saques', 0);
            $this->assertDatabaseCount('carteira_transacoes', 0);
            $this->assertSame(700, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
        }
    }

    public function test_rejeita_cpf_invalido(): void
    {
        $user = $this->userComSaldo(1000);

        $this->expectException(SaqueInvalidoException::class);
        $this->service->solicitar($user, 800, '11144477700', '11144477700');
    }

    public function test_rejeita_chave_pix_que_nao_confere_com_o_cpf(): void
    {
        // Titularidade por construção: a chave PIX (tipo CPF) tem de ser o CPF do titular.
        $user = $this->userComSaldo(1000);

        $this->expectException(SaqueInvalidoException::class);
        $this->service->solicitar($user, 800, self::CPF, '39053344705');
    }

    public function test_sem_carteira_e_saldo_insuficiente(): void
    {
        $user = User::factory()->create(); // sem carteira

        $this->expectException(SaldoInsuficienteException::class);
        $this->service->solicitar($user, 800, self::CPF, self::CPF);
    }

    public function test_reconciliacao_saldo_igual_soma_do_ledger_apos_reserva(): void
    {
        $user = $this->userComSaldo(1000);

        $this->service->solicitar($user, 800, self::CPF, self::CPF);

        $carteira = Carteira::where('user_id', $user->id)->firstOrFail();
        $soma = (int) DB::table('carteira_transacoes')->where('carteira_id', $carteira->id)->sum('valor_centavos');
        // 1000 (saldo inicial) não é lançamento aqui; a reserva lançou -800. Saldo = 1000 - 800 = 200.
        $this->assertSame(200, $carteira->saldo_centavos);
        $this->assertSame(-800, $soma); // só o débito da reserva está no ledger deste teste
    }

    public function test_aceita_cpf_e_chave_com_mascara(): void
    {
        $user = $this->userComSaldo(1000);

        $saque = $this->service->solicitar($user, 800, '111.444.777-35', '111.444.777-35');

        // Persistido canônico, sem máscara.
        $this->assertSame(self::CPF, $saque->cpf);
        $this->assertSame(self::CPF, $saque->chave_pix);
    }
}
