<?php

namespace Tests\Feature\DesignSystem;

use Tests\TestCase;

/**
 * Contrato de tokens do componente Button do DS (CA-1/CA-2).
 *
 * Espelha o padrão de `NoRawColorInHelloTest`: varre o fonte do componente e
 * garante que (a) as 5 variantes existem, (b) cada uma mapeia para os tokens
 * canônicos do DS, e (c) nenhum valor cru de cor aparece no componente.
 * O comportamento em runtime (cor computada, contraste, foco, clique) é coberto
 * pelo Dusk em browser real (`Tests\Browser\ButtonTest`).
 */
class ButtonTokenContractTest extends TestCase
{
    private function buttonSource(): string
    {
        $path = base_path('resources/js/Components/Button.jsx');
        $this->assertFileExists($path, 'Components/Button.jsx deveria existir.');

        return file_get_contents($path);
    }

    /** (a) feliz — o componente existe e expõe as 5 variantes do DS. */
    public function test_button_defines_five_variants(): void
    {
        $src = $this->buttonSource();

        foreach (['primary', 'secondary', 'tertiary', 'danger', 'icon'] as $variant) {
            $this->assertStringContainsString(
                "$variant:",
                $src,
                "Button deveria definir a variante '$variant' no mapa de variantes."
            );
        }
    }

    /** (a) feliz — a variante primary usa o verde de marca + texto on-primary (único CTA). */
    public function test_primary_variant_uses_brand_tokens(): void
    {
        $src = $this->buttonSource();

        $this->assertStringContainsString('bg-primary', $src,
            'button.primary deveria usar o token de fundo bg-primary (#9fe870).');
        $this->assertStringContainsString('text-on-primary', $src,
            'button.primary deveria usar o token de texto text-on-primary (#0e0f0c).');
    }

    /** (b) inválido — cada variante restante mapeia para os tokens do DS de `components.md`. */
    public function test_each_variant_maps_to_ds_tokens(): void
    {
        $src = $this->buttonSource();

        // secondary: sage — fundo canvas-soft, texto ink.
        $this->assertStringContainsString('bg-canvas-soft', $src, 'button.secondary usa bg-canvas-soft.');
        // tertiary: outline — borda ink (elev.1 hairline), texto ink, fundo canvas.
        $this->assertStringContainsString('border-ink', $src, 'button.tertiary usa border-ink (elev.1).');
        // danger: destrutivo — fundo negative, texto sobre superfície branca via token canvas.
        $this->assertStringContainsString('bg-negative', $src, 'button.danger usa bg-negative.');
        // icon: circular — raio full.
        $this->assertStringContainsString('rounded-full', $src, 'button.icon usa rounded-full (círculo).');
    }

    /** (b) inválido — assinatura da marca: raio xl (24px), label button-md, alvo de toque ≥48px. */
    public function test_button_uses_signature_radius_label_and_touch_target(): void
    {
        $src = $this->buttonSource();

        $this->assertStringContainsString('rounded-xl', $src,
            'Botões (não-icon) devem usar rounded-xl (24px) — assinatura da marca.');
        $this->assertStringContainsString('text-button-md', $src,
            'Label do botão deve usar a escala tipográfica button-md.');
        $this->assertStringContainsString('min-h-3xl', $src,
            'Alvo de toque ≥48px deve usar o token min-h-3xl (48px).');
    }

    /** (c) exceção/estados — cobre hover/focus/pressed/disabled/loading. */
    public function test_button_covers_all_interaction_states(): void
    {
        $src = $this->buttonSource();

        $this->assertStringContainsString('hover:', $src, 'Estado hover ausente.');
        $this->assertStringContainsString('focus-visible:', $src, 'Estado focus (foco visível) ausente.');
        $this->assertStringContainsString('active:', $src, 'Estado pressed (active:) ausente.');
        $this->assertStringContainsString('disabled:', $src, 'Estado disabled ausente.');
        $this->assertStringContainsString('aria-busy', $src, 'Estado loading deve expor aria-busy.');
    }

    /** (b) inválido — nenhum hex cru de cor no componente. */
    public function test_button_has_no_raw_hex_color(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?\b/',
            $this->buttonSource(),
            'Button.jsx não pode conter hex cru de cor — use um token do DS.'
        );
    }

    /** (b) inválido — nenhuma cor arbitrária Tailwind (bg-[#...]) nem paleta neutra/black/white crua. */
    public function test_button_has_no_arbitrary_or_neutral_color(): void
    {
        $src = $this->buttonSource();

        $this->assertStringNotContainsString('[#', $src,
            'Button.jsx não pode usar cor arbitrária Tailwind (bg-[#...]).');

        $forbidden = [
            '/\b(?:bg|text|border|ring|from|to|via|fill|stroke|divide|placeholder)-(?:gray|slate|zinc|neutral|stone|red|green|blue|yellow|amber|lime)-\d{2,3}\b/',
            '/\b(?:bg|text|border|ring|fill|stroke)-black\b/',
            '/\b(?:bg|text|border|ring|fill|stroke)-white\b/',
        ];

        foreach ($forbidden as $pattern) {
            $this->assertDoesNotMatchRegularExpression(
                $pattern,
                $src,
                "Button.jsx usa cor crua do Tailwind ($pattern) — troque pelo token do DS."
            );
        }
    }
}
