<?php

namespace Tests\Feature\Saque;

use App\Domain\Saque\SolicitarSaqueService;
use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Role;
use App\Models\Saque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Backoffice de saques (STORY-017) — atrás do Gate `operar-saques` (ADR-009). O operador
 * conduz a máquina de estados; ações estão protegidas; rejeição estorna. HTTP + autorização.
 */
class BackofficeSaquesHttpTest extends TestCase
{
    use RefreshDatabase;

    private const CPF = '111.444.777-35';

    private function operador(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['nome' => Role::OPERADOR]));

        return $user;
    }

    private function saqueSolicitado(): Saque
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 3000]);

        return app(SolicitarSaqueService::class)->solicitar($user, 2000, self::CPF, self::CPF);
    }

    public function test_guest_e_redirecionado(): void
    {
        $this->get('/backoffice/saques')->assertRedirect('/login');
    }

    public function test_nao_operador_recebe_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/backoffice/saques')->assertForbidden();
    }

    public function test_operador_ve_a_lista(): void
    {
        $this->saqueSolicitado();

        $this->actingAs($this->operador())->get('/backoffice/saques')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Saques/Index')
                ->has('saques', 1)
                ->where('saques.0.status', Saque::STATUS_SOLICITADO)
                ->where('saques.0.valor_reais', '20,00'));
    }

    public function test_cpf_vem_mascarado_na_lista(): void
    {
        $this->saqueSolicitado();

        $this->actingAs($this->operador())->get('/backoffice/saques')
            ->assertInertia(fn (Assert $page) => $page
                ->where('saques.0.cpf_mascarado', '111.***.***-35'));
    }

    public function test_operador_ve_o_detalhe_com_cpf_completo(): void
    {
        $saque = $this->saqueSolicitado();

        $this->actingAs($this->operador())->get("/backoffice/saques/{$saque->id}")
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Saques/Detalhe')
                ->where('saque.cpf', '11144477735')
                ->where('saque.valor_reais', '20,00'));
    }

    public function test_filtra_por_status(): void
    {
        $this->saqueSolicitado(); // solicitado

        $this->actingAs($this->operador())->get('/backoffice/saques?status=pago')
            ->assertInertia(fn (Assert $page) => $page->has('saques', 0));
    }

    public function test_operador_assume_o_saque(): void
    {
        $saque = $this->saqueSolicitado();
        $op = $this->operador();

        $this->actingAs($op)->post("/backoffice/saques/{$saque->id}/assumir")->assertRedirect();

        $saque->refresh();
        $this->assertSame(Saque::STATUS_EM_ANALISE, $saque->status);
        $this->assertSame($op->id, $saque->processado_por);
    }

    public function test_fluxo_ate_pago(): void
    {
        $saque = $this->saqueSolicitado();
        $op = $this->operador();

        $this->actingAs($op)->post("/backoffice/saques/{$saque->id}/assumir");
        $this->actingAs($op)->post("/backoffice/saques/{$saque->id}/aprovar");
        $this->actingAs($op)->post("/backoffice/saques/{$saque->id}/pagar", ['comprovante' => 'E2E-PIX-9']);

        $saque->refresh();
        $this->assertSame(Saque::STATUS_PAGO, $saque->status);
        $this->assertSame('E2E-PIX-9', $saque->comprovante);
    }

    public function test_pagar_exige_comprovante(): void
    {
        $saque = $this->saqueSolicitado();
        $op = $this->operador();
        $this->actingAs($op)->post("/backoffice/saques/{$saque->id}/assumir");
        $this->actingAs($op)->post("/backoffice/saques/{$saque->id}/aprovar");

        $this->actingAs($op)->from("/backoffice/saques/{$saque->id}")
            ->post("/backoffice/saques/{$saque->id}/pagar", ['comprovante' => ''])
            ->assertSessionHasErrors('comprovante');

        $this->assertSame(Saque::STATUS_APROVADO, $saque->refresh()->status);
    }

    public function test_rejeitar_estorna_o_saldo(): void
    {
        $saque = $this->saqueSolicitado();
        $op = $this->operador();
        $this->actingAs($op)->post("/backoffice/saques/{$saque->id}/assumir");

        $this->actingAs($op)->post("/backoffice/saques/{$saque->id}/rejeitar", ['motivo' => 'não confere']);

        $saque->refresh();
        $this->assertSame(Saque::STATUS_REJEITADO, $saque->status);
        $this->assertSame(3000, Carteira::find($saque->carteira_id)->saldo_centavos); // 1000 + 2000 estorno
        $this->assertDatabaseHas('carteira_transacoes', [
            'saque_id' => $saque->id,
            'tipo' => CarteiraTransacao::TIPO_ESTORNO_SAQUE,
            'valor_centavos' => 2000,
        ]);
    }

    public function test_transicao_invalida_volta_com_erro(): void
    {
        $saque = $this->saqueSolicitado(); // solicitado
        $op = $this->operador();

        // Pagar pulando análise → barrado; saque intacto.
        $this->actingAs($op)->from("/backoffice/saques/{$saque->id}")
            ->post("/backoffice/saques/{$saque->id}/pagar", ['comprovante' => 'X'])
            ->assertSessionHasErrors();

        $this->assertSame(Saque::STATUS_SOLICITADO, $saque->refresh()->status);
    }

    public function test_nao_operador_nao_executa_acao(): void
    {
        $saque = $this->saqueSolicitado();

        $this->actingAs(User::factory()->create())
            ->post("/backoffice/saques/{$saque->id}/assumir")
            ->assertForbidden();

        $this->assertSame(Saque::STATUS_SOLICITADO, $saque->refresh()->status);
    }
}
