<?php

namespace Tests\Feature;

use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

class HelloWorldTest extends TestCase
{
    /** CA-4 — caminho feliz: a raiz é servida via Inertia e responde 200. */
    public function test_hello_world_is_served_via_inertia_and_returns_200(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Hello'));
    }

    /** CA-4 — conteúdo: expõe o nome do app e o ambiente para a página React. */
    public function test_hello_world_exposes_app_name_and_environment(): void
    {
        $this->get('/')
            ->assertInertia(fn (Assert $page) => $page
                ->component('Hello')
                ->where('appName', 'Quantah')
                ->has('environment')
            );
    }

    /** CA-4 — borda/acesso: a hello-world é pública — serve a própria página, sem redirecionar para login. */
    public function test_hello_world_is_public_and_requires_no_auth(): void
    {
        // Como visitante (sem login), responde 200 e entrega a Hello — não 302 para /login.
        $this->get('/')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Hello'));
    }

    /** CA-4 — negativo/roteamento: rota inexistente devolve 404, não a hello-world. */
    public function test_unknown_route_returns_404(): void
    {
        $this->get('/rota-que-nao-existe-'.uniqid())->assertNotFound();
    }
}
