<?php

namespace Tests\Browser;

use App\Models\Lead;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da captação de lead B2B (STORY-026 · CA-6).
 * Caminhos mapeados: (feliz, mobile) preencher → enviar → tela de agradecimento; (desvio) e-mail
 * inválido bloqueia por campo, em pt-BR, sem sair da landing. Roda contra o banco de dev — limpa
 * o lead criado.
 */
class LandingB2BTest extends DuskTestCase
{
    private const EMAIL = 'dusk-lead-b2b@quantah.test';

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
        Lead::where('email', self::EMAIL)->delete();
    }

    /** (i) feliz em mobile — preencher e enviar leva à tela dedicada de agradecimento em pt-BR. */
    public function test_captura_feliz_mobile_leva_ao_agradecimento(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(390, 844)
                ->visit('/intelligence')
                ->waitFor('[data-testid=landing-b2b-cta-enviar]', 10)
                ->assertSee('Do cupom ao insight.')
                ->type('[data-testid=landing-b2b-field-nome]', 'Ana Souza')
                ->type('[data-testid=landing-b2b-field-email]', self::EMAIL)
                ->type('[data-testid=landing-b2b-field-empresa]', 'Acme')
                ->click('[data-testid=landing-b2b-cta-enviar]')
                ->waitForLocation('/intelligence/obrigado', 10)
                ->waitFor('[data-testid=landing-b2b-sucesso]', 10)
                ->assertSeeIn('[data-testid=landing-b2b-sucesso]', 'Recebemos seu contato.');
        });

        $this->assertDatabaseHas('leads', ['email' => self::EMAIL, 'nome' => 'Ana Souza', 'empresa' => 'Acme']);
    }

    /** (ii) desvio — e-mail inválido bloqueia por campo em pt-BR, sem sair da landing nem persistir. */
    public function test_email_invalido_bloqueia_por_campo(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(390, 844)
                ->visit('/intelligence')
                ->waitFor('[data-testid=landing-b2b-field-email]', 10)
                ->type('[data-testid=landing-b2b-field-nome]', 'Ana Souza')
                ->type('[data-testid=landing-b2b-field-email]', 'nao-e-email')
                ->type('[data-testid=landing-b2b-field-empresa]', 'Acme')
                ->click('[data-testid=landing-b2b-cta-enviar]')
                ->waitFor('[data-testid=landing-b2b-error-email]', 10)
                ->assertSeeIn('[data-testid=landing-b2b-error-email]', 'Use um e-mail válido')
                ->assertPathIs('/intelligence');
        });

        $this->assertDatabaseMissing('leads', ['nome' => 'Ana Souza']);
    }
}
