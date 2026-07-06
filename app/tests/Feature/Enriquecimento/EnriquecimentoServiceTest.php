<?php

namespace Tests\Feature\Enriquecimento;

use App\Domain\Enriquecimento\EmitenteEnriquecido;
use App\Domain\Enriquecimento\EnriquecimentoService;
use App\Jobs\EnriquecerEmitenteJob;
use App\Models\Emitente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Queue;
use Tests\Support\Enriquecimento\FakeEnriquecedor;
use Tests\TestCase;

/**
 * Política de cache/fallback do serviço de enriquecimento (ADR-012/013/014) — o núcleo
 * de regra de negócio desta estória. O enriquecedor externo é sempre um dublê com
 * contador de chamadas: é assim que se prova "zero chamada dentro do TTL" (CA-2).
 */
class EnriquecimentoServiceTest extends TestCase
{
    use RefreshDatabase;

    private const CNPJ = '43259548002883';

    private function service(FakeEnriquecedor $fake): EnriquecimentoService
    {
        return new EnriquecimentoService($fake);
    }

    public function test_cnpj_novo_consulta_e_persiste(): void // CA-1
    {
        $fake = new FakeEnriquecedor;

        $emitente = $this->service($fake)->enriquecer(self::CNPJ);

        $this->assertSame(1, $fake->chamadas);
        $this->assertSame(Emitente::STATUS_ENRIQUECIDO, $emitente->status_enriquecimento);
        $this->assertDatabaseHas('emitentes', [
            'cnpj' => self::CNPJ,
            'cnae_principal_codigo' => '4711302',
            'status_enriquecimento' => Emitente::STATUS_ENRIQUECIDO,
        ]);
        $this->assertNotNull($emitente->enriquecido_em);
    }

    public function test_cnpj_fresco_nao_chama_externo(): void // CA-2
    {
        Emitente::factory()->enriquecido()->create(['cnpj' => self::CNPJ, 'enriquecido_em' => now()->subDays(5)]);
        $fake = new FakeEnriquecedor;

        $emitente = $this->service($fake)->enriquecer(self::CNPJ);

        $this->assertSame(0, $fake->chamadas);
        $this->assertSame(self::CNPJ, $emitente->cnpj);
    }

    public function test_cnpj_vencido_reconsulta_e_renova_cache(): void // CA-3
    {
        $antigo = Emitente::factory()->enriquecido()->create([
            'cnpj' => self::CNPJ,
            'enriquecido_em' => now()->subDays(40),
        ]);
        $fake = new FakeEnriquecedor;

        $emitente = $this->service($fake)->enriquecer(self::CNPJ);

        $this->assertSame(1, $fake->chamadas);
        $this->assertTrue($emitente->enriquecido_em->greaterThan($antigo->enriquecido_em));
    }

    public function test_ttl_lido_da_config(): void // CA-4
    {
        Config::set('enriquecimento.ttl_dias', 1);
        Emitente::factory()->enriquecido()->create(['cnpj' => self::CNPJ, 'enriquecido_em' => now()->subDays(2)]);
        $fake = new FakeEnriquecedor;

        $this->service($fake)->enriquecer(self::CNPJ);

        $this->assertSame(1, $fake->chamadas, 'Com TTL=1 dia, registro de 2 dias está vencido e deve reconsultar.');
    }

    public function test_persiste_status_nao_encontrado(): void // CA-6
    {
        $fake = (new FakeEnriquecedor)->programarResposta(self::CNPJ, EmitenteEnriquecido::naoEncontrado(self::CNPJ, 'fake'));

        $emitente = $this->service($fake)->enriquecer(self::CNPJ);

        $this->assertSame(Emitente::STATUS_NAO_ENCONTRADO, $emitente->status_enriquecimento);
        $this->assertNull($emitente->cnae_principal_codigo);
        $this->assertNotNull($emitente->enriquecido_em, 'Resposta de negócio é definitiva — cacheia dentro do TTL.');
    }

    public function test_registrar_nao_enriquecido_fica_reconsultavel(): void // CA-5
    {
        $fake = new FakeEnriquecedor;

        $this->service($fake)->registrarNaoEnriquecido(self::CNPJ);
        $emitente = Emitente::firstWhere('cnpj', self::CNPJ);

        $this->assertSame(Emitente::STATUS_NAO_ENRIQUECIDO, $emitente->status_enriquecimento);
        $this->assertNull($emitente->enriquecido_em, 'Falha transitória não vira cache — deve permitir nova tentativa.');

        // e uma próxima solicitação consulta de novo (não é cache-hit)
        $this->service($fake)->enriquecer(self::CNPJ);
        $this->assertSame(1, $fake->chamadas);
    }

    public function test_registrar_nao_enriquecido_nao_rebaixa_registro_fresco(): void // CA-5 (borda)
    {
        Emitente::factory()->enriquecido()->create(['cnpj' => self::CNPJ, 'enriquecido_em' => now()->subDay()]);

        (new EnriquecimentoService(new FakeEnriquecedor))->registrarNaoEnriquecido(self::CNPJ);

        $this->assertSame(Emitente::STATUS_ENRIQUECIDO, Emitente::firstWhere('cnpj', self::CNPJ)->status_enriquecimento);
    }

    public function test_solicitar_despacha_job_em_cache_miss(): void // CA-1
    {
        Queue::fake();

        (new EnriquecimentoService(new FakeEnriquecedor))->solicitar(self::CNPJ);

        Queue::assertPushed(EnriquecerEmitenteJob::class, fn ($job) => $job->cnpj === self::CNPJ);
    }

    public function test_solicitar_nao_despacha_em_cache_fresco(): void // CA-2
    {
        Queue::fake();
        Emitente::factory()->enriquecido()->create(['cnpj' => self::CNPJ, 'enriquecido_em' => now()->subDay()]);

        (new EnriquecimentoService(new FakeEnriquecedor))->solicitar(self::CNPJ);

        Queue::assertNothingPushed();
    }

    public function test_normaliza_cnpj_com_mascara(): void // borda
    {
        $fake = new FakeEnriquecedor;

        $emitente = $this->service($fake)->enriquecer('43.259.548/0028-83');

        $this->assertSame(self::CNPJ, $emitente->cnpj);
    }
}
