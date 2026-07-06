<?php

namespace Tests\Feature;

use Tests\TestCase;

/**
 * A PWA instalável (EPIC-008 · STORY-038) depende de dois assets estáticos servidos na raiz
 * pelo web server (não pelo PHP): o service worker mínimo (public/sw.js) e o web app manifest
 * (public/manifest.json). Como não passam pelo roteador do Laravel, validamos os arquivos em
 * disco — o mesmo padrão do CascaMobileTest (CA-4).
 *
 * Regra de projeto (IDR-016): o SW não pode cachear nada — o `versionWatcher` segue como único
 * mecanismo de atualização, e um cache aqui reintroduziria as armadilhas que evitamos.
 */
class PwaAssetsTest extends TestCase
{
    public function test_service_worker_minimo_existe_e_nao_cacheia(): void
    {
        $caminho = public_path('sw.js');
        $this->assertFileExists($caminho, 'public/sw.js inexistente');

        $sw = (string) file_get_contents($caminho);

        // Handlers que habilitam a instalabilidade e a ativação imediata.
        $this->assertStringContainsString("addEventListener('install'", $sw);
        $this->assertStringContainsString("addEventListener('activate'", $sw);
        $this->assertStringContainsString("addEventListener('fetch'", $sw);
        $this->assertStringContainsString('skipWaiting', $sw);
        $this->assertStringContainsString('clients.claim', $sw);

        // Sem cache: nada de Cache Storage e um fetch handler PASSIVO (sem respondWith).
        // Checamos as CHAMADAS (com parênteses) para não colidir com os comentários explicativos.
        $this->assertStringNotContainsString('caches.open(', $sw, 'o SW mínimo não pode abrir Cache Storage');
        $this->assertStringNotContainsString('.respondWith(', $sw, 'o fetch handler deve ser passivo (sem respondWith)');
    }

    public function test_manifest_habilita_instalacao(): void
    {
        $caminho = public_path('manifest.json');
        $this->assertFileExists($caminho, 'public/manifest.json inexistente');

        $manifest = json_decode((string) file_get_contents($caminho), true);
        $this->assertIsArray($manifest, 'manifest.json não é JSON válido');

        // Critérios de instalabilidade (Chrome): id estável, standalone, start_url e nome.
        $this->assertSame('/', $manifest['id'] ?? null, 'manifest sem id estável');
        $this->assertSame('standalone', $manifest['display'] ?? null);
        $this->assertSame('/inicio', $manifest['start_url'] ?? null);
        $this->assertNotEmpty($manifest['name'] ?? null);

        // Ícones 192 e 512 presentes.
        $tamanhos = array_map(fn ($i) => $i['sizes'] ?? '', $manifest['icons'] ?? []);
        $this->assertContains('192x192', $tamanhos, 'faltou ícone 192');
        $this->assertContains('512x512', $tamanhos, 'faltou ícone 512');

        // Propósitos separados: um "maskable" dedicado (evita recorte quando usado como "any").
        $propositos = array_map(fn ($i) => $i['purpose'] ?? '', $manifest['icons'] ?? []);
        $this->assertContains('maskable', $propositos, 'faltou ícone maskable dedicado');
        $this->assertContains('any', $propositos, 'faltou ícone "any" dedicado');

        // Atalho de app para a ação-núcleo (coletar cupom).
        $urls = array_map(fn ($s) => $s['url'] ?? '', $manifest['shortcuts'] ?? []);
        $this->assertContains('/coletar', $urls, 'faltou atalho para /coletar');
    }
}
