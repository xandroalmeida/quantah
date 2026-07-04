<?php

namespace App\Domain\Saque;

/**
 * CPF — value object do KYC mínimo do saque (STORY-017, ADR-005).
 *
 * Normaliza para o **valor canônico sem máscara** (só dígitos — ver `database-method.md`)
 * e valida os **dígitos verificadores** (mod 11). É a base da titularidade: no MVP a chave
 * PIX é do tipo CPF, então CPF válido do titular = chave verificável por construção.
 *
 * Puro, sem dependência externa (a validação de CPF é um algoritmo fechado e testável).
 */
final class Cpf
{
    /** Remove tudo que não for dígito (tira máscara `.`/`-`/espaços). */
    public static function apenasDigitos(string $valor): string
    {
        return preg_replace('/\D/', '', $valor) ?? '';
    }

    /** Valida um CPF (aceita com ou sem máscara): 11 dígitos, não-repetidos, DV correto. */
    public static function ehValido(string $valor): bool
    {
        $cpf = self::apenasDigitos($valor);

        if (strlen($cpf) !== 11) {
            return false;
        }

        // Repetidos (00000000000, 11111111111, …) passam no DV mas são inválidos por definição.
        if (preg_match('/^(\d)\1{10}$/', $cpf) === 1) {
            return false;
        }

        return self::digitoVerificador($cpf, 9) === (int) $cpf[9]
            && self::digitoVerificador($cpf, 10) === (int) $cpf[10];
    }

    /**
     * Calcula o dígito verificador na posição $pos (9 = 1º DV, 10 = 2º DV) por mod 11.
     */
    private static function digitoVerificador(string $cpf, int $pos): int
    {
        $soma = 0;
        $peso = $pos + 1; // 10 para o 1º DV, 11 para o 2º
        for ($i = 0; $i < $pos; $i++) {
            $soma += ((int) $cpf[$i]) * $peso;
            $peso--;
        }

        $resto = $soma % 11;

        return $resto < 2 ? 0 : 11 - $resto;
    }
}
