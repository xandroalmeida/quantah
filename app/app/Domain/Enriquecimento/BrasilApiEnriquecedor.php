<?php

namespace App\Domain\Enriquecimento;

/**
 * Fonte primária de CNPJ: BrasilAPI (ADR-012). Provada ao vivo no spike STORY-039
 * (todos os campos exigidos, ~0,1–0,5s, rate limit generoso).
 */
final class BrasilApiEnriquecedor extends RfbOpenDataEnriquecedor
{
    private const BASE = 'https://brasilapi.com.br/api/cnpj/v1/';

    protected function url(string $cnpj): string
    {
        return self::BASE.$cnpj;
    }

    protected function fonte(): string
    {
        return 'brasilapi';
    }
}
