<?php

namespace Tests\Feature;

use App\Support\AppVersion;
use Tests\TestCase;

/**
 * O endpoint /version é o heartbeat de auto-atualização do PWA. Ele expõe a mesma
 * tag do deploy (`version`) e a assinatura do bundle (`asset`). O carimbo visível e a
 * auto-atualização usam essa tag — a mesma que dispara o deploy (ver docs/deploy.md).
 */
class VersionEndpointTest extends TestCase
{
    public function test_version_endpoint_exposes_tag_and_asset(): void
    {
        config(['app.version' => 'v9.9.9-rc-7']);

        $response = $this->getJson('/version')
            ->assertOk()
            ->assertJsonStructure(['version', 'asset'])
            ->assertJson([
                'version' => 'v9.9.9-rc-7',
                'asset' => AppVersion::asset(),
            ]);

        // Heartbeat não pode ser cacheado (Laravel normaliza a ordem dos diretivos).
        $this->assertStringContainsString('no-store', $response->headers->get('Cache-Control'));
    }

    public function test_label_reflects_the_deploy_tag(): void
    {
        config(['app.version' => 'v1.2.3-rc-0']);

        $this->assertSame('v1.2.3-rc-0', AppVersion::label());
    }

    /**
     * A tag do deploy é CRAVADA no HTML pelo servidor (Blade), não puxada por fetch/prop:
     * é a garantia de que o HTML recebido é o do deploy atual (view-source confirma). Como
     * o mesmo HTML referencia o bundle com hash, versão certa no HTML ⇒ bundle certo.
     */
    public function test_html_crava_a_versao_e_o_asset_do_servidor(): void
    {
        config(['app.version' => 'v9.9.9-rc-7']);

        $this->get('/')
            ->assertOk()
            ->assertSee('<meta name="app-version" content="v9.9.9-rc-7">', false)
            ->assertSee('<meta name="app-asset" content="'.AppVersion::asset().'">', false);
    }
}
