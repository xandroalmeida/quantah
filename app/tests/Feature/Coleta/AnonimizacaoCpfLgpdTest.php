<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\AnonimizadorCpf;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\ResultadoIngestao;
use App\Domain\Coleta\Sefaz\SpSefazAdapter;
use App\Jobs\ExtrairCupomJob;
use App\Models\Cupom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Log\Events\MessageLogged;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Tests\Support\Coleta\FakeSefazSpFetcher;
use Tests\TestCase;

/**
 * Conformidade LGPD da coleta (STORY-011, ADR-006) — a garantia de que NENHUM CPF em
 * claro entra ou permanece na base. Complementa o `AnonimizadorCpfTest` (unidade) e o
 * spike (`IngestaoCupomSpikeTest`) com a prova de ponta a ponta e a regressão que trava
 * o comportamento decidido (Opção A: descartar).
 *
 * Mapa CA → teste:
 *  - CA-1 (nunca em claro): test_cpf_do_retorno_sefaz_nunca_e_persistido,
 *                           test_cpf_no_conteudo_do_qr_nao_e_persistido
 *  - CA-2 (segue ADR-006: descarta): test_normalizacao_descarta_cpf_preservando_o_resto
 *  - CA-3 (dedup independe de CPF): test_dedup_por_chave_independe_da_presenca_de_cpf
 *  - CA-4 (logs não vazam): test_logs_do_pipeline_nao_vazam_cpf
 *  - CA-5 (regressão): test_regressao_banco_inteiro_sem_cpf_apos_pipeline
 */
class AnonimizacaoCpfLgpdTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    private const CHAVE_SP_2 = '35260112345678000195650010009999991000000018';

    /** CPF válido usado como isca — nunca pode sobreviver ao pipeline. */
    private const CPF_FORMATADO = '390.533.447-05';

    private const CPF_NUMERICO = '39053344705';

    private function servico(FakeSefazSpFetcher $fetcher): IngestaoCupomService
    {
        return new IngestaoCupomService(
            new SpSefazAdapter($fetcher, new AnonimizadorCpf)
        );
    }

    /**
     * Payload de SP com CPF plantado em TRÊS lugares distintos, incluindo uma coluna
     * que É persistida (`descricao` do item) — é o que dá dente à regressão do CA-5:
     * se o AnonimizadorCpf sumir do caminho, o CPF vaza para o banco e o teste quebra.
     *
     * @return array<string, mixed>
     */
    private function payloadComCpf(): array
    {
        return [
            'data_emissao' => '2026-01-15 14:32:00',
            'valor_total' => '87.90',
            'numero' => 123456,
            'serie' => 1,
            'cpf_consumidor' => self::CPF_FORMATADO,                       // chave dedicada de CPF
            'observacao' => 'Consumidor informou CPF '.self::CPF_FORMATADO.' na nota', // texto livre
            'itens' => [
                [
                    'descricao' => 'ARROZ TIPO 1 5KG CPF '.self::CPF_NUMERICO, // coluna PERSISTIDA
                    'codigo_loja' => 'A123',
                    'gtin' => '7891234567895',
                    'quantidade' => '1.0000',
                    'unidade' => 'UN',
                    'valor_unitario' => '87.90',
                    'valor_total' => '87.90',
                ],
            ],
        ];
    }

    /** Nenhuma linha de nenhuma tabela canônica pode conter o CPF (formatado ou não). */
    private function assertBancoSemCpf(): void
    {
        foreach (['cupons', 'cupom_itens'] as $tabela) {
            foreach (DB::table($tabela)->get() as $linha) {
                $serial = json_encode((array) $linha, JSON_UNESCAPED_UNICODE);
                $this->assertStringNotContainsString(self::CPF_FORMATADO, $serial, "CPF formatado vazou em {$tabela}");
                $this->assertStringNotContainsString(self::CPF_NUMERICO, $serial, "CPF numérico vazou em {$tabela}");
                $this->assertFalse(AnonimizadorCpf::contemCpf($serial), "Padrão de CPF vazou em {$tabela}: {$serial}");
            }
        }
    }

    /** CA-1 / CA-2: o CPF vem no retorno da SEFAZ mas não sobrevive à normalização. */
    public function test_cpf_do_retorno_sefaz_nunca_e_persistido(): void
    {
        $resultado = $this->servico((new FakeSefazSpFetcher)->comPayload($this->payloadComCpf()))
            ->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::ACEITO, $resultado->situacao);
        $this->assertBancoSemCpf();
    }

    /**
     * CA-1: o CPF pode vir no PRÓPRIO conteúdo do QR colado pelo usuário (não só no
     * retorno da SEFAZ). Esse conteúdo é persistido em `cupons.qr_conteudo` — tabela
     * canônica — então também precisa ser escovado antes de gravar.
     */
    public function test_cpf_no_conteudo_do_qr_nao_e_persistido(): void
    {
        // QR válido de SP (p=chave|...) com um CPF grudado como lixo de colagem.
        $qrComCpf = 'https://www.nfce.fazenda.sp.gov.br/qrcode?p='.self::CHAVE_SP.'|2|1|1|ABC123DEF&cpf='.self::CPF_FORMATADO;

        $this->servico((new FakeSefazSpFetcher)->comPayload($this->payloadComCpf()))
            ->ingerir($qrComCpf);

        $qrPersistido = (string) DB::table('cupons')->value('qr_conteudo');
        $this->assertStringNotContainsString(self::CPF_FORMATADO, $qrPersistido);
        $this->assertFalse(AnonimizadorCpf::contemCpf($qrPersistido));
        // A chave assinada (p=...) foi preservada — só o CPF saiu.
        $this->assertStringContainsString(self::CHAVE_SP.'|2|1|1|ABC123DEF', $qrPersistido);
        $this->assertBancoSemCpf();
    }

    /** CA-2: descarte (Opção A da ADR-006) — o CPF some, mas o resto do dado fica intacto. */
    public function test_normalizacao_descarta_cpf_preservando_o_resto(): void
    {
        $this->servico((new FakeSefazSpFetcher)->comPayload($this->payloadComCpf()))
            ->ingerir(self::CHAVE_SP);

        $item = Cupom::with('itens')->firstOrFail()->itens->first();

        // O CPF foi removido do texto, mas a descrição real do produto continua legível.
        $this->assertStringContainsString('ARROZ TIPO 1 5KG', $item->descricao);
        $this->assertStringNotContainsString(self::CPF_NUMERICO, $item->descricao);
        $this->assertSame('7891234567895', $item->gtin, 'o GTIN (13 dígitos) não pode ser confundido com CPF');
    }

    /**
     * CA-3: a deduplicação (ADR-003) é pela chave de 44 dígitos — funciona igual com ou
     * sem CPF no retorno. A presença de CPF nunca muda o desfecho da dedup.
     */
    public function test_dedup_por_chave_independe_da_presenca_de_cpf(): void
    {
        $comCpf = (new FakeSefazSpFetcher)->comPayload($this->payloadComCpf());
        $semCpf = new FakeSefazSpFetcher; // payload padrão também traz CPF; usamos p/ variar

        // 1ª ingestão da chave A (com CPF) → aceita.
        $this->assertSame(ResultadoIngestao::ACEITO, $this->servico($comCpf)->ingerir(self::CHAVE_SP)->situacao);
        // 2ª ingestão da MESMA chave A → duplicada, independente do CPF.
        $this->assertSame(ResultadoIngestao::DUPLICADO, $this->servico($semCpf)->ingerir(self::CHAVE_SP)->situacao);
        // Chave B (diferente) → aceita: a dedup separa por chave, não por CPF.
        $this->assertSame(ResultadoIngestao::ACEITO, $this->servico($comCpf)->ingerir(self::CHAVE_SP_2)->situacao);

        $this->assertDatabaseCount('cupons', 2); // A e B; a repetição de A não duplicou
        $this->assertBancoSemCpf();
    }

    /**
     * CA-4: nenhum caminho de log/erro do pipeline vaza CPF — nem no caminho feliz
     * (que não deve logar o payload), nem no único ponto que loga (`Job::failed`).
     */
    public function test_logs_do_pipeline_nao_vazam_cpf(): void
    {
        $capturado = [];
        Log::listen(function (MessageLogged $e) use (&$capturado) {
            $capturado[] = $e->message.' '.json_encode($e->context, JSON_UNESCAPED_UNICODE);
        });

        // Caminho feliz com CPF: não pode logar o payload.
        $resultado = $this->servico((new FakeSefazSpFetcher)->comPayload($this->payloadComCpf()))
            ->ingerir(self::CHAVE_SP);

        // Único ponto de log do pipeline: o dead-letter do Job. Exercita-o de fato.
        (new ExtrairCupomJob($resultado->cupom->id))->failed(
            new \RuntimeException('extração esgotou tentativas')
        );

        $this->assertNotEmpty($capturado, 'o Job::failed deveria ter registrado ao menos uma linha de log');
        foreach ($capturado as $linha) {
            $this->assertStringNotContainsString(self::CPF_FORMATADO, $linha, "CPF vazou em log: {$linha}");
            $this->assertStringNotContainsString(self::CPF_NUMERICO, $linha, "CPF vazou em log: {$linha}");
            $this->assertFalse(AnonimizadorCpf::contemCpf($linha), "padrão de CPF vazou em log: {$linha}");
        }
    }

    /**
     * CA-5: regressão de banco inteiro. Roda o pipeline com CPF plantado em coluna
     * persistida e varre TODAS as linhas de TODAS as tabelas canônicas. Se, no futuro,
     * qualquer caminho voltar a gravar CPF em claro, este teste quebra.
     */
    public function test_regressao_banco_inteiro_sem_cpf_apos_pipeline(): void
    {
        $this->servico((new FakeSefazSpFetcher)->comPayload($this->payloadComCpf()))
            ->ingerir(self::CHAVE_SP);

        $this->assertDatabaseCount('cupom_itens', 1);
        $this->assertBancoSemCpf();
    }
}
