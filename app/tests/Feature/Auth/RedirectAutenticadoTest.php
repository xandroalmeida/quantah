<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Guarda `guest`: usuário JÁ autenticado que acessa /login ou /register deve ir para o app
 * (`/inicio`), não cair no fallback `/` (landing). Regressão do rename dashboard→inicio
 * (IDR-011): o `RedirectIfAuthenticated` procurava a rota `dashboard`/`home` e, sem elas,
 * voltava para `/` — então clicar "Entrar" logado "não fazia nada" (bounce para a landing).
 */
class RedirectAutenticadoTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_redireciona_usuario_autenticado_para_inicio(): void
    {
        $user = User::factory()->create(); // factory cria verificado

        $this->actingAs($user)->get('/login')->assertRedirect('/inicio');
    }

    public function test_register_redireciona_usuario_autenticado_para_inicio(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)->get('/register')->assertRedirect('/inicio');
    }

    public function test_guest_ainda_ve_o_login(): void
    {
        $this->get('/login')->assertOk();
    }
}
