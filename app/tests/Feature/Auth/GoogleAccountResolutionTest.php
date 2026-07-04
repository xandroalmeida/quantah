<?php

namespace Tests\Feature\Auth;

use App\Actions\Auth\UpsertGoogleUser;
use App\Exceptions\Auth\UnverifiedGoogleEmailException;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Socialite\Two\User as SocialiteUser;
use Tests\TestCase;

/**
 * Núcleo de contas do login Google (STORY-022 · ADR-010, eixo 2). Testa a ação que decide
 * criar / vincular / logar / recusar a partir da identidade Google. Cobertura de núcleo ≥98%.
 */
class GoogleAccountResolutionTest extends TestCase
{
    use RefreshDatabase;

    private function googleUser(array $o = []): SocialiteUser
    {
        $u = new SocialiteUser;
        $u->map([
            'id' => $o['id'] ?? 'google-abc-123',
            'name' => $o['name'] ?? 'Maria Coletadora',
            // array_key_exists p/ permitir email null explícito (o ?? engoliria o null).
            'email' => array_key_exists('email', $o) ? $o['email'] : 'maria@gmail.com',
            'avatar' => $o['avatar'] ?? 'https://lh3.google/a.png',
        ]);
        // Bruto do provedor (OIDC): posse do e-mail comprovada pelo Google.
        $u->user = ['email_verified' => $o['verified'] ?? true];

        return $u;
    }

    private function resolve(SocialiteUser $u): User
    {
        return app(UpsertGoogleUser::class)($u);
    }

    /** (a) feliz — e-mail novo e verificado cria conta Google-only (CA-1). */
    public function test_cria_conta_para_email_novo_verificado(): void
    {
        $user = $this->resolve($this->googleUser(['email' => 'nova@gmail.com', 'id' => 'gid-1']));

        $this->assertDatabaseCount('users', 1);
        $this->assertSame('nova@gmail.com', $user->email);
        $this->assertSame('gid-1', $user->google_id);
        $this->assertNull($user->password);
        $this->assertNotNull($user->email_verified_at);
    }

    /** (b/vínculo) — conta e-mail/senha existente é VINCULADA, sem duplicar (CA-2). */
    public function test_vincula_google_a_conta_email_senha_existente(): void
    {
        $existente = User::factory()->create([
            'email' => 'maria@gmail.com',
            'password' => bcrypt('senha-forte-123'),
            'google_id' => null,
        ]);

        $user = $this->resolve($this->googleUser(['email' => 'maria@gmail.com', 'id' => 'gid-9']));

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($existente->id, $user->id);
        $this->assertSame('gid-9', $user->fresh()->google_id);
        $this->assertNotNull($user->fresh()->password); // mantém a senha existente
    }

    /** (borda) — conta que já tem google_id apenas loga, sem duplicar. */
    public function test_login_quando_conta_ja_tem_google_id(): void
    {
        $existente = User::factory()->create([
            'email' => 'maria@gmail.com',
            'google_id' => 'gid-existente',
        ]);

        $user = $this->resolve($this->googleUser(['email' => 'maria@gmail.com', 'id' => 'gid-existente']));

        $this->assertDatabaseCount('users', 1);
        $this->assertSame($existente->id, $user->id);
    }

    /** (borda) — conta local não verificada é marcada como verificada ao vincular (Google prova posse). */
    public function test_vincula_e_marca_verificado_quando_conta_local_nao_verificada(): void
    {
        $existente = User::factory()->unverified()->create([
            'email' => 'maria@gmail.com',
            'google_id' => null,
        ]);
        $this->assertNull($existente->email_verified_at);

        $user = $this->resolve($this->googleUser(['email' => 'maria@gmail.com']));

        $this->assertNotNull($user->fresh()->email_verified_at);
    }

    /** (exceção/fail-secure) — e-mail Google NÃO verificado é recusado: não cria nem vincula. */
    public function test_recusa_quando_email_google_nao_verificado(): void
    {
        $this->expectException(UnverifiedGoogleEmailException::class);

        try {
            $this->resolve($this->googleUser(['email' => 'x@gmail.com', 'verified' => false]));
        } finally {
            $this->assertDatabaseCount('users', 0);
        }
    }

    /** (exceção) — sem e-mail no perfil Google também é recusado. */
    public function test_recusa_quando_google_nao_retorna_email(): void
    {
        $this->expectException(UnverifiedGoogleEmailException::class);

        $this->resolve($this->googleUser(['email' => null]));
    }
}
