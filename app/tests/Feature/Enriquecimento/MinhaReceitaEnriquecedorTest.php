<?php

namespace Tests\Feature\Enriquecimento;

use App\Domain\Enriquecimento\EmitenteEnriquecido;
use App\Domain\Enriquecimento\EnriquecimentoException;
use App\Domain\Enriquecimento\MinhaReceitaEnriquecedor;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * Fonte de fallback (ADR-012): Minha Receita tem o mesmo shape RFB da BrasilAPI. Aqui
 * provamos que a implementação concreta (URL + fonte) casa com o mapeamento da base.
 */
class MinhaReceitaEnriquecedorTest extends TestCase
{
    private const CNPJ = '45543915098211';

    public function test_mapeia_resposta_e_marca_fonte(): void
    {
        Http::fake(['minhareceita.org/*' => Http::response([
            'cnpj' => self::CNPJ,
            'razao_social' => 'CARREFOUR COMERCIO E INDUSTRIA LTDA',
            'cnae_fiscal' => 4711301,
            'cnae_fiscal_descricao' => 'Hipermercados',
            'descricao_situacao_cadastral' => 'ATIVA',
            'municipio' => 'PRESIDENTE PRUDENTE',
            'uf' => 'SP',
        ], 200)]);

        $dto = (new MinhaReceitaEnriquecedor(2))->consultar(self::CNPJ);

        $this->assertSame(EmitenteEnriquecido::STATUS_ENRIQUECIDO, $dto->status);
        $this->assertSame('minhareceita', $dto->fonte);
        $this->assertSame('4711301', $dto->cnaePrincipalCodigo);
    }

    public function test_5xx_e_transitoria(): void
    {
        Http::fake(['minhareceita.org/*' => Http::response('', 502)]);

        try {
            (new MinhaReceitaEnriquecedor(2))->consultar(self::CNPJ);
            $this->fail('Deveria lançar transitória.');
        } catch (EnriquecimentoException $e) {
            $this->assertSame(EnriquecimentoException::TRANSITORIA, $e->tipo);
        }
    }
}
