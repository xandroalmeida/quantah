<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Tema do DS aplicado no hello-world, verificado em browser real (CSS de verdade,
 * cor computada, foco por teclado). O jsdom não pega nada disto — este é o gate.
 */
class ThemeTest extends DuskTestCase
{
    /** CA-4 — a hello-world exibe um título display e um parágrafo de corpo. */
    public function test_hello_shows_display_title_and_body(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('[data-testid=hello-title]', 10)
                ->assertVisible('[data-testid=hello-title]')
                ->assertVisible('[data-testid=hello-body]')
                ->assertSeeIn('[data-testid=hello-title]', 'Quantah');
        });
    }

    /** CA-2 — o título display usa a fonte Inter no peso 900 (DDR-001). */
    public function test_display_title_uses_inter_900(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->waitFor('[data-testid=hello-title]', 10);

            $style = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=hello-title]');
                var cs = getComputedStyle(el);
                return { fontFamily: cs.fontFamily.toLowerCase(), fontWeight: cs.fontWeight };
            JS)[0];

            $this->assertStringContainsString('inter', $style['fontFamily'],
                'Título display deveria usar a família Inter.');
            $this->assertSame('900', (string) $style['fontWeight'],
                'Título display deveria ter peso 900 (DDR-001).');
        });
    }

    /** CA-4 — button.primary com tokens de marca: verde #9fe870, texto on-primary, raio 24px. */
    public function test_primary_button_renders_with_brand_tokens(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->waitFor('[data-testid=hello-cta]', 10);

            $style = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=hello-cta]');
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

    /** CA-5 — contraste texto/fundo do button.primary passa AA (>= 4.5:1), medido no rgb real. */
    public function test_primary_button_contrast_passes_AA(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')->waitFor('[data-testid=hello-cta]', 10);

            $ratio = $browser->script(<<<'JS'
                function parse(rgb){ return rgb.match(/\d+/g).slice(0,3).map(Number); }
                function lum(c){
                    var a = c.map(function(v){ v/=255; return v<=0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055,2.4); });
                    return 0.2126*a[0] + 0.7152*a[1] + 0.0722*a[2];
                }
                var el = document.querySelector('[data-testid=hello-cta]');
                var cs = getComputedStyle(el);
                var L1 = lum(parse(cs.color)), L2 = lum(parse(cs.backgroundColor));
                var hi = Math.max(L1,L2), lo = Math.min(L1,L2);
                return (hi + 0.05) / (lo + 0.05);
            JS)[0];

            $this->assertGreaterThanOrEqual(4.5, $ratio,
                'Contraste on-primary sobre primary deveria passar WCAG AA (>= 4.5:1). Medido: '.$ratio);
        });
    }

    /** CA-5 — foco por teclado é visível: navegar por Tab foca o CTA com indicador visível. */
    public function test_primary_button_focus_is_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('[data-testid=hello-cta]', 10)
                // clica em texto não-focável para garantir foco no documento (activeElement = body)
                ->click('[data-testid=hello-eyebrow]');

            // Tab de verdade (evento de teclado) → dispara :focus-visible no CTA.
            $browser->driver->getKeyboard()->sendKeys(WebDriverKeys::TAB);

            $focus = $browser->script(<<<'JS'
                var el = document.activeElement;
                var cs = getComputedStyle(el);
                return {
                    testid: el.getAttribute('data-testid'),
                    boxShadow: cs.boxShadow,
                    outlineStyle: cs.outlineStyle,
                    outlineWidth: cs.outlineWidth
                };
            JS)[0];

            $this->assertSame('hello-cta', $focus['testid'],
                'Tab a partir do topo deveria focar o CTA (único elemento interativo).');

            $hasRing = ($focus['boxShadow'] ?? 'none') !== 'none';
            $hasOutline = ($focus['outlineStyle'] ?? 'none') !== 'none'
                && ($focus['outlineWidth'] ?? '0px') !== '0px';

            $this->assertTrue($hasRing || $hasOutline,
                'Foco do CTA deveria ter indicador visível (ring/box-shadow ou outline).');
        });
    }
}
