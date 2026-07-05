<?php

namespace Tests\Browser;

use App\Models\User;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da confirmação de e-mail (cadastro por e-mail/senha, aprovado por Alexandro).
 * Cadastro leva à tela de confirmação; confirmar (link assinado) libera o acesso. Reenvio funciona.
 * Roda contra o banco de dev; auto-limpa os usuários de teste.
 */
class ConfirmacaoEmailTest extends DuskTestCase
{
    protected function tearDown(): void
    {
        User::where('email', 'like', 'dusk-verif+%@quantah.test')->delete();
        parent::tearDown();
    }

    private function cadastrar(Browser $browser, string $email): void
    {
        $browser->visit('/register')
            ->type('[data-testid=acesso-campo-nome]', 'Coletador Verif')
            ->type('[data-testid=acesso-campo-email]', $email)
            ->type('[data-testid=acesso-campo-senha]', 'senha-forte-123')
            ->type('[data-testid=acesso-campo-senha_conf]', 'senha-forte-123')
            ->click('[data-testid=acesso-criar-submit]')
            ->waitForLocation('/verify-email', 10)
            ->assertSee('Confirme seu e-mail');
    }

    /** (i) feliz — cadastro leva à confirmação; abrir o link de verificação libera o dashboard. */
    public function test_confirmar_email_libera_o_dashboard(): void
    {
        $email = 'dusk-verif+'.Str::lower(Str::random(8)).'@quantah.test';

        $this->browse(function (Browser $browser) use ($email) {
            $this->cadastrar($browser, $email);

            // Link de verificação assinado (o que o usuário receberia por e-mail).
            $user = User::where('email', $email)->firstOrFail();
            $url = URL::temporarySignedRoute('verification.verify', now()->addMinutes(60), [
                'id' => $user->id,
                'hash' => sha1($user->email),
            ]);

            $browser->visit($url)
                ->waitForLocation('/dashboard', 10)
                ->assertSee('Seu saldo') // destino pós-login é a home-hub (STORY-029), não o scaffolding
                ->logout();
        });

        $this->assertNotNull(User::where('email', $email)->first()->email_verified_at);
    }

    /** (ii) alternativo — reenviar o e-mail de confirmação mostra a confirmação de envio. */
    public function test_reenviar_email_de_confirmacao(): void
    {
        $email = 'dusk-verif+'.Str::lower(Str::random(8)).'@quantah.test';

        $this->browse(function (Browser $browser) use ($email) {
            $this->cadastrar($browser, $email);

            $browser->click('[data-testid=acesso-verif-reenviar]')
                ->waitFor('[data-testid=acesso-verif-reenviado]', 10)
                ->assertSeeIn('[data-testid=acesso-verif-reenviado]', 'novo link de confirmação')
                ->logout();
        });
    }
}
