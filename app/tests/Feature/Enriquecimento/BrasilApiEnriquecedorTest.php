<?php

namespace Tests\Feature\Enriquecimento;

use App\Domain\Enriquecimento\BrasilApiEnriquecedor;
use App\Domain\Enriquecimento\EmitenteEnriquecido;
use App\Domain\Enriquecimento\EnriquecimentoException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

/**
 * ACL da fonte primária CNPJ (ADR-012): mapeia a resposta da RFB → DTO canônico e
 * classifica falhas. Sem tocar a rede — respostas via `Http::fake`. Os fixtures
 * espelham o shape real observado na prova ao vivo do spike (STORY-039).
 */
class BrasilApiEnriquecedorTest extends TestCase
{
    private const CNPJ = '43259548002883';

    private function corpoOk(array $override = []): array
    {
        return array_merge([
            'cnpj' => self::CNPJ,
            'razao_social' => 'SUPERMERCADOS CAVICCHIOLLI LTDA',
            'nome_fantasia' => 'CAVICCHIOLLI',
            'cnae_fiscal' => 4711302,
            'cnae_fiscal_descricao' => 'Comércio varejista de mercadorias em geral, com predominância de produtos alimentícios - supermercados',
            'cnaes_secundarios' => [['codigo' => 4729699, 'descricao' => 'Comércio varejista de outros produtos alimentícios']],
            'descricao_situacao_cadastral' => 'ATIVA',
            'municipio' => 'ITU',
            'uf' => 'SP',
        ], $override);
    }

    private function enriquecedor(): BrasilApiEnriquecedor
    {
        return new BrasilApiEnriquecedor(timeoutSegundos: 2);
    }

    public function test_mapeia_resposta_real_para_dto_enriquecido(): void
    {
        Http::fake(['brasilapi.com.br/*' => Http::response($this->corpoOk(), 200)]);

        $dto = $this->enriquecedor()->consultar(self::CNPJ);

        $this->assertSame(EmitenteEnriquecido::STATUS_ENRIQUECIDO, $dto->status);
        $this->assertSame(self::CNPJ, $dto->cnpj);
        $this->assertSame('SUPERMERCADOS CAVICCHIOLLI LTDA', $dto->razaoSocial);
        $this->assertSame('4711302', $dto->cnaePrincipalCodigo);
        $this->assertStringContainsString('supermercados', $dto->cnaePrincipalDescricao);
        $this->assertSame('ATIVA', $dto->situacaoCadastral);
        $this->assertSame('ITU', $dto->municipio);
        $this->assertSame('SP', $dto->uf);
        $this->assertSame('brasilapi', $dto->fonte);
        $this->assertNotEmpty($dto->cnaesSecundarios);
    }

    public function test_200_sem_cnae_vira_status_sem_cnae(): void
    {
        Http::fake(['brasilapi.com.br/*' => Http::response($this->corpoOk(['cnae_fiscal' => null, 'cnae_fiscal_descricao' => null]), 200)]);

        $dto = $this->enriquecedor()->consultar(self::CNPJ);

        $this->assertSame(EmitenteEnriquecido::STATUS_SEM_CNAE, $dto->status);
        $this->assertNull($dto->cnaePrincipalCodigo);
    }

    public function test_404_vira_status_nao_encontrado(): void
    {
        Http::fake(['brasilapi.com.br/*' => Http::response(['message' => 'CNPJ inválido.'], 404)]);

        $dto = $this->enriquecedor()->consultar(self::CNPJ);

        $this->assertSame(EmitenteEnriquecido::STATUS_NAO_ENCONTRADO, $dto->status);
        $this->assertSame(self::CNPJ, $dto->cnpj);
    }

    public function test_timeout_de_conexao_e_transitoria(): void
    {
        Http::fake(fn () => throw new ConnectionException('timeout'));

        $this->esperarExcecao(EnriquecimentoException::TRANSITORIA);
    }

    public function test_erro_5xx_e_transitoria(): void
    {
        Http::fake(['brasilapi.com.br/*' => Http::response('', 503)]);

        $this->esperarExcecao(EnriquecimentoException::TRANSITORIA);
    }

    public function test_rate_limit_429_e_transitoria(): void
    {
        Http::fake(['brasilapi.com.br/*' => Http::response('', 429)]);

        $this->esperarExcecao(EnriquecimentoException::TRANSITORIA);
    }

    public function test_corpo_inesperado_e_estrutural(): void
    {
        Http::fake(['brasilapi.com.br/*' => Http::response(['algo' => 'diferente'], 200)]);

        $this->esperarExcecao(EnriquecimentoException::ESTRUTURAL);
    }

    private function esperarExcecao(string $tipo): void
    {
        try {
            $this->enriquecedor()->consultar(self::CNPJ);
            $this->fail("Deveria lançar EnriquecimentoException do tipo $tipo.");
        } catch (EnriquecimentoException $e) {
            $this->assertSame($tipo, $e->tipo);
        }
    }
}
