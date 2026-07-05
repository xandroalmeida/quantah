<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da landing B2C (STORY-025 · CA-6, cobrindo também CA-2/CA-3/CA-4/CA-5).
 *
 * Caminhos mapeados:
 *  - (feliz, mobile) visitante anônimo abre '/', vê a proposta "Cada nota conta." em pt-BR, sem
 *    resíduo de scaffolding (Laravel/Hello), e o CTA de entrada leva ao login (EPIC-004);
 *  - (alternativo) o CTA "Para empresas" leva à landing B2B (/intelligence).
 *
 * Landing estática (sem dados) — nada a limpar no banco de dev.
 */
class LandingB2CTest extends DuskTestCase
{
    /** (i) feliz em mobile — proposta em pt-BR + CTA de entrada até o login (CA-2/CA-3/CA-5/CA-6). */
    public function test_visitante_mobile_ve_proposta_ptbr_e_cta_leva_ao_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(390, 844) // mobile-first: viewport de celular
                ->visit('/')
                ->waitFor('[data-testid=landing-b2c-hero]', 10)
                ->assertSee('Cada nota conta.')
                ->assertSeeIn('[data-testid=landing-b2c-como-funciona]', 'Como funciona')
                ->assertDontSee('Laravel') // sem resíduo de scaffolding
                ->assertDontSee('Hello')
                ->click('[data-testid=landing-b2c-cta-entrar]')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login')
                ->assertSee('Entrar'); // login do Coletador em pt-BR (t('Sign in'))
        });
    }

    /** (ii) alternativo — o CTA "Para empresas" leva à landing B2B Quantah Intelligence (CA-4). */
    public function test_cta_para_empresas_leva_ao_b2b(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('[data-testid=landing-b2c-cta-b2b]', 10)
                ->click('[data-testid=landing-b2c-cta-b2b]')
                ->waitForLocation('/intelligence', 10)
                ->waitFor('[data-testid=landing-b2b-cta-enviar]', 10) // aguarda a landing B2B hidratar
                ->assertPathIs('/intelligence')
                ->assertSee('Do cupom ao insight.');
        });
    }
}
