<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\AnonimizadorCpf;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\ResultadoIngestao;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use App\Domain\Coleta\Sefaz\SefazSpFetcher;
use App\Jobs\ExtrairCupomJob;
use App\Models\Cupom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Queue;
use Tests\Support\Coleta\FakeSefazSpFetcher;
use Tests\TestCase;

/**
 * Núcleo de regra do épico (STORY-010, ADR-002/003): extração assíncrona, dedup
 * idempotente e classificação de falha. Cobertura alvo ≥98% nesta lógica.
 *
 * O portal é substituído por um fake no container (nenhuma rede em teste).
 */
class ExtrairCupomJobTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    private function comFetcher(FakeSefazSpFetcher $fake): FakeSefazSpFetcher
    {
        $this->app->instance(SefazSpFetcher::class, $fake);

        return $fake;
    }

    private function ingestao(): IngestaoCupomService
    {
        return $this->app->make(IngestaoCupomService::class);
    }

    private function cupomPendente(): Cupom
    {
        return Cupom::create([
            'chave_acesso' => self::CHAVE_SP,
            'uf' => '35', 'ano_mes' => '2601', 'cnpj_emitente' => '12345678000195',
            'modelo' => '65', 'status' => Cupom::STATUS_PENDENTE, 'origem' => 'scan',
        ]);
    }

    private function rodarJob(Cupom $cupom): void
    {
        (new ExtrairCupomJob($cupom->id))->handle($this->ingestao());
    }

    /** CA-1: o Job extrai, valida e persiste o modelo canônico. */
    public function test_job_extrai_valida_e_persiste(): void
    {
        $this->comFetcher(new FakeSefazSpFetcher);
        $cupom = $this->cupomPendente();

        $this->rodarJob($cupom);

        $cupom->refresh();
        $this->assertSame(Cupom::STATUS_VALIDADO, $cupom->status);
        $this->assertSame('87.90', (string) $cupom->valor_total);
        $this->assertCount(2, $cupom->itens);
        $this->assertNotNull($cupom->extraido_em);
    }

    /** CA-3: falha transitória relança para a fila retentar (backoff), cupom fica `falha`. */
    public function test_falha_transitoria_relanca_para_retry(): void
    {
        $this->comFetcher((new FakeSefazSpFetcher)->falharCom(
            SefazExtracaoException::transitoria('portal 503')
        ));
        $cupom = $this->cupomPendente();

        try {
            $this->rodarJob($cupom);
            $this->fail('Falha transitória deveria relançar para a fila retentar.');
        } catch (SefazExtracaoException $e) {
            $this->assertSame(SefazExtracaoException::TRANSITORIA, $e->tipo);
        }

        $this->assertSame(Cupom::STATUS_FALHA, $cupom->refresh()->status);
        $this->assertSame('transitoria', $cupom->motivo_falha);
    }

    /** CA-3: falha estrutural (layout/captcha) NÃO relança — cupom `falha`, para alerta. */
    public function test_falha_estrutural_nao_relanca(): void
    {
        $this->comFetcher((new FakeSefazSpFetcher)->comPayload([
            'data_emissao' => '2026-01-15 14:32:00', 'valor_total' => '10.00',
            'numero' => 1, 'serie' => 1, // sem `itens` → layout inesperado
        ]));
        $cupom = $this->cupomPendente();

        $this->rodarJob($cupom); // não deve lançar

        $this->assertSame(Cupom::STATUS_FALHA, $cupom->refresh()->status);
        $this->assertSame('estrutural', $cupom->motivo_falha);
    }

    /** CA-4: falha de negócio (cupom inexistente/cancelado) → rejeitado, sem retry. */
    public function test_falha_de_negocio_rejeita_sem_retry(): void
    {
        $this->comFetcher((new FakeSefazSpFetcher)->falharCom(
            SefazExtracaoException::negocio('cupom cancelado')
        ));
        $cupom = $this->cupomPendente();

        $this->rodarJob($cupom); // não deve lançar

        $this->assertSame(Cupom::STATUS_REJEITADO, $cupom->refresh()->status);
    }

    /** CA-2: o Job é idempotente — cupom já terminal não é reprocessado. */
    public function test_job_ignora_cupom_ja_terminal(): void
    {
        $fake = $this->comFetcher(new FakeSefazSpFetcher);
        $cupom = $this->cupomPendente();
        $cupom->update(['status' => Cupom::STATUS_VALIDADO]);

        $this->rodarJob($cupom);

        $this->assertSame(0, $fake->chamadas, 'não deve tocar o portal para cupom já validado');
    }

    /** CA-2: reprocessar do zero não duplica itens (idempotência da normalização). */
    public function test_reprocessar_nao_duplica_itens(): void
    {
        $this->comFetcher(new FakeSefazSpFetcher);
        $cupom = $this->cupomPendente();

        $this->ingestao()->processarExtracao($cupom);
        $cupom->update(['status' => Cupom::STATUS_FALHA]); // força reprocesso
        $this->ingestao()->processarExtracao($cupom->fresh());

        $this->assertDatabaseCount('cupons', 1);
        $this->assertDatabaseCount('cupom_itens', 2);
    }

    /** CA-1/CA-3: a captura enfileira a extração (não bloqueia o request). */
    public function test_captura_enfileira_a_extracao(): void
    {
        Queue::fake();

        $resultado = $this->ingestao()->capturar(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::CAPTURADO, $resultado->situacao);
        $this->assertSame(Cupom::STATUS_PENDENTE, $resultado->cupom->status);
        Queue::assertPushed(ExtrairCupomJob::class, fn ($job) => $job->cupomId === $resultado->cupom->id);
    }

    /** CA-3: reprocessar um cupom em falha re-enfileira o Job (não duplica cupom). */
    public function test_reprocessar_reenfileira_o_job(): void
    {
        Queue::fake();
        $cupom = $this->cupomPendente();
        $cupom->update(['status' => Cupom::STATUS_FALHA]);

        $this->ingestao()->reprocessar(self::CHAVE_SP);

        Queue::assertPushed(ExtrairCupomJob::class, fn ($job) => $job->cupomId === $cupom->id);
        $this->assertDatabaseCount('cupons', 1);
    }

    /** Defensivo: sem adaptador para a UF, a extração vira falha estrutural (não persiste lixo). */
    public function test_sem_adaptador_para_a_uf_marca_falha(): void
    {
        $cupom = $this->cupomPendente();

        // Serviço sem nenhum adaptador registrado.
        $resultado = (new IngestaoCupomService)->processarExtracao($cupom);

        $this->assertSame(ResultadoIngestao::FALHA_EXTRACAO, $resultado->situacao);
        $this->assertSame('sem_adaptador_uf', $cupom->refresh()->motivo_falha);
    }

    /** Reprocessar um cupom que não está em falha é no-op (idempotente). */
    public function test_reprocessar_cupom_nao_falho_e_no_op(): void
    {
        Queue::fake();
        $cupom = $this->cupomPendente();
        $cupom->update(['status' => Cupom::STATUS_VALIDADO]);

        $resultado = $this->ingestao()->reprocessar(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::DUPLICADO, $resultado->situacao);
        Queue::assertNothingPushed();
    }

    /** CA-3: esgotado o retry, `failed()` deixa o cupom em `falha` (dead-letter). */
    public function test_failed_marca_cupom_em_falha(): void
    {
        $cupom = $this->cupomPendente();
        $cupom->update(['status' => Cupom::STATUS_EXTRAINDO]);

        (new ExtrairCupomJob($cupom->id))->failed(new \RuntimeException('esgotou'));

        $this->assertSame(Cupom::STATUS_FALHA, $cupom->refresh()->status);
    }

    /** CA-1 ponta a ponta: Job + fetcher REAL + DANFE real de SP → validado, 18 itens, sem CPF. */
    public function test_extracao_ao_vivo_com_danfe_real(): void
    {
        // O fetcher real (binding padrão) roda; o portal é fakeado com o HTML real.
        Http::fake(['*' => Http::response(
            file_get_contents(base_path('tests/fixtures/coleta/danfe-sp.html')), 200
        )]);

        $cupom = Cupom::create([
            'chave_acesso' => '35260743259548002883652030000666061954634872',
            'uf' => '35', 'ano_mes' => '2607', 'cnpj_emitente' => '43259548002883',
            'modelo' => '65', 'status' => Cupom::STATUS_PENDENTE, 'origem' => 'scan',
            'qr_conteudo' => 'https://www.nfce.fazenda.sp.gov.br/qrcode?p=35260743259548002883652030000666061954634872|2|1|1|4FBDA25AD2D9AD27A38431225D8C0788404236FC',
        ]);

        (new ExtrairCupomJob($cupom->id))->handle($this->app->make(IngestaoCupomService::class));

        $cupom->refresh()->load('itens');
        $this->assertSame(Cupom::STATUS_VALIDADO, $cupom->status);
        $this->assertSame('235.43', (string) $cupom->valor_total);
        $this->assertCount(18, $cupom->itens);
        $this->assertFalse(
            AnonimizadorCpf::contemCpf(json_encode($cupom->toArray(), JSON_UNESCAPED_UNICODE)),
            'Nenhum CPF pode ser persistido (ADR-006).'
        );
    }

    /** ADR-002: política de retry com backoff crescente configurada. */
    public function test_configuracao_de_retry_e_backoff(): void
    {
        $job = new ExtrairCupomJob('qualquer');

        $this->assertSame(3, $job->tries);
        $this->assertSame([10, 60, 300], $job->backoff());
    }

    /** CA-5: cupom validado é contável como "válido, único e novo" (base da north-star). */
    public function test_cupom_validado_conta_como_valido_unico_novo(): void
    {
        $this->comFetcher(new FakeSefazSpFetcher);
        $cupom = $this->cupomPendente();
        $this->rodarJob($cupom);

        $this->assertSame(1, Cupom::validosUnicosNovos()->count());
    }
}
