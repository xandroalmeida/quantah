<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\AnonimizadorCpf;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\Sefaz\SpSefazAdapter;
use App\Models\ColetaEvento;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Coleta\FakeSefazSpFetcher;
use Tests\TestCase;

/**
 * Instrumentação da coleta (STORY-012, CA-2/CA-5): cada tentativa de envio registra
 * exatamente UM evento de telemetria (denominador da taxa de sucesso), sem PII. O
 * evento espelha o desfecho (ResultadoIngestao::situacao) — inclusive os que NÃO
 * viram cupom (rejeição de parse, duplicata).
 */
class TelemetriaColetaTest extends TestCase
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

    /** CA-2: envio válido registra um evento `aceito` ligado ao cupom (uuid, sem PII). */
    public function test_envio_valido_registra_evento_aceito(): void
    {
        $resultado = $this->servico(new FakeSefazSpFetcher)->ingerir(self::CHAVE_SP);

        $this->assertDatabaseCount('coleta_eventos', 1);
        $evento = ColetaEvento::firstOrFail();
        $this->assertSame('aceito', $evento->situacao);
        $this->assertSame($resultado->cupom->id, $evento->cupom_id);
        $this->assertNull($evento->motivo);
    }

    /** CA-2: reenvio da mesma chave conta como envio (evento `duplicado`), não some. */
    public function test_reenvio_registra_evento_duplicado(): void
    {
        $servico = $this->servico(new FakeSefazSpFetcher);
        $servico->ingerir(self::CHAVE_SP);
        $servico->ingerir(self::CHAVE_SP);

        $this->assertDatabaseCount('coleta_eventos', 2);
        $this->assertSame(1, ColetaEvento::where('situacao', 'duplicado')->count());
    }

    /**
     * CA-2/CA-5: rejeição de parse (chave fora de escopo) — que NÃO persiste cupom —
     * ainda registra o envio, com o motivo, e sem cupom_id.
     */
    public function test_rejeicao_de_parse_registra_evento_com_motivo(): void
    {
        $this->servico(new FakeSefazSpFetcher)->ingerir(self::CHAVE_RJ);

        $this->assertDatabaseCount('cupons', 0);
        $evento = ColetaEvento::firstOrFail();
        $this->assertSame('rejeitado', $evento->situacao);
        $this->assertSame('fora_de_escopo_uf', $evento->motivo);
        $this->assertNull($evento->cupom_id);
    }

    /** CA-2: falha de extração é um envio (evento `falha_extracao` com o tipo da falha). */
    public function test_falha_de_extracao_registra_evento(): void
    {
        $fetcher = (new FakeSefazSpFetcher)->falharCom(
            \App\Domain\Coleta\Sefaz\SefazExtracaoException::transitoria('portal fora do ar')
        );

        $this->servico($fetcher)->ingerir(self::CHAVE_SP);

        $evento = ColetaEvento::firstOrFail();
        $this->assertSame('falha_extracao', $evento->situacao);
        $this->assertSame('transitoria', $evento->motivo);
    }

    /** CA-5: nenhuma coluna de evento carrega CPF, e o registro não guarda a chave em claro. */
    public function test_evento_nao_vaza_pii(): void
    {
        $this->servico(new FakeSefazSpFetcher)->ingerir(self::CHAVE_SP);

        $serial = json_encode(ColetaEvento::firstOrFail()->toArray(), JSON_UNESCAPED_UNICODE);
        $this->assertFalse(AnonimizadorCpf::contemCpf($serial));
    }
}
