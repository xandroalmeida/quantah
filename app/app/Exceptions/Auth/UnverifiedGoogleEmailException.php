<?php

namespace App\Exceptions\Auth;

use RuntimeException;

/**
 * O Google não devolveu um e-mail verificado (ou nenhum e-mail). Fail-secure do ADR-010:
 * não criamos nem vinculamos conta — o Google é a autoridade sobre a posse do e-mail.
 */
class UnverifiedGoogleEmailException extends RuntimeException
{
}
