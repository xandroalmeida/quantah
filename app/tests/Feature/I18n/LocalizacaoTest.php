<?php

namespace Tests\Feature\I18n;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Fundação de i18n (STORY-020, ADR-011) — o mecanismo de localização pt-BR.
 *
 * Cobre o contrato do mecanismo: locale ativo pt-BR, resolução de strings pela fonte única
 * (`lang/`), a prop `translations` compartilhada com o Inertia (consumida pelo helper `t()`
 * no React) e as mensagens de validação/credencial do back-end em pt-BR (CA-1, CA-3).
 */
class LocalizacaoTest extends TestCase
{
    use RefreshDatabase;

    /** (a) feliz — o locale ativo da aplicação é pt-BR. */
    public function test_locale_ativo_e_ptbr(): void
    {
        $this->assertSame('pt_BR', app()->getLocale());
    }

    /** (a) feliz — uma string de scaffolding do Breeze resolve para pt-BR pela fonte única. */
    public function test_traducao_de_string_do_breeze_resolve_para_ptbr(): void
    {
        $this->assertSame('Iniciar sessão', __('Log in'));
        $this->assertSame('Senha', __('Password'));
    }

    /** (d) borda — chave sem tradução retorna a própria chave (nunca quebra a tela). */
    public function test_chave_sem_traducao_retorna_a_propria_chave(): void
    {
        $this->assertSame('Zzz Chave Inexistente 42', __('Zzz Chave Inexistente 42'));
    }

    /** (a) feliz — a tela de login compartilha o dicionário do locale como prop `translations`. */
    public function test_login_compartilha_prop_translations(): void
    {
        $this->get('/login')->assertInertia(
            fn (Assert $page) => $page
                ->has('translations')
                ->where('translations.Log in', 'Iniciar sessão')
        );
    }

    /** (outra ramificação) — página autenticada também recebe o dicionário compartilhado. */
    public function test_pagina_autenticada_tambem_compartilha_translations(): void
    {
        $this->actingAs(User::factory()->create())
            ->get('/carteira')
            ->assertInertia(fn (Assert $page) => $page->has('translations'));
    }

    /** (b) inválido — validação de campo obrigatório volta em pt-BR, sem resíduo de inglês. */
    public function test_validacao_de_campo_obrigatorio_em_ptbr(): void
    {
        $this->post('/login', ['email' => '', 'password' => ''])
            ->assertSessionHasErrors('email');

        $msg = session('errors')->get('email')[0];
        $this->assertStringContainsString('obrigat', mb_strtolower($msg));
        $this->assertStringNotContainsString('field', mb_strtolower($msg));
        $this->assertStringNotContainsString('required', mb_strtolower($msg));
    }

    /** (c) exceção esperada — credencial inválida devolve a mensagem de auth em pt-BR. */
    public function test_credenciais_invalidas_em_ptbr(): void
    {
        User::factory()->create(['email' => 'colaborador@quantah.test']);

        $this->post('/login', [
            'email' => 'colaborador@quantah.test',
            'password' => 'senha-errada',
        ])->assertSessionHasErrors('email');

        // STORY-021 (CA-3/DDR-004): mensagem genérica em pt-BR, sem vazar qual campo falhou.
        $msg = session('errors')->get('email')[0];
        $this->assertStringContainsString('incorret', mb_strtolower($msg));
        $this->assertStringNotContainsString('credentials', mb_strtolower($msg));
    }
}
