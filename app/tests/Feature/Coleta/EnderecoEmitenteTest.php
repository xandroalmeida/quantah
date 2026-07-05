<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\AnonimizadorCpf;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\Sefaz\SpSefazAdapter;
use App\Models\Cupom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Coleta\FakeSefazSpFetcher;
use Tests\TestCase;

/**
 * Captura do endereço/município/UF do estabelecimento (emitente) na extração — extensão
 * da STORY-034. Como `nome_emitente`, é dado público do emitente, não PII (ADR-006); quando
 * a fonte não traz o bloco de endereço, os campos ficam nulos (UI degrada com fallback).
 */
class EnderecoEmitenteTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    private function servico(FakeSefazSpFetcher $fetcher): IngestaoCupomService
    {
        return new IngestaoCupomService(new SpSefazAdapter($fetcher, new AnonimizadorCpf));
    }

    /** Cupom validado persiste endereço, município e UF do estabelecimento. */
    public function test_ingestao_persiste_endereco_do_emitente(): void
    {
        $resultado = $this->servico(new FakeSefazSpFetcher)->ingerir(self::CHAVE_SP);

        $this->assertSame(Cupom::STATUS_VALIDADO, $resultado->cupom->status);
        $this->assertSame('Rua das Flores, 123, Centro', $resultado->cupom->endereco_emitente);
        $this->assertSame('Sao Paulo', $resultado->cupom->municipio_emitente);
        $this->assertSame('SP', $resultado->cupom->uf_emitente);
        $this->assertDatabaseHas('cupons', [
            'chave_acesso' => self::CHAVE_SP,
            'endereco_emitente' => 'Rua das Flores, 123, Centro',
            'municipio_emitente' => 'Sao Paulo',
            'uf_emitente' => 'SP',
        ]);
    }

    /** Sem o bloco de endereço na fonte, os três campos ficam nulos. */
    public function test_endereco_ausente_fica_nulo(): void
    {
        $fetcher = (new FakeSefazSpFetcher)->comPayload([
            'data_emissao' => now('America/Sao_Paulo')->subDay()->format('Y-m-d H:i:s'),
            'valor_total' => '10.00',
            'numero' => 1,
            'serie' => 1,
            // sem endereco_emitente/municipio_emitente/uf_emitente
            'itens' => [[
                'descricao' => 'PAO', 'quantidade' => '1.0000',
                'unidade' => 'UN', 'valor_unitario' => '10.00', 'valor_total' => '10.00',
            ]],
        ]);

        $resultado = $this->servico($fetcher)->ingerir(self::CHAVE_SP);

        $this->assertSame(Cupom::STATUS_VALIDADO, $resultado->cupom->status);
        $this->assertNull($resultado->cupom->endereco_emitente);
        $this->assertNull($resultado->cupom->municipio_emitente);
        $this->assertNull($resultado->cupom->uf_emitente);
    }
}
