<?php

namespace Tests\Browser;

use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real do login com Google (STORY-022 · ADR-010), com o provedor **fake**
 * (GOOGLE_FAKE=true — sem credencial real). Cobre CA-1 (cria), CA-2 (vincula), CA-3 (cancela)
 * e CA-5 (o botão leva ao fluxo). Roda contra o banco de dev; auto-limpa os usuários de teste.
 */
class AcessoGoogleTest extends DuskTestCase
{
    private const EMAIL_NOVO = 'google-user@quantah.test'; // default do FakeGoogleProvider

    private const EMAIL_LINK = 'dusk-google-link@quantah.test';

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
        User::whereIn('email', [self::EMAIL_NOVO, self::EMAIL_LINK])->delete();
    }

    /** (i) feliz — o botão "Entrar com Google" está ativo e cria a conta (CA-1, CA-5). */
    public function test_botao_google_ativo_cria_conta_e_autentica(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/login')
                ->assertPresent('[data-testid=acesso-google-btn]')
                ->assertSee('Entrar com Google')
                ->assertDontSee('Em breve') // placeholder da STORY-021 saiu
                ->click('[data-testid=acesso-google-btn]')
                ->waitForLocation('/inicio', 10)
                ->assertSee('Seu saldo') // destino pós-login é a home-hub (STORY-029), não o scaffolding
                ->logout(); // isola a sessão para os próximos testes
        });

        $this->assertDatabaseHas('users', ['email' => self::EMAIL_NOVO]);
        $this->assertNotNull(User::where('email', self::EMAIL_NOVO)->first()->google_id);
    }

    /** (ii) alternativo — login Google vincula a conta e-mail/senha existente, sem duplicar (CA-2). */
    public function test_login_google_vincula_conta_existente(): void
    {
        User::factory()->create([
            'email' => self::EMAIL_LINK,
            'password' => bcrypt('senha-forte-123'),
            'google_id' => null,
        ]);

        $this->browse(function (Browser $browser) {
            // e-mail controlado via query (o fake reconstrói a identidade).
            $browser->visit('/auth/google/redirect?email='.self::EMAIL_LINK.'&id=gid-e2e')
                ->waitForLocation('/inicio', 10)
                ->assertSee('Seu saldo') // destino pós-login é a home-hub (STORY-029), não o scaffolding
                ->logout(); // isola a sessão para os próximos testes
        });

        $this->assertSame(1, User::where('email', self::EMAIL_LINK)->count());
        $this->assertSame('gid-e2e', User::where('email', self::EMAIL_LINK)->first()->google_id);
    }

    /** (iii) exceção/erro — cancelar no Google volta ao login com erro em pt-BR (CA-3). */
    public function test_cancelamento_google_volta_ao_login_com_erro_ptbr(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/auth/google/callback?error=access_denied')
                ->waitForLocation('/login', 10)
                ->waitFor('[data-testid=acesso-erro-credencial]', 10)
                ->assertSeeIn('[data-testid=acesso-erro-credencial]', 'Não foi possível entrar com o Google');
        });
    }
}
