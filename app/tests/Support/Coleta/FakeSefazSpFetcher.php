<?php

namespace Tests\Support\Coleta;

use App\Domain\Coleta\ChaveAcesso;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use App\Domain\Coleta\Sefaz\SefazSpFetcher;

/**
 * Fake do portal da SEFAZ-SP para o spike (STORY-008) — simula portal OK, instável
 * (falha transitória/estrutural) e retorno com CPF, sem tocar a rede (princípio #6/#10).
 */
final class FakeSefazSpFetcher implements SefazSpFetcher
{
    /** @var list<SefazExtracaoException> falhas a lançar nas próximas chamadas, em ordem */
    private array $falhas = [];

    /** @var array<string, mixed>|null payload a retornar quando não houver falha */
    private ?array $payload = null;

    public int $chamadas = 0;

    /** @param array<string, mixed> $payload */
    public function comPayload(array $payload): self
    {
        $this->payload = $payload;

        return $this;
    }

    public function falharCom(SefazExtracaoException ...$falhas): self
    {
        $this->falhas = array_values($falhas);

        return $this;
    }

    public function buscar(ChaveAcesso $chave, ?string $qrConteudo = null): array
    {
        $this->chamadas++;

        if ($this->falhas !== []) {
            throw array_shift($this->falhas);
        }

        return $this->payload ?? self::payloadPadrao();
    }

    /**
     * Payload padrão de um cupom de SP — inclui CPF de propósito para exercitar o
     * descarte (ADR-006). O CPF NÃO deve sobreviver à normalização.
     *
     * @return array<string, mixed>
     */
    public static function payloadPadrao(): array
    {
        return [
            // Data recente (dentro da janela de validade — STORY-035) para o caminho feliz
            // seguir válido; testes de expiração passam uma data antiga via comPayload().
            'data_emissao' => now('America/Sao_Paulo')->subDay()->format('Y-m-d H:i:s'),
            'nome_emitente' => 'Mercado Modelo Ltda',
            'endereco_emitente' => 'Rua das Flores, 123, Centro',
            'municipio_emitente' => 'Sao Paulo',
            'uf_emitente' => 'SP',
            'valor_total' => '87.90',
            'numero' => 123456,
            'serie' => 1,
            'cpf_consumidor' => '390.533.447-05',
            'observacao' => 'Consumidor informou CPF 390.533.447-05 na nota',
            'itens' => [
                [
                    'descricao' => 'ARROZ TIPO 1 5KG',
                    'codigo_loja' => 'A123',
                    'gtin' => '7891234567895',
                    'quantidade' => '1.0000',
                    'unidade' => 'UN',
                    'valor_unitario' => '27.90',
                    'valor_total' => '27.90',
                ],
                [
                    'descricao' => 'FEIJAO CARIOCA 1KG',
                    'codigo_loja' => 'B456',
                    'gtin' => null,
                    'quantidade' => '2.0000',
                    'unidade' => 'UN',
                    'valor_unitario' => '30.00',
                    'valor_total' => '60.00',
                ],
            ],
        ];
    }
}
