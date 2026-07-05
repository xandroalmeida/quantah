<?php

namespace App\Http\Controllers\Backoffice;

use App\Http\Controllers\Controller;
use App\Models\Lead;
use App\Support\Formato;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Backoffice — lista de leads B2B (STORY-027 · EPIC-005). Superfície interna atrás do Gate
 * `operar-saques` (ADR-009, mesma barreira do Backoffice de saques). Torna a demanda B2B
 * capturada na landing (STORY-026) observável pela operação. Só leitura; PII exibida apenas ao
 * papel operacional (LGPD §4), sem PII em log. Ordenação determinística (mais recentes primeiro).
 */
class LeadsController extends Controller
{
    public function index(): Response
    {
        $leads = Lead::query()
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get()
            ->map(fn (Lead $lead) => [
                'id' => $lead->id,
                'nome' => $lead->nome,
                'email' => $lead->email,
                'empresa' => $lead->empresa,
                'captado_em' => Formato::dataHora($lead->created_at),
            ]);

        return Inertia::render('Backoffice/Leads/Index', [
            'leads' => $leads,
        ]);
    }
}
