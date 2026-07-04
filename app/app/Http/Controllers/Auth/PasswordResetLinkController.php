<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Password;
use Inertia\Inertia;
use Inertia\Response;

class PasswordResetLinkController extends Controller
{
    /**
     * Display the password reset link request view.
     */
    public function create(): Response
    {
        return Inertia::render('Auth/ForgotPassword', [
            'status' => session('status'),
        ]);
    }

    /**
     * Handle an incoming password reset link request.
     */
    public function store(Request $request): RedirectResponse
    {
        $request->validate([
            'email' => 'required|email',
        ]);

        // Envia o link — o broker só dispara de fato quando o e-mail existe.
        Password::sendResetLink($request->only('email'));

        // Anti-enumeração (LGPD / STORY-021 CA-4): NÃO revelamos se o e-mail está
        // cadastrado. Conta existente, inexistente e throttled recebem a mesma
        // resposta neutra — nunca um erro de campo do tipo "usuário não encontrado".
        return back()->with('status', __(Password::RESET_LINK_SENT));
    }
}
