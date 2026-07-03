<?php

namespace App\Domain\Coleta\Metricas;

use App\Models\ColetaEvento;
use App\Models\Cupom;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

/**
 * Leitura da north-star do MVP (STORY-012): cupons válidos, únicos e novos por semana,
 * e a taxa de sucesso de envio. É o lado de leitura da fronteira de coleta (ADR-001) —
 * o painel só conhece este serviço, nunca o Eloquent do cupom.
 *
 * Definições (product/north-star.md):
 *  - **válido-único-novo**: cupom `validado`. "Único" e "novo" são garantidos por
 *    construção — `chave_acesso` é UNIQUE (ADR-003), então cada `validado` é uma chave
 *    distinta de primeira ocorrência. Não conta em dobro nem conta inválido.
 *  - **enviados**: toda tentativa de envio (coleta_eventos), incluindo duplicata e
 *    inválido — que não viram linha em `cupons`. Por isso o denominador vem da
 *    telemetria, não do modelo canônico.
 *  - **taxa de sucesso** = validados ÷ enviados (nula quando não houve envio).
 *
 * A semana é a de COLETA (created_at), no fuso de SP (só SP nesta onda).
 */
final class MetricasColeta
{
    private const FUSO = 'America/Sao_Paulo';

    /**
     * Contagem por semana de coleta, mais recente primeiro.
     *
     * @return list<array{semana: string, semana_label: string, validos: int, enviados: int, taxa: float|null}>
     */
    public function porSemana(): array
    {
        $validos = $this->porSemanaBruto(
            Cupom::query()->where('status', Cupom::STATUS_VALIDADO)
        );
        $enviados = $this->porSemanaBruto(ColetaEvento::query());

        $semanas = array_values(array_unique([...array_keys($validos), ...array_keys($enviados)]));
        rsort($semanas); // mais recente primeiro

        return array_map(function (string $semana) use ($validos, $enviados) {
            $v = $validos[$semana] ?? 0;
            $e = $enviados[$semana] ?? 0;

            return [
                'semana' => $semana,
                'semana_label' => $this->rotular($semana),
                'validos' => $v,
                'enviados' => $e,
                'taxa' => $this->taxa($v, $e),
            ];
        }, $semanas);
    }

    /**
     * Números do topo do painel — agregados de toda a série.
     *
     * @return array{validos_total: int, enviados_total: int, taxa_geral: float|null, por_situacao: array<string, int>}
     */
    public function resumo(): array
    {
        $validosTotal = (int) Cupom::query()->where('status', Cupom::STATUS_VALIDADO)->count();
        $enviadosTotal = (int) ColetaEvento::query()->count();

        $porSituacao = ColetaEvento::query()
            ->select('situacao', DB::raw('count(*) as total'))
            ->groupBy('situacao')
            ->pluck('total', 'situacao')
            ->map(fn ($t) => (int) $t)
            ->all();

        return [
            'validos_total' => $validosTotal,
            'enviados_total' => $enviadosTotal,
            'taxa_geral' => $this->taxa($validosTotal, $enviadosTotal),
            'por_situacao' => $porSituacao,
        ];
    }

    /**
     * Conta linhas por semana de coleta (segunda-feira, fuso SP), como mapa semana→total.
     *
     * @param  Builder<covariant \Illuminate\Database\Eloquent\Model>  $query
     * @return array<string, int>
     */
    private function porSemanaBruto($query): array
    {
        // Fuso inline (constante da app, não input): parametrizar quebraria o casamento
        // da expressão entre SELECT e GROUP BY no Postgres.
        $semana = "date_trunc('week', created_at AT TIME ZONE '".self::FUSO."')::date";

        return $query
            ->selectRaw("{$semana} as semana, count(*) as total")
            ->groupByRaw($semana)
            ->pluck('total', 'semana')
            ->mapWithKeys(fn ($total, $semana) => [(string) $semana => (int) $total])
            ->all();
    }

    private function taxa(int $validos, int $enviados): ?float
    {
        return $enviados > 0 ? round($validos / $enviados, 4) : null;
    }

    /** "2026-01-12" → "Semana de 12/01/2026". */
    private function rotular(string $semana): string
    {
        [$ano, $mes, $dia] = explode('-', $semana);

        return "Semana de {$dia}/{$mes}/{$ano}";
    }
}
