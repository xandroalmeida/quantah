<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real do acesso do Coletador no padrão visual (STORY-021 · DDR-004).
 *
 * Cobre os caminhos mapeados da spec `SCREEN-STORY-021-login-cadastro-coletador`:
 *  - CA-1: tela no DS (marca Quantah, sem o logo do Laravel), em pt-BR.
 *  - CA-3: credencial inválida → erro global em pt-BR, sem vazar qual campo falhou.
 *  - CA-4: recuperação de senha em pt-BR (link enviado).
 *  - CA-5: lugar do botão "Entrar com Google" presente e desabilitado (placeholder).
 *  - CA-6: jornada cadastro → logout → login por e-mail/senha, com texto em pt-BR.
 *
 * Roda contra o banco de dev (quantah); auto-limpa os usuários de teste que cria.
 */
class AcessoColetadorTest extends DuskTestCase
{
    private const EMAIL = 'dusk-acesso@quantah.test';

    private const SENHA = 'senha-forte-123';

    /** viewBox do ApplicationLogo do Laravel (scaffolding) — não pode aparecer. */
    private const LOGO_LARAVEL = '0 0 316 316';

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
        User::where('email', self::EMAIL)
            ->orWhere('email', 'like', 'dusk-acesso+%@quantah.test')
            ->delete();
    }

    /** (i) feliz — login no padrão visual: marca Quantah, pt-BR, sem logo do Laravel. */
    public function test_login_no_padrao_visual_em_ptbr_sem_logo_laravel(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertSee('Quantah')                 // wordmark da marca
                ->assertSee('Que bom te ver de novo.') // subtítulo pt-BR
                ->assertSee('Entrar')                  // CTA primário pt-BR (DS, sem uppercase)
                ->assertSourceMissing(self::LOGO_LARAVEL) // logo do Laravel removido
                ->assertDontSee('Log in')
                ->assertDontSee('Remember me')
                ->assertDontSee('Password');
        });
    }

    /**
     * (iv) borda — o botão "Entrar com Google" existe na tela (CA-5). Desde a STORY-022 ele é
     * funcional (link para o fluxo OAuth), sem o selo "Em breve". O fluxo Google em si é coberto
     * pelo AcessoGoogleTest.
     */
    public function test_botao_google_presente_na_tela(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertPresent('[data-testid=acesso-google-btn]')
                ->assertSee('Entrar com Google')
                ->assertDontSee('Em breve');
        });
    }

    /** (i) feliz — registro no padrão visual e em pt-BR (CA-1). */
    public function test_registro_no_padrao_visual_em_ptbr(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/register')
                ->assertSee('Criar conta')
                ->assertSee('Nome')
                ->assertSee('Confirmar senha')
                ->assertSee('Criar conta com Google')
                ->assertSee('Já tem conta?')
                ->assertSourceMissing(self::LOGO_LARAVEL)
                ->assertDontSee('Already registered');
        });
    }

    /** (iii) exceção/erro — credencial inválida: erro global em pt-BR, sem vazar campo (CA-3). */
    public function test_erro_de_credencial_e_global_e_em_ptbr(): void
    {
        User::factory()->create([
            'email' => self::EMAIL,
            'password' => bcrypt(self::SENHA),
        ]);

        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->type('[data-testid=acesso-campo-email]', self::EMAIL)
                ->type('[data-testid=acesso-campo-senha]', 'senha-errada')
                ->click('[data-testid=acesso-entrar-submit]')
                ->waitFor('[data-testid=acesso-erro-credencial]', 10)
                ->assertSeeIn('[data-testid=acesso-erro-credencial]', 'incorret') // "E-mail ou senha incorretos."
                ->assertDontSee('credentials')
                ->assertDontSee('do not match');
        });
    }

    /**
     * (ii) alternativo — recuperação de senha em pt-BR (CA-4). Usa um e-mail NÃO cadastrado
     * e mesmo assim vê o callout neutro de confirmação, sem vazar que a conta não existe
     * (anti-enumeração). Ancora no testid do callout, não no subtítulo da tela.
     */
    public function test_recuperacao_de_senha_nao_vaza_existencia_em_ptbr(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/forgot-password')
                ->assertSee('Redefinir senha')
                ->assertSourceMissing(self::LOGO_LARAVEL)
                ->type('[data-testid=acesso-campo-email]', 'nao-cadastrado@quantah.test')
                ->click('[data-testid=acesso-reset-submit]')
                ->waitFor('[data-testid=acesso-reset-enviado]', 10)
                ->assertSeeIn('[data-testid=acesso-reset-enviado]', 'Se houver uma conta')
                ->assertDontSee('Não existe nenhum usuário')
                ->assertDontSee('não encontramos');
        });
    }

    /** (i) feliz ponta a ponta — cadastro → logout → login por e-mail/senha, em pt-BR (CA-6). */
    public function test_jornada_cadastro_logout_login(): void
    {
        // e-mail em minúsculas: o registro exige a regra `lowercase`.
        $email = 'dusk-acesso+'.Str::lower(Str::random(8)).'@quantah.test';

        $this->browse(function (Browser $browser) use ($email) {
            // Cadastro → cai na confirmação de e-mail (conta ainda não verificada).
            $browser->visit('/register')
                ->assertSee('Criar conta')
                ->type('[data-testid=acesso-campo-nome]', 'Maria Coletadora')
                ->type('[data-testid=acesso-campo-email]', $email)
                ->type('[data-testid=acesso-campo-senha]', self::SENHA)
                ->type('[data-testid=acesso-campo-senha_conf]', self::SENHA)
                ->click('[data-testid=acesso-criar-submit]')
                ->waitForLocation('/verify-email', 10)
                ->assertSee('Confirme seu e-mail');

            // Logout pela própria tela de confirmação.
            $browser->click('[data-testid=acesso-sair]')
                ->waitForLocation('/', 10);

            // Login com as mesmas credenciais → não verificado, volta à confirmação.
            $browser->visit('/login')
                ->assertSee('Que bom te ver de novo.')
                ->type('[data-testid=acesso-campo-email]', $email)
                ->type('[data-testid=acesso-campo-senha]', self::SENHA)
                ->click('[data-testid=acesso-entrar-submit]')
                ->waitForLocation('/verify-email', 10)
                ->assertSee('Confirme seu e-mail')
                ->logout(); // isola a sessão para os próximos testes
        });

        $this->assertDatabaseHas('users', ['email' => $email]);
    }
}
