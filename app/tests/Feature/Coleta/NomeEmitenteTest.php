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
 * Captura do nome do estabelecimento (emitente) na extração (STORY-034 · CA-1).
 * Estende o modelo canônico (ADR-001) com `nome_emitente` — não é PII do consumidor (ADR-006).
 */
class NomeEmitenteTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    private function servico(FakeSefazSpFetcher $fetcher): IngestaoCupomService
    {
        return new IngestaoCupomService(new SpSefazAdapter($fetcher, new AnonimizadorCpf));
    }

    /** CA-1 — cupom validado persiste o nome do estabelecimento vindo da extração. */
    public function test_ingestao_persiste_nome_do_emitente(): void
    {
        $resultado = $this->servico(new FakeSefazSpFetcher)->ingerir(self::CHAVE_SP);

        $this->assertSame(Cupom::STATUS_VALIDADO, $resultado->cupom->status);
        $this->assertSame('Mercado Modelo Ltda', $resultado->cupom->nome_emitente);
        $this->assertDatabaseHas('cupons', [
            'chave_acesso' => self::CHAVE_SP,
            'nome_emitente' => 'Mercado Modelo Ltda',
        ]);
    }

    /** CA-1 — quando a fonte não traz o nome, o campo fica nulo (UI degrada com fallback). */
    public function test_nome_emitente_ausente_fica_nulo(): void
    {
        $fetcher = (new FakeSefazSpFetcher)->comPayload([
            'data_emissao' => now('America/Sao_Paulo')->subDay()->format('Y-m-d H:i:s'),
            'valor_total' => '10.00',
            'numero' => 1,
            'serie' => 1,
            // sem 'nome_emitente'
            'itens' => [[
                'descricao' => 'PAO', 'quantidade' => '1.0000',
                'unidade' => 'UN', 'valor_unitario' => '10.00', 'valor_total' => '10.00',
            ]],
        ]);

        $resultado = $this->servico($fetcher)->ingerir(self::CHAVE_SP);

        $this->assertSame(Cupom::STATUS_VALIDADO, $resultado->cupom->status);
        $this->assertNull($resultado->cupom->nome_emitente);
    }
}
