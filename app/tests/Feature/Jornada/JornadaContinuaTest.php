<?php

namespace Tests\Feature\Jornada;

use App\Domain\Cashback\CreditarCashbackService;
use App\Models\Carteira;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Jornada contínua ponta a ponta (STORY-031 · EPIC-006) — a costura do loop de valor.
 *
 * Prova que, depois de uma coleta válida que gera crédito (caminho real do EPIC-003), o **saldo da
 * home-hub reflete o novo crédito na mesma sessão** (CA-1) e que o crédito **aparece no extrato**
 * (CA-2). A validação assíncrona do cupom (SEFAZ, EPIC-002) é representada aqui pela criação de um
 * cupom já `validado` + o serviço real de crédito — o loop navegável (browser) é o E2E Dusk.
 */
class JornadaContinuaTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE = '35260112345678000195650010009999991000000019';

    /** Simula o resultado de uma coleta válida do usuário: cupom validado + crédito real (EPIC-003). */
    private function coletaValidaCredita(User $user, string $valorReais): void
    {
        $cupom = Cupom::create([
            'chave_acesso' => self::CHAVE, 'uf' => '35', 'ano_mes' => '2601',
            'cnpj_emitente' => '12345678000195', 'modelo' => '65', 'valor_total' => $valorReais,
            'data_emissao' => '2026-01-15 10:00:00', 'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ]);
        CupomAtribuicao::create(['cupom_id' => $cupom->id, 'user_id' => $user->id]);

        app(CreditarCashbackService::class)->creditarPorCupom($cupom);
    }

    /** CA-1 — o saldo da home reflete o crédito da coleta, na mesma sessão (sem novo login). */
    public function test_saldo_da_home_reflete_a_coleta_sem_novo_login(): void
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 0]);

        // Antes da coleta: saldo zero.
        $this->actingAs($user)->get('/inicio')
            ->assertInertia(fn (Assert $page) => $page->component('Home/Hub')->where('saldo.reais', '0,00'));

        $this->coletaValidaCredita($user, '4890.00'); // 0,1% = R$ 4,89

        // Mesma sessão, sem novo login: o saldo reflete o novo crédito.
        $this->actingAs($user)->get('/inicio')
            ->assertInertia(fn (Assert $page) => $page->component('Home/Hub')->where('saldo.reais', '4,89'));
    }

    /** CA-2 — a coleta recém-creditada aparece no extrato da carteira. */
    public function test_extrato_mostra_o_credito_da_coleta(): void
    {
        $user = User::factory()->create();
        $this->coletaValidaCredita($user, '4890.00');

        $this->actingAs($user)->get('/carteira')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Carteira/Index')
                ->where('saldo.reais', '4,89')
                ->has('extrato', 1)
                ->where('extrato.0.cupom_valor', '4.890,00')
                ->where('extrato.0.credito', '4,89'));
    }
}
