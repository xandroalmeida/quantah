<?php

namespace Tests\Feature\Enriquecimento;

use App\Domain\Enriquecimento\EmitenteEnriquecido;
use App\Domain\Enriquecimento\EnriquecimentoException;
use App\Domain\Enriquecimento\FallbackEnriquecedor;
use Tests\Support\Enriquecimento\FakeEnriquecedor;
use Tests\TestCase;

/**
 * Decorator de fallback (ADR-012): a primária falhando de forma transitória/estrutural,
 * a secundária é tentada. Se ambas falharem, a exceção propaga (o Job retenta).
 */
class FallbackEnriquecedorTest extends TestCase
{
    private const CNPJ = '43259548002883';

    public function test_usa_secundaria_quando_primaria_falha(): void
    {
        $primaria = (new FakeEnriquecedor)->programarExcecao(EnriquecimentoException::transitoria('fora do ar'));
        $secundaria = new FakeEnriquecedor;

        $dto = (new FallbackEnriquecedor($primaria, $secundaria))->consultar(self::CNPJ);

        $this->assertSame(EmitenteEnriquecido::STATUS_ENRIQUECIDO, $dto->status);
        $this->assertSame(1, $primaria->chamadas);
        $this->assertSame(1, $secundaria->chamadas);
    }

    public function test_nao_chama_secundaria_quando_primaria_responde(): void
    {
        $primaria = new FakeEnriquecedor;
        $secundaria = new FakeEnriquecedor;

        (new FallbackEnriquecedor($primaria, $secundaria))->consultar(self::CNPJ);

        $this->assertSame(1, $primaria->chamadas);
        $this->assertSame(0, $secundaria->chamadas);
    }

    public function test_propaga_quando_ambas_falham(): void
    {
        $primaria = (new FakeEnriquecedor)->programarExcecao(EnriquecimentoException::transitoria('primaria fora'));
        $secundaria = (new FakeEnriquecedor)->programarExcecao(EnriquecimentoException::transitoria('secundaria fora'));

        $this->expectException(EnriquecimentoException::class);

        (new FallbackEnriquecedor($primaria, $secundaria))->consultar(self::CNPJ);
    }
}
