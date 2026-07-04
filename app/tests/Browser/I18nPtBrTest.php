<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da varredura pt-BR (STORY-020, CA-2/CA-5 · ADR-011).
 *
 * Verifica, nas superfícies de acesso já existentes (login, registro e o fluxo de erro de
 * credencial), que o texto sai em português e que **não há resíduo de scaffolding em inglês**
 * ("Log in", "Remember me", "Password", "Whoops!"). Roda contra o banco de dev (quantah),
 * por isso auto-limpa o usuário de teste.
 */
class I18nPtBrTest extends DuskTestCase
{
    private const EMAIL = 'dusk-i18n@quantah.test';

    private const SENHA = 'senha-correta-123';

    protected function setUp(): void
    {
        parent::setUp();
        $this->limpar();
    }

    protected function tearDown(): void
    {
        $this->limpar();
        parent::tearDown();
    }

    private function limpar(): void
    {
        User::where('email', self::EMAIL)->delete();
    }

    /** (i) feliz — a tela de login exibe rótulos em pt-BR e nenhuma string de scaffolding. */
    public function test_login_em_ptbr_sem_residuo_de_ingles(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('E-mail')
                ->assertSee('Senha')
                ->assertSee('Iniciar sessão')
                ->assertSee('Lembrar-me')
                ->assertDontSee('Log in')
                ->assertDontSee('Remember me')
                ->assertDontSee('Password')
                ->assertDontSee('Whoops');
        });
    }

    /** (ii) alternativo — a tela de registro também está em pt-BR. */
    public function test_registro_em_ptbr(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('Nome')
                ->assertSee('Confirmar Senha')
                ->assertSee('Registrar')
                ->assertSee('Já está registrado?')
                ->assertDontSee('Name')
                ->assertDontSee('Confirm Password')
                ->assertDontSee('Already registered');
        });
    }

    /** (iii) exceção/erro do usuário — credencial inválida devolve a mensagem em pt-BR. */
    public function test_erro_de_credencial_em_ptbr(): void
    {
        User::factory()->create([
            'email' => self::EMAIL,
            'password' => bcrypt(self::SENHA),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('email', self::EMAIL)
                ->type('password', 'senha-errada')
                ->press('Iniciar sessão')
                ->waitForText('credenciais')
                ->assertSee('credenciais')
                ->assertDontSee('credentials')
                ->assertDontSee('do not match');
        });
    }
}
