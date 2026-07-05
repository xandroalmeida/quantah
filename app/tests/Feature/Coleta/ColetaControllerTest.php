<?php

namespace Tests\Feature\Coleta;

use App\Http\Controllers\ColetaController;
use App\Jobs\ExtrairCupomJob;
use App\Models\Cupom;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Queue;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Captura do cupom (STORY-009) — a tela faz handoff para a ingestão (ADR-001) sem
 * reimplementar validação/dedup (CA-5). A extração é assíncrona (STORY-010): a captura
 * persiste `pendente` e enfileira o ExtrairCupomJob. Aqui a fila é fake (não bate no portal).
 */
class ColetaControllerTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    private const CHAVE_RJ = '33260112345678000195650010001234561000000014';

    private const URL_QR = 'https://www.nfce.fazenda.sp.gov.br/qrcode?p=35260112345678000195650010001234561000000019|2|1|1|ABC';

    private User $colaborador;

    protected function setUp(): void
    {
        parent::setUp();
        Queue::fake();
        // A coleta agora exige Colaborador logado (STORY-015) — ele é o dono do cashback.
        $this->colaborador = User::factory()->create();
        $this->actingAs($this->colaborador);
    }

    public function test_coletar_exige_autenticacao(): void
    {
        Auth::logout();

        $this->get('/coletar')->assertRedirect('/login');
        $this->post('/coletar', ['entrada' => self::CHAVE_SP])->assertRedirect('/login');
        $this->assertDatabaseCount('cupons', 0);
    }

    public function test_captura_autenticada_atribui_o_cupom_ao_colaborador(): void
    {
        $this->post('/coletar', ['entrada' => self::CHAVE_SP, 'origem' => 'scan'])
            ->assertRedirect();

        $cupom = Cupom::where('chave_acesso', self::CHAVE_SP)->firstOrFail();
        $this->assertDatabaseHas('cupom_atribuicoes', [
            'cupom_id' => $cupom->id,
            'user_id' => $this->colaborador->id,
        ]);
    }

    public function test_reenvio_por_outro_colaborador_nao_reatribui(): void
    {
        // 1º coletor cria o cupom e ganha a atribuição.
        $this->post('/coletar', ['entrada' => self::CHAVE_SP]);
        $cupom = Cupom::where('chave_acesso', self::CHAVE_SP)->firstOrFail();

        // 2º Colaborador reenvia a mesma chave → duplicado (dedup ADR-003): não reatribui.
        $outro = User::factory()->create();
        $this->actingAs($outro)
            ->post('/coletar', ['entrada' => self::CHAVE_SP])
            ->assertSessionHas('coleta', fn ($c) => $c['situacao'] === 'duplicado');

        $this->assertDatabaseCount('cupom_atribuicoes', 1);
        $this->assertDatabaseHas('cupom_atribuicoes', [
            'cupom_id' => $cupom->id,
            'user_id' => $this->colaborador->id,
        ]);
    }

    public function test_tela_de_captura_renderiza(): void
    {
        $this->get('/coletar')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Coleta/Captura'));
    }

    public function test_link_compartilhado_preenche_a_entrada_inicial(): void
    {
        $this->get('/coletar?p='.urlencode(self::URL_QR))
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Coleta/Captura')
                ->where('entradaInicial', self::URL_QR));
    }

    public function test_captura_persiste_o_cupom_pendente_e_confirma(): void
    {
        $this->post('/coletar', ['entrada' => self::CHAVE_SP, 'origem' => 'scan'])
            ->assertRedirect()
            ->assertSessionHas('coleta', fn ($c) => $c['situacao'] === 'capturado'
                && $c['chave'] === self::CHAVE_SP);

        // Handoff: fica `pendente` e a extração é enfileirada (ADR-002), sem CPF.
        $this->assertDatabaseHas('cupons', [
            'chave_acesso' => self::CHAVE_SP,
            'status' => Cupom::STATUS_PENDENTE,
            'uf' => '35',
        ]);
        $this->assertDatabaseCount('cupons', 1);
        Queue::assertPushed(ExtrairCupomJob::class);
    }

    public function test_captura_pela_url_do_qr_compartilhado(): void
    {
        $this->post('/coletar', ['entrada' => self::URL_QR, 'origem' => 'compartilhado'])
            ->assertRedirect()
            ->assertSessionHas('coleta', fn ($c) => $c['situacao'] === 'capturado');

        $this->assertDatabaseHas('cupons', ['chave_acesso' => self::CHAVE_SP, 'origem' => 'compartilhado']);
    }

    public function test_reenvio_da_mesma_chave_e_idempotente(): void
    {
        $this->post('/coletar', ['entrada' => self::CHAVE_SP]);
        $this->post('/coletar', ['entrada' => self::CHAVE_SP])
            ->assertRedirect()
            ->assertSessionHas('coleta', fn ($c) => $c['situacao'] === 'duplicado');

        $this->assertDatabaseCount('cupons', 1);
    }

    public function test_chave_malformada_gera_erro_no_campo(): void
    {
        $this->post('/coletar', ['entrada' => '123-nao-e-chave'])
            ->assertSessionHasErrors(['entrada']);

        $this->assertDatabaseCount('cupons', 0);
    }

    public function test_nota_de_outro_estado_e_rejeitada_com_mensagem_de_sp(): void
    {
        $this->from('/coletar')
            ->post('/coletar', ['entrada' => self::CHAVE_RJ])
            ->assertRedirect('/coletar')
            ->assertSessionHasErrors(['entrada' => 'Por enquanto o Quantah só coleta notas de São Paulo.']);

        $this->assertDatabaseCount('cupons', 0);
    }

    public function test_entrada_obrigatoria(): void
    {
        $this->post('/coletar', [])->assertSessionHasErrors(['entrada']);
    }

    /** STORY-035 CA-4 — microcopy pt-BR do prazo, com o N da config, pelo mesmo mecanismo de campo. */
    public function test_microcopy_de_cupom_expirado_e_ptbr_com_o_prazo_da_config(): void
    {
        config(['coleta.janela_dias' => 7]);
        $msg = ColetaController::microcopyRejeicao('cupom_expirado');

        $this->assertStringContainsString('prazo', $msg);
        $this->assertStringContainsString('7 dias', $msg);
        // Não é banner global genérico nem o fallback.
        $this->assertNotSame('Não foi possível ler esse cupom.', $msg);

        // O N acompanha a config (sem número mágico).
        config(['coleta.janela_dias' => 15]);
        $this->assertStringContainsString('15 dias', ColetaController::microcopyRejeicao('cupom_expirado'));
    }
}
