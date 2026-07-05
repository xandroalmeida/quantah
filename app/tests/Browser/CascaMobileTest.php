<?php

namespace Tests\Browser;

use App\Models\Carteira;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da casca mobile app-like (STORY-033 · EPIC-007), mobile-first (360–390px).
 *
 * Cobre os CAs pela UI:
 *  - CA-1: nav.bottom fixo e visível (dentro do viewport) em TODAS as telas logadas.
 *  - CA-2: sem overflow horizontal em 360–390px nas telas logadas e na landing.
 *  - CA-3: botão "Entrar" inteiro (não cortado à direita) na landing pública mobile.
 *  - CA-4: manifest.json (display: standalone, ícones, theme-color) e metatags iOS presentes.
 *  - CA-5: alvos de toque ≥48px e labels pt-BR preservados no nav.bottom.
 *
 * Nota de método: o bug de raiz do CA-1 (Safari iOS conta 100vh com a barra do navegador) é
 * específico do Safari e NÃO é reproduzível no Chrome headless — aqui os testes de CA-1/CA-2
 * são guardas de regressão (nav dentro do viewport, sem overflow); a validação do comportamento
 * dinâmico (100dvh + safe-area) é feita no device/emulador (roteiro manual nas Notas do agente).
 */
class CascaMobileTest extends DuskTestCase
{
    private const EMAIL = 'dusk-casca@quantah.test';

    /** Rotas logadas que compartilham a casca AppLayout (nav.bottom). */
    private const ROTAS_LOGADAS = ['/inicio', '/coletar', '/carteira', '/carteira/saque', '/profile'];

    protected function setUp(): void
    {
        parent::setUp();
        $this->limpar();
    }

    protected function tearDown(): void
    {
        $this->limpar();
        parent::tearDown();
    }

    private function limpar(): void
    {
        User::where('email', self::EMAIL)->delete();
    }

    private function usuario(): User
    {
        $user = User::factory()->create(['email' => self::EMAIL, 'name' => 'Ana Coletadora']);
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1247]);

        return $user;
    }

    private function overflowHorizontal(Browser $browser): int
    {
        return (int) $browser->script('return document.body.scrollWidth - window.innerWidth;')[0];
    }

    private function rect(Browser $browser, string $testid): array
    {
        return $browser->script(
            "var e=document.querySelector('[data-testid={$testid}]');".
            'if(!e){return null;}var r=e.getBoundingClientRect();'.
            'return {top:r.top,bottom:r.bottom,left:r.left,right:r.right,height:r.height};'
        )[0];
    }

    /** CA-1 — nav.bottom visível dentro do viewport em todas as telas logadas (360×640). */
    public function test_nav_bottom_visivel_sem_rolar_em_todas_as_telas_logadas(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->resize(360, 640);

            foreach (self::ROTAS_LOGADAS as $rota) {
                $browser->visit($rota)->waitFor('[data-testid=app-nav]', 10);

                $innerH = (int) $browser->script('return window.innerHeight;')[0];
                $nav = $this->rect($browser, 'app-nav');

                $this->assertNotNull($nav, "nav.bottom ausente em {$rota}");
                $this->assertLessThanOrEqual($innerH + 1, $nav['bottom'], "nav.bottom abaixo do viewport em {$rota}");
                $this->assertGreaterThanOrEqual(-1, $nav['top'], "nav.bottom acima do viewport em {$rota}");
            }
        });
    }

    /** CA-2 — sem overflow horizontal nas telas logadas em 360px e na borda 390px. */
    public function test_sem_overflow_horizontal_nas_telas_logadas(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user);

            foreach ([360, 390] as $largura) {
                $browser->resize($largura, 800);
                foreach (self::ROTAS_LOGADAS as $rota) {
                    $browser->visit($rota)->waitFor('[data-testid=app-nav]', 10);
                    $this->assertLessThanOrEqual(
                        1,
                        $this->overflowHorizontal($browser),
                        "overflow horizontal em {$rota} @ {$largura}px"
                    );
                }
            }
        });
    }

    /** CA-3 — botão "Entrar" inteiro (não cortado) na landing pública mobile (360 e 390px). */
    public function test_botao_entrar_inteiro_na_landing_mobile(): void
    {
        $this->browse(function (Browser $browser) {
            foreach ([360, 390] as $largura) {
                $browser->resize($largura, 800)
                    ->visit('/')
                    ->waitFor('[data-testid=landing-b2c-nav-entrar]', 10);

                $innerW = (int) $browser->script('return window.innerWidth;')[0];
                $entrar = $this->rect($browser, 'landing-b2c-nav-entrar');

                $this->assertNotNull($entrar, "botão Entrar ausente @ {$largura}px");
                $this->assertGreaterThanOrEqual(0, $entrar['left'], "Entrar cortado à esquerda @ {$largura}px");
                $this->assertLessThanOrEqual($innerW + 1, $entrar['right'], "Entrar cortado à direita @ {$largura}px");
                $this->assertLessThanOrEqual(1, $this->overflowHorizontal($browser), "overflow na landing @ {$largura}px");

                // O header não pode rolar na horizontal (scrollbar interno = overflow visível).
                $navOverflow = (int) $browser->script(
                    "var n=document.querySelector('[data-testid=public-nav]');return n.scrollWidth - n.clientWidth;"
                )[0];
                $this->assertLessThanOrEqual(1, $navOverflow, "header rola na horizontal @ {$largura}px");
            }
        });
    }

    /** CA-4 — manifest.json e metatags iOS presentes; manifest válido (standalone + ícones). */
    public function test_manifest_e_metatags_pwa_presentes(): void
    {
        // As tags vivem no <head> (fora do escopo `body` do assertPresent do Dusk) → checa via DOM.
        $seletores = [
            'link[rel="manifest"]',
            'meta[name="theme-color"]',
            'meta[name="apple-mobile-web-app-capable"]',
            'meta[name="apple-mobile-web-app-status-bar-style"]',
            'meta[name="apple-mobile-web-app-title"]',
            'link[rel="apple-touch-icon"]',
        ];

        $this->browse(function (Browser $browser) use ($seletores) {
            $browser->visit('/')->waitFor('[data-testid=public-nav]', 10);

            foreach ($seletores as $sel) {
                $existe = $browser->script("return document.querySelector('{$sel}') !== null;")[0];
                $this->assertTrue($existe, "tag ausente no documento: {$sel}");
            }

            // O manifest referenciado é servido e habilita standalone (Android/Chrome).
            $capable = $browser->script(
                "return document.querySelector('meta[name=\"apple-mobile-web-app-capable\"]').content;"
            )[0];
            $this->assertSame('yes', $capable);
        });

        $caminho = public_path('manifest.json');
        $this->assertFileExists($caminho, 'public/manifest.json inexistente');

        $manifest = json_decode((string) file_get_contents($caminho), true);
        $this->assertIsArray($manifest, 'manifest.json não é JSON válido');
        $this->assertSame('standalone', $manifest['display'] ?? null, 'display não é standalone');
        $this->assertNotEmpty($manifest['name'] ?? null, 'manifest sem name');
        $this->assertNotEmpty($manifest['theme_color'] ?? null, 'manifest sem theme_color');
        $this->assertNotEmpty($manifest['icons'] ?? [], 'manifest sem ícones');

        $tamanhos = array_map(fn ($i) => $i['sizes'] ?? '', $manifest['icons']);
        $this->assertContains('192x192', $tamanhos, 'faltou ícone 192');
        $this->assertContains('512x512', $tamanhos, 'faltou ícone 512');
    }

    /** CA-5 — alvos de toque ≥48px e labels pt-BR no nav.bottom. */
    public function test_alvos_de_toque_e_labels_ptbr_no_nav(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->resize(360, 640)
                ->visit('/inicio')
                ->waitFor('[data-testid=app-nav]', 10);

            foreach (['inicio', 'cupons', 'carteira', 'perfil'] as $secao) {
                $item = $this->rect($browser, "app-nav-{$secao}");
                $this->assertNotNull($item, "item {$secao} ausente");
                $this->assertGreaterThanOrEqual(48, $item['height'], "alvo {$secao} < 48px");
            }

            // Labels pt-BR (i18n) — Cupons é renomeado na STORY-036; aqui garante que já é pt-BR.
            $browser->assertSeeIn('[data-testid=app-nav-inicio]', 'Início')
                ->assertSeeIn('[data-testid=app-nav-carteira]', 'Carteira')
                ->assertSeeIn('[data-testid=app-nav-perfil]', 'Perfil');
        });
    }
}
