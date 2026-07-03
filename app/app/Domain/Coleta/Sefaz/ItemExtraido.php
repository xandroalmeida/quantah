<?php

namespace App\Domain\Coleta\Sefaz;

/** Item normalizado de um cupom (ADR-001). GTIN/código da loja são só armazenados; o matching é ADR-004 (fora da onda). */
final class ItemExtraido
{
    public function __construct(
        public readonly int $sequencia,
        public readonly string $descricao,
        public readonly ?string $codigoLoja,
        public readonly ?string $gtin,
        public readonly string $quantidade,
        public readonly string $unidade,
        public readonly string $valorUnitario,
        public readonly string $valorTotal,
    ) {}
}
