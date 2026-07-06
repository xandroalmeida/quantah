<?php

namespace Tests\Feature\Enriquecimento;

use App\Domain\Coleta\Events\CupomValidado;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\Sefaz\SefazSpFetcher;
use App\Domain\Enriquecimento\EnriquecedorCnpj;
use App\Domain\Enriquecimento\EnriquecimentoService;
use App\Domain\Enriquecimento\Listeners\EnriquecerEmitenteAoValidar;
use App\Jobs\EnriquecerEmitenteJob;
use App\Models\Cupom;
use App\Models\Emitente;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Queue;
use Tests\Support\Coleta\FakeSefazSpFetcher;
use Tests\Support\Enriquecimento\FakeEnriquecedor;
use Tests\TestCase;

/**
 * Amarra a coleta (EPIC-002) ao enriquecimento (EPIC-009): quando um cupom fica validado,
 * o evento `CupomValidado` dispara o enriquecimento do emitente de forma assíncrona —
 * fora do request (ADR-013, padrão IDR-008). Em teste a fila roda `sync` (inline).
 */
class EnriquecerEmitenteAoValidarTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    private const CNPJ_DA_CHAVE = '12345678000195';

    private function comFetcher(FakeSefazSpFetcher $fake): void
    {
        $this->app->instance(SefazSpFetcher::class, $fake);
    }

    public function test_handle_solicita_enriquecimento_do_cnpj_do_cupom(): void // CA-1
    {
        Queue::fake();
        $cupom = Cupom::create([
            'chave_acesso' => self::CHAVE_SP,
            'uf' => '35', 'ano_mes' => '2601', 'cnpj_emitente' => self::CNPJ_DA_CHAVE,
            'modelo' => '65', 'status' => Cupom::STATUS_VALIDADO,
        ]);

        (new EnriquecerEmitenteAoValidar(new EnriquecimentoService(new FakeEnriquecedor)))
            ->handle(new CupomValidado($cupom->id));

        Queue::assertPushed(EnriquecerEmitenteJob::class, fn ($job) => $job->cnpj === self::CNPJ_DA_CHAVE);
    }

    public function test_cupom_inexistente_e_noop(): void // borda
    {
        Queue::fake();

        (new EnriquecerEmitenteAoValidar(new EnriquecimentoService(new FakeEnriquecedor)))
            ->handle(new CupomValidado('00000000-0000-0000-0000-000000000000'));

        Queue::assertNothingPushed();
    }

    public function test_emitente_ja_em_cache_nao_redispara(): void // CA-2
    {
        Queue::fake();
        Emitente::factory()->enriquecido()->create(['cnpj' => self::CNPJ_DA_CHAVE, 'enriquecido_em' => now()->subDay()]);
        $cupom = Cupom::create([
            'chave_acesso' => self::CHAVE_SP,
            'uf' => '35', 'ano_mes' => '2601', 'cnpj_emitente' => self::CNPJ_DA_CHAVE,
            'modelo' => '65', 'status' => Cupom::STATUS_VALIDADO,
        ]);

        (new EnriquecerEmitenteAoValidar(new EnriquecimentoService(new FakeEnriquecedor)))
            ->handle(new CupomValidado($cupom->id));

        Queue::assertNothingPushed();
    }

    public function test_pipeline_real_enriquece_o_emitente_ao_validar(): void // CA-1 (wiring completo)
    {
        $this->comFetcher(new FakeSefazSpFetcher);
        $this->app->instance(EnriquecedorCnpj::class, new FakeEnriquecedor);
        $user = User::factory()->create();

        // Fluxo real: coleta → valida (sync) → CupomValidado → listener → job (sync) → persiste.
        $this->app->make(IngestaoCupomService::class)->capturar(self::CHAVE_SP, 'scan', $user->id);

        $this->assertSame(Cupom::STATUS_VALIDADO, Cupom::where('chave_acesso', self::CHAVE_SP)->firstOrFail()->status);
        $this->assertDatabaseHas('emitentes', [
            'cnpj' => self::CNPJ_DA_CHAVE,
            'status_enriquecimento' => Emitente::STATUS_ENRIQUECIDO,
        ]);
    }

    public function test_dois_cupons_do_mesmo_cnpj_compartilham_um_emitente(): void // CA-5
    {
        $this->app->instance(EnriquecedorCnpj::class, $fake = new FakeEnriquecedor);
        $service = new EnriquecimentoService($fake);

        $service->enriquecer(self::CNPJ_DA_CHAVE);
        $service->enriquecer(self::CNPJ_DA_CHAVE); // 2º cupom do mesmo CNPJ

        $this->assertSame(1, Emitente::where('cnpj', self::CNPJ_DA_CHAVE)->count());
        $this->assertSame(1, $fake->chamadas, 'Segundo cupom do mesmo CNPJ usa o cache — sem nova consulta.');
    }
}
