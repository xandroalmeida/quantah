<?php

namespace App\Support;

use DateTimeInterface;
use Illuminate\Support\Carbon;

/**
 * Formatos brasileiros de exibição (ADR-011, quality-standards §5.1): moeda `R$ 1.234,56`,
 * data `dd/mm/aaaa` e datetime no fuso `America/Sao_Paulo`.
 *
 * Ponto único para não espalhar `number_format`/`->format` pelas telas. A **persistência**
 * segue em UTC / ISO 8601 (stacks/database); aqui só se converte o fuso para **exibição**.
 */
final class Formato
{
    /** Fuso de exibição do Brasil (persistência continua em UTC). */
    public const FUSO = 'America/Sao_Paulo';

    /** Centavos inteiros → "1.234,56" (milhar `.`, decimal `,`), sem símbolo. */
    public static function moeda(int $centavos): string
    {
        return number_format($centavos / 100, 2, ',', '.');
    }

    /** Centavos inteiros → "R$ 1.234,56". */
    public static function moedaComSimbolo(int $centavos): string
    {
        return 'R$ '.self::moeda($centavos);
    }

    /** Datetime (UTC na base) → "dd/mm/aaaa" no fuso de São Paulo. Null → "". */
    public static function data(?DateTimeInterface $dt): string
    {
        return $dt === null ? '' : self::emSaoPaulo($dt)->format('d/m/Y');
    }

    /** CNPJ de 14 dígitos → "12.345.678/0001-95". Entrada fora do padrão volta como veio. */
    public static function cnpj(?string $cnpj): string
    {
        if ($cnpj === null || ! preg_match('/^\d{14}$/', $cnpj)) {
            return (string) $cnpj;
        }

        return preg_replace('/^(\d{2})(\d{3})(\d{3})(\d{4})(\d{2})$/', '$1.$2.$3/$4-$5', $cnpj);
    }

    /** Datetime (UTC na base) → "dd/mm/aaaa HH:MM" no fuso de São Paulo. Null → "". */
    public static function dataHora(?DateTimeInterface $dt): string
    {
        return $dt === null ? '' : self::emSaoPaulo($dt)->format('d/m/Y H:i');
    }

    private static function emSaoPaulo(DateTimeInterface $dt): Carbon
    {
        return Carbon::instance($dt)->setTimezone(self::FUSO);
    }
}
