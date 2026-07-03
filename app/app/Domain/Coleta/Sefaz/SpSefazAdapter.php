<?php

namespace App\Domain\Coleta\Sefaz;

use App\Domain\Coleta\AnonimizadorCpf;
use App\Domain\Coleta\ChaveAcesso;

/**
 * Adaptador de extração da SEFAZ-SP (ADR-001/002/006).
 *
 * Orquestra: busca o payload bruto (SefazSpFetcher) → descarta CPF (AnonimizadorCpf)
 * → normaliza no DTO canônico. Se o payload não tiver a forma esperada, trata como
 * falha ESTRUTURAL (quebra de layout / captcha) — não insiste, sinaliza.
 */
final class SpSefazAdapter implements SefazAdapter
{
    public function __construct(
        private readonly SefazSpFetcher $fetcher,
        private readonly AnonimizadorCpf $anonimizador,
    ) {}

    public function uf(): string
    {
        return '35';
    }

    public function extrair(ChaveAcesso $chave, ?string $qrConteudo = null): CupomExtraido
    {
        // 1. Payload bruto do portal (pode conter CPF).
        $bruto = $this->fetcher->buscar($chave, $qrConteudo);

        // 2. LGPD (ADR-006): descarta CPF ANTES de qualquer normalização/persistência/log.
        $limpo = $this->anonimizador->limpar($bruto);

        // 3. Normaliza no modelo canônico (ADR-001). Estrutura inesperada = quebra de layout.
        return $this->normalizar($limpo);
    }

    /** @param array<string, mixed> $p */
    private function normalizar(array $p): CupomExtraido
    {
        foreach (['data_emissao', 'valor_total', 'numero', 'serie', 'itens'] as $campo) {
            if (! array_key_exists($campo, $p)) {
                throw SefazExtracaoException::estrutural(
                    "Layout inesperado da SEFAZ-SP: campo \"{$campo}\" ausente."
                );
            }
        }

        if (! is_array($p['itens']) || $p['itens'] === []) {
            throw SefazExtracaoException::estrutural('Layout inesperado da SEFAZ-SP: cupom sem itens.');
        }

        $itens = [];
        foreach (array_values($p['itens']) as $i => $item) {
            $itens[] = new ItemExtraido(
                sequencia: $i + 1,
                descricao: (string) ($item['descricao'] ?? ''),
                codigoLoja: isset($item['codigo_loja']) ? (string) $item['codigo_loja'] : null,
                gtin: isset($item['gtin']) ? (string) $item['gtin'] : null,
                quantidade: (string) ($item['quantidade'] ?? '0'),
                unidade: (string) ($item['unidade'] ?? 'UN'),
                valorUnitario: (string) ($item['valor_unitario'] ?? '0'),
                valorTotal: (string) ($item['valor_total'] ?? '0'),
            );
        }

        return new CupomExtraido(
            dataEmissao: $p['data_emissao'] !== null ? (string) $p['data_emissao'] : null,
            valorTotal: (string) $p['valor_total'],
            numero: (int) $p['numero'],
            serie: (int) $p['serie'],
            itens: $itens,
        );
    }
}
