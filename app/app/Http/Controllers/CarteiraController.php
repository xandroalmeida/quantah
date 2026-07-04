<?php

namespace App\Http\Controllers;

use App\Domain\Cashback\ExtratoCarteira;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Tela de carteira do Colaborador (STORY-016) — saldo em reais + histórico de créditos.
 * Controller fino: delega a leitura ao read-model ExtratoCarteira e devolve a página Inertia.
 * Sem PII de pagamento na tela: só saldo e cupom/crédito. Saque é a STORY-017 (fora daqui).
 */
class CarteiraController extends Controller
{
    public function index(Request $request, ExtratoCarteira $extrato): Response
    {
        return Inertia::render('Carteira/Index', $extrato->para($request->user()));
    }
}
