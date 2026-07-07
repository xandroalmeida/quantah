<?php

namespace App\Domain\Cashback;

use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use App\Models\User;
use App\Support\Formato;
use Illuminate\Support\Carbon;

/**
 * Read-model da tela de carteira (STORY-016) — leitura, sem escrita.
 *
 * Monta o que a tela precisa: o **saldo** (do cache reconciliável) e o **histórico** de
 * créditos de cashback, cada um juntado ao seu **cupom** (valor/data) pela referência
 * lógica `cupom_id`. Respeita a segregação (ADR-006): não há FK dura; a junção é feita em
 * uma segunda query (`whereIn`), sem N+1. Formata dinheiro e data na **borda de apresentação**.
 */
final class ExtratoCarteira
{
    /** Fallback quando o cupom não tem nome do emitente (STORY-034). */
    public const ESTABELECIMENTO_FALLBACK = 'Estabelecimento não identificado';

    private const MESES = [
        1 => 'jan', 2 => 'fev', 3 => 'mar', 4 => 'abr', 5 => 'mai', 6 => 'jun',
        7 => 'jul', 8 => 'ago', 9 => 'set', 10 => 'out', 11 => 'nov', 12 => 'dez',
    ];

    /**
     * @return array{saldo: array{centavos: int, reais: string}, extrato: list<array{id: string, cupom_valor: string, data: string, credito: string}>}
     */
    public function para(User $user): array
    {
        $carteira = Carteira::where('user_id', $user->id)->first();
        $saldoCentavos = $carteira?->saldo_centavos ?? 0;

        return [
            'saldo' => [
                'centavos' => $saldoCentavos,
                'reais' => self::reais($saldoCentavos),
            ],
            'extrato' => $carteira ? $this->historico($carteira) : [],
            // Cupons recentes AINDA não creditados (em processamento / não aceitos). Dá ao
            // Colaborador a prova visível de que o scan funcionou e o cupom está sendo validado
            // — os validados já aparecem no `extrato` acima com o cashback.
            'cupons' => $this->cuponsEmAndamento($user),
        ];
    }

    /**
     * Cupons do Colaborador (via atribuição, ADR-006) que ainda NÃO viraram crédito: em
     * processamento (aguardando/consultando a SEFAZ) ou não aceitos (rejeitados). Os validados
     * ficam de fora — eles já estão no histórico de créditos.
     *
     * @return list<array{cupom_id: string, titulo: string, status: string, detalhe: string}>
     */
    private function cuponsEmAndamento(User $user): array
    {
        $ids = CupomAtribuicao::where('user_id', $user->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->pluck('cupom_id');

        if ($ids->isEmpty()) {
            return [];
        }

        $cupons = Cupom::whereIn('id', $ids)->get()->keyBy('id');

        return $ids
            ->map(fn ($id) => $cupons->get($id))
            ->filter()
            ->reject(fn (Cupom $c) => $c->status === Cupom::STATUS_VALIDADO)
            ->map(function (Cupom $c) {
                $rejeitado = $c->status === Cupom::STATUS_REJEITADO;

                return [
                    'cupom_id' => $c->id,
                    'titulo' => $rejeitado ? 'Cupom não aceito' : 'Cupom recebido',
                    // 'processando' | 'nao_aceito' — a SEFAZ instável mantém o cupom em
                    // processamento (o Job reenfileira até ela voltar); só negócio/expiração recusa.
                    'status' => $rejeitado ? 'nao_aceito' : 'processando',
                    'detalhe' => $rejeitado
                        ? self::motivoLegivel($c->motivo_falha)
                        : 'Validando na Sefaz. Isso pode levar alguns minutos.',
                ];
            })
            ->values()
            ->all();
    }

    /** Motivo de recusa em linguagem do usuário (espelha ColetaController::microcopyRejeicao). */
    private static function motivoLegivel(?string $motivo): string
    {
        return match ($motivo) {
            'cupom_expirado' => 'Fora do prazo de validade.',
            'fora_de_escopo_uf' => 'Por enquanto só aceitamos notas de São Paulo.',
            'modelo_invalido' => 'Este documento não é uma NFC-e.',
            default => 'Não foi possível validar na Sefaz.',
        };
    }

    /**
     * @return list<array{id: string, cupom_valor: string, data: string, credito: string}>
     */
    private function historico(Carteira $carteira): array
    {
        $creditos = CarteiraTransacao::query()
            ->where('carteira_id', $carteira->id)
            ->where('tipo', CarteiraTransacao::TIPO_CREDITO_CASHBACK)
            ->orderByDesc('created_at')
            ->orderByDesc('id')
            ->get();

        // Junção com a base analítica por referência lógica — uma query, sem N+1.
        $cupons = Cupom::whereIn('id', $creditos->pluck('cupom_id')->filter())->get()->keyBy('id');

        return $creditos->map(function (CarteiraTransacao $c) use ($cupons) {
            $cupom = $cupons->get($c->cupom_id);

            return [
                'id' => $c->id,
                'cupom_id' => $c->cupom_id,
                'estabelecimento' => self::estabelecimento($cupom),
                'cupom_valor' => $cupom?->valor_total !== null ? self::reaisDeDecimal((string) $cupom->valor_total) : '0,00',
                'data' => self::dataCurta($cupom?->data_emissao),
                'credito' => self::reais($c->valor_centavos),
            ];
        })->all();
    }

    /** Nome do estabelecimento, ou o fallback quando o cupom não o tem (STORY-034). */
    private static function estabelecimento(?Cupom $cupom): string
    {
        $nome = $cupom?->nome_emitente;

        return ($nome !== null && $nome !== '') ? $nome : self::ESTABELECIMENTO_FALLBACK;
    }

    /** Centavos inteiros → "1.234,56" (pt-BR), sem passar valor cru para a tela. */
    private static function reais(int $centavos): string
    {
        return Formato::moeda($centavos);
    }

    /** Valor decimal do cupom ("87.90") → "87,90" (pt-BR). */
    private static function reaisDeDecimal(string $decimal): string
    {
        return number_format((float) $decimal, 2, ',', '.');
    }

    /** Data de emissão → "15 jan 2026" (curta, pt-BR); vazio se ausente. */
    private static function dataCurta(?Carbon $data): string
    {
        if ($data === null) {
            return '';
        }

        return $data->day.' '.self::MESES[$data->month].' '.$data->year;
    }
}
