<?php

namespace Tests\Unit\Cashback;

use App\Domain\Cashback\CalculadoraCashback;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;

/**
 * Núcleo do cashback (STORY-015, ADR-005) — função pura, sem banco: 0,1% do valor do
 * cupom, em centavos inteiros (nunca float), com arredondamento **meio-para-cima** ao
 * centavo (decisão do dono, 2026-07-03). É a regra reconciliável do épico (cobertura ≥98%).
 */
class CalculadoraCashbackTest extends TestCase
{
    // ---- creditoEmCentavos: a taxa de 0,1% sobre um valor já em centavos ----

    public function test_mil_reais_credita_um_real(): void
    {
        // R$ 1.000,00 = 100000 centavos → 0,1% = R$ 1,00 = 100 centavos ("R$1 a cada R$1.000").
        $this->assertSame(100, CalculadoraCashback::creditoEmCentavos(100_000));
    }

    public function test_arredonda_meio_para_cima(): void
    {
        // R$ 87,90 = 8790 centavos → 0,1% = 8,79 centavos → 9 (meio-para-cima).
        $this->assertSame(9, CalculadoraCashback::creditoEmCentavos(8_790));
    }

    public function test_fracao_exatamente_meio_arredonda_para_cima(): void
    {
        // 1500 → 1,5 → 2 ; 2500 → 2,5 → 3 (nunca para baixo no empate).
        $this->assertSame(2, CalculadoraCashback::creditoEmCentavos(1_500));
        $this->assertSame(3, CalculadoraCashback::creditoEmCentavos(2_500));
    }

    public function test_valor_pequeno_arredonda_para_zero(): void
    {
        // R$ 4,99 = 499 centavos → 0,499 centavo → 0.
        $this->assertSame(0, CalculadoraCashback::creditoEmCentavos(499));
    }

    public function test_valor_zero_credita_zero(): void
    {
        $this->assertSame(0, CalculadoraCashback::creditoEmCentavos(0));
    }

    public function test_valor_grande_nao_perde_precisao(): void
    {
        // R$ 999.999,99 = 99999999 centavos → 99999,999 → 100000 centavos = R$ 1.000,00.
        $this->assertSame(100_000, CalculadoraCashback::creditoEmCentavos(99_999_999));
    }

    public function test_valor_negativo_e_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CalculadoraCashback::creditoEmCentavos(-1);
    }

    // ---- centavosDeReais: conversão decimal(12,2) → centavos, sem float ----

    public function test_converte_reais_para_centavos(): void
    {
        $this->assertSame(8_790, CalculadoraCashback::centavosDeReais('87.90'));
        $this->assertSame(100_000, CalculadoraCashback::centavosDeReais('1000.00'));
        $this->assertSame(123_456, CalculadoraCashback::centavosDeReais('1234.56'));
        $this->assertSame(0, CalculadoraCashback::centavosDeReais('0.00'));
    }

    public function test_converte_reais_com_um_decimal(): void
    {
        // Defensivo: mesmo que a casa venha "raspada", o canônico é centavos inteiros.
        $this->assertSame(8_790, CalculadoraCashback::centavosDeReais('87.9'));
        $this->assertSame(500, CalculadoraCashback::centavosDeReais('5'));
    }

    public function test_reais_negativo_e_invalido(): void
    {
        $this->expectException(InvalidArgumentException::class);
        CalculadoraCashback::centavosDeReais('-5.00');
    }

    // ---- creditoDeReais: conveniência (conversão + taxa) ----

    public function test_credito_de_reais_combina_conversao_e_taxa(): void
    {
        $this->assertSame(9, CalculadoraCashback::creditoDeReais('87.90'));
        $this->assertSame(100, CalculadoraCashback::creditoDeReais('1000.00'));
        $this->assertSame(0, CalculadoraCashback::creditoDeReais('4.99'));
    }
}
