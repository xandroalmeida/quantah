<?php

namespace App\Http\Controllers;

use App\Domain\Cashback\ExtratoCarteira;
use App\Domain\Coleta\DetalheCupom;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
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

    /**
     * Detalhe do cupom (STORY-034) — cabeçalho + itens. Posse pela atribuição (ADR-006):
     * `cupons` não tem `user_id`, então a junção com o Coletador logado é via CupomAtribuicao.
     * Cupom de outro Coletador responde 404 (não vaza existência).
     */
    public function cupom(Request $request, Cupom $cupom, DetalheCupom $detalhe): Response
    {
        $ehDono = CupomAtribuicao::where('cupom_id', $cupom->id)
            ->where('user_id', $request->user()->id)
            ->exists();

        abort_unless($ehDono, 404);

        return Inertia::render('Carteira/CupomDetalhe', [
            'cupom' => $detalhe->para($cupom->load('itens')),
        ]);
    }
}
