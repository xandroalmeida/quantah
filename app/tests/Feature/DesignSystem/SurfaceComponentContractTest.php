<?php

namespace Tests\Feature\DesignSystem;

use Tests\TestCase;

/**
 * Contrato de tokens dos componentes de superfície/status/feedback/nav do DS
 * (STORY-006 · CA-1/CA-4). Espelha os contratos das STORY-004/005 (IDR-002/003):
 * varre o fonte e garante existência, mapeamento token-a-token e "zero valor cru".
 * Comportamento/a11y em browser real fica no Dusk (`Tests\Browser\KitchenSinkTest`).
 */
class SurfaceComponentContractTest extends TestCase
{
    /** @var array<string,string> nome → caminho relativo do .jsx */
    private array $components = [
        'Card' => 'Card',
        'Badge' => 'Badge',
        'Snackbar' => 'Snackbar',
        'EmptyState' => 'EmptyState',
        'Skeleton' => 'Skeleton',
        'NavBar' => 'nav/NavBar',
        'NavLink' => 'nav/NavLink',
        'NavBottom' => 'nav/NavBottom',
        'Footer' => 'nav/Footer',
    ];

    private function sourceOf(string $name): string
    {
        $path = base_path("resources/js/Components/{$this->components[$name]}.jsx");
        $this->assertFileExists($path, "Components/{$this->components[$name]}.jsx deveria existir.");

        return file_get_contents($path);
    }

    private function allSources(): string
    {
        return implode("\n", array_map(fn ($n) => $this->sourceOf($n), array_keys($this->components)));
    }

    /** (a) feliz — todos os componentes existem e exportam default. */
    public function test_defines_all_surface_components(): void
    {
        foreach (array_keys($this->components) as $name) {
            $this->assertStringContainsString('export default function', $this->sourceOf($name),
                "$name deveria exportar um componente default.");
        }
    }

    /** (a) feliz — Card: assinatura raio xl (24px) + padding xl e as 4 variantes por token. */
    public function test_card_uses_signature_radius_padding_and_variants(): void
    {
        $src = $this->sourceOf('Card');

        $this->assertStringContainsString('rounded-xl', $src, 'Card deveria usar raio xl (24px).');
        $this->assertStringContainsString('p-xl', $src, 'Card deveria usar padding xl.');
        // content, feature-sage, feature-green, feature-dark (momento de marca ink+primary).
        $this->assertStringContainsString('bg-canvas', $src, 'card.content = fundo canvas.');
        $this->assertStringContainsString('bg-canvas-soft', $src, 'card.feature-sage = fundo canvas-soft.');
        $this->assertStringContainsString('bg-primary-pale', $src, 'card.feature-green = fundo primary-pale.');
        $this->assertStringContainsString('bg-ink', $src, 'card.feature-dark = fundo ink.');
        $this->assertStringContainsString('text-primary', $src, 'card.feature-dark = texto primary (verde sobre ink).');
    }

    /** (a) feliz — Badge: pill + tokens semânticos (positive/negative). */
    public function test_badge_is_pill_with_semantic_tokens(): void
    {
        $src = $this->sourceOf('Badge');

        $this->assertStringContainsString('rounded-pill', $src, 'Badge deveria ser pill.');
        $this->assertStringContainsString('bg-primary-pale', $src, 'badge.positive = fundo primary-pale.');
        $this->assertStringContainsString('text-positive-deep', $src, 'badge.positive = texto positive-deep.');
        $this->assertStringContainsString('bg-negative-bg', $src, 'badge.negative = fundo negative-bg.');
    }

    /** (c) exceção/feedback — Snackbar anuncia via aria-live e usa tokens. */
    public function test_snackbar_announces_and_uses_tokens(): void
    {
        $src = $this->sourceOf('Snackbar');

        $this->assertStringContainsString('aria-live', $src, 'Snackbar deveria anunciar via aria-live.');
        $this->assertStringContainsString('rounded-xl', $src, 'Snackbar deveria usar raio xl.');
        $this->assertStringContainsString('bg-canvas', $src, 'Snackbar deveria ter fundo canvas.');
    }

    /** (a) feliz — EmptyState: título display-xs + instrução + CTA (button.primary). */
    public function test_empty_state_has_title_instruction_and_cta(): void
    {
        $src = $this->sourceOf('EmptyState');

        $this->assertStringContainsString('text-display-xs', $src, 'EmptyState deveria ter título display-xs.');
        $this->assertStringContainsString('Button', $src, 'EmptyState deveria oferecer um CTA (button.primary).');
    }

    /** (d) borda/loading — Skeleton: placeholder acessível (aria-hidden) com pulse. */
    public function test_skeleton_is_accessible_placeholder(): void
    {
        $src = $this->sourceOf('Skeleton');

        $this->assertStringContainsString('animate-pulse', $src, 'Skeleton deveria pulsar (loading).');
        $this->assertStringContainsString('aria-hidden', $src, 'Skeleton é decorativo → aria-hidden.');
    }

    /** (a) feliz — Footer: band escuro (ink) com texto canvas-soft. */
    public function test_footer_uses_dark_band_tokens(): void
    {
        $src = $this->sourceOf('Footer');

        $this->assertStringContainsString('bg-ink', $src, 'Footer = band escuro (fundo ink).');
        $this->assertStringContainsString('text-canvas-soft', $src, 'Footer = texto canvas-soft.');
    }

    /** (a) feliz — NavLink/NavBottom: item ativo indica com primary. */
    public function test_nav_active_indicator_uses_primary(): void
    {
        foreach (['NavLink', 'NavBottom'] as $name) {
            $this->assertStringContainsString('primary', $this->sourceOf($name),
                "$name deveria indicar o item ativo com o token primary.");
        }
    }

    /** (c) exceção — NavBottom garante alvo de toque ≥48px por token. */
    public function test_bottom_nav_uses_touch_target_token(): void
    {
        $this->assertStringContainsString('min-h-3xl', $this->sourceOf('NavBottom'),
            'NavBottom deveria garantir alvo de toque ≥48px (min-h-3xl).');
    }

    /** (b) inválido — nenhum hex cru de cor. */
    public function test_no_raw_hex_color(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?\b/',
            $this->allSources(),
            'Componentes de superfície não podem conter hex cru — use um token do DS.'
        );
    }

    /** (b) inválido — nenhuma cor arbitrária nem paleta neutra/crua do Tailwind. */
    public function test_no_arbitrary_or_neutral_color(): void
    {
        $src = $this->allSources();

        $this->assertStringNotContainsString('[#', $src,
            'Componentes de superfície não podem usar cor arbitrária Tailwind (bg-[#...]).');

        $forbidden = [
            '/\b(?:bg|text|border|ring|from|to|via|fill|stroke|divide|placeholder|accent)-(?:gray|slate|zinc|neutral|stone|red|green|blue|yellow|amber|lime)-\d{2,3}\b/',
            '/\b(?:bg|text|border|ring|fill|stroke|accent)-black\b/',
            '/\b(?:bg|text|border|ring|fill|stroke|accent)-white\b/',
        ];

        foreach ($forbidden as $pattern) {
            $this->assertDoesNotMatchRegularExpression($pattern, $src,
                "Componentes de superfície usam cor crua do Tailwind ($pattern) — troque pelo token.");
        }
    }
}
