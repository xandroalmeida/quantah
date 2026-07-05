<?php

namespace Tests\Feature\Home;

use App\Models\Carteira;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Home-hub do Coletador (STORY-029 · EPIC-006) — destino pós-login da área B2C.
 *
 * Contrato controller↔página Inertia + guarda de acesso. O saldo vem do read-model
 * ExtratoCarteira (EPIC-003), nunca hardcoded. Cobre:
 *  - CA-1: o destino pós-login renderiza a home-hub (Home/Hub), não a página genérica.
 *  - CA-2: exibe o saldo real da carteira no formato brasileiro (R$ 0,00).
 *  - CA-5: visitante não autenticado é redirecionado ao login (guarda do EPIC-004).
 */
class HomeHubTest extends TestCase
{
    use RefreshDatabase;

    /** CA-5 — visitante anônimo é barrado para o login. */
    public function test_home_hub_exige_autenticacao(): void
    {
        $this->get(route('inicio'))->assertRedirect('/login');
    }

    /** CA-1 — o destino pós-login renderiza a home-hub, e não o scaffolding genérico. */
    public function test_destino_pos_login_renderiza_home_hub(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('inicio'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Home/Hub'));
    }

    /** CA-2 — exibe o saldo real da carteira, formato brasileiro. */
    public function test_exibe_saldo_da_carteira_em_formato_br(): void
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1247]);

        $this->actingAs($user)->get(route('inicio'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home/Hub')
                ->where('saldo.reais', '12,47')
                ->where('saldo.centavos', 1247));
    }

    /** CA-2 (estado zero) — Coletador sem carteira/crédito vê saldo R$ 0,00. */
    public function test_coletador_sem_credito_ve_saldo_zero(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get(route('inicio'))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Home/Hub')
                ->where('saldo.reais', '0,00')
                ->where('saldo.centavos', 0));
    }
}
