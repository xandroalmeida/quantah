<?php

namespace App\Domain\Coleta;

use App\Domain\Coleta\Sefaz\CupomExtraido;
use App\Domain\Coleta\Sefaz\SefazAdapter;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use App\Jobs\ExtrairCupomJob;
use App\Models\Cupom;
use Illuminate\Support\Facades\DB;

/**
 * Fronteira de ingestão do cupom (ADR-001) — a única porta que a camada web conhece.
 *
 * Dois caminhos, mesma regra de negócio (ADR-003):
 *  - `capturar()` (STORY-009/010, produção): valida a chave → dedup idempotente →
 *    persiste `pendente` → **despacha o ExtrairCupomJob** (extração assíncrona na fila
 *    Postgres, ADR-002). A fronteira responde em segundos; a validação SEFAZ roda depois.
 *  - `ingerir()` (síncrono): faz o mesmo e **extrai na hora** — usado pelo comando de CLI
 *    e pelos testes de núcleo. Compartilha `processarExtracao()` com o Job.
 *
 * A extração descarta CPF (ADR-006) e classifica falha (transitória/estrutural/negócio).
 */
final class IngestaoCupomService
{
    /** @var array<string, SefazAdapter> adaptador por UF (só SP no MVP) */
    private array $adaptadores = [];

    public function __construct(SefazAdapter ...$adaptadores)
    {
        foreach ($adaptadores as $adaptador) {
            $this->adaptadores[$adaptador->uf()] = $adaptador;
        }
    }

    /**
     * Captura + handoff assíncrono (produção): persiste `pendente` e enfileira a extração.
     */
    public function capturar(string $entrada, string $origem = 'scan'): ResultadoIngestao
    {
        $parse = $this->validarChave($entrada);
        if ($parse instanceof ResultadoIngestao) {
            return $parse; // rejeitado
        }

        [$cupom, $novo] = $this->persistirPendente($parse, $origem);

        // Enfileira a extração para cupom novo ou em falha (reprocessável) — ADR-002.
        if ($novo || $cupom->status === Cupom::STATUS_FALHA) {
            ExtrairCupomJob::dispatch($cupom->id);
        }

        return $novo
            ? ResultadoIngestao::capturado($cupom)
            : ResultadoIngestao::duplicado($cupom);
    }

    /**
     * Ingestão síncrona (CLI/testes): persiste e extrai na hora, sem passar pela fila.
     */
    public function ingerir(string $entrada, string $origem = 'scan'): ResultadoIngestao
    {
        $parse = $this->validarChave($entrada);
        if ($parse instanceof ResultadoIngestao) {
            return $parse;
        }

        [$cupom, $novo] = $this->persistirPendente($parse, $origem);

        if (! $novo && $cupom->status !== Cupom::STATUS_FALHA) {
            return ResultadoIngestao::duplicado($cupom);
        }

        return $this->processarExtracao($cupom);
    }

    /** Reprocessa um cupom em `falha` (ADR-002) — re-enfileira; idempotente, não duplica. */
    public function reprocessar(string $chaveAcesso): ResultadoIngestao
    {
        $cupom = Cupom::where('chave_acesso', $chaveAcesso)->firstOrFail();

        if ($cupom->status !== Cupom::STATUS_FALHA) {
            return ResultadoIngestao::duplicado($cupom);
        }

        ExtrairCupomJob::dispatch($cupom->id);

        return ResultadoIngestao::capturado($cupom);
    }

    /**
     * Extrai o cupom na SEFAZ, normaliza no modelo canônico e persiste — ou marca a falha
     * classificada. Chamado pelo ExtrairCupomJob (fila) e por `ingerir()` (síncrono).
     *
     * Retorna:
     *  - ACEITO           → validado e persistido;
     *  - FALHA_EXTRACAO   → transitória/estrutural (reprocessável; o Job decide retry);
     *  - REJEITADO        → negócio (cupom inexistente/cancelado na SEFAZ), sem retry.
     */
    public function processarExtracao(Cupom $cupom): ResultadoIngestao
    {
        $chave = ChaveAcesso::deEntrada($cupom->chave_acesso);

        $adaptador = $this->adaptadores[$chave->uf()] ?? null;
        if ($adaptador === null) {
            $cupom->update(['status' => Cupom::STATUS_FALHA, 'motivo_falha' => 'sem_adaptador_uf']);

            return ResultadoIngestao::falhaExtracao($cupom, SefazExtracaoException::ESTRUTURAL);
        }

        $cupom->update(['status' => Cupom::STATUS_EXTRAINDO, 'motivo_falha' => null]);

        try {
            $extraido = $adaptador->extrair($chave);
        } catch (SefazExtracaoException $e) {
            if ($e->tipo === SefazExtracaoException::NEGOCIO) {
                $cupom->update(['status' => Cupom::STATUS_REJEITADO, 'motivo_falha' => $e->tipo]);

                return ResultadoIngestao::rejeitado($e->tipo, $cupom);
            }

            // transitória/estrutural = reprocessável (ADR-002).
            $cupom->update(['status' => Cupom::STATUS_FALHA, 'motivo_falha' => $e->tipo]);

            return ResultadoIngestao::falhaExtracao($cupom, $e->tipo);
        }

        $this->normalizarEpersistir($cupom, $extraido);

        return ResultadoIngestao::aceito($cupom->fresh('itens'));
    }

    /**
     * Parse + validação estrutural e de escopo da chave (ADR-003). Retorna o VO válido,
     * ou um ResultadoIngestao REJEITADO quando a chave não serve (sem tocar o banco/portal).
     */
    private function validarChave(string $entrada): ChaveAcesso|ResultadoIngestao
    {
        try {
            $chave = ChaveAcesso::deEntrada($entrada);
        } catch (ChaveAcessoInvalidaException) {
            return ResultadoIngestao::rejeitado('chave_malformada');
        }

        if ($chave->uf() !== '35') {
            return ResultadoIngestao::rejeitado('fora_de_escopo_uf');
        }
        if ($chave->modelo() !== '65') {
            return ResultadoIngestao::rejeitado('modelo_invalido');
        }

        return $chave;
    }

    /**
     * Insere o cupom em `pendente` de forma idempotente (unique na chave, à prova de corrida).
     *
     * @return array{0: Cupom, 1: bool} o cupom e se foi criado agora
     */
    private function persistirPendente(ChaveAcesso $chave, string $origem): array
    {
        return DB::transaction(function () use ($chave, $origem) {
            $cupom = Cupom::firstOrCreate(
                ['chave_acesso' => $chave->valor()],
                [
                    'uf' => $chave->uf(),
                    'ano_mes' => $chave->anoMes(),
                    'cnpj_emitente' => $chave->cnpjEmitente(),
                    'modelo' => $chave->modelo(),
                    'status' => Cupom::STATUS_PENDENTE,
                    'origem' => $origem,
                ],
            );

            return [$cupom, $cupom->wasRecentlyCreated];
        });
    }

    private function normalizarEpersistir(Cupom $cupom, CupomExtraido $extraido): void
    {
        DB::transaction(function () use ($cupom, $extraido) {
            $cupom->update([
                'numero' => $extraido->numero,
                'serie' => $extraido->serie,
                'data_emissao' => $extraido->dataEmissao,
                'valor_total' => $extraido->valorTotal,
                'status' => Cupom::STATUS_VALIDADO,
                'motivo_falha' => null,
                'extraido_em' => now(),
            ]);

            // Idempotência do reprocessamento: recria os itens do zero.
            $cupom->itens()->delete();
            foreach ($extraido->itens as $item) {
                $cupom->itens()->create([
                    'sequencia' => $item->sequencia,
                    'descricao' => $item->descricao,
                    'codigo_loja' => $item->codigoLoja,
                    'gtin' => $item->gtin,
                    'quantidade' => $item->quantidade,
                    'unidade' => $item->unidade,
                    'valor_unitario' => $item->valorUnitario,
                    'valor_total' => $item->valorTotal,
                ]);
            }
        });
    }
}
