<?php

namespace Tests\Feature\Carteira;

use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Tela de carteira (STORY-016) — contrato controller↔página Inertia + auth. A fonte é o
 * read-model ExtratoCarteira (STORY-015 como dados). Guest é barrado; dados nunca hardcoded.
 */
class CarteiraControllerTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE = '35260112345678000195650010001234561000000019';

    public function test_carteira_exige_autenticacao(): void
    {
        $this->get('/carteira')->assertRedirect('/login');
    }

    public function test_renderiza_saldo_e_extrato_do_colaborador(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 9]);
        $cupom = Cupom::create([
            'chave_acesso' => self::CHAVE, 'uf' => '35', 'ano_mes' => '2601',
            'cnpj_emitente' => '12345678000195', 'modelo' => '65', 'valor_total' => '87.90',
            'data_emissao' => '2026-01-15 10:00:00', 'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ]);
        CarteiraTransacao::create([
            'carteira_id' => $carteira->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
            'valor_centavos' => 9, 'cupom_id' => $cupom->id,
        ]);

        $this->actingAs($user)->get('/carteira')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Carteira/Index')
                ->where('saldo.reais', '0,09')
                ->where('saldo.centavos', 9)
                ->has('extrato', 1)
                ->where('extrato.0.cupom_valor', '87,90')
                ->where('extrato.0.credito', '0,09')
                ->where('extrato.0.data', '15 jan 2026'));
    }

    public function test_colaborador_sem_creditos_ve_saldo_zero_e_extrato_vazio(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/carteira')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Carteira/Index')
                ->where('saldo.reais', '0,00')
                ->where('saldo.centavos', 0)
                ->has('extrato', 0));
    }
}
