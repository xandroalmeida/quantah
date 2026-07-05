<?php

namespace Tests\Feature\DesignSystem;

use Tests\TestCase;

/**
 * STORY-025/CA-2 — nenhuma cor crua nas superfícies públicas novas (landing B2C, casca
 * pública e bands). Cor entra sempre por token do DS (utilitário Tailwind mapeado), nunca
 * por hex cru, valor arbitrário `[#...]` ou paleta neutra padrão do Tailwind. Espelha o
 * contrato da hello-world (NoRawColorInHelloTest), agora sobre o que substitui a raiz.
 */
class NoRawColorInLandingTest extends TestCase
{
    /** Arquivos-fonte das superfícies públicas do EPIC-005 que devem usar só tokens do DS. */
    private function sources(): array
    {
        $paths = [
            'resources/js/Pages/LandingB2C.jsx',
            'resources/js/Layouts/PublicLayout.jsx',
            'resources/js/Components/Band.jsx',
        ];

        $out = [];
        foreach ($paths as $rel) {
            $abs = base_path($rel);
            $this->assertFileExists($abs, "$rel deveria existir.");
            $out[$rel] = file_get_contents($abs);
        }

        return $out;
    }

    /** (b) inválido — hex cru de cor é proibido no JSX. */
    public function test_no_raw_hex_color(): void
    {
        foreach ($this->sources() as $rel => $src) {
            $this->assertDoesNotMatchRegularExpression(
                '/#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?\b/',
                $src,
                "$rel não pode conter hex cru de cor — use um token do DS."
            );
        }
    }

    /** (b) inválido — valor arbitrário de cor do Tailwind (bg-[#...]) é proibido. */
    public function test_no_tailwind_arbitrary_color(): void
    {
        foreach ($this->sources() as $rel => $src) {
            $this->assertStringNotContainsString('[#', $src,
                "$rel não pode usar cor arbitrária Tailwind (bg-[#...]).");
        }
    }

    /** (b) inválido — a paleta neutra crua do Tailwind não pode substituir os tokens. */
    public function test_no_raw_neutral_palette(): void
    {
        $forbidden = [
            '/\b(?:bg|text|border|ring|from|to|via|fill|stroke|divide|placeholder)-(?:gray|slate|zinc|neutral|stone|red|green|blue|yellow|amber|lime)-\d{2,3}\b/',
            '/\b(?:bg|text|border|ring|fill|stroke)-black\b/',
            '/\b(?:bg|text|border|ring|fill|stroke)-white\b/',
        ];

        foreach ($this->sources() as $rel => $src) {
            foreach ($forbidden as $pattern) {
                $this->assertDoesNotMatchRegularExpression(
                    $pattern,
                    $src,
                    "$rel usa cor crua do Tailwind ($pattern) — troque pelo token do DS."
                );
            }
        }
    }
}
