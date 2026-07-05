<?php

namespace App\Domain\Coleta;

use App\Domain\Cashback\ExtratoCarteira;
use App\Models\Cupom;
use App\Models\CupomItem;
use App\Support\Formato;

/**
 * Read-model da tela de detalhe do cupom (STORY-034) — leitura, sem escrita.
 *
 * Monta o cabeçalho (estabelecimento com fallback, CNPJ, data pt-BR, valor total, status) e a
 * lista de itens já formatados na borda de apresentação. Sem PII (ADR-006): usa só dados do
 * cupom/itens; nunca CPF. O nome do estabelecimento (novo, STORY-034) degrada com fallback.
 */
final class DetalheCupom
{
    /** Rótulo + variante de badge (DS) por status do cupom (ADR-002). */
    private const STATUS = [
        Cupom::STATUS_PENDENTE => ['label' => 'Processando', 'variante' => 'info'],
        Cupom::STATUS_EXTRAINDO => ['label' => 'Processando', 'variante' => 'info'],
        Cupom::STATUS_VALIDADO => ['label' => 'Validado', 'variante' => 'positive'],
        Cupom::STATUS_FALHA => ['label' => 'Não validado', 'variante' => 'negative'],
        Cupom::STATUS_REJEITADO => ['label' => 'Recusado', 'variante' => 'negative'],
    ];

    /**
     * @return array{estabelecimento: string, localizacao: ?string, cnpj: string, data: string, valor_total: ?string,
     *     status: array{codigo: string, label: string, variante: string},
     *     itens: list<array{descricao: string, quantidade: string, unidade: string, valor_unitario: string, valor_total: string}>}
     */
    public function para(Cupom $cupom): array
    {
        $nome = $cupom->nome_emitente;

        return [
            'estabelecimento' => ($nome !== null && $nome !== '') ? $nome : ExtratoCarteira::ESTABELECIMENTO_FALLBACK,
            'localizacao' => self::localizacao($cupom->municipio_emitente, $cupom->uf_emitente),
            'cnpj' => Formato::cnpj($cupom->cnpj_emitente),
            'data' => Formato::data($cupom->data_emissao),
            'valor_total' => $cupom->valor_total !== null ? self::reais((string) $cupom->valor_total) : null,
            'status' => $this->status($cupom->status),
            'itens' => $cupom->itens
                ->sortBy('sequencia')
                ->map(fn (CupomItem $i) => [
                    'descricao' => (string) $i->descricao,
                    'quantidade' => self::quantidade((string) $i->quantidade),
                    'unidade' => (string) $i->unidade,
                    'valor_unitario' => self::reais((string) $i->valor_unitario),
                    'valor_total' => self::reais((string) $i->valor_total),
                ])
                ->values()
                ->all(),
        ];
    }

    /**
     * "Cidade, UF" do estabelecimento — só o que a extração trouxer (ambos opcionais):
     * "Itu, SP" / "Itu" / "SP" / null. Sem dado, a UI não renderiza a linha.
     */
    private static function localizacao(?string $municipio, ?string $uf): ?string
    {
        $partes = array_values(array_filter([
            $municipio !== null && $municipio !== '' ? $municipio : null,
            $uf !== null && $uf !== '' ? $uf : null,
        ]));

        return $partes === [] ? null : implode(', ', $partes);
    }

    /** @return array{codigo: string, label: string, variante: string} */
    private function status(string $codigo): array
    {
        $meta = self::STATUS[$codigo] ?? ['label' => 'Processando', 'variante' => 'info'];

        return ['codigo' => $codigo, 'label' => $meta['label'], 'variante' => $meta['variante']];
    }

    /** Decimal ("14.85") → "14,85" (pt-BR). */
    private static function reais(string $decimal): string
    {
        return number_format((float) $decimal, 2, ',', '.');
    }

    /** Quantidade ("1.0000"/"0.5060") → "1"/"0,506" (sem zeros à direita, decimal pt-BR). */
    private static function quantidade(string $decimal): string
    {
        $n = rtrim(rtrim(number_format((float) $decimal, 4, ',', ''), '0'), ',');

        return $n === '' ? '0' : $n;
    }
}
