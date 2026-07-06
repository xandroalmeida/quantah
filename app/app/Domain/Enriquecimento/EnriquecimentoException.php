<?php

namespace App\Domain\Enriquecimento;

use RuntimeException;

/**
 * Falha ao consultar a fonte pública de CNPJ (ADR-012). Espelha o padrão de falha
 * tipada da SEFAZ (ADR-002); "negócio" (CNPJ inexistente/sem CNAE) NÃO é exceção —
 * volta como `status` no DTO EmitenteEnriquecido.
 *
 *  - TRANSITORIA: timeout, 5xx, rate limit (429) → retry com backoff / troca de fonte.
 *  - ESTRUTURAL: contrato mudou (corpo inesperado) → alerta, sem retry automático.
 */
final class EnriquecimentoException extends RuntimeException
{
    public const TRANSITORIA = 'transitoria';

    public const ESTRUTURAL = 'estrutural';

    private function __construct(public readonly string $tipo, string $mensagem)
    {
        parent::__construct($mensagem);
    }

    public static function transitoria(string $mensagem): self
    {
        return new self(self::TRANSITORIA, $mensagem);
    }

    public static function estrutural(string $mensagem): self
    {
        return new self(self::ESTRUTURAL, $mensagem);
    }
}
