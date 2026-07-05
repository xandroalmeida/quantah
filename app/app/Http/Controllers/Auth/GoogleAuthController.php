<?php

namespace App\Http\Controllers\Auth;

use App\Actions\Auth\UpsertGoogleUser;
use App\Exceptions\Auth\UnverifiedGoogleEmailException;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;
use Symfony\Component\HttpFoundation\RedirectResponse as SymfonyRedirect;
use Throwable;

/**
 * Login com Google (STORY-022 · ADR-010). Inicia o fluxo OAuth e trata o retorno:
 * cria/vincula a conta do Coletador (via UpsertGoogleUser) e autentica na sessão `web`.
 * Não persiste access/refresh token — só a identidade no momento do login.
 */
class GoogleAuthController extends Controller
{
    /** Redireciona ao consentimento do Google. */
    public function redirect(): SymfonyRedirect
    {
        return Socialite::driver('google')->redirect();
    }

    /** Retorno do Google: resolve a conta e autentica, ou volta ao login com erro pt-BR (CA-3). */
    public function callback(Request $request, UpsertGoogleUser $upsert): RedirectResponse
    {
        // Usuário cancelou/negou no provedor (Google devolve ?error=access_denied).
        if ($request->filled('error')) {
            return $this->falha();
        }

        try {
            $googleUser = Socialite::driver('google')->user();
            $user = $upsert($googleUser);
        } catch (UnverifiedGoogleEmailException) {
            return $this->falha();
        } catch (Throwable $e) {
            report($e); // observabilidade — nunca expõe detalhe técnico ao usuário

            return $this->falha();
        }

        Auth::login($user, remember: true);
        $request->session()->regenerate(); // fixation: nova sessão só no sucesso

        return redirect()->intended(route('inicio', absolute: false));
    }

    /** Volta ao login com mensagem em pt-BR, sem derrubar a sessão nem vazar detalhe técnico. */
    private function falha(): RedirectResponse
    {
        return redirect()->route('login')->withErrors([
            'google' => __('Could not sign in with Google. Please try again.'),
        ]);
    }
}
