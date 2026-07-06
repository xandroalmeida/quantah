<?php

namespace App\Http\Controllers\Backoffice;

use App\Domain\Enriquecimento\ApresentacaoEmitente;
use App\Http\Controllers\Controller;
use App\Models\Cupom;
use App\Support\Formato;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Cupons no Backoffice (STORY-041 · EPIC-009): o operador vê os cupons processados e,
 * no detalhe, os dados cadastrais do emitente enriquecido (razão social, CNAE,
 * situação, município/UF) — a evidência observável do épico. Guard por RBAC no grupo
 * de rotas (ADR-009). O emitente é resolvido pelo CNPJ (vínculo lógico, ADR-014).
 */
class CuponsController extends Controller
{
    public function index(): Response
    {
        $cupons = Cupom::query()
            ->where('status', Cupom::STATUS_VALIDADO)
            ->with('emitente')
            ->latest('extraido_em')
            ->latest('created_at')
            ->paginate(20)
            ->through(fn (Cupom $cupom) => [
                'id' => $cupom->id,
                'chave_acesso' => $cupom->chave_acesso,
                'estabelecimento' => $cupom->emitente?->razao_social ?? $cupom->nome_emitente,
                'cnpj' => Formato::cnpj($cupom->cnpj_emitente),
                'emissao' => Formato::data($cupom->data_emissao),
                'enriquecimento' => ApresentacaoEmitente::montar($cupom->emitente, $cupom->cnpj_emitente),
            ]);

        return Inertia::render('Backoffice/Cupons/Index', ['cupons' => $cupons]);
    }

    public function show(Cupom $cupom): Response
    {
        $cupom->load('emitente', 'itens');

        return Inertia::render('Backoffice/Cupons/Detalhe', [
            'cupom' => [
                'id' => $cupom->id,
                'chave_acesso' => $cupom->chave_acesso,
                'nome_emitente' => $cupom->nome_emitente,
                'emissao' => Formato::dataHora($cupom->data_emissao),
                'valor_total' => $cupom->valor_total !== null
                    ? 'R$ '.number_format((float) $cupom->valor_total, 2, ',', '.')
                    : null,
                'itens' => $cupom->itens->count(),
            ],
            'emitente' => ApresentacaoEmitente::montar($cupom->emitente, $cupom->cnpj_emitente),
        ]);
    }
}
