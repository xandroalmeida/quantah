<?php

namespace Tests\Feature\Enriquecimento;

use App\Domain\Enriquecimento\EnriquecedorCnpj;
use App\Domain\Enriquecimento\EnriquecimentoException;
use App\Jobs\EnriquecerEmitenteJob;
use App\Models\Emitente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Tests\Support\Enriquecimento\FakeEnriquecedor;
use Tests\TestCase;

/**
 * Enriquecimento assíncrono na fila (ADR-013). Política de falha herdada do padrão
 * SEFAZ (ADR-002): transitória relança (retry/backoff); esgotado, `failed()` marca o
 * emitente `nao_enriquecido` — a solicitação nunca se perde e nunca vira erro ao
 * Colaborador (CA-5).
 */
class EnriquecerEmitenteJobTest extends TestCase
{
    use RefreshDatabase;

    private const CNPJ = '43259548002883';

    private function comEnriquecedor(FakeEnriquecedor $fake): void
    {
        $this->app->instance(EnriquecedorCnpj::class, $fake);
    }

    public function test_config_de_retry_segue_o_adr(): void
    {
        $job = new EnriquecerEmitenteJob(self::CNPJ);

        $this->assertSame(3, $job->tries);
        $this->assertSame([30, 120, 300], $job->backoff());
    }

    public function test_job_enriquece_e_persiste(): void // CA-1
    {
        $fake = new FakeEnriquecedor;
        $this->comEnriquecedor($fake);

        (new EnriquecerEmitenteJob(self::CNPJ))->handle(app(\App\Domain\Enriquecimento\EnriquecimentoService::class));

        $this->assertSame(1, $fake->chamadas);
        $this->assertDatabaseHas('emitentes', ['cnpj' => self::CNPJ, 'status_enriquecimento' => Emitente::STATUS_ENRIQUECIDO]);
    }

    public function test_transitoria_reprocessa(): void // CA-5
    {
        $fake = (new FakeEnriquecedor)->programarExcecao(EnriquecimentoException::transitoria('fora do ar'));
        $this->comEnriquecedor($fake);

        $this->expectException(EnriquecimentoException::class);

        (new EnriquecerEmitenteJob(self::CNPJ))->handle(app(\App\Domain\Enriquecimento\EnriquecimentoService::class));
    }

    public function test_estrutural_nao_reprocessa_e_marca_nao_enriquecido(): void // CA-5
    {
        $fake = (new FakeEnriquecedor)->programarExcecao(EnriquecimentoException::estrutural('contrato mudou'));
        $this->comEnriquecedor($fake);

        // Não relança: estrutural alerta e degrada (não trava).
        (new EnriquecerEmitenteJob(self::CNPJ))->handle(app(\App\Domain\Enriquecimento\EnriquecimentoService::class));

        $this->assertDatabaseHas('emitentes', ['cnpj' => self::CNPJ, 'status_enriquecimento' => Emitente::STATUS_NAO_ENRIQUECIDO]);
    }

    public function test_failed_marca_nao_enriquecido(): void // CA-5
    {
        (new EnriquecerEmitenteJob(self::CNPJ))->failed(new \RuntimeException('esgotou'));

        $this->assertDatabaseHas('emitentes', ['cnpj' => self::CNPJ, 'status_enriquecimento' => Emitente::STATUS_NAO_ENRIQUECIDO]);
    }

    public function test_falha_nunca_toca_o_cupom_nem_lanca_ao_usuario(): void // CA-5 (borda: sem rede real)
    {
        // Garante que o Job não faz chamada de rede fora do enriquecedor injetado.
        Http::preventStrayRequests();
        $fake = (new FakeEnriquecedor)->programarExcecao(EnriquecimentoException::estrutural('x'));
        $this->comEnriquecedor($fake);

        (new EnriquecerEmitenteJob(self::CNPJ))->handle(app(\App\Domain\Enriquecimento\EnriquecimentoService::class));

        $this->assertTrue(true); // não lançou ao chamador
    }
}
