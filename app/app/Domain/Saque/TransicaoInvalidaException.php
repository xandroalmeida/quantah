<?php

namespace App\Domain\Saque;

use App\Models\Saque;
use RuntimeException;

/**
 * Transição de estado do saque não permitida pela máquina de estados (ADR-005) — ex.:
 * pagar sem passar pela análise, ou rejeitar um saque já rejeitado. Protege a integridade
 * do fluxo e a idempotência (não estornar duas vezes).
 */
class TransicaoInvalidaException extends RuntimeException
{
    public static function de(Saque $saque, string $acao): self
    {
        return new self("Não é possível {$acao} um saque no estado '{$saque->status}'.");
    }
}
