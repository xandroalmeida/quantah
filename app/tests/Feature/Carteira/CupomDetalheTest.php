<?php

namespace Tests\Feature\Carteira;

use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Detalhe do cupom (STORY-034 · CA-3/CA-4/CA-5) — rota própria com guarda `auth` e posse
 * (via CupomAtribuicao, ADR-006). Cabeçalho (estabelecimento, data, total, status) + itens.
 */
class CupomDetalheTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE = '35260112345678000195650010001234561000000019';

    private function cupomDoUsuario(User $user, array $overrides = []): Cupom
    {
        $cupom = Cupom::create(array_merge([
            'chave_acesso' => self::CHAVE,
            'uf' => '35', 'ano_mes' => '2601', 'cnpj_emitente' => '43259548000283',
            'modelo' => '65', 'valor_total' => '235.43', 'data_emissao' => '2026-07-01 16:43:54',
            'nome_emitente' => 'Supermercados Cavicchiolli Ltda',
            'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ], $overrides));

        CupomAtribuicao::create(['cupom_id' => $cupom->id, 'user_id' => $user->id]);

        return $cupom;
    }

    public function test_detalhe_exige_autenticacao(): void
    {
        $user = User::factory()->create();
        $cupom = $this->cupomDoUsuario($user);

        $this->get("/carteira/cupom/{$cupom->id}")->assertRedirect('/login');
    }

    /** CA-4 — dono vê cabeçalho (estabelecimento, data pt-BR, total, status) e itens. */
    public function test_dono_ve_cabecalho_e_itens(): void
    {
        $user = User::factory()->create();
        $cupom = $this->cupomDoUsuario($user);
        $cupom->itens()->create([
            'sequencia' => 1, 'descricao' => 'SALSICHA HOT DOG SADIA 500G',
            'quantidade' => '1.0000', 'unidade' => 'UN', 'valor_unitario' => '14.85', 'valor_total' => '14.85',
        ]);

        $this->actingAs($user)
            ->get("/carteira/cupom/{$cupom->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Carteira/CupomDetalhe')
                ->where('cupom.estabelecimento', 'Supermercados Cavicchiolli Ltda')
                ->where('cupom.data', '01/07/2026')
                ->where('cupom.valor_total', '235,43')
                ->where('cupom.status.codigo', 'validado')
                ->has('cupom.itens', 1)
                ->where('cupom.itens.0.descricao', 'SALSICHA HOT DOG SADIA 500G')
                ->where('cupom.itens.0.unidade', 'UN')
            );
    }

    /** CA-3/CA-5 — cupom de outro Coletador não é acessível (posse via atribuição). */
    public function test_nao_dono_recebe_404(): void
    {
        $dono = User::factory()->create();
        $cupom = $this->cupomDoUsuario($dono);

        $intruso = User::factory()->create();
        $this->actingAs($intruso)->get("/carteira/cupom/{$cupom->id}")->assertNotFound();
    }

    /** CA-1/CA-4 — cupom sem nome do emitente degrada com fallback (não quebra). */
    public function test_fallback_de_estabelecimento_sem_nome(): void
    {
        $user = User::factory()->create();
        $cupom = $this->cupomDoUsuario($user, ['nome_emitente' => null]);

        $this->actingAs($user)
            ->get("/carteira/cupom/{$cupom->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->where('cupom.estabelecimento', 'Estabelecimento não identificado')
                ->where('cupom.cnpj', '43.259.548/0028-83')
            );
    }

    /** CA-4 — cupom pendente/sem itens: estado vazio tratado (extração assíncrona). */
    public function test_cupom_pendente_sem_itens_trata_estado_vazio(): void
    {
        $user = User::factory()->create();
        $cupom = $this->cupomDoUsuario($user, [
            'status' => Cupom::STATUS_PENDENTE, 'nome_emitente' => null,
            'valor_total' => null, 'data_emissao' => null,
        ]);

        $this->actingAs($user)
            ->get("/carteira/cupom/{$cupom->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Carteira/CupomDetalhe')
                ->where('cupom.status.codigo', 'pendente')
                ->has('cupom.itens', 0)
            );
    }

    /** CA-5 — o payload do detalhe não expõe CPF (ADR-006). */
    public function test_detalhe_nao_expoe_cpf(): void
    {
        $user = User::factory()->create();
        $cupom = $this->cupomDoUsuario($user);

        $resposta = $this->actingAs($user)->get("/carteira/cupom/{$cupom->id}");
        $resposta->assertOk();
        $this->assertStringNotContainsString('cpf', strtolower($resposta->getContent()));
    }
}
