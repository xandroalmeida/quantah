<?php

namespace App\Domain\Cashback;

use InvalidArgumentException;

/**
 * Núcleo do cálculo do cashback (STORY-015, ADR-005).
 *
 * Regra: **R$ 1,00 a cada R$ 1.000,00** em cupom válido = **0,1%** do valor. Toda a
 * aritmética é em **centavos inteiros** (nunca float — `float` arredonda e vira erro
 * financeiro silencioso, ver `database-method.md`). O arredondamento do 0,1% ao centavo
 * é **meio-para-cima** (decisão do dono, 2026-07-03): `⌊(centavos + 500) / 1000⌋`.
 *
 * Função pura, sem estado nem banco — é o ponto reconciliável do épico (cobertura ≥98%).
 */
final class CalculadoraCashback
{
    /** Denominador da taxa: 0,1% = 1/1000 (R$ 1 a cada R$ 1.000). */
    private const DIVISOR_TAXA = 1000;

    /**
     * Cashback em centavos de um valor **já em centavos**, com meio-para-cima ao centavo.
     *
     * @param  int  $valorCentavos  valor total do cupom em centavos (≥ 0)
     */
    public static function creditoEmCentavos(int $valorCentavos): int
    {
        if ($valorCentavos < 0) {
            throw new InvalidArgumentException('Valor do cupom não pode ser negativo.');
        }

        // Meio-para-cima: soma metade do divisor antes da divisão inteira.
        return intdiv($valorCentavos + intdiv(self::DIVISOR_TAXA, 2), self::DIVISOR_TAXA);
    }

    /**
     * Converte um valor em reais (`decimal(12,2)` — string vinda do Eloquent) para
     * centavos inteiros, sem passar por `float`. Usa bcmath para evitar imprecisão.
     */
    public static function centavosDeReais(string $valorReais): int
    {
        if (bccomp($valorReais, '0', 2) < 0) {
            throw new InvalidArgumentException('Valor do cupom não pode ser negativo.');
        }

        // bcmul com escala 0 trunca a fração residual — o canônico é centavo inteiro.
        return (int) bcmul($valorReais, '100', 0);
    }

    /**
     * Conveniência: cashback em centavos direto do valor em reais do cupom.
     */
    public static function creditoDeReais(string $valorReais): int
    {
        return self::creditoEmCentavos(self::centavosDeReais($valorReais));
    }
}
