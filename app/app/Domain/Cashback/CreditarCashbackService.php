<?php

namespace App\Domain\Cashback;

use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use Illuminate\Support\Facades\DB;

/**
 * Credita o cashback (0,1%) ao Colaborador quando o cupom fica válido-único-novo (ADR-005).
 *
 * É o coração financeiro do épico. Garantias:
 *  - só credita cupom **validado** que tenha **atribuição** (1º coletor — dedup ADR-003);
 *  - lançamento no **ledger append-only** + atualização do **cache de saldo** na MESMA
 *    transação (saldo == SUM(ledger), reconciliação sem divergência);
 *  - **idempotente por cupom**: no máximo um `credito_cashback` por `cupom_id` — a checagem
 *    roda sob **lock** da carteira (`FOR UPDATE`), e o índice único parcial no banco é a
 *    salvaguarda final contra corrida;
 *  - **no-op** (retorna `null`) sem atribuição, cupom não validado, ou crédito arredondado a 0.
 */
final class CreditarCashbackService
{
    /**
     * Credita o cashback do cupom, se couber. Idempotente e seguro para reprocessamento.
     *
     * @return CarteiraTransacao|null o lançamento criado, ou null se nada foi creditado
     */
    public function creditarPorCupom(Cupom $cupom): ?CarteiraTransacao
    {
        if (! $cupom->validado()) {
            return null;
        }

        $atribuicao = CupomAtribuicao::where('cupom_id', $cupom->id)->first();
        if ($atribuicao === null) {
            return null; // cupom sem coletor identificado (ex.: ingestão via CLI) — ninguém a creditar
        }

        $creditoCentavos = CalculadoraCashback::creditoDeReais((string) $cupom->valor_total);
        if ($creditoCentavos <= 0) {
            return null; // 0,1% arredondou a zero centavo — não há o que lançar
        }

        return DB::transaction(function () use ($cupom, $atribuicao, $creditoCentavos) {
            $carteira = Carteira::firstOrCreate(['user_id' => $atribuicao->user_id]);

            // Lock pessimista: serializa créditos concorrentes desta carteira (mesmo cupom →
            // mesma carteira), tornando a checagem de idempotência confiável.
            $carteira = Carteira::whereKey($carteira->getKey())->lockForUpdate()->first();

            $jaCreditado = CarteiraTransacao::where('cupom_id', $cupom->id)
                ->where('tipo', CarteiraTransacao::TIPO_CREDITO_CASHBACK)
                ->exists();
            if ($jaCreditado) {
                return null;
            }

            $transacao = CarteiraTransacao::create([
                'carteira_id' => $carteira->getKey(),
                'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
                'valor_centavos' => $creditoCentavos,
                'cupom_id' => $cupom->id,
            ]);

            // Atualiza o cache de saldo na MESMA transação do lançamento — nunca divergem.
            $carteira->increment('saldo_centavos', $creditoCentavos);

            return $transacao;
        });
    }
}
