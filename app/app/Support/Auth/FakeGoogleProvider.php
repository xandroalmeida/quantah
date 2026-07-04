<?php

namespace App\Support\Auth;

use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Request;
use Laravel\Socialite\Two\User as SocialiteUser;

/**
 * Provedor Google **simulado** para dev/CI/E2E (STORY-022 · ADR-010: "provedor simulado/stub").
 * Ligado só quando `services.google.fake` é verdadeiro — dispensa credencial real do Google (CA-4).
 *
 * `redirect()` volta direto para o callback (sem sair para o Google), carregando a identidade que
 * o teste escolher via query (`?email=&name=&id=&verified=`). `user()` reconstrói essa identidade.
 * Nunca é registrado em produção (flag off) — lá vale o driver real do Socialite.
 */
class FakeGoogleProvider
{
    /** Simula o consentimento: retorna ao callback com a identidade escolhida. */
    public function redirect(): RedirectResponse
    {
        return redirect()->route('google.callback', Request::only(['email', 'name', 'id', 'verified']));
    }

    /** Reconstrói o usuário Google a partir da query (com defaults). */
    public function user(): SocialiteUser
    {
        $user = new SocialiteUser;
        $user->map([
            'id' => Request::query('id', 'fake-google-id'),
            'name' => Request::query('name', 'Coletador Google'),
            'email' => Request::query('email', 'google-user@quantah.test'),
            'avatar' => 'https://lh3.google/fake.png',
        ]);
        $user->user = [
            'email_verified' => filter_var(Request::query('verified', 'true'), FILTER_VALIDATE_BOOL),
        ];

        return $user;
    }
}
