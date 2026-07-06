<?php

namespace Tests\Feature\Backoffice;

use App\Models\Cupom;
use App\Models\Emitente;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Detalhe do cupom no Backoffice com o emitente enriquecido (STORY-041 · EPIC-009).
 * Guard por RBAC (ADR-009): só operador entra. O emitente é resolvido pelo CNPJ
 * (vínculo lógico, ADR-014) — cupons do mesmo CNPJ compartilham o registro (CA-5).
 */
class CuponsBackofficeTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private const CNPJ = '43259548002883';

    private const CHAVE = '35260743259548002883652030000666061954634872';

    private function operador(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['nome' => Role::OPERADOR]));

        return $user;
    }

    private function cupom(string $chave = self::CHAVE, string $cnpj = self::CNPJ): Cupom
    {
        return Cupom::create([
            'chave_acesso' => $chave,
            'uf' => '35', 'ano_mes' => '2607', 'cnpj_emitente' => $cnpj,
            'nome_emitente' => 'Supermercados Cavicchiolli',
            'modelo' => '65', 'status' => Cupom::STATUS_VALIDADO, 'valor_total' => '235.43',
        ]);
    }

    public function test_operador_ve_cupom_na_lista(): void // CA-6 (lista)
    {
        $this->cupom();

        $this->actingAs($this->operador())->get(route('backoffice.cupons.index'))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Backoffice/Cupons/Index')
                ->has('cupons.data', 1)
                ->where('cupons.data.0.chave_acesso', self::CHAVE));
    }

    public function test_detalhe_mostra_emitente_enriquecido(): void // CA-3
    {
        $cupom = $this->cupom();
        Emitente::factory()->enriquecido()->create([
            'cnpj' => self::CNPJ,
            'razao_social' => 'SUPERMERCADOS CAVICCHIOLLI LTDA',
            'cnae_principal_codigo' => '4711302',
            'cnae_principal_descricao' => 'Comércio varejista — supermercados',
            'situacao_cadastral' => 'ATIVA', 'municipio' => 'ITU', 'uf' => 'SP',
        ]);

        $this->actingAs($this->operador())->get(route('backoffice.cupons.show', $cupom))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->component('Backoffice/Cupons/Detalhe')
                ->where('emitente.estado', 'enriquecido')
                ->where('emitente.razao_social', 'SUPERMERCADOS CAVICCHIOLLI LTDA')
                ->where('emitente.cnae', '4711-3/02 — Comércio varejista — supermercados')
                ->where('emitente.localizacao', 'ITU/SP'));
    }

    public function test_detalhe_sem_emitente_mostra_pendente(): void // CA-4
    {
        $cupom = $this->cupom();

        $this->actingAs($this->operador())->get(route('backoffice.cupons.show', $cupom))
            ->assertOk()
            ->assertInertia(fn (Assert $p) => $p
                ->where('emitente.estado', 'pendente')
                ->where('emitente.razao_social', null));
    }

    public function test_cupons_do_mesmo_cnpj_compartilham_emitente(): void // CA-5
    {
        $c1 = $this->cupom(self::CHAVE, self::CNPJ);
        $c2 = $this->cupom('35260743259548002883652030000666061954634899', self::CNPJ);
        Emitente::factory()->enriquecido()->create(['cnpj' => self::CNPJ, 'razao_social' => 'CAVICCHIOLLI LTDA']);

        $op = $this->operador();
        foreach ([$c1, $c2] as $c) {
            $this->actingAs($op)->get(route('backoffice.cupons.show', $c))
                ->assertInertia(fn (Assert $p) => $p->where('emitente.razao_social', 'CAVICCHIOLLI LTDA'));
        }
        $this->assertSame(1, Emitente::where('cnpj', self::CNPJ)->count());
    }

    public function test_nao_operador_recebe_403_na_lista(): void // RBAC
    {
        $this->actingAs(User::factory()->create())
            ->get(route('backoffice.cupons.index'))->assertForbidden();
    }

    public function test_nao_operador_recebe_403_no_detalhe(): void // RBAC
    {
        $cupom = $this->cupom();
        $this->actingAs(User::factory()->create())
            ->get(route('backoffice.cupons.show', $cupom))->assertForbidden();
    }
}
