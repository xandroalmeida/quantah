<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Facades\Socialite;
use Mockery;
use Tests\TestCase;

/**
 * Fluxo HTTP do login com Google (STORY-022 · ADR-010). Usa o provedor **fake**
 * (GOOGLE_FAKE=true no phpunit.xml) — sem credencial real. Cobre CA-1/2/3 + fail-secure.
 */
class GoogleLoginControllerTest extends TestCase
{
    use RefreshDatabase;

    /** O redirect inicia o fluxo (fake volta ao callback; real iria ao Google). */
    public function test_redirect_inicia_o_fluxo(): void
    {
        $this->get('/auth/google/redirect?email=maria@gmail.com')
            ->assertRedirectContains('/auth/google/callback');
    }

    /** (CA-1) e-mail novo verificado cria conta e autentica, indo ao destino pós-login. */
    public function test_callback_cria_conta_e_autentica(): void
    {
        $this->get('/auth/google/callback?email=nova@gmail.com&id=gid-1&name=Nova')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticated();
        $this->assertDatabaseHas('users', ['email' => 'nova@gmail.com', 'google_id' => 'gid-1']);
    }

    /** (CA-2) conta e-mail/senha existente é vinculada, sem duplicar. */
    public function test_callback_vincula_conta_existente_sem_duplicar(): void
    {
        $existente = User::factory()->create([
            'email' => 'maria@gmail.com',
            'password' => bcrypt('senha-forte-123'),
            'google_id' => null,
        ]);

        $this->get('/auth/google/callback?email=maria@gmail.com&id=gid-9')
            ->assertRedirect(route('dashboard'));

        $this->assertAuthenticatedAs($existente->fresh());
        $this->assertDatabaseCount('users', 1);
        $this->assertSame('gid-9', $existente->fresh()->google_id);
    }

    /** (CA-3) cancelamento no provedor volta ao login com erro em pt-BR, sem autenticar. */
    public function test_callback_cancelado_volta_ao_login_com_erro_ptbr(): void
    {
        $this->get('/auth/google/callback?error=access_denied')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
        $this->assertSame(
            'Não foi possível entrar com o Google. Tente de novo.',
            session('errors')->get('google')[0],
        );
    }

    /** (fail-secure) e-mail Google não verificado é recusado: não cria conta nem autentica. */
    public function test_callback_email_nao_verificado_volta_ao_login(): void
    {
        $this->get('/auth/google/callback?email=x@gmail.com&verified=0')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
        $this->assertDatabaseCount('users', 0);
    }

    /** (exceção do provedor) falha inesperada do Socialite volta ao login, sem vazar detalhe. */
    public function test_callback_falha_do_provedor_volta_ao_login(): void
    {
        $provider = Mockery::mock(\Laravel\Socialite\Contracts\Provider::class);
        $provider->shouldReceive('user')->andThrow(new \RuntimeException('boom'));
        Socialite::shouldReceive('driver')->with('google')->andReturn($provider);

        $this->get('/auth/google/callback')
            ->assertRedirect(route('login'))
            ->assertSessionHasErrors('google');

        $this->assertGuest();
    }
}
