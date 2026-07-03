<?php

namespace App\Domain\Coleta\Events;

use Illuminate\Foundation\Events\Dispatchable;

/**
 * Fato de domínio: um cupom se tornou **válido-único-novo** (status `validado`) — ADR-003.
 *
 * Produzido pela fronteira de Coleta ao fim da validação. É o gancho que o contexto de
 * Cashback (STORY-015) consome para creditar o Colaborador, sem acoplar Coleta a pagamento.
 * Carrega só o uuid técnico do cupom (sem PII); o listener recarrega o que precisar.
 */
final class CupomValidado
{
    use Dispatchable;

    public function __construct(public readonly string $cupomId) {}
}
