<?php

namespace App\Domain\Enriquecimento;

use App\Jobs\EnriquecerEmitenteJob;
use App\Models\Emitente;

/**
 * Serviço de enriquecimento cadastral do emitente (ADR-012/013/014).
 *
 * Fronteira do contexto `Enriquecimento`. Duas entradas:
 *  - `solicitar()`  — entrada assíncrona: cache-first; em cache-miss/vencido, enfileira
 *    o Job. Em cache-hit fresco NÃO enfileira nem chama o externo (CA-2).
 *  - `enriquecer()` — trabalho efetivo (chamado pelo Job / CLI): cache-first; consulta a
 *    fonte só em miss/vencido e faz upsert na tabela `emitentes` (cache = registro
 *    canônico, ADR-014).
 *
 * O TTL vem de `config('enriquecimento.ttl_dias')` (default 30) — parametrizável sem
 * deploy (CA-4).
 */
final class EnriquecimentoService
{
    public function __construct(
        private readonly EnriquecedorCnpj $enriquecedor,
    ) {}

    /** Solicita o enriquecimento de forma assíncrona; no-op se já há cache fresco. */
    public function solicitar(string $cnpj): void
    {
        $cnpj = $this->normalizar($cnpj);

        $emitente = Emitente::firstWhere('cnpj', $cnpj);
        if ($emitente !== null && $emitente->estaFresco($this->ttlDias())) {
            return;
        }

        EnriquecerEmitenteJob::dispatch($cnpj);
    }

    /**
     * Enriquece e persiste o emitente. Cache-first: só bate na fonte em miss/vencido.
     *
     * @throws EnriquecimentoException (transitória/estrutural) — o Job decide retry.
     */
    public function enriquecer(string $cnpj): Emitente
    {
        $cnpj = $this->normalizar($cnpj);

        $emitente = Emitente::firstWhere('cnpj', $cnpj);
        if ($emitente !== null && $emitente->estaFresco($this->ttlDias())) {
            return $emitente;
        }

        $dto = $this->enriquecedor->consultar($cnpj);

        return $this->persistir($cnpj, $dto);
    }

    /**
     * Marca o emitente como `nao_enriquecido` (fallback do CA-5): a fonte falhou de
     * forma definitiva (retry esgotado / estrutural). Sem `enriquecido_em`, para que a
     * próxima solicitação reconsulte — não vira cache. Não rebaixa um registro que já
     * está enriquecido.
     */
    public function registrarNaoEnriquecido(string $cnpj): void
    {
        $cnpj = $this->normalizar($cnpj);

        $emitente = Emitente::firstWhere('cnpj', $cnpj);
        if ($emitente !== null && $emitente->estaFresco($this->ttlDias())) {
            return;
        }

        Emitente::updateOrCreate(
            ['cnpj' => $cnpj],
            ['status_enriquecimento' => Emitente::STATUS_NAO_ENRIQUECIDO, 'enriquecido_em' => null],
        );
    }

    private function persistir(string $cnpj, EmitenteEnriquecido $dto): Emitente
    {
        return Emitente::updateOrCreate(
            ['cnpj' => $cnpj],
            [
                'razao_social' => $dto->razaoSocial,
                'nome_fantasia' => $dto->nomeFantasia,
                'cnae_principal_codigo' => $dto->cnaePrincipalCodigo,
                'cnae_principal_descricao' => $dto->cnaePrincipalDescricao,
                'cnaes_secundarios' => $dto->cnaesSecundarios,
                'situacao_cadastral' => $dto->situacaoCadastral,
                'municipio' => $dto->municipio,
                'uf' => $dto->uf,
                'status_enriquecimento' => $dto->status,
                'fonte' => $dto->fonte,
                'enriquecido_em' => now(),
            ],
        );
    }

    private function ttlDias(): int
    {
        return (int) config('enriquecimento.ttl_dias', 30);
    }

    /** Mantém só os 14 dígitos — a máscara é UX, o canônico é o dígito puro. */
    private function normalizar(string $cnpj): string
    {
        return preg_replace('/\D/', '', $cnpj) ?? '';
    }
}
