<?php

namespace Tests\Feature\Auth;

use App\Actions\Auth\UpsertGoogleUser;
use App\Models\User;
use Illuminate\Auth\Notifications\VerifyEmail;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

/**
 * Confirmação de e-mail para cadastros por e-mail/senha (aprovado por Alexandro).
 * Ao ligar `MustVerifyEmail`: o cadastro dispara o e-mail de verificação e o não-verificado
 * é barrado nas áreas autenticadas. Quem entra por Google já vem verificado (ADR-010) e passa.
 */
class EmailVerificationEnforcementTest extends TestCase
{
    use RefreshDatabase;

    /** Cadastro por e-mail/senha dispara a notificação de verificação. */
    public function test_registro_por_email_dispara_verificacao(): void
    {
        Notification::fake();

        $this->post('/register', [
            'name' => 'Novo Coletador',
            'email' => 'novo-verif@quantah.test',
            'password' => 'senha-forte-123',
            'password_confirmation' => 'senha-forte-123',
        ]);

        $user = User::where('email', 'novo-verif@quantah.test')->firstOrFail();
        Notification::assertSentTo($user, VerifyEmail::class);
    }

    /** Usuário não verificado é redirecionado do dashboard para a tela de confirmação. */
    public function test_nao_verificado_e_barrado_no_dashboard(): void
    {
        $user = User::factory()->unverified()->create();

        $this->actingAs($user)->get('/inicio')
            ->assertRedirect(route('verification.notice'));
    }

    /** Usuário verificado acessa normalmente. */
    public function test_verificado_acessa_dashboard(): void
    {
        $user = User::factory()->create(); // verificado por padrão na factory

        $this->actingAs($user)->get('/inicio')->assertOk();
    }

    /** Conta criada via Google já vem verificada — não é barrada. */
    public function test_conta_google_nao_precisa_verificar(): void
    {
        $google = new SocialiteUser;
        $google->map([
            'id' => 'gid-verif',
            'name' => 'Maria Google',
            'email' => 'maria-google@quantah.test',
            'avatar' => 'https://lh3.google/a.png',
        ]);
        $google->user = ['email_verified' => true];

        $user = app(UpsertGoogleUser::class)($google);

        $this->actingAs($user)->get('/inicio')->assertOk();
    }
}
