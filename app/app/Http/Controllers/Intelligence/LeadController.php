<?php

namespace App\Http\Controllers\Intelligence;

use App\Domain\Lead\CapturarLead;
use App\Http\Controllers\Controller;
use App\Http\Requests\CapturarLeadRequest;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Landing B2B (Quantah Intelligence) e captação de lead (STORY-026 · EPIC-005). Área pública,
 * sem login (PDR-003). Controller fino: renderiza a vitrine, delega a persistência à ação de
 * domínio `CapturarLead` (idempotente) e confirma via tela dedicada de agradecimento (DDR-006,
 * PRG — Post/Redirect/Get; refresh/voltar não reenviam).
 */
class LeadController extends Controller
{
    /** Vitrine pública do Quantah Intelligence com o formulário de captação. */
    public function landing(): Response
    {
        return Inertia::render('Intelligence/LandingB2B');
    }

    /** Captura o lead (valida por campo em pt-BR; idempotente por e-mail) e vai ao agradecimento. */
    public function store(CapturarLeadRequest $request, CapturarLead $capturar): RedirectResponse
    {
        // Mesmo caminho para e-mail novo e duplicado — a confirmação não revela existência de terceiro.
        $capturar($request->validated());

        return redirect('/intelligence/obrigado');
    }

    /** Tela dedicada de agradecimento (DDR-006 · pattern.lead-confirmacao). */
    public function obrigado(): Response
    {
        return Inertia::render('Intelligence/LeadObrigado');
    }
}
