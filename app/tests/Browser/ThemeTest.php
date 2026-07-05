<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Tema do DS aplicado na home (landing B2C), verificado em browser real (CSS de verdade,
 * cor computada, foco por teclado). O jsdom não pega nada disto — este é o gate.
 *
 * Antes verificava a hello-world de scaffolding (EPIC-000); a raiz agora serve a landing B2C
 * (STORY-025), então os mesmos contratos de token do DS são conferidos no título display e no
 * button.primary (CtaLink) reais da porta de entrada.
 */
class ThemeTest extends DuskTestCase
{
    /** A home exibe o título display (hero) e a seção de corpo "Como funciona". */
    public function test_home_shows_display_title_and_body(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('#hero-h1', 10)
                ->assertVisible('#hero-h1')
                ->assertVisible('[data-testid=landing-b2c-como-funciona]')
                ->assertSeeIn('#hero-h1', 'Cada nota conta.');
        });
    }

    /** O título display (hero) usa a fonte Inter no peso 900 (DDR-001). */
    public function test_display_title_uses_inter_900(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->waitFor('#hero-h1', 10);

            $style = $browser->script(<<<'JS'
                var el = document.querySelector('#hero-h1');
                var cs = getComputedStyle(el);
                return { fontFamily: cs.fontFamily.toLowerCase(), fontWeight: cs.fontWeight };
            JS)[0];

            $this->assertStringContainsString('inter', $style['fontFamily'],
                'Título display deveria usar a família Inter.');
            $this->assertSame('900', (string) $style['fontWeight'],
                'Título display deveria ter peso 900 (DDR-001).');
        });
    }

    /** button.primary (CTA de entrada) com tokens de marca: verde #9fe870, texto on-primary, raio 24px. */
    public function test_primary_cta_renders_with_brand_tokens(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->waitFor('[data-testid=landing-b2c-cta-entrar]', 10);

            $style = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=landing-b2c-cta-entrar]');
                var cs = getComputedStyle(el);
                return {
                    bg: cs.backgroundColor,
                    color: cs.color,
                    radius: cs.borderTopLeftRadius
                };
            JS)[0];

            $this->assertSame('rgb(159, 232, 112)', $style['bg'],
                'Fundo do button.primary deveria ser o verde primary (#9fe870).');
            $this->assertSame('rgb(14, 15, 12)', $style['color'],
                'Texto do button.primary deveria ser on-primary (#0e0f0c).');
            $this->assertSame('24px', $style['radius'],
                'Raio do button.primary deveria ser xl (24px) — assinatura da marca.');
        });
    }

    /** Contraste texto/fundo do button.primary passa AA (>= 4.5:1), medido no rgb real. */
    public function test_primary_cta_contrast_passes_aa(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->waitFor('[data-testid=landing-b2c-cta-entrar]', 10);

            $ratio = $browser->script(<<<'JS'
                function parse(rgb){ return rgb.match(/\d+/g).slice(0,3).map(Number); }
                function lum(c){
                    var a = c.map(function(v){ v/=255; return v<=0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055,2.4); });
                    return 0.2126*a[0] + 0.7152*a[1] + 0.0722*a[2];
                }
                var el = document.querySelector('[data-testid=landing-b2c-cta-entrar]');
                var cs = getComputedStyle(el);
                var L1 = lum(parse(cs.color)), L2 = lum(parse(cs.backgroundColor));
                var hi = Math.max(L1,L2), lo = Math.min(L1,L2);
                return (hi + 0.05) / (lo + 0.05);
            JS)[0];

            $this->assertGreaterThanOrEqual(4.5, $ratio,
                'Contraste on-primary sobre primary deveria passar WCAG AA (>= 4.5:1). Medido: '.$ratio);
        });
    }

    /** Foco por teclado é visível: navegar por Tab alcança o CTA de entrada com indicador visível. */
    public function test_primary_cta_focus_is_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('[data-testid=landing-b2c-cta-entrar]', 10)
                // clica na seção do hero (não-focável) para pôr o foco no documento.
                ->click('[data-testid=landing-b2c-hero]');

            // Tab de verdade (evento de teclado) até alcançar o CTA de entrada → dispara :focus-visible.
            $focado = null;
            for ($i = 0; $i < 8; $i++) {
                $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::TAB);
                $testid = $browser->script("return document.activeElement.getAttribute('data-testid');")[0];
                if ($testid === 'landing-b2c-cta-entrar') {
                    $focado = $testid;
                    break;
                }
            }

            $this->assertSame('landing-b2c-cta-entrar', $focado,
                'Navegar por Tab deveria alcançar o CTA de entrada.');

            $style = $browser->script(<<<'JS'
                var el = document.activeElement;
                var cs = getComputedStyle(el);
                return { boxShadow: cs.boxShadow, outlineStyle: cs.outlineStyle, outlineWidth: cs.outlineWidth };
            JS)[0];

            $hasRing = ($style['boxShadow'] ?? 'none') !== 'none';
            $hasOutline = ($style['outlineStyle'] ?? 'none') !== 'none'
                && ($style['outlineWidth'] ?? '0px') !== '0px';

            $this->assertTrue($hasRing || $hasOutline,
                'Foco do CTA deveria ter indicador visível (ring/box-shadow ou outline).');
        });
    }
}
