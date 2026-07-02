<?php

namespace Tests\Feature\DesignSystem;

use Tests\TestCase;

/**
 * Contrato de tokens dos componentes de input do DS (STORY-005 · CA-1/CA-2).
 *
 * Espelha `ButtonTokenContractTest` (IDR-002): varre o fonte dos componentes e
 * garante que (a) os 7 componentes existem, (b) o chrome de input mapeia para os
 * tokens canônicos do DS (`input.text`: raio md, borda ink, fundo canvas, body-md),
 * (c) o estado de erro usa tokens semânticos negativos com wiring de a11y
 * (`aria-invalid`/`aria-describedby`/`role=alert`), (d) selecionado usa `primary`
 * como indicador, e (e) nenhum valor cru de cor aparece. O comportamento em runtime
 * (valor unmasked/ISO, foco, contraste, alvo ≥48px) é coberto em browser real por
 * `Tests\Browser\InputTest`.
 */
class InputTokenContractTest extends TestCase
{
    /** @var string[] os 7 componentes de input desta estória. */
    private array $components = [
        'TextField', 'MaskedField', 'DateTimeField', 'SelectField',
        'Checkbox', 'Radio', 'Switch',
    ];

    /** @var string[] wrappers compartilhados onde vivem as classes de token. */
    private array $shared = ['Field', 'ChoiceField'];

    private function sourceOf(string $name): string
    {
        // DS inputs vivem em Components/inputs/ — namespaced para não colidir com o
        // scaffolding do Breeze (Components/Checkbox.jsx, TextInput.jsx…), que é débito
        // pré-DS fora do escopo desta estória.
        $path = base_path("resources/js/Components/inputs/$name.jsx");
        $this->assertFileExists($path, "Components/inputs/$name.jsx deveria existir.");

        return file_get_contents($path);
    }

    /** Todos os .jsx que compõem os inputs (concretos + wrappers). */
    private function allSources(): string
    {
        $all = array_merge($this->components, $this->shared);

        return implode("\n", array_map(fn ($n) => $this->sourceOf($n), $all));
    }

    /** (a) feliz — os 7 componentes de entrada existem como componentes reutilizáveis. */
    public function test_defines_seven_input_components(): void
    {
        foreach ($this->components as $name) {
            $src = $this->sourceOf($name);
            $this->assertStringContainsString('export default function', $src,
                "Components/$name.jsx deveria exportar um componente default.");
        }
    }

    /** (a) feliz — o chrome de input compõe os tokens do DS de `input.text`. */
    public function test_input_chrome_uses_ds_tokens(): void
    {
        $src = $this->sourceOf('Field');

        // input.text: raio md (12px), borda 1px ink (elev.1), fundo canvas, body-md.
        $this->assertStringContainsString('rounded-md', $src, 'Input deveria usar raio md (12px).');
        $this->assertStringContainsString('border-ink', $src, 'Input deveria ter borda ink (elev.1 hairline).');
        $this->assertStringContainsString('bg-canvas', $src, 'Input deveria ter fundo canvas.');
        $this->assertStringContainsString('text-body-md', $src, 'Texto do input deveria ser body-md.');
        // padding md lg + alvo de toque ≥48px.
        $this->assertStringContainsString('px-lg', $src, 'Input deveria usar padding lg horizontal.');
        $this->assertStringContainsString('py-md', $src, 'Input deveria usar padding md vertical.');
        $this->assertStringContainsString('min-h-3xl', $src, 'Input deveria garantir alvo de toque ≥48px (min-h-3xl).');
    }

    /** (a) feliz — foco visível por teclado (ring por token). */
    public function test_focus_is_visible_via_token_ring(): void
    {
        $src = $this->sourceOf('Field');

        $this->assertMatchesRegularExpression('/focus:ring-2|focus:ring-ink/', $src,
            'Input deveria ter indicador de foco visível (ring por token).');
    }

    /** (b) inválido/erro — estado de erro usa tokens semânticos negativos. */
    public function test_error_state_uses_negative_tokens(): void
    {
        $src = $this->sourceOf('Field');

        $this->assertStringContainsString('border-negative', $src,
            'Borda de erro deveria usar o token negative.');
        $this->assertStringContainsString('text-negative-darkest', $src,
            'Texto da mensagem de erro deveria usar negative-darkest (texto destrutivo, contraste AA).');
    }

    /** (c) exceção/erro — mensagem de erro e hint têm wiring de a11y em ambos os wrappers. */
    public function test_error_wires_aria_invalid_describedby_and_alert(): void
    {
        foreach ($this->shared as $wrapper) {
            $src = $this->sourceOf($wrapper);

            $this->assertStringContainsString('aria-invalid', $src,
                "$wrapper deveria expor aria-invalid no estado de erro.");
            $this->assertStringContainsString('aria-describedby', $src,
                "$wrapper deveria ligar hint/erro via aria-describedby.");
            $this->assertStringContainsString('role="alert"', $src,
                "$wrapper deveria anunciar a mensagem de erro (role=alert).");
        }
    }

    /** (a) feliz — selecionado (checkbox/radio/switch) usa `primary` como indicador. */
    public function test_selected_indicator_uses_primary(): void
    {
        foreach (['Checkbox', 'Radio', 'Switch'] as $name) {
            $this->assertStringContainsString('primary', $this->sourceOf($name),
                "$name deveria usar o token primary como indicador de selecionado.");
        }
    }

    /** (b) inválido — nenhum hex cru de cor em nenhum arquivo de input. */
    public function test_inputs_have_no_raw_hex_color(): void
    {
        $this->assertDoesNotMatchRegularExpression(
            '/#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?\b/',
            $this->allSources(),
            'Componentes de input não podem conter hex cru de cor — use um token do DS.'
        );
    }

    /** (b) inválido — nenhuma cor arbitrária (bg-[#...]) nem paleta neutra/crua do Tailwind. */
    public function test_inputs_have_no_arbitrary_or_neutral_color(): void
    {
        $src = $this->allSources();

        $this->assertStringNotContainsString('[#', $src,
            'Componentes de input não podem usar cor arbitrária Tailwind (bg-[#...]).');

        $forbidden = [
            '/\b(?:bg|text|border|ring|from|to|via|fill|stroke|divide|placeholder|accent)-(?:gray|slate|zinc|neutral|stone|red|green|blue|yellow|amber|lime)-\d{2,3}\b/',
            '/\b(?:bg|text|border|ring|fill|stroke|accent)-black\b/',
            '/\b(?:bg|text|border|ring|fill|stroke|accent)-white\b/',
        ];

        foreach ($forbidden as $pattern) {
            $this->assertDoesNotMatchRegularExpression(
                $pattern,
                $src,
                "Componentes de input usam cor crua do Tailwind ($pattern) — troque pelo token do DS."
            );
        }
    }
}
