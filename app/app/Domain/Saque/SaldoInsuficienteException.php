<?php

namespace App\Domain\Saque;

use RuntimeException;

/**
 * Tentativa de sacar mais do que o saldo disponível. Verificada **sob lock** da carteira
 * (impede saque duplo por corrida). Erro de domínio → 422 na borda.
 */
class SaldoInsuficienteException extends RuntimeException
{
    public function __construct()
    {
        parent::__construct('Saldo insuficiente para este saque.');
    }
}
