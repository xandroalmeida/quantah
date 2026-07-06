<?php

namespace App\Domain\Enriquecimento;

use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Http;

/**
 * Base das fontes que servem o **dump aberto da RFB** com o mesmo shape de campos
 * (`razao_social`, `cnae_fiscal`, `descricao_situacao_cadastral`, ...): BrasilAPI e
 * Minha Receita (ADR-012). Cada fonte concreta informa só a URL e o nome da fonte;
 * o mapeamento response→DTO e a classificação de falha vivem aqui, num lugar só.
 */
abstract class RfbOpenDataEnriquecedor implements EnriquecedorCnpj
{
    public function __construct(
        protected readonly int $timeoutSegundos = 15,
    ) {}

    /** URL da consulta para o CNPJ (14 dígitos). */
    abstract protected function url(string $cnpj): string;

    /** Nome da fonte, gravado no DTO/registro para auditoria. */
    abstract protected function fonte(): string;

    public function consultar(string $cnpj): EmitenteEnriquecido
    {
        $resposta = $this->requisitar($cnpj);

        if ($resposta->status() === 404) {
            return EmitenteEnriquecido::naoEncontrado($cnpj, $this->fonte());
        }
        if ($resposta->status() === 429) {
            throw EnriquecimentoException::transitoria($this->fonte().' aplicou rate limit (429).');
        }
        if ($resposta->serverError()) {
            throw EnriquecimentoException::transitoria($this->fonte().' retornou '.$resposta->status().'.');
        }
        if (! $resposta->ok()) {
            throw EnriquecimentoException::transitoria($this->fonte().' retornou status inesperado '.$resposta->status().'.');
        }

        return $this->mapear($cnpj, $resposta->json());
    }

    private function requisitar(string $cnpj): Response
    {
        try {
            return Http::timeout($this->timeoutSegundos)
                ->withHeaders(['User-Agent' => config('enriquecimento.user_agent')])
                ->get($this->url($cnpj));
        } catch (ConnectionException $e) {
            throw EnriquecimentoException::transitoria($this->fonte().' indisponível: '.$e->getMessage());
        }
    }

    /**
     * @param  mixed  $corpo
     */
    private function mapear(string $cnpj, $corpo): EmitenteEnriquecido
    {
        if (! is_array($corpo) || (! isset($corpo['razao_social']) && ! isset($corpo['cnpj']))) {
            throw EnriquecimentoException::estrutural($this->fonte().' respondeu em formato inesperado.');
        }

        $cnae = $corpo['cnae_fiscal'] ?? null;

        return EmitenteEnriquecido::enriquecido(
            cnpj: $cnpj,
            razaoSocial: $corpo['razao_social'] ?? null,
            nomeFantasia: ($corpo['nome_fantasia'] ?? '') !== '' ? $corpo['nome_fantasia'] : null,
            cnaePrincipalCodigo: $cnae !== null && $cnae !== '' ? (string) $cnae : null,
            cnaePrincipalDescricao: $corpo['cnae_fiscal_descricao'] ?? null,
            cnaesSecundarios: $this->mapearSecundarios($corpo['cnaes_secundarios'] ?? []),
            situacaoCadastral: $corpo['descricao_situacao_cadastral'] ?? null,
            municipio: $corpo['municipio'] ?? null,
            uf: $corpo['uf'] ?? null,
            fonte: $this->fonte(),
        );
    }

    /**
     * @param  mixed  $secundarios
     * @return array<int, array{codigo: string, descricao: ?string}>
     */
    private function mapearSecundarios($secundarios): array
    {
        if (! is_array($secundarios)) {
            return [];
        }

        $mapeados = [];
        foreach ($secundarios as $item) {
            $codigo = is_array($item) ? ($item['codigo'] ?? null) : null;
            if ($codigo === null || $codigo === '') {
                continue;
            }
            $mapeados[] = [
                'codigo' => (string) $codigo,
                'descricao' => is_array($item) ? ($item['descricao'] ?? null) : null,
            ];
        }

        return $mapeados;
    }
}
