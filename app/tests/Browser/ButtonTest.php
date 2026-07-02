<?php

namespace Tests\Browser;

use Facebook\WebDriver\WebDriverKeys;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Button do DS verificado em browser real: CSS de verdade, cor computada, contraste,
 * foco por teclado e bloqueio de clique (disabled/loading). O jsdom não pega nada disto.
 *
 * Host de teste: a vitrine `/ds/buttons` (CA-5) — todas as variantes e estados, com um
 * contador de cliques para provar onClick / disabled / loading.
 */
class ButtonTest extends DuskTestCase
{
    /** @var string[] variantes que renderizam com fundo sólido e label. */
    private array $variants = ['btn-primary', 'btn-secondary', 'btn-tertiary', 'btn-danger', 'btn-icon'];

    /** CA-5 — a vitrine lista todas as variantes e os estados disabled/loading. */
    public function test_showcase_lists_all_variants_and_states(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/buttons')->waitFor('[data-testid=btn-primary]', 10);

            foreach ($this->variants as $testid) {
                $browser->assertVisible("[data-testid=$testid]");
            }
            $browser->assertVisible('[data-testid=btn-disabled]')
                ->assertVisible('[data-testid=btn-loading]');
        });
    }

    /** CA-1 — button.primary com tokens de marca: verde #9fe870, texto on-primary, raio 24px. */
    public function test_primary_renders_brand_tokens(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/buttons')->waitFor('[data-testid=btn-primary]', 10);

            $style = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=btn-primary]');
                var cs = getComputedStyle(el);
                return { bg: cs.backgroundColor, color: cs.color, radius: cs.borderTopLeftRadius };
            JS)[0];

            $this->assertSame('rgb(159, 232, 112)', $style['bg'], 'Fundo do primary deveria ser #9fe870.');
            $this->assertSame('rgb(14, 15, 12)', $style['color'], 'Texto do primary deveria ser on-primary.');
            $this->assertSame('24px', $style['radius'], 'Raio do primary deveria ser xl (24px).');
        });
    }

    /** CA-3 — cada variante passa contraste WCAG AA (>= 4.5:1), medido no rgb real. */
    public function test_all_variants_pass_aa_contrast(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/buttons')->waitFor('[data-testid=btn-primary]', 10);

            foreach ($this->variants as $testid) {
                $ratio = $browser->script(<<<JS
                    function parse(rgb){ return rgb.match(/\\d+/g).slice(0,3).map(Number); }
                    function lum(c){
                        var a = c.map(function(v){ v/=255; return v<=0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055,2.4); });
                        return 0.2126*a[0] + 0.7152*a[1] + 0.0722*a[2];
                    }
                    var el = document.querySelector('[data-testid=$testid]');
                    var cs = getComputedStyle(el);
                    var L1 = lum(parse(cs.color)), L2 = lum(parse(cs.backgroundColor));
                    var hi = Math.max(L1,L2), lo = Math.min(L1,L2);
                    return (hi + 0.05) / (lo + 0.05);
                JS)[0];

                $this->assertGreaterThanOrEqual(4.5, $ratio,
                    "Variante $testid deveria passar AA (>= 4.5:1). Medido: $ratio");
            }
        });
    }

    /** CA-3 — alvo de toque >= 48px de altura em cada variante (mobile-first). */
    public function test_touch_target_is_at_least_48px(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/buttons')->waitFor('[data-testid=btn-primary]', 10);

            foreach ($this->variants as $testid) {
                $height = $browser->script(
                    "return document.querySelector('[data-testid=$testid]').offsetHeight;"
                )[0];

                $this->assertGreaterThanOrEqual(48, $height,
                    "Variante $testid deveria ter alvo de toque >= 48px. Medido: {$height}px");
            }
        });
    }

    /** CA-3 — foco por teclado é visível: Tab a partir do topo foca o 1º botão com indicador. */
    public function test_keyboard_focus_is_visible(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/buttons')
                ->waitFor('[data-testid=btn-primary]', 10)
                ->click('[data-testid=showcase-title]'); // foco no documento (não-focável)

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

            $this->assertSame('btn-primary', $focus['testid'],
                'Tab a partir do topo deveria focar o primeiro botão (btn-primary).');

            $hasRing = ($focus['boxShadow'] ?? 'none') !== 'none';
            $hasOutline = ($focus['outlineStyle'] ?? 'none') !== 'none'
                && ($focus['outlineWidth'] ?? '0px') !== '0px';

            $this->assertTrue($hasRing || $hasOutline,
                'O foco por teclado deveria ter indicador visível (ring/box-shadow ou outline).');
        });
    }

    /** CA-4 (feliz) — botão habilitado dispara onClick. */
    public function test_enabled_primary_fires_onclick(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/buttons')
                ->waitFor('[data-testid=btn-primary]', 10)
                ->assertSeeIn('[data-testid=click-count]', '0')
                ->click('[data-testid=btn-primary]')
                ->waitForTextIn('[data-testid=click-count]', '1', 5)
                ->assertSeeIn('[data-testid=click-count]', '1');
        });
    }

    /** CA-4 (inválido) — botão disabled não dispara onClick nem incrementa o contador. */
    public function test_disabled_does_not_fire_onclick(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/buttons')->waitFor('[data-testid=btn-disabled]', 10);

            $isDisabled = $browser->script(
                "return document.querySelector('[data-testid=btn-disabled]').disabled === true;"
            )[0];
            $this->assertTrue($isDisabled, 'btn-disabled deveria ter o atributo disabled.');

            // .click() nativo em <button disabled> não dispara o handler.
            $browser->script("document.querySelector('[data-testid=btn-disabled]').click();");

            $browser->assertSeeIn('[data-testid=click-count]', '0');
        });
    }

    /** CA-4 (exceção) — loading bloqueia o clique, expõe aria-busy e mostra spinner (label oculto). */
    public function test_loading_blocks_click_and_sets_aria_busy(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/buttons')->waitFor('[data-testid=btn-loading]', 10);

            $state = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=btn-loading]');
                var spinner = el.querySelector('[data-testid=spinner]');
                return {
                    ariaBusy: el.getAttribute('aria-busy'),
                    disabled: el.disabled === true,
                    hasSpinner: !!spinner
                };
            JS)[0];

            $this->assertSame('true', $state['ariaBusy'], 'Loading deveria expor aria-busy="true".');
            $this->assertTrue($state['disabled'], 'Loading deveria bloquear o clique (disabled).');
            $this->assertTrue($state['hasSpinner'], 'Loading deveria mostrar um spinner.');

            $browser->script("document.querySelector('[data-testid=btn-loading]').click();");
            $browser->assertSeeIn('[data-testid=click-count]', '0');
        });
    }
}
