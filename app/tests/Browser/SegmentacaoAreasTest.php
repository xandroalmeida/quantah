<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da barreira entre áreas (STORY-023 · CA-5 · ADR-010 §3).
 *
 * Cobre os caminhos mapeados da segmentação:
 *  - (feliz) área B2B `/intelligence` reservada é pública e alcançável sem login;
 *  - (exceção/erro) Coletador autenticado é barrado no Backoffice e vê o 403 em pt-BR;
 *  - (alternativo) a navegação do Coletador não anuncia o Backoffice (sem CTA público).
 *
 * Roda contra o banco de dev (auto-limpo), no padrão dos demais testes de browser.
 */
class SegmentacaoAreasTest extends DuskTestCase
{
    private const COLETADOR = 'dusk-segmentacao-col@quantah.test';

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
        User::where('email', self::COLETADOR)->delete();
    }

    /** (i) feliz — a landing B2B (Quantah Intelligence) abre sem login, em pt-BR, sem CTA de entrada. */
    public function test_b2b_intelligence_acessivel_sem_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/intelligence')
                ->waitFor('[data-testid=landing-b2b-cta-enviar]', 10)
                ->assertSee('Do cupom ao insight.')
                ->assertDontSee('Entrar')  // face b2b: sem login B2B nesta onda
                ->assertPathIs('/intelligence');
        });
    }

    /** (iii) exceção/erro — Coletador logado tenta o Backoffice e vê o 403 branded em pt-BR. */
    public function test_coletador_logado_barrado_no_backoffice_ve_403_ptbr(): void
    {
        $col = User::factory()->create(['email' => self::COLETADOR]);

        $this->browse(function (Browser $browser) use ($col) {
            $browser->loginAs($col)
                ->visit('/backoffice/saques')
                ->waitFor('[data-testid=barreira-403]', 10)
                ->assertSee('Acesso restrito')
                ->assertSee('não tem permissão')
                ->assertDontSee('This action is unauthorized')
                ->logout();
        });
    }

    /** (ii) alternativo — a navegação do Coletador não expõe link/CTA para o Backoffice (CA-3). */
    public function test_navegacao_do_coletador_nao_expoe_backoffice(): void
    {
        $col = User::factory()->create(['email' => self::COLETADOR]);

        $this->browse(function (Browser $browser) use ($col) {
            $browser->loginAs($col)
                ->visit('/profile')
                ->waitForText('Perfil', 10)
                ->assertDontSee('Backoffice')
                ->assertSourceMissing('backoffice/saques')
                ->logout();
        });
    }
}
