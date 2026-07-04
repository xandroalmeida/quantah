<?php

namespace App\Domain\Cashback;

use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\User;
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
        ];
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
                'cupom_valor' => $cupom?->valor_total !== null ? self::reaisDeDecimal((string) $cupom->valor_total) : '0,00',
                'data' => self::dataCurta($cupom?->data_emissao),
                'credito' => self::reais($c->valor_centavos),
            ];
        })->all();
    }

    /** Centavos inteiros → "1.234,56" (pt-BR), sem passar valor cru para a tela. */
    private static function reais(int $centavos): string
    {
        return number_format($centavos / 100, 2, ',', '.');
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
