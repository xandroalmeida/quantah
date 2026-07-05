<?php

namespace Tests\Feature\Landing;

use App\Models\Lead;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * STORY-026 — Landing B2B (Quantah Intelligence) + captação de lead.
 * CA-1 (rota pública), CA-2 (persistir + confirmar), CA-3 (bloquear inválido sem persistir),
 * CA-4 (duplicado idempotente sem vazar). Texto visível pt-BR e navegação real ficam no Dusk (CA-6).
 */
class LandingB2BTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->withoutVite();
    }

    /** CA-1 — a landing B2B é servida via Inertia em /intelligence e é pública. */
    public function test_landing_b2b_publica_via_inertia(): void
    {
        $this->get('/intelligence')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Intelligence/LandingB2B'));
    }

    /** CA-1 — borda: usuário autenticado também alcança (home B2B independe de sessão). */
    public function test_landing_b2b_acessivel_autenticado(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/intelligence')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Intelligence/LandingB2B'));
    }

    /** CA-2 — feliz: lead válido é persistido e o visitante é levado à tela de agradecimento. */
    public function test_captura_lead_valido_persiste_e_redireciona(): void
    {
        $resposta = $this->post('/intelligence/leads', [
            'nome' => 'Ana Souza',
            'email' => 'ana@acme.com.br',
            'empresa' => 'Acme',
        ]);

        $resposta->assertRedirect('/intelligence/obrigado');
        $this->assertDatabaseHas('leads', ['email' => 'ana@acme.com.br', 'nome' => 'Ana Souza', 'empresa' => 'Acme']);
    }

    /** CA-3 — inválido: campo ausente / e-mail inválido bloqueia por campo, sem persistir. */
    public function test_captura_invalida_bloqueia_por_campo_sem_persistir(): void
    {
        $resposta = $this->from('/intelligence')->post('/intelligence/leads', [
            'nome' => '',
            'email' => 'nao-e-email',
            'empresa' => 'Acme',
        ]);

        $resposta->assertSessionHasErrors(['nome', 'email']);
        $this->assertDatabaseCount('leads', 0);
    }

    /** CA-4 — duplicado: reenvio do mesmo e-mail é idempotente (1 lead) e responde com sucesso, sem vazar. */
    public function test_captura_duplicada_idempotente(): void
    {
        Lead::create(['nome' => 'Ana Souza', 'email' => 'ana@acme.com.br', 'empresa' => 'Acme']);

        $resposta = $this->post('/intelligence/leads', [
            'nome' => 'Outra Pessoa',
            'email' => 'ana@acme.com.br',
            'empresa' => 'Outra',
        ]);

        $resposta->assertRedirect('/intelligence/obrigado'); // mesma confirmação — não revela que já existia
        $resposta->assertSessionHasNoErrors();
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('leads', ['email' => 'ana@acme.com.br', 'nome' => 'Ana Souza']); // não sobrescreve
    }

    /** CA-2 — a tela de agradecimento (DDR-006) é pública e servida via Inertia. */
    public function test_tela_de_agradecimento_publica(): void
    {
        $this->get('/intelligence/obrigado')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Intelligence/LeadObrigado'));
    }

    /** LGPD — a página de privacidade (destino do aviso) é pública e servida via Inertia. */
    public function test_privacidade_publica(): void
    {
        $this->get('/privacidade')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Privacidade'));
    }
}
