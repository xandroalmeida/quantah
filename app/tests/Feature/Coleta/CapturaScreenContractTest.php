<?php

namespace Tests\Feature\Coleta;

use Tests\TestCase;

/**
 * Contrato-em-fonte da tela de captura (STORY-009) — espelha o padrão de
 * `DesignSystem\ButtonTokenContractTest`. Garante que a página materializa o spec
 * (`design/screens/STORY-009-*`): microcopy exata, identificadores estáveis,
 * estados e só tokens do DS (sem valor cru). O comportamento em runtime é coberto
 * pelo Dusk em browser real (`Tests\Browser\ColetaCapturaTest`).
 */
class CapturaScreenContractTest extends TestCase
{
    private function source(string $rel): string
    {
        $path = base_path($rel);
        $this->assertFileExists($path, "$rel deveria existir.");

        return file_get_contents($path);
    }

    /** (a) feliz — a página existe e traz a microcopy do spec §5, palavra por palavra. */
    public function test_pagina_traz_a_microcopy_do_spec(): void
    {
        $src = $this->source('resources/js/Pages/Coleta/Captura.jsx');

        foreach ([
            'Capturar cupom',
            'Escaneie o QR Code da sua nota ou cole o link.',
            'Escanear QR Code',
            'Aponte a câmera para o QR Code da NFC-e.',
            'Link ou chave da nota',
            'Enviar cupom',
            'Cupom capturado!',
            'Recebemos sua nota. Vamos validar e confirmar em instantes.',
            'Enviar outro cupom',
        ] as $copy) {
            $this->assertStringContainsString($copy, $src, "Microcopy do spec ausente: \"$copy\".");
        }
    }

    /** (b) identificadores estáveis do spec §7 presentes para ancorar o E2E. */
    public function test_identificadores_estaveis_presentes(): void
    {
        $src = $this->source('resources/js/Pages/Coleta/Captura.jsx');

        foreach ([
            'screen-captura',
            'screen-captura-title',
            'screen-captura-scan-btn',
            'screen-captura-paste-field',
            'screen-captura-submit-btn',
            'screen-captura-success',
            'screen-captura-another-btn',
        ] as $id) {
            $this->assertStringContainsString($id, $src, "data-testid ausente: $id.");
        }
    }

    /** (c) estados — sucesso, duplicado e erro de câmera cobertos (spec §4). */
    public function test_cobre_os_estados_do_spec(): void
    {
        $src = $this->source('resources/js/Pages/Coleta/Captura.jsx');

        $this->assertStringContainsString("'capturado'", $src, 'Estado de sucesso ausente.');
        $this->assertStringContainsString("'duplicado'", $src, 'Estado duplicado ausente.');
        $this->assertStringContainsString('screen-captura-camera-error', $src, 'Aviso de câmera ausente.');
        $this->assertStringContainsString('errors.entrada', $src, 'Erro ancorado no campo ausente (CA-4).');
    }

    /** (b) inválido — nenhuma cor crua na página nem no scanner (só tokens do DS). */
    public function test_sem_cor_crua_na_tela(): void
    {
        foreach ([
            'resources/js/Pages/Coleta/Captura.jsx',
            'resources/js/Components/coleta/QrScanner.jsx',
        ] as $rel) {
            $src = $this->source($rel);

            $this->assertDoesNotMatchRegularExpression(
                '/#[0-9a-fA-F]{3}(?:[0-9a-fA-F]{3})?\b/',
                $src,
                "$rel não pode conter hex cru — use um token do DS."
            );
            $this->assertStringNotContainsString('[#', $src, "$rel não pode usar cor arbitrária Tailwind.");
            $this->assertDoesNotMatchRegularExpression(
                '/\b(?:bg|text|border|ring)-(?:gray|slate|zinc|neutral|stone|red|green|blue|yellow|amber|lime)-\d{2,3}\b/',
                $src,
                "$rel usa cor crua do Tailwind — troque pelo token do DS."
            );
        }
    }

    /** (b) o QR é carregado por import dinâmico (fora do bundle inicial — IDR-003). */
    public function test_scanner_usa_import_dinamico_do_zxing(): void
    {
        $src = $this->source('resources/js/Components/coleta/QrScanner.jsx');

        $this->assertStringContainsString("await import('@zxing/browser')", $src,
            'O decode do QR deve ser carregado por import dinâmico (lazy chunk).');
    }

    /**
     * (b) robustez para impressão térmica ruim: o scanner tenta o detector nativo antes
     * do zxing, abre a câmera traseira com constraints próprias e oferece lanterna e foto.
     * Motivo de campo: cupom térmico desbotado/brilhoso — reflexo e vídeo ao vivo falham.
     */
    public function test_scanner_e_robusto_para_impressao_ruim(): void
    {
        $src = $this->source('resources/js/Components/coleta/QrScanner.jsx');

        // Detector nativo primeiro (Android decodifica QR sujo/torto melhor que JS puro).
        $this->assertStringContainsString('BarcodeDetector', $src,
            'Deveria tentar o BarcodeDetector nativo antes do zxing.');
        // Abre o stream com constraints próprias para controlar câmera/foco/lanterna.
        $this->assertStringContainsString('getUserMedia', $src,
            'Deveria abrir a câmera com constraints próprias (não o default do zxing).');
        $this->assertStringContainsString('facingMode', $src,
            'Deveria pedir a câmera traseira (environment).');
        // Lanterna: o reflexo no papel térmico brilhoso é a maior causa de falha de leitura.
        $this->assertStringContainsString('torch', $src,
            'Deveria oferecer a lanterna (torch) para matar o reflexo.');
        // "Tirar foto": uma foto estática de alta resolução lê onde o vídeo ao vivo falha.
        $this->assertMatchesRegularExpression('/capture=["\']environment["\']/', $src,
            'Deveria ter o fallback de foto (input capture="environment").');
    }

    /** (b) identificadores estáveis dos controles de robustez, para ancorar o E2E. */
    public function test_identificadores_dos_controles_de_camera(): void
    {
        $src = $this->source('resources/js/Components/coleta/QrScanner.jsx');

        foreach ([
            'screen-captura-torch-btn',
            'screen-captura-photo-btn',
            'screen-captura-photo-input',
        ] as $id) {
            $this->assertStringContainsString($id, $src, "data-testid ausente: $id.");
        }
    }
}
