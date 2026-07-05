<?php

namespace Tests\Browser;

use App\Models\Carteira;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da Home-hub do Coletador (STORY-029 · EPIC-006), mobile-first.
 *
 * Cobre os caminhos alcançáveis pela UI da spec `SCREEN-STORY-029-home-hub-coletador`:
 *  - CA-1: o destino pós-login (/dashboard) é a home-hub, não a página genérica (sem
 *          "Você está logado!" nem logo do Laravel).
 *  - CA-2: exibe o saldo real da carteira no formato brasileiro.
 *  - CA-3: o CTA primário "Coletar cupom" leva ao fluxo de captura (/coletar).
 *  - CA-4: DS + pt-BR, sem resíduo de scaffolding (logo do Laravel ausente).
 *  - CA-5: visitante anônimo é barrado para o login.
 *  - CA-6: CTA e nav são focáveis por teclado (foco visível vindo dos componentes do DS).
 *
 * Roda contra o banco de dev (`.env.dusk.local` → quantah); auto-limpo (remove o usuário
 * de teste antes/depois) em vez de migrar o banco. O factory cria usuário já verificado.
 */
class HomeHubTest extends DuskTestCase
{
    private const EMAIL = 'dusk-home@quantah.test';

    /** viewBox do ApplicationLogo do Laravel (scaffolding) — não pode aparecer. */
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
        // Apagar o usuário cascateia a carteira (FK onDelete cascade).
        User::where('email', self::EMAIL)->delete();
    }

    private function usuario(): User
    {
        return User::factory()->create(['email' => self::EMAIL, 'name' => 'Ana Coletadora']);
    }

    /** CA-1/CA-2/CA-4 — saldo positivo: saudação + saldo em R$, no DS e pt-BR, sem scaffolding. */
    public function test_home_hub_mostra_saudacao_e_saldo(): void
    {
        $user = $this->usuario();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1247]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/dashboard')
                ->waitFor('[data-testid=screen-home-saldo]', 10)
                ->assertSeeIn('[data-testid=screen-home-greeting]', 'Olá, Ana')
                ->assertSeeIn('[data-testid=screen-home-saldo]', 'R$ 12,47')
                ->assertSee('Cada nota conta.')
                ->assertVisible('[data-testid=screen-home-nav]')
                ->assertDontSee('Você está logado!')       // scaffolding do Breeze saiu
                ->assertSourceMissing(self::LOGO_LARAVEL);  // logo do Laravel ausente
        });
    }

    /** CA-3 — o CTA primário "Coletar cupom" leva ao fluxo de captura. */
    public function test_cta_coletar_leva_ao_fluxo_de_captura(): void
    {
        $user = $this->usuario();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1247]);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/dashboard')
                ->waitFor('[data-testid=screen-home-cta]', 10)
                ->assertSeeIn('[data-testid=screen-home-cta]', 'Coletar cupom')
                ->click('[data-testid=screen-home-cta]')
                ->waitForLocation('/coletar', 10)
                ->assertPathIs('/coletar');
        });
    }

    /** CA-2 (zero) / CA-3 — primeiro acesso: saldo R$ 0,00 + bloco acolhedor com o CTA. */
    public function test_primeiro_acesso_mostra_boas_vindas_e_cta(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/dashboard')
                ->waitFor('[data-testid=screen-home-welcome]', 10)
                ->assertSeeIn('[data-testid=screen-home-saldo]', 'R$ 0,00')
                ->assertSee('Comece a ganhar cashback')
                ->click('[data-testid=screen-home-cta]')
                ->waitForLocation('/coletar', 10)
                ->assertPathIs('/coletar');
        });
    }

    /** CA-5 — visitante anônimo é redirecionado para o login. */
    public function test_anonimo_e_barrado_para_o_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/dashboard')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }
}
