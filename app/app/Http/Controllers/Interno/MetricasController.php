<?php

namespace App\Http\Controllers\Interno;

use App\Domain\Coleta\Metricas\MetricasColeta;
use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response;

/**
 * Painel interno da north-star (STORY-012) — cupons válidos, únicos e novos por semana
 * e a taxa de sucesso de envio. Só de leitura; delega o cálculo ao MetricasColeta
 * (fronteira de leitura da coleta, ADR-001). Rota atrás de `auth` (interno).
 */
class MetricasController extends Controller
{
    public function index(MetricasColeta $metricas): Response
    {
        return Inertia::render('Interno/Metricas', [
            'resumo' => $metricas->resumo(),
            'porSemana' => $metricas->porSemana(),
        ]);
    }
}
