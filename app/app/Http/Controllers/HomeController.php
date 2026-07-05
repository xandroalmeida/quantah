<?php

namespace App\Http\Controllers;

use App\Domain\Cashback\ExtratoCarteira;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Home-hub do Coletador (STORY-029 · EPIC-006) — destino pós-login da área B2C.
 *
 * Substitui a página genérica de scaffolding (Breeze Dashboard) como centro da jornada:
 * mostra o **saldo** da carteira (read-model ExtratoCarteira, EPIC-003) e o **CTA de coleta**
 * (EPIC-002). Controller fino, só leitura; a saudação usa o `auth.user` já compartilhado.
 * A navegação coesa (atalhos extrato/saque, ≤2 toques) é a STORY-030.
 */
class HomeController extends Controller
{
    public function index(Request $request, ExtratoCarteira $extrato): Response
    {
        return Inertia::render('Home/Hub', [
            'saldo' => $extrato->para($request->user())['saldo'],
        ]);
    }
}
