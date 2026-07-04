<?php

namespace App\Domain\Saque;

use RuntimeException;

/**
 * Solicitação de saque inválida por regra de negócio (valor abaixo do mínimo, CPF inválido,
 * chave PIX que não confere com o CPF, comprovante ausente). Erro de domínio — vira 422 na
 * borda (ver error-handling.md), com mensagem amigável.
 */
class SaqueInvalidoException extends RuntimeException
{
    public static function valorAbaixoDoMinimo(int $minimoCentavos): self
    {
        return new self('O valor mínimo de saque é de R$ '.number_format($minimoCentavos / 100, 2, ',', '.').'.');
    }

    public static function cpfInvalido(): self
    {
        return new self('CPF inválido.');
    }

    public static function chaveNaoConfere(): self
    {
        return new self('A chave PIX precisa ser o CPF do titular do saque.');
    }

    public static function comprovanteObrigatorio(): self
    {
        return new self('Informe o comprovante do PIX para marcar como pago.');
    }
}
