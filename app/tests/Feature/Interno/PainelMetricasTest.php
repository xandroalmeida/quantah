<?php

namespace Tests\Feature\Interno;

use App\Models\ColetaEvento;
use App\Models\Cupom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Painel interno da north-star (STORY-012, CA-3). Rota interna (atrás de `auth`) que
 * expõe as métricas de coleta compondo o DS. O cálculo é coberto por MetricasColetaTest;
 * aqui garantimos o contrato da rota: autenticação, componente e forma das props.
 */
class PainelMetricasTest extends TestCase
{
    use RefreshDatabase;

    private int $seq = 0;

    private function cupomValidado(): Cupom
    {
        $chave = str_pad((string) (++$this->seq), 44, '3', STR_PAD_LEFT);
        $cupom = Cupom::create([
            'chave_acesso' => $chave, 'uf' => '35', 'ano_mes' => '2601',
            'cnpj_emitente' => '12345678000195', 'modelo' => '65',
            'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ]);
        ColetaEvento::create(['situacao' => 'aceito', 'cupom_id' => $cupom->id]);

        return $cupom;
    }

    /** CA-3: a rota é interna — visitante anônimo é mandado para o login. */
    public function test_painel_exige_autenticacao(): void
    {
        $this->get('/interno/metricas')->assertRedirect('/login');
    }

    /** CA-3: autenticado, o painel renderiza o componente do DS com as props de métrica. */
    public function test_painel_renderiza_metricas_para_usuario_autenticado(): void
    {
        $this->cupomValidado();
        $this->cupomValidado();

        $this->actingAs(User::factory()->create())
            ->get('/interno/metricas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Interno/Metricas')
                ->where('resumo.validos_total', 2)
                ->where('resumo.enviados_total', 2)
                ->where('resumo.taxa_geral', 1) // 1.0 serializa como inteiro no JSON
                ->has('porSemana', 1)
                ->has('porSemana.0', fn (Assert $linha) => $linha
                    ->where('validos', 2)
                    ->where('enviados', 2)
                    ->where('taxa', 1)
                    ->has('semana')
                    ->has('semana_label')
                )
            );
    }

    /** CA-3 borda: sem dados, o painel abre com resumo zerado e sem semanas (estado vazio). */
    public function test_painel_vazio_abre_com_resumo_zerado(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/interno/metricas')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Interno/Metricas')
                ->where('resumo.validos_total', 0)
                ->where('resumo.enviados_total', 0)
                ->where('resumo.taxa_geral', null)
                ->has('porSemana', 0)
            );
    }
}
