<?php

namespace Tests\Feature\Navegacao;

use App\Models\Carteira;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Casca de navegação da área B2C (STORY-030 · DDR-007). Cobre o contrato de roteamento/guarda:
 * toda rota logada exige `auth` (CA-5) e renderiza a **sua** tela de produto (não uma página
 * genérica/scaffolding — CA-4). A coerência visível da barra e o "≤2 toques" são E2E (Dusk).
 */
class CascaNavegacaoTest extends TestCase
{
    use RefreshDatabase;

    /** CA-5 — toda rota da área B2C autenticada barra o anônimo para o login. */
    public function test_rotas_logadas_exigem_autenticacao(): void
    {
        foreach (['/inicio', '/coletar', '/carteira', '/carteira/saque', '/profile'] as $rota) {
            $this->get($rota)->assertRedirect('/login');
        }
    }

    /** CA-4 — cada rota logada renderiza a sua tela de produto, não a página genérica de scaffolding. */
    public function test_rotas_logadas_renderizam_a_propria_tela(): void
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1247]);

        $esperado = [
            '/inicio' => 'Home/Hub',
            '/coletar' => 'Coleta/Captura',
            '/carteira' => 'Carteira/Index',
            '/carteira/saque' => 'Saque/Solicitar',
            '/profile' => 'Profile/Edit',
        ];

        foreach ($esperado as $rota => $componente) {
            $this->actingAs($user)->get($rota)
                ->assertOk()
                ->assertInertia(fn (Assert $page) => $page->component($componente));
        }
    }
}
