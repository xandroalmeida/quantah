<?php

namespace Tests\Unit\Support;

use App\Support\Formato;
use Illuminate\Support\Carbon;
use PHPUnit\Framework\TestCase;

/**
 * Formatos brasileiros (STORY-020, CA-4 · ADR-011): moeda `R$ 1.234,56`, data `dd/mm/aaaa`
 * e fuso de exibição `America/Sao_Paulo`. A persistência segue em UTC — este helper só
 * formata a exibição, convertendo o fuso na borda.
 */
class FormatoTest extends TestCase
{
    /** (a) feliz — centavos viram moeda pt-BR com milhar e decimal. */
    public function test_moeda_formata_com_separadores_ptbr(): void
    {
        $this->assertSame('1.234,56', Formato::moeda(123456));
    }

    /** (d) borda — zero, valor sub-real e valor grande. */
    public function test_moeda_bordas(): void
    {
        $this->assertSame('0,00', Formato::moeda(0));
        $this->assertSame('0,05', Formato::moeda(5));
        $this->assertSame('999.999,99', Formato::moeda(99999999));
    }

    /** (b) inválido/atípico — valor negativo (estorno) mantém o sinal. */
    public function test_moeda_negativa(): void
    {
        $this->assertSame('-1,00', Formato::moeda(-100));
    }

    /** (a) feliz — versão com símbolo. */
    public function test_moeda_com_simbolo(): void
    {
        $this->assertSame('R$ 20,00', Formato::moedaComSimbolo(2000));
    }

    /** (a) feliz — datetime UTC exibido no fuso de São Paulo (−3h). */
    public function test_data_hora_converte_para_sao_paulo(): void
    {
        $utc = Carbon::parse('2026-01-15 10:00:00', 'UTC');
        $this->assertSame('15/01/2026 07:00', Formato::dataHora($utc));
    }

    /** (d) borda — conversão de fuso que atravessa a meia-noite muda o dia exibido. */
    public function test_data_hora_vira_o_dia_na_conversao(): void
    {
        $utc = Carbon::parse('2026-01-15 02:00:00', 'UTC');
        $this->assertSame('14/01/2026 23:00', Formato::dataHora($utc));
    }

    /** (a) feliz + (d) borda — data curta no fuso SP; null vira string vazia. */
    public function test_data_curta_e_null(): void
    {
        $utc = Carbon::parse('2026-07-02 01:00:00', 'UTC'); // SP: 01/07 22:00
        $this->assertSame('01/07/2026', Formato::data($utc));
        $this->assertSame('', Formato::data(null));
        $this->assertSame('', Formato::dataHora(null));
    }
}
