<?php

namespace Tests\Browser;

use App\Models\ColetaEvento;
use App\Models\Cupom;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real do painel interno da north-star (STORY-012, CA-3). Cobre os
 * caminhos alcançáveis pela UI: interno autenticado → vê as métricas compondo o DS;
 * anônimo → é barrado para o login.
 *
 * Roda contra o banco de dev (`.env.dusk.local` → quantah); por isso é auto-limpo
 * (remove o usuário/cupom/evento de teste antes e depois) em vez de migrar o banco.
 */
class PainelMetricasTest extends DuskTestCase
{
    private const EMAIL = 'dusk-metricas@quantah.test';

    private const CHAVE = '35260177777777000199650010000000011000000017';

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
        $cupom = Cupom::where('chave_acesso', self::CHAVE)->first();
        if ($cupom !== null) {
            ColetaEvento::where('cupom_id', $cupom->id)->delete();
            $cupom->delete();
        }
        User::where('email', self::EMAIL)->delete();
    }

    private function usuario(): User
    {
        return User::factory()->create(['email' => self::EMAIL]);
    }

    /** Semeia um cupom validado + um envio, para o painel ter uma semana para exibir. */
    private function semearColeta(): void
    {
        $cupom = Cupom::create([
            'chave_acesso' => self::CHAVE, 'uf' => '35', 'ano_mes' => '2601',
            'cnpj_emitente' => '77777777000199', 'modelo' => '65',
            'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ]);
        ColetaEvento::create(['situacao' => 'aceito', 'cupom_id' => $cupom->id]);
    }

    /** CA-3 (feliz): interno autenticado vê os KPIs e a tabela por semana, compondo o DS. */
    public function test_painel_renderiza_metricas_para_interno_autenticado(): void
    {
        $this->semearColeta();

        $this->browse(function (Browser $browser) {
            $browser->loginAs($this->usuario())
                ->visit('/interno/metricas')
                ->waitFor('[data-testid=painel-metricas-title]', 10)
                ->assertSee('Métricas de coleta')
                ->assertVisible('[data-testid=stat-validos-total]')
                ->assertVisible('[data-testid=stat-enviados-total]')
                ->assertVisible('[data-testid=stat-taxa-geral]')
                ->assertPresent('[data-testid=metricas-tabela]')
                ->assertVisible('[data-testid=semana-row]');
        });
    }

    /** CA-3 (exceção): anônimo é barrado — a rota interna redireciona para o login. */
    public function test_anonimo_e_redirecionado_para_o_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/interno/metricas')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }
}
