<?php

namespace App\Domain\Enriquecimento;

/**
 * Fonte de fallback de CNPJ: Minha Receita (ADR-012). Mesmo shape da RFB aberta que a
 * BrasilAPI; open source e self-hostável (caminho de evolução para independência).
 */
final class MinhaReceitaEnriquecedor extends RfbOpenDataEnriquecedor
{
    private const BASE = 'https://minhareceita.org/';

    protected function url(string $cnpj): string
    {
        return self::BASE.$cnpj;
    }

    protected function fonte(): string
    {
        return 'minhareceita';
    }
}
