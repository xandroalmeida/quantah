<?php

namespace Tests\Feature\Landing;

use App\Models\User;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * STORY-025 — Landing B2C pública ("Cada nota conta.").
 * A raiz `/` deixa de servir o scaffolding (Hello) e passa a servir a landing B2C.
 * Cobre CA-1 (rota pública sem redirect) e ancora os destinos dos CTAs (CA-3/CA-4)
 * no lado do servidor; o texto visível em pt-BR e a navegação real ficam no Dusk (CA-6).
 */
class LandingB2CTest extends TestCase
{
    /** CA-1 — caminho feliz: a raiz é servida via Inertia com a página LandingB2C e responde 200. */
    public function test_landing_b2c_is_served_at_root_via_inertia(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('LandingB2C'));
    }

    /** CA-1 — acesso: a landing é pública — visitante anônimo recebe a própria página, não 302 para /login. */
    public function test_landing_b2c_is_public_and_requires_no_auth(): void
    {
        $response = $this->get('/');

        $response->assertOk();
        $response->assertInertia(fn (Assert $page) => $page->component('LandingB2C'));
    }

    /** CA-1 — borda de acesso: usuário autenticado também vê a landing na raiz (home pública, sem redirect). */
    public function test_landing_b2c_visible_to_authenticated_user(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('LandingB2C'));
    }

    /** CA-3/CA-4 — destinos dos CTAs existem no servidor: login (EPIC-004) e landing B2B (/intelligence). */
    public function test_cta_destinations_exist(): void
    {
        // CTA primário → login do Coletador (EPIC-004).
        $this->assertTrue(\Illuminate\Support\Facades\Route::has('login'), 'A rota nomeada login deveria existir.');

        // CTA para o B2B → landing Quantah Intelligence (rota pública viva).
        $this->get('/intelligence')->assertOk();
    }

    /** CA-1 — negativo/roteamento: rota inexistente devolve 404, não a landing. */
    public function test_unknown_route_returns_404(): void
    {
        $this->get('/rota-que-nao-existe-'.uniqid())->assertNotFound();
    }
}
