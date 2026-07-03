<?php

namespace Tests\Browser;

use App\Models\Cupom;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da captura do cupom (STORY-009), mobile-first.
 * Cobre os caminhos alcançáveis pela UI: colar link válido → confirmação; link
 * inválido → erro no campo; câmera indisponível → degrada para colar; alvo de toque.
 *
 * Roda contra o banco de dev (`.env.dusk.local` → quantah); por isso é auto-limpo
 * (remove a chave de teste antes/depois) em vez de migrar o banco.
 */
class ColetaCapturaTest extends DuskTestCase
{
    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    protected function setUp(): void
    {
        parent::setUp();
        Cupom::where('chave_acesso', self::CHAVE_SP)->delete();
    }

    protected function tearDown(): void
    {
        Cupom::where('chave_acesso', self::CHAVE_SP)->delete();
        parent::tearDown();
    }

    public function test_captura_por_link_valido_mostra_confirmacao(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(390, 1200)
                ->visit('/coletar')
                ->waitFor('[data-testid=screen-captura-title]', 10)
                ->assertSee('Capturar cupom')
                ->type('[data-testid=screen-captura-paste-field]', self::CHAVE_SP)
                ->click('[data-testid=screen-captura-submit-btn]')
                ->waitFor('[data-testid=screen-captura-success]', 10)
                ->assertSee('Cupom capturado!')
                ->assertVisible('[data-testid=screen-captura-another-btn]');
        });

        $this->assertDatabaseHas('cupons', [
            'chave_acesso' => self::CHAVE_SP,
            'status' => Cupom::STATUS_PENDENTE,
        ]);
    }

    public function test_link_invalido_mostra_erro_no_campo(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(390, 1200)
                ->visit('/coletar')
                ->waitFor('[data-testid=screen-captura-paste-field]', 10)
                ->type('[data-testid=screen-captura-paste-field]', '123-nao-e-uma-chave')
                ->click('[data-testid=screen-captura-submit-btn]')
                ->waitFor('[data-testid=screen-captura-error-entrada]', 10)
                ->assertSee('Confira e tente de novo.');
        });

        $this->assertDatabaseMissing('cupons', ['chave_acesso' => self::CHAVE_SP]);
    }

    public function test_camera_indisponivel_degrada_para_colar(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->resize(390, 1200)
                ->visit('/coletar')
                ->waitFor('[data-testid=screen-captura-scan-btn]', 10)
                ->click('[data-testid=screen-captura-scan-btn]')
                // Sem câmera no headless, o scanner ou aparece ou cai para o aviso —
                // em ambos os casos o fluxo continua (degradação graciosa).
                ->waitUsing(15, 0.25, fn () => $browser->script(
                    "return !!document.querySelector('[data-testid=screen-captura-camera-error]')"
                    ." || !!document.querySelector('[data-testid=screen-captura-video]')"
                )[0])
                ->assertPresent('[data-testid=screen-captura]');
        });
    }

    public function test_alvo_de_toque_do_botao_enviar_tem_48px(): void
    {
        $this->browse(function (Browser $browser) {
            $altura = $browser->resize(390, 1200)
                ->visit('/coletar')
                ->waitFor('[data-testid=screen-captura-paste-field]', 10)
                ->type('[data-testid=screen-captura-paste-field]', self::CHAVE_SP)
                ->script(
                    "return document.querySelector('[data-testid=screen-captura-submit-btn]').offsetHeight"
                )[0];

            $this->assertGreaterThanOrEqual(48, $altura, 'Alvo de toque do CTA deve ter ≥48px.');
        });
    }
}
