<?php

namespace Tests\Feature\Enriquecimento;

use App\Domain\Enriquecimento\EnriquecedorCnpj;
use App\Domain\Enriquecimento\EnriquecimentoException;
use App\Models\Emitente;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\Support\Enriquecimento\FakeEnriquecedor;
use Tests\TestCase;

/**
 * Comando de verificação em homologação (STORY-040): exercita o enriquecimento síncrono
 * a partir de um CNPJ. O enriquecedor é um dublê — não toca a rede.
 */
class EnriquecerCnpjCommandTest extends TestCase
{
    use RefreshDatabase;

    private const CNPJ = '43259548002883';

    public function test_enriquece_e_persiste_com_sucesso(): void
    {
        $this->app->instance(EnriquecedorCnpj::class, new FakeEnriquecedor);

        $this->artisan('enriquecimento:cnpj', ['cnpj' => self::CNPJ])
            ->assertSuccessful();

        $this->assertDatabaseHas('emitentes', ['cnpj' => self::CNPJ, 'status_enriquecimento' => Emitente::STATUS_ENRIQUECIDO]);
    }

    public function test_falha_transitoria_retorna_erro(): void
    {
        $this->app->instance(
            EnriquecedorCnpj::class,
            (new FakeEnriquecedor)->programarExcecao(EnriquecimentoException::transitoria('fonte fora'))
        );

        $this->artisan('enriquecimento:cnpj', ['cnpj' => self::CNPJ])
            ->assertFailed();
    }
}
