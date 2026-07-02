<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * Vitrine kitchen-sink `/ds` verificada em browser real (STORY-006): renderiza TODOS
 * os componentes do DS (botões + inputs + superfície/feedback/nav) com estados,
 * navegável por âncoras, com a11y (aria-live no snackbar, foco, contraste, ≥48px).
 */
class KitchenSinkTest extends DuskTestCase
{
    /** CA-2/CA-5 — a vitrine renderiza todos os grupos de componentes do DS. */
    public function test_showcase_renders_all_component_groups(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds')->waitFor('[data-testid=showcase-title]', 10);

            foreach ([
                'nav-bar', 'showcase-button', 'showcase-text-input',
                'card-content', 'card-feature-dark',
                'badge-positive', 'badge-negative',
                'snackbar-success', 'empty-state', 'skeleton',
                'nav-bottom', 'footer',
            ] as $testid) {
                $browser->assertVisible("[data-testid=$testid]");
            }
        });
    }

    /** CA-2 — a navegação interna por âncora leva à seção (hash muda). */
    public function test_in_page_nav_anchor_navigates(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds')->waitFor('[data-testid=nav-link-cards]', 10)
                ->click('[data-testid=nav-link-cards]')
                ->pause(300);

            $hash = $browser->script('return window.location.hash;')[0];
            $this->assertSame('#cards', $hash, 'O nav-link deveria navegar para a âncora #cards.');
        });
    }

    /** CA-3 — snackbar anuncia mudança dinâmica via aria-live e traz ícone + texto. */
    public function test_snackbar_announces_via_aria_live(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds')->waitFor('[data-testid=snackbar-success]', 10);

            $state = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=snackbar-success]');
                return [
                    el.getAttribute('aria-live') || '',
                    (el.textContent || '').trim().length > 0 ? 'has-text' : 'no-text',
                    el.querySelector('svg') ? 'has-icon' : 'no-icon'
                ];
            JS)[0];

            $this->assertSame('polite', $state[0], 'Snackbar deveria ter aria-live="polite".');
            $this->assertSame('has-text', $state[1], 'Snackbar deveria ter texto (feedback nunca só cor).');
            $this->assertSame('has-icon', $state[2], 'Snackbar deveria ter ícone (feedback nunca só cor).');
        });
    }

    /** CA-1/CA-3 — empty-state oferece um CTA button.primary focável. */
    public function test_empty_state_offers_focusable_primary_cta(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds')->waitFor('[data-testid=empty-state-cta]', 10);

            $ok = $browser->script(<<<'JS'
                var btn = document.querySelector('[data-testid=empty-state-cta]');
                btn.focus();
                return btn.tagName === 'BUTTON' && document.activeElement === btn;
            JS)[0];

            $this->assertTrue($ok, 'O empty-state deveria ter um <button> (CTA) focável.');
        });
    }

    /** CA-1 (borda) — skeleton é decorativo: escondido da árvore de acessibilidade. */
    public function test_skeleton_is_hidden_from_a11y_tree(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds')->waitFor('[data-testid=skeleton]', 10);

            $hidden = $browser->script(
                "return document.querySelector('[data-testid=skeleton]').getAttribute('aria-hidden');"
            )[0];

            $this->assertSame('true', $hidden, 'Skeleton deveria ter aria-hidden="true".');
        });
    }

    /** CA-3 — itens da nav inferior têm alvo de toque ≥48px. */
    public function test_bottom_nav_targets_are_at_least_48px(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds')->waitFor('[data-testid=nav-bottom-item]', 10);

            $heights = $browser->script(<<<'JS'
                return Array.prototype.map.call(
                    document.querySelectorAll('[data-testid=nav-bottom-item]'),
                    function (el) { return el.offsetHeight; }
                );
            JS)[0];

            $this->assertNotEmpty($heights, 'A nav inferior deveria ter itens.');
            foreach ($heights as $h) {
                $this->assertGreaterThanOrEqual(48, $h, "Item da nav inferior deveria ter ≥48px. Medido: {$h}px");
            }
        });
    }

    /** CA-3 — card escuro (marca) e badges passam contraste AA (texto vs fundo). */
    public function test_dark_card_and_badges_pass_aa_contrast(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds')->waitFor('[data-testid=card-feature-dark]', 10);

            $ratios = $browser->script(<<<'JS'
                function parse(c){ var m = c.match(/\d+(\.\d+)?/g); return m ? m.slice(0,3).map(Number) : [255,255,255]; }
                function lum(c){ var a = c.map(function(v){ v/=255; return v<=0.03928 ? v/12.92 : Math.pow((v+0.055)/1.055,2.4); }); return 0.2126*a[0]+0.7152*a[1]+0.0722*a[2]; }
                function ratio(fg,bg){ var L1=lum(parse(fg)),L2=lum(parse(bg)); var hi=Math.max(L1,L2),lo=Math.min(L1,L2); return (hi+0.05)/(lo+0.05); }
                function r(sel){ var el=document.querySelector(sel); var cs=getComputedStyle(el); return ratio(cs.color, cs.backgroundColor); }
                return [r('[data-testid=card-feature-dark]'), r('[data-testid=badge-positive]'), r('[data-testid=badge-negative]')];
            JS)[0];

            [$dark, $pos, $neg] = $ratios;
            $this->assertGreaterThanOrEqual(4.5, $dark, "Card escuro (primary sobre ink) deveria passar AA. Medido: {$dark}");
            $this->assertGreaterThanOrEqual(4.5, $pos, "badge.positive deveria passar AA. Medido: {$pos}");
            $this->assertGreaterThanOrEqual(4.5, $neg, "badge.negative deveria passar AA. Medido: {$neg}");
        });
    }

    /** CA-3 (mobile) — a página não rola na horizontal (regra de ouro do DS). */
    public function test_no_horizontal_page_overflow_on_mobile(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(390, 1200)->visit('/ds')->waitFor('[data-testid=showcase-title]', 10);

            $overflow = $browser->script(
                'return document.documentElement.scrollWidth - document.documentElement.clientWidth;'
            )[0];

            $this->assertLessThanOrEqual(1, $overflow,
                "A página não deveria rolar na horizontal em mobile. Overflow: {$overflow}px");
        });
    }

    /** CA-3 — foco por teclado é visível no nav-link (ring/outline). */
    public function test_focus_visible_on_nav_link(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/ds')->waitFor('[data-testid=nav-link-cards]', 10);

            $focus = $browser->script(<<<'JS'
                var el = document.querySelector('[data-testid=nav-link-cards]');
                el.focus();
                var cs = getComputedStyle(el);
                return [document.activeElement === el ? 'active' : 'no', cs.boxShadow, cs.outlineStyle, cs.outlineWidth];
            JS)[0];

            $this->assertSame('active', $focus[0], 'O nav-link deveria ser focável por teclado.');
            $hasRing = ($focus[1] ?? 'none') !== 'none';
            $hasOutline = ($focus[2] ?? 'none') !== 'none' && ($focus[3] ?? '0px') !== '0px';
            $this->assertTrue($hasRing || $hasOutline, 'O foco do nav-link deveria ter indicador visível.');
        });
    }
}
