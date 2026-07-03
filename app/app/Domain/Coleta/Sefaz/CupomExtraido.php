<?php

namespace App\Domain\Coleta\Sefaz;

/**
 * DTO canônico do cupom extraído (ADR-001) — a saída do adaptador, já normalizada
 * e SEM PII (o CPF é descartado pelo AnonimizadorCpf antes de chegar aqui — ADR-006).
 *
 * @param  list<ItemExtraido>  $itens
 */
final class CupomExtraido
{
    /** @param list<ItemExtraido> $itens */
    public function __construct(
        public readonly ?string $dataEmissao,
        public readonly string $valorTotal,
        public readonly int $numero,
        public readonly int $serie,
        public readonly array $itens,
    ) {}
}
