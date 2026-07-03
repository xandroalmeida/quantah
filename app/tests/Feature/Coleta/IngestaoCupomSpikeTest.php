<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\AnonimizadorCpf;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\ResultadoIngestao;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use App\Domain\Coleta\Sefaz\SpSefazAdapter;
use App\Models\Cupom;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Support\Coleta\FakeSefazSpFetcher;
use Tests\TestCase;

/**
 * Spike vertical da coleta (STORY-008) — prova, para UMA chave de SP, o caminho
 * fim a fim das ADR-001/002/003/006:
 *   parse da chave → extração (adaptador fake) → normalização no modelo canônico →
 *   dedup idempotente → CPF nunca persistido.
 */
class IngestaoCupomSpikeTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    private const CHAVE_RJ = '33260112345678000195650010001234561000000014';

    private function servico(FakeSefazSpFetcher $fetcher): IngestaoCupomService
    {
        return new IngestaoCupomService(
            new SpSefazAdapter($fetcher, new AnonimizadorCpf)
        );
    }

    /** CA-3: caminho feliz — a chave percorre parse → extração → normalização → persistência. */
    public function test_caminho_feliz_valida_e_normaliza_o_cupom(): void
    {
        $resultado = $this->servico(new FakeSefazSpFetcher)->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::ACEITO, $resultado->situacao);

        $cupom = $resultado->cupom;
        $this->assertSame(Cupom::STATUS_VALIDADO, $cupom->status);
        $this->assertSame('35', $cupom->uf);
        $this->assertSame('65', $cupom->modelo);
        $this->assertSame(123456, (int) $cupom->numero);
        $this->assertSame('87.90', (string) $cupom->valor_total);
        $this->assertCount(2, $cupom->itens);
        $this->assertSame('ARROZ TIPO 1 5KG', $cupom->itens->first()->descricao);
        $this->assertSame('7891234567895', $cupom->itens->first()->gtin);

        $this->assertDatabaseCount('cupons', 1);
        $this->assertDatabaseCount('cupom_itens', 2);
    }

    /** CA-3: dedup idempotente — reenviar a mesma chave não gera segundo registro. */
    public function test_reenvio_da_mesma_chave_e_idempotente(): void
    {
        $servico = $this->servico(new FakeSefazSpFetcher);

        $primeiro = $servico->ingerir(self::CHAVE_SP);
        $segundo = $servico->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::ACEITO, $primeiro->situacao);
        $this->assertSame(ResultadoIngestao::DUPLICADO, $segundo->situacao);
        $this->assertSame($primeiro->cupom->id, $segundo->cupom->id);

        $this->assertDatabaseCount('cupons', 1);
        $this->assertDatabaseCount('cupom_itens', 2);
    }

    /** ADR-003: a unicidade é garantida pelo banco (à prova de corrida), não só pelo app. */
    public function test_unicidade_da_chave_e_garantida_pelo_banco(): void
    {
        $this->servico(new FakeSefazSpFetcher)->ingerir(self::CHAVE_SP);

        $this->expectException(QueryException::class);
        DB::table('cupons')->insert([
            'id' => Str::uuid()->toString(),
            'chave_acesso' => self::CHAVE_SP,
            'uf' => '35', 'ano_mes' => '2601', 'cnpj_emitente' => '12345678000195',
            'modelo' => '65', 'status' => 'pendente', 'origem' => 'scan',
            'created_at' => now(), 'updated_at' => now(),
        ]);
    }

    /** CA-3: chave malformada é rejeitada sem virar dado nem tocar o portal. */
    public function test_chave_malformada_e_rejeitada(): void
    {
        $fetcher = new FakeSefazSpFetcher;
        $resultado = $this->servico($fetcher)->ingerir('123-nao-e-chave');

        $this->assertSame(ResultadoIngestao::REJEITADO, $resultado->situacao);
        $this->assertSame('chave_malformada', $resultado->motivo);
        $this->assertSame(0, $fetcher->chamadas, 'não deve acessar o portal para chave inválida');
        $this->assertDatabaseCount('cupons', 0);
    }

    /** CA-3 / ADR-003: chave de outro estado (RJ) é rejeitada por escopo, sem extração. */
    public function test_chave_de_outro_estado_e_rejeitada_por_escopo(): void
    {
        $fetcher = new FakeSefazSpFetcher;
        $resultado = $this->servico($fetcher)->ingerir(self::CHAVE_RJ);

        $this->assertSame(ResultadoIngestao::REJEITADO, $resultado->situacao);
        $this->assertSame('fora_de_escopo_uf', $resultado->motivo);
        $this->assertSame(0, $fetcher->chamadas);
        $this->assertDatabaseCount('cupons', 0);
    }

    /** CA-4: falha de extração deixa o cupom reprocessável (não perde o cupom). */
    public function test_falha_transitoria_deixa_o_cupom_reprocessavel(): void
    {
        $fetcher = (new FakeSefazSpFetcher)->falharCom(
            SefazExtracaoException::transitoria('portal indisponível (503)')
        );

        $resultado = $this->servico($fetcher)->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::FALHA_EXTRACAO, $resultado->situacao);
        $this->assertSame(Cupom::STATUS_FALHA, $resultado->cupom->status);
        $this->assertSame('transitoria', $resultado->cupom->motivo_falha);
        $this->assertDatabaseCount('cupons', 1); // o cupom foi preservado
    }

    /** CA-4: quebra de layout/captcha é falha estrutural — reprocessável, marcada para alerta. */
    public function test_falha_estrutural_por_layout_inesperado(): void
    {
        // Payload sem o campo `itens` → o adaptador detecta layout inesperado.
        $fetcher = (new FakeSefazSpFetcher)->comPayload([
            'data_emissao' => '2026-01-15 14:32:00',
            'valor_total' => '10.00',
            'numero' => 1,
            'serie' => 1,
        ]);

        $resultado = $this->servico($fetcher)->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::FALHA_EXTRACAO, $resultado->situacao);
        $this->assertSame('estrutural', $resultado->cupom->motivo_falha);
    }

    /** CA-4 / ADR-002: reprocessar um cupom em falha o valida, sem duplicar. */
    public function test_reprocessamento_valida_o_cupom_apos_falha(): void
    {
        // Primeira chamada falha (transitória), segunda (reprocesso) tem sucesso.
        $fetcher = (new FakeSefazSpFetcher)->falharCom(
            SefazExtracaoException::transitoria('timeout')
        );
        $servico = $this->servico($fetcher);

        $primeiro = $servico->ingerir(self::CHAVE_SP);
        $this->assertSame(ResultadoIngestao::FALHA_EXTRACAO, $primeiro->situacao);

        $reprocesso = $servico->reprocessar(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::ACEITO, $reprocesso->situacao);
        $this->assertSame(Cupom::STATUS_VALIDADO, $reprocesso->cupom->status);
        $this->assertDatabaseCount('cupons', 1);      // não duplicou
        $this->assertDatabaseCount('cupom_itens', 2);
    }

    /** CA-5 / ADR-006: o cupom traz CPF, mas NADA de CPF é persistido no modelo canônico. */
    public function test_cpf_nunca_e_persistido_em_claro(): void
    {
        // O payload padrão do fake inclui CPF de propósito.
        $this->servico(new FakeSefazSpFetcher)->ingerir(self::CHAVE_SP);

        // Nenhuma coluna de cupom nem de item pode conter o CPF (formatado ou não).
        $cupom = Cupom::with('itens')->firstOrFail();
        $serializado = json_encode($cupom->toArray(), JSON_UNESCAPED_UNICODE);

        $this->assertFalse(AnonimizadorCpf::contemCpf($serializado), 'CPF vazou no cupom persistido');
        $this->assertStringNotContainsString('390.533.447-05', $serializado);
        $this->assertStringNotContainsString('39053344705', $serializado);

        // Prova estrutural: a tabela sequer tem coluna de CPF (ADR-006).
        $colunas = Schema::getColumnListing('cupons');
        foreach ($colunas as $coluna) {
            $this->assertStringNotContainsString('cpf', strtolower($coluna));
        }
    }
}
