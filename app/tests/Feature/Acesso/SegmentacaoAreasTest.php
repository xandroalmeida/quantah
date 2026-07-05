<?php

namespace Tests\Feature\Acesso;

use App\Models\Role;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Segmentação das 3 áreas e guardas (STORY-023 · ADR-010 eixo 3, reusando ADR-009).
 *
 * Cobre, no nível HTTP/roteamento:
 *  - CA-1: cada área tem ponto de entrada; toda rota de Backoffice nasce atrás do guard (fail-secure).
 *  - CA-2: Coletador autenticado é barrado no Backoffice (403), reusando o Gate `operar-saques`.
 *  - CA-3: Backoffice tem entrada própria não anunciada — guest é redirecionado ao login (sem CTA).
 *  - CA-4: área B2B `/intelligence` reservada — pública, sem login e sem rota autenticada/feature.
 *  - CA-5: a barreira renderiza mensagem em pt-BR (página 403).
 */
class SegmentacaoAreasTest extends TestCase
{
    use RefreshDatabase;

    private function operador(): User
    {
        $user = User::factory()->create();
        $user->roles()->attach(Role::firstOrCreate(['nome' => Role::OPERADOR]));

        return $user;
    }

    // ---- CA-1 · segmentação e guarda fail-secure -------------------------------------------------

    /** (borda) Nenhuma rota do Backoffice existe fora do guard — fail-secure por grupo (ADR-010 §3). */
    public function test_toda_rota_backoffice_esta_atras_do_guard(): void
    {
        $rotasBackoffice = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($rota) => Str::startsWith($rota->uri(), 'backoffice'));

        $this->assertTrue($rotasBackoffice->isNotEmpty(), 'Esperava rotas de Backoffice registradas.');

        $rotasBackoffice->each(function ($rota): void {
            $middleware = $rota->gatherMiddleware();
            $this->assertContains('auth', $middleware, "Rota {$rota->uri()} sem `auth`.");
            $this->assertContains('can:operar-saques', $middleware, "Rota {$rota->uri()} sem o Gate.");
        });
    }

    // ---- CA-2 · Coletador barrado no Backoffice --------------------------------------------------

    /** (inválido/autz) Coletador autenticado (sem papel) recebe 403 no Backoffice. */
    public function test_coletador_autenticado_recebe_403_no_backoffice(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/backoffice/saques')
            ->assertForbidden();
    }

    /** (feliz — contraste) Operador atravessa a mesma barreira. */
    public function test_operador_acessa_o_backoffice(): void
    {
        $this->actingAs($this->operador())
            ->get('/backoffice/saques')
            ->assertOk();
    }

    // ---- CA-3 · entrada não anunciada ------------------------------------------------------------

    /** (borda) Guest no Backoffice é redirecionado ao login — entrada própria, sem exposição pública. */
    public function test_guest_no_backoffice_redireciona_para_login(): void
    {
        $this->get('/backoffice/saques')->assertRedirect('/login');
    }

    // ---- CA-4 · área B2B reservada ---------------------------------------------------------------

    /** (feliz) A área B2B `/intelligence` é pública e não exige login. */
    public function test_area_b2b_intelligence_e_publica_sem_login(): void
    {
        $this->get('/intelligence')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Intelligence/Reservado'));
    }

    /** (borda) Usuário autenticado também alcança a área B2B — o acesso independe de sessão. */
    public function test_area_b2b_acessivel_tambem_autenticado(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/intelligence')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page->component('Intelligence/Reservado'));
    }

    /** (borda) A área B2B está reservada: nenhuma rota `/intelligence` exige `auth` nem expõe feature. */
    public function test_area_b2b_nao_tem_rota_autenticada_nem_feature(): void
    {
        $rotasB2b = collect(Route::getRoutes()->getRoutes())
            ->filter(fn ($rota) => Str::startsWith($rota->uri(), 'intelligence'));

        $this->assertTrue($rotasB2b->isNotEmpty(), 'Esperava o namespace /intelligence reservado.');

        $rotasB2b->each(function ($rota): void {
            $this->assertNotContains('auth', $rota->gatherMiddleware(), "Rota B2B {$rota->uri()} não pode exigir login.");
            $this->assertSame(['GET', 'HEAD'], $rota->methods(), "Rota B2B {$rota->uri()} só deve responder GET (sem feature/escrita).");
        });
    }

    // ---- CA-5 · barreira em pt-BR ----------------------------------------------------------------

    /** (exceção/erro) A página 403 da barreira está em pt-BR, sem vazar o texto default em inglês. */
    public function test_pagina_403_esta_em_ptbr(): void
    {
        $resposta = $this->actingAs(User::factory()->create())->get('/backoffice/saques');

        $resposta->assertForbidden();
        $resposta->assertSee('Acesso restrito');
        $resposta->assertDontSee('This action is unauthorized');
        $resposta->assertDontSee('Forbidden');
    }
}
