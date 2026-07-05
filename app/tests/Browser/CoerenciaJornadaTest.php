<?php

namespace Tests\Browser;

use App\Models\Carteira;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da coerência de jornada (STORY-036 · EPIC-007), mobile-first (390px).
 *
 *  - CA-1: a tela de Perfil não exibe a opção de excluir conta.
 *  - CA-3/CA-4: o menu antes "Cupons" agora diz "Escanear" (pt-BR), com destino /coletar e
 *    seção ativa intactos, alvo ≥48px e aria-current preservados.
 */
class CoerenciaJornadaTest extends DuskTestCase
{
    private const EMAIL = 'dusk-coerencia@quantah.test';

    protected function setUp(): void
    {
        parent::setUp();
        $this->limpar();
    }

    protected function tearDown(): void
    {
        $this->limpar();
        parent::tearDown();
    }

    private function limpar(): void
    {
        User::where('email', self::EMAIL)->delete();
    }

    private function usuario(): User
    {
        $user = User::factory()->create(['email' => self::EMAIL, 'name' => 'Ana Coletadora']);
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1247]);

        return $user;
    }

    /** CA-1 — Perfil sem opção de excluir conta (mantém os demais formulários). */
    public function test_perfil_nao_exibe_excluir_conta(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->resize(390, 900)
                ->visit('/profile')
                ->waitFor('[data-testid=screen-perfil]', 10)
                ->assertDontSee('Excluir Conta')
                ->assertDontSee('Excluir conta')
                // Os demais formulários seguem presentes (dados e senha).
                ->assertSee('Informações do Perfil')
                ->assertSee('Atualizar Senha');
        });
    }

    /** CA-3/CA-4 — menu "Escanear" (pt-BR), destino /coletar e seção ativa intactos. */
    public function test_menu_escanear_renomeado_mantem_destino_e_secao(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->resize(390, 900)
                ->visit('/inicio')
                ->waitFor('[data-testid=app-nav-cupons]', 10)
                ->assertSeeIn('[data-testid=app-nav-cupons]', 'Escanear')
                ->assertDontSeeIn('[data-testid=app-nav-cupons]', 'Cupons');

            // Alvo ≥48px preservado.
            $altura = $browser->script(
                "return document.querySelector('[data-testid=app-nav-cupons]').getBoundingClientRect().height;"
            )[0];
            $this->assertGreaterThanOrEqual(48, $altura);

            // Destino intacto: leva a /coletar e marca a seção ativa (aria-current).
            $browser->click('[data-testid=app-nav-cupons]')
                ->waitForLocation('/coletar', 10)
                ->assertPathIs('/coletar')
                ->assertAttribute('[data-testid=app-nav-cupons]', 'aria-current', 'page');
        });
    }
}
