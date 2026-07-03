<?php

namespace App\Domain\Coleta\Sefaz;

use RuntimeException;

/**
 * Falha na extração do portal da SEFAZ (ADR-002).
 *
 * O `tipo` classifica a falha e determina o tratamento na fila/reprocessamento:
 *  - TRANSITORIA: portal fora do ar, timeout, rate limit → retry com backoff.
 *  - ESTRUTURAL: parser não reconhece o HTML / captcha → parar e alertar (sem retry automático).
 *  - NEGOCIO: cupom inexistente/cancelado na SEFAZ → rejeitar, sem retry.
 */
final class SefazExtracaoException extends RuntimeException
{
    public const TRANSITORIA = 'transitoria';

    public const ESTRUTURAL = 'estrutural';

    public const NEGOCIO = 'negocio';

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

    public static function negocio(string $mensagem): self
    {
        return new self(self::NEGOCIO, $mensagem);
    }

    public function reprocessavel(): bool
    {
        return $this->tipo !== self::NEGOCIO;
    }
}
