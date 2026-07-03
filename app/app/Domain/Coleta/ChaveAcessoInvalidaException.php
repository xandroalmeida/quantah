<?php

namespace App\Domain\Coleta;

use InvalidArgumentException;

/** Chave de acesso malformada ou com dígito verificador inválido (ADR-003). */
final class ChaveAcessoInvalidaException extends InvalidArgumentException {}
