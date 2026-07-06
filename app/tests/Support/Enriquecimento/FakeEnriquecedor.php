<?php

namespace Tests\Support\Enriquecimento;

use App\Domain\Enriquecimento\EmitenteEnriquecido;
use App\Domain\Enriquecimento\EnriquecedorCnpj;
use App\Domain\Enriquecimento\EnriquecimentoException;

/**
 * Dublê da porta de enriquecimento (ADR-012). Programável por CNPJ: devolve um DTO
 * ou lança uma EnriquecimentoException, e **conta as chamadas** — é assim que os
 * testes provam que o cache-hit não bate no externo (CA-2).
 */
final class FakeEnriquecedor implements EnriquecedorCnpj
{
    public int $chamadas = 0;

    /** @var array<string, EmitenteEnriquecido> */
    private array $respostas = [];

    private ?EnriquecimentoException $excecao = null;

    public function programarResposta(string $cnpj, EmitenteEnriquecido $dto): self
    {
        $this->respostas[$cnpj] = $dto;

        return $this;
    }

    public function programarExcecao(EnriquecimentoException $e): self
    {
        $this->excecao = $e;

        return $this;
    }

    public function consultar(string $cnpj): EmitenteEnriquecido
    {
        $this->chamadas++;

        if ($this->excecao !== null) {
            throw $this->excecao;
        }

        return $this->respostas[$cnpj]
            ?? EmitenteEnriquecido::enriquecido(
                cnpj: $cnpj,
                razaoSocial: 'EMPRESA TESTE LTDA',
                nomeFantasia: null,
                cnaePrincipalCodigo: '4711302',
                cnaePrincipalDescricao: 'Comércio varejista — supermercados',
                cnaesSecundarios: [],
                situacaoCadastral: 'ATIVA',
                municipio: 'SAO PAULO',
                uf: 'SP',
                fonte: 'fake',
            );
    }
}
