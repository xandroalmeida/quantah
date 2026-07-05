<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E: usuário JÁ autenticado que clica "Entrar" na landing vai para o app (`/inicio`),
 * não volta para a landing (bounce do fallback `/` do guard `guest`). Regressão do
 * rename dashboard→inicio (IDR-011).
 */
class EntrarAutenticadoTest extends DuskTestCase
{
    private const EMAIL = 'dusk-entrar-auth@quantah.test';

    protected function setUp(): void
    {
        parent::setUp();
        User::where('email', self::EMAIL)->delete();
    }

    protected function tearDown(): void
    {
        User::where('email', self::EMAIL)->delete();
        parent::tearDown();
    }

    public function test_entrar_logado_abre_o_app(): void
    {
        $user = User::factory()->create(['email' => self::EMAIL, 'name' => 'Ana Coletadora']);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(1440, 900)
                ->visit('/')
                ->waitFor('[data-testid=landing-b2c-nav-entrar]', 10)
                ->click('[data-testid=landing-b2c-nav-entrar]')
                ->waitForLocation('/inicio', 10)
                ->assertPathIs('/inicio')
                ->logout(); // isola a sessão para os próximos testes (convenção do repo)
        });
    }

    public function test_visitante_vai_para_o_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('[data-testid=landing-b2c-nav-entrar]', 10)
                ->click('[data-testid=landing-b2c-nav-entrar]')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }
}
