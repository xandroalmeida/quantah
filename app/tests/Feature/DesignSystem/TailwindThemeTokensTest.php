<?php

namespace Tests\Feature\DesignSystem;

use Tests\TestCase;

/**
 * CA-1 / CA-2 / CA-3 — os tokens do DS vivem no tema Tailwind (a config é o ÚNICO
 * lugar onde o valor cru legitimamente aparece: tokens.md → tailwind.config.js).
 * Guarda de mapeamento: se um token some da config, isto quebra.
 */
class TailwindThemeTokensTest extends TestCase
{
    private function config(): string
    {
        $path = base_path('tailwind.config.js');
        $this->assertFileExists($path);

        return file_get_contents($path);
    }

    /** CA-1 — cores de marca/superfície/tinta/semânticas do DS mapeadas (por valor canônico). */
    public function test_theme_maps_design_system_colors(): void
    {
        $cfg = $this->config();

        $expected = [
            'primary' => '#9fe870',
            'on-primary' => '#0e0f0c',
            'canvas' => '#ffffff',
            'canvas-soft' => '#e8ebe6',
            'ink' => '#0e0f0c',
            'body' => '#454745',
            'mute' => '#868685',
            'positive' => '#2ead4b',
            'warning' => '#ffd11a',
            'negative' => '#d03238',
        ];

        foreach ($expected as $token => $hex) {
            $this->assertStringContainsStringIgnoringCase($token, $cfg,
                "Token de cor '$token' ausente do tailwind.config.js.");
            $this->assertStringContainsStringIgnoringCase($hex, $cfg,
                "Valor canônico '$hex' (do token '$token') ausente do tailwind.config.js.");
        }
    }

    /** CA-2 — fonte display é Inter (peso 900 vem do utilitário font-black). */
    public function test_theme_sets_inter_as_display_and_sans(): void
    {
        $cfg = $this->config();

        $this->assertStringContainsString('display', $cfg,
            'fontFamily.display ausente da config.');
        $this->assertStringContainsString('Inter', $cfg,
            'Inter deveria ser a família de fonte do tema (DDR-001).');
    }

    /** CA-3 — raio canônico xl = 24px (assinatura de botões e cards). */
    public function test_theme_defines_signature_radius_xl_24px(): void
    {
        $cfg = $this->config();

        $this->assertMatchesRegularExpression(
            "/borderRadius[\\s\\S]*?['\"]?xl['\"]?\\s*:\\s*['\"]24px['\"]/",
            $cfg,
            'borderRadius.xl deveria ser 24px (raio canônico do DS).'
        );
    }

    /** CA-3 — breakpoints do DS alinhados ao Tailwind (md=768px, lg=1024px). */
    public function test_theme_defines_design_system_breakpoints(): void
    {
        $cfg = $this->config();

        $this->assertStringContainsString('768px', $cfg, 'Breakpoint tablet (md=768px) ausente.');
        $this->assertStringContainsString('1024px', $cfg, 'Breakpoint desktop (lg=1024px) ausente.');
    }

    /** CA-3 — spacing base-4 do DS (ex.: xl=24px) e elevação (boxShadow) presentes. */
    public function test_theme_defines_spacing_and_elevation(): void
    {
        $cfg = $this->config();

        $this->assertStringContainsString('spacing', $cfg, 'Escala de spacing do DS ausente.');
        $this->assertStringContainsString('boxShadow', $cfg, 'Elevação (boxShadow) do DS ausente.');
    }

    /** CA-2 — a fonte Inter com pesos 400/600/900 é carregada no documento. */
    public function test_document_loads_inter_weights(): void
    {
        $blade = file_get_contents(base_path('resources/views/app.blade.php'));

        $this->assertStringContainsStringIgnoringCase('inter', $blade,
            'app.blade.php deveria carregar a fonte Inter.');
        foreach (['400', '600', '900'] as $weight) {
            $this->assertStringContainsString($weight, $blade,
                "Peso $weight da Inter deveria ser carregado (DDR-001).");
        }
    }
}
