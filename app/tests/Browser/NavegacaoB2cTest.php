<?php

namespace Tests\Browser;

use App\Models\Carteira;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da navegação coesa da área B2C (STORY-030 · DDR-007), mobile-first (390px).
 *
 * Cobre os CAs pela UI:
 *  - CA-1: atalho "Histórico" abre o extrato em 1 toque a partir da home.
 *  - CA-2: atalho "Prêmios" abre o saque em 1 toque a partir da home.
 *  - CA-3: de coleta e de saque (antes becos sem saída), "Início" na barra volta à home-hub.
 *  - CA-4: nenhuma rota logada mostra o logo do Laravel (scaffolding).
 *
 * Roda contra o banco de dev; auto-limpo. O factory cria usuário já verificado.
 */
class NavegacaoB2cTest extends DuskTestCase
{
    private const EMAIL = 'dusk-nav@quantah.test';

    /** viewBox do ApplicationLogo do Laravel (scaffolding) — não pode aparecer em rota logada. */
    private const LOGO_LARAVEL = '0 0 316 316';

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

    /** CA-1 — atalho "Histórico" abre o extrato em 1 toque. */
    public function test_atalho_historico_abre_extrato_em_um_toque(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/inicio')
                ->waitFor('[data-testid=screen-home-atalho-historico]', 10)
                ->click('[data-testid=screen-home-atalho-historico]')
                ->waitForLocation('/carteira', 10)
                ->assertPathIs('/carteira');
        });
    }

    /** CA-2 — atalho "Prêmios" abre o saque em 1 toque. */
    public function test_atalho_premios_abre_saque_em_um_toque(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/inicio')
                ->waitFor('[data-testid=screen-home-atalho-premios]', 10)
                ->click('[data-testid=screen-home-atalho-premios]')
                ->waitForLocation('/carteira/saque', 10)
                ->assertPathIs('/carteira/saque');
        });
    }

    /** CA-3 — de coleta e de saque (antes becos), "Início" na barra volta à home-hub. */
    public function test_retorno_a_home_de_coleta_e_de_saque(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->resize(390, 1400);

            // De coleta → Início
            $browser->visit('/coletar')
                ->waitFor('[data-testid=app-nav-inicio]', 10)
                ->click('[data-testid=app-nav-inicio]')
                ->waitForLocation('/inicio', 10)
                ->assertPathIs('/inicio');

            // De saque → Início
            $browser->visit('/carteira/saque')
                ->waitFor('[data-testid=app-nav-inicio]', 10)
                ->click('[data-testid=app-nav-inicio]')
                ->waitForLocation('/inicio', 10)
                ->assertPathIs('/inicio');
        });
    }

    /** CA-4 — nenhuma rota logada mostra o logo do Laravel. */
    public function test_nenhuma_rota_logada_mostra_logo_do_laravel(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->resize(390, 1400);

            foreach (['/inicio', '/coletar', '/carteira', '/carteira/saque', '/profile'] as $rota) {
                $browser->visit($rota)
                    ->waitFor('[data-testid=app-nav]', 10)
                    ->assertSourceMissing(self::LOGO_LARAVEL);
            }
        });
    }
}
