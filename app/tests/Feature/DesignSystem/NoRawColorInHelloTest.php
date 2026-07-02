<?php

namespace Tests\Feature\DesignSystem;

use Tests\TestCase;

/**
 * CA-1 — nenhum valor cru de cor pode aparecer no JSX da hello-world.
 * Cor entra sempre por token (utilitário Tailwind mapeado do DS), nunca por hex
 * cru nem pela paleta neutra padrão do Tailwind (gray/slate/zinc/…) ou black/white.
 */
class NoRawColorInHelloTest extends TestCase
{
    private function helloSource(): string
    {
        $path = base_path('resources/js/Pages/Hello.jsx');
        $this->assertFileExists($path, 'Hello.jsx deveria existir.');

        return file_get_contents($path);
    }

    /** (b) inválido — hex cru de cor é proibido no JSX. */
    public function test_hello_has_no_raw_hex_color(): void
    {
        $src = $this->helloSource();

        $this->assertDoesNotMatchRegularExpression(
            '/#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?\b/',
            $src,
            'Hello.jsx não pode conter hex cru de cor — use um token do DS.'
        );
    }

    /** (b) inválido — valor arbitrário de cor do Tailwind (bg-[#...]) é proibido. */
    public function test_hello_has_no_tailwind_arbitrary_color(): void
    {
        $src = $this->helloSource();

        $this->assertStringNotContainsString('[#', $src,
            'Hello.jsx não pode usar cor arbitrária Tailwind (bg-[#...]).');
    }

    /** (b) inválido — a paleta neutra crua do Tailwind não pode substituir os tokens. */
    public function test_hello_does_not_use_raw_neutral_palette(): void
    {
        $src = $this->helloSource();

        $forbidden = [
            '/\b(?:bg|text|border|ring|from|to|via|fill|stroke|divide|placeholder)-(?:gray|slate|zinc|neutral|stone|red|green|blue|yellow|amber|lime)-\d{2,3}\b/',
            '/\b(?:bg|text|border|ring|fill|stroke)-black\b/',
            '/\b(?:bg|text|border|ring|fill|stroke)-white\b/',
        ];

        foreach ($forbidden as $pattern) {
            $this->assertDoesNotMatchRegularExpression(
                $pattern,
                $src,
                "Hello.jsx usa cor crua do Tailwind ($pattern) — troque pelo token do DS."
            );
        }
    }
}
