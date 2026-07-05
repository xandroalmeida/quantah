<?php

namespace Tests\Feature\Backoffice;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * STORY-027 — Lista de leads B2B no Backoffice, sob o papel operacional.
 * CA-1 (lista com nome/e-mail/empresa/data), CA-2 (operador vê), CA-3 (guarda de acesso — núcleo
 * ≥98%: sem papel = 403, anônimo = redirect login), CA-4 (vazio + ordenação determinística).
 */
class LeadsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    private function operador(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['nome' => Role::OPERADOR]));

        return $user;
    }

    /** CA-2/CA-1 — operador vê a lista com nome, e-mail, empresa e data de captação. */
    public function test_operador_ve_lista_de_leads_com_campos(): void
    {
        Lead::create(['nome' => 'Ana Souza', 'email' => 'ana@acme.com.br', 'empresa' => 'Acme']);

        $this->actingAs($this->operador())
            ->get('/backoffice/leads')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Leads/Index')
                ->has('leads', 1)
                ->has('leads.0', fn (Assert $lead) => $lead
                    ->where('nome', 'Ana Souza')
                    ->where('email', 'ana@acme.com.br')
                    ->where('empresa', 'Acme')
                    ->has('captado_em')
                    ->etc()
                )
            );
    }

    /** CA-3 — guarda: Coletador comum (sem papel operacional) é barrado com 403. */
    public function test_coletador_sem_papel_e_barrado_403(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/backoffice/leads')
            ->assertForbidden();
    }

    /** CA-3 — guarda: visitante anônimo é redirecionado ao login (não vê PII). */
    public function test_anonimo_e_redirecionado_ao_login(): void
    {
        $this->get('/backoffice/leads')->assertRedirect('/login');
    }

    /** CA-4 — estado vazio: sem leads, a lista vem vazia (a UI mostra a mensagem em pt-BR). */
    public function test_estado_vazio(): void
    {
        $this->actingAs($this->operador())
            ->get('/backoffice/leads')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Backoffice/Leads/Index')
                ->has('leads', 0)
            );
    }

    /** CA-4 — ordenação determinística: mais recentes primeiro. */
    public function test_ordenacao_mais_recentes_primeiro(): void
    {
        $antigo = Lead::create(['nome' => 'Antigo', 'email' => 'antigo@x.com', 'empresa' => 'X']);
        $antigo->forceFill(['created_at' => now()->subDay()])->save();
        Lead::create(['nome' => 'Novo', 'email' => 'novo@x.com', 'empresa' => 'X']);

        $this->actingAs($this->operador())
            ->get('/backoffice/leads')
            ->assertInertia(fn (Assert $page) => $page
                ->where('leads.0.nome', 'Novo')
                ->where('leads.1.nome', 'Antigo')
            );
    }
}
