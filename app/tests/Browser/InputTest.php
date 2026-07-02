<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Inputs do DS verificados em browser real (STORY-005): CSS de verdade, foco por
 * teclado, alvo ≥48px, contraste computado, wiring de a11y (label/hint/erro) e a
 * lógica de cliente (máscara guarda valor unmasked; datetime guarda ISO 8601). O
 * jsdom não pega nada disto — por isso o gate é Dusk (IDR-002).
 *
 * Host: a vitrine `/ds/inputs` (CA-5) — os 7 componentes com seus estados, mais
 * displays do valor canônico p/ provar unmasked/ISO.
 */
class InputTest extends DuskTestCase
{
    /** Controles com chrome de "campo" (fundo/borda/raio) e alvo ≥48px. */
    private array $boxControls = ['text-input', 'masked-input', 'datetime-input', 'select-input'];

    /** CA-5 — a vitrine lista os 7 componentes e os estados de erro/disabled. */
    public function test_showcase_lists_all_input_components_and_states(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=text-input]', 10);

            foreach (['text-input', 'masked-input', 'datetime-input', 'select-input',
                'checkbox-input', 'radio-a', 'switch-input'] as $testid) {
                $browser->assertVisible("[data-testid=$testid]");
            }
            $browser->assertVisible('[data-testid=text-error-input]')
                ->assertVisible('[data-testid=text-disabled-input]');
        });
    }

    /** CA-2 (feliz) — o label está associado ao controle (clicar no label foca o input). */
    public function test_label_is_associated_with_control(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=text-input]', 10);

            $labelled = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=text-input]');
                return el.labels && el.labels.length > 0;
            JS)[0];

            $this->assertTrue($labelled, 'O TextField deveria ter um <label> associado ao controle.');
        });
    }

    /** CA-2 (feliz) — o hint é ligado ao controle por aria-describedby e está visível. */
    public function test_hint_is_linked_by_aria_describedby(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=text-input]', 10);

            $ok = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=text-input]');
                var ids = (el.getAttribute('aria-describedby') || '').split(/\s+/);
                var hint = document.querySelector('[data-testid=text-hint]');
                return !!hint && hint.offsetParent !== null && ids.indexOf(hint.id) !== -1 && hint.id.length > 0;
            JS)[0];

            $this->assertTrue($ok, 'O hint deveria estar visível e referenciado por aria-describedby.');
        });
    }

    /** CA-2/CA-3 (exceção/erro) — erro expõe aria-invalid + mensagem role=alert ligada por aria. */
    public function test_error_shows_message_wired_by_aria(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=text-error-input]', 10);

            $state = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=text-error-input]');
                var ids = (el.getAttribute('aria-describedby') || '').split(/\s+/);
                var msg = document.querySelector('[data-testid=text-error-msg]');
                return {
                    invalid: el.getAttribute('aria-invalid'),
                    linked: !!msg && ids.indexOf(msg.id) !== -1,
                    role: msg ? msg.getAttribute('role') : null,
                    text: msg ? msg.textContent.trim() : ''
                };
            JS)[0];

            $this->assertSame('true', $state['invalid'], 'Campo em erro deveria expor aria-invalid="true".');
            $this->assertTrue($state['linked'], 'A mensagem de erro deveria ser ligada por aria-describedby.');
            $this->assertSame('alert', $state['role'], 'A mensagem de erro deveria ter role="alert".');
            $this->assertNotEmpty($state['text'], 'A mensagem de erro deveria ter texto (não só cor).');
        });
    }

    /** CA-3 (borda) — foco por teclado tem indicador visível (ring/outline) em cada controle. */
    public function test_all_controls_have_visible_keyboard_focus(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=text-input]', 10);

            foreach (['text-input', 'masked-input', 'select-input', 'switch-input'] as $testid) {
                $focus = $browser->script(<<<JS
                    var el = document.querySelector('[data-testid=$testid]');
                    el.focus();
                    var cs = getComputedStyle(el);
                    return {
                        active: document.activeElement === el,
                        boxShadow: cs.boxShadow,
                        outlineStyle: cs.outlineStyle,
                        outlineWidth: cs.outlineWidth
                    };
                JS)[0];

                $this->assertTrue($focus['active'], "O controle $testid deveria ser focável por teclado.");
                $hasRing = ($focus['boxShadow'] ?? 'none') !== 'none';
                $hasOutline = ($focus['outlineStyle'] ?? 'none') !== 'none'
                    && ($focus['outlineWidth'] ?? '0px') !== '0px';
                $this->assertTrue($hasRing || $hasOutline,
                    "O foco de $testid deveria ter indicador visível (ring/box-shadow ou outline).");
            }
        });
    }

    /** CA-3 (borda) — alvo de toque ≥48px nos controles de caixa e nos campos de escolha. */
    public function test_touch_targets_are_at_least_48px(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=text-input]', 10);

            foreach (array_merge($this->boxControls,
                ['checkbox-field', 'radio-field-a', 'switch-input']) as $testid) {
                $height = $browser->script(
                    "return document.querySelector('[data-testid=$testid]').offsetHeight;"
                )[0];

                $this->assertGreaterThanOrEqual(48, $height,
                    "O alvo de toque de $testid deveria ser ≥48px. Medido: {$height}px");
            }
        });
    }

    /** CA-3 (inválido) — borda passa contraste UI (≥3:1) e o texto de erro passa AA (≥4.5:1). */
    public function test_border_and_error_text_pass_contrast(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=text-input]', 10);

            $ratios = $browser->script(<<<'JS'
                function parse(c){ return c.match(/\d+(\.\d+)?/g).slice(0,3).map(Number); }
                function lum(c){
                    var a = c.map(function(v){ v/=255; return v<=0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055,2.4); });
                    return 0.2126*a[0] + 0.7152*a[1] + 0.0722*a[2];
                }
                function ratio(fg, bg){
                    var L1 = lum(parse(fg)), L2 = lum(parse(bg));
                    var hi = Math.max(L1,L2), lo = Math.min(L1,L2);
                    return (hi + 0.05) / (lo + 0.05);
                }
                var input = document.querySelector('[data-testid=text-input]');
                var ics = getComputedStyle(input);
                var msg = document.querySelector('[data-testid=text-error-msg]');
                var mcs = getComputedStyle(msg);
                var page = getComputedStyle(document.body).backgroundColor;
                return {
                    border: ratio(ics.borderTopColor, ics.backgroundColor),
                    error: ratio(mcs.color, mcs.backgroundColor === 'rgba(0, 0, 0, 0)' ? page : mcs.backgroundColor)
                };
            JS)[0];

            $this->assertGreaterThanOrEqual(3.0, $ratios['border'],
                "A borda do input deveria passar contraste de UI (≥3:1). Medido: {$ratios['border']}");
            $this->assertGreaterThanOrEqual(4.5, $ratios['error'],
                "O texto de erro deveria passar AA (≥4.5:1). Medido: {$ratios['error']}");
        });
    }

    /** CA-3 (inválido) — controle disabled não é editável. */
    public function test_disabled_control_is_not_editable(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=text-disabled-input]', 10);

            $disabled = $browser->script(
                "return document.querySelector('[data-testid=text-disabled-input]').disabled === true;"
            )[0];

            $this->assertTrue($disabled, 'O TextField disabled deveria ter o atributo disabled.');
        });
    }

    /** CA-4 (feliz) — masked formata na tela e guarda o valor unmasked (só dígitos). */
    public function test_masked_field_formats_and_stores_unmasked_value(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=masked-input]', 10);

            $browser->click('[data-testid=masked-input]')
                ->keys('[data-testid=masked-input]', '1234', '5678');

            $result = $browser->script(<<<'JS'
                return {
                    display: document.querySelector('[data-testid=masked-input]').value,
                    canonical: document.querySelector('[data-testid=masked-value]').textContent.trim()
                };
            JS)[0];

            $this->assertStringContainsString(' ', $result['display'],
                'O valor exibido deveria estar formatado (grupos separados por espaço).');
            $this->assertSame('12345678', $result['canonical'],
                'O valor canônico deveria ser só os dígitos (unmasked).');
        });
    }

    /** CA-4 (inválido) — máscara numérica ignora não-dígitos (letras não entram no canônico). */
    public function test_masked_field_ignores_non_digits(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=masked-input]', 10);

            $browser->click('[data-testid=masked-input]')
                ->keys('[data-testid=masked-input]', '12ab34');

            $canonical = $browser->script(
                "return document.querySelector('[data-testid=masked-value]').textContent.trim();"
            )[0];

            $this->assertSame('1234', $canonical,
                'A máscara numérica deveria ignorar letras — canônico só com dígitos.');
        });
    }

    /** CA-4 (borda) — masked vazio mantém o valor canônico vazio. */
    public function test_masked_field_empty_stays_empty(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=masked-value]', 10);

            $canonical = $browser->script(
                "return document.querySelector('[data-testid=masked-value]').textContent.trim();"
            )[0];

            $this->assertSame('', $canonical, 'Sem digitação, o valor canônico deveria ser vazio.');
        });
    }

    /** CA-4 (feliz) — datetime guarda o valor canônico ISO 8601 (AAAA-MM-DD). */
    public function test_datetime_field_stores_iso_value(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds/inputs')->waitFor('[data-testid=datetime-input]', 10);

            $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=datetime-input]');
                var setter = Object.getOwnPropertyDescriptor(window.HTMLInputElement.prototype, 'value').set;
                setter.call(el, '2026-07-02');
                el.dispatchEvent(new Event('input', { bubbles: true }));
                el.dispatchEvent(new Event('change', { bubbles: true }));
            JS);

            $canonical = $browser->script(
                "return document.querySelector('[data-testid=datetime-value]').textContent.trim();"
            )[0];

            $this->assertSame('2026-07-02', $canonical,
                'O valor canônico do datetime deveria ser ISO 8601 (AAAA-MM-DD).');
        });
    }
}
