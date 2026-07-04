<?php

namespace App\Actions\Auth;

use App\Exceptions\Auth\UnverifiedGoogleEmailException;
use App\Models\User;
use Laravel\Socialite\Contracts\User as SocialiteUser;

/**
 * Resolve a identidade Google numa conta do Coletador (STORY-022 · ADR-010, eixo 2).
 *
 * Regras (vínculo por e-mail **verificado pelo Google**):
 *  - Sem usuário com o e-mail → cria conta Google-only (senha nula, e-mail já verificado).
 *  - Usuário existe sem `google_id` → **vincula** (grava `google_id`) e marca verificado se faltava.
 *  - Usuário existe com `google_id` → apenas retorna (login).
 *  - Google sem e-mail verificado → recusa (fail-secure) — não cria nem vincula.
 *
 * Não persiste access/refresh token — só a identidade no momento do login.
 */
class UpsertGoogleUser
{
    public function __invoke(SocialiteUser $googleUser): User
    {
        $email = $googleUser->getEmail();

        if (! $email || ! $this->emailVerified($googleUser)) {
            throw new UnverifiedGoogleEmailException;
        }

        $user = User::where('email', $email)->first();

        if ($user) {
            return $this->linkIfNeeded($user, $googleUser);
        }

        $user = User::create([
            'name' => $googleUser->getName() ?: $email,
            'email' => $email,
            'google_id' => $googleUser->getId(),
            'avatar' => $googleUser->getAvatar(),
            'password' => null,
        ]);

        // email_verified_at não é mass-assignable (segurança) — o Google já verificou o e-mail.
        $user->forceFill(['email_verified_at' => now()])->save();

        return $user;
    }

    /** Vincula a identidade Google a uma conta existente e verifica o e-mail se faltava. */
    private function linkIfNeeded(User $user, SocialiteUser $googleUser): User
    {
        $updates = [];

        if (! $user->google_id) {
            $updates['google_id'] = $googleUser->getId();
        }
        if (! $user->email_verified_at) {
            $updates['email_verified_at'] = now();
        }
        if (! $user->avatar && $googleUser->getAvatar()) {
            $updates['avatar'] = $googleUser->getAvatar();
        }

        if ($updates) {
            $user->forceFill($updates)->save();
        }

        return $user;
    }

    /** O provedor OIDC do Google devolve `email_verified` (bool). Fallback ao legado `verified_email`. */
    private function emailVerified(SocialiteUser $googleUser): bool
    {
        $raw = (array) ($googleUser->user ?? []);

        return (bool) ($raw['email_verified'] ?? $raw['verified_email'] ?? false);
    }
}
