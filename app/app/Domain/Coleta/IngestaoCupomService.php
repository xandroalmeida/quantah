<?php

namespace App\Domain\Coleta;

use App\Domain\Coleta\Sefaz\CupomExtraido;
use App\Domain\Coleta\Sefaz\SefazAdapter;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use App\Models\Cupom;
use Illuminate\Support\Facades\DB;

/**
 * Fronteira de ingestão do cupom (ADR-001) — a única porta que a camada web conhece.
 *
 * Fluxo: parse/valida a chave (ADR-003) → decide escopo (SP, NFC-e) → deduplica de
 * forma idempotente (unique + firstOrCreate) → extrai/normaliza (ADR-002) descartando
 * CPF (ADR-006). Reenviar a mesma chave é no-op observável (DUPLICADO).
 *
 * NOTA DO SPIKE (STORY-008): aqui a extração roda de forma SÍNCRONA para provar o
 * caminho vertical fim a fim. Em produção (STORY-010) o passo de extração é um Job
 * enfileirado no Postgres com retry/backoff/reprocessamento (ADR-002); a fronteira e
 * a idempotência permanecem idênticas.
 */
final class IngestaoCupomService
{
    /** @var array<string, SefazAdapter> adaptador por UF (só SP no MVP) */
    private array $adaptadores;

    public function __construct(SefazAdapter ...$adaptadores)
    {
        foreach ($adaptadores as $adaptador) {
            $this->adaptadores[$adaptador->uf()] = $adaptador;
        }
    }

    public function ingerir(string $entrada, string $origem = 'scan'): ResultadoIngestao
    {
        // 1. Parse + validação estrutural da chave (formato + DV mod 11) — ADR-003.
        try {
            $chave = ChaveAcesso::deEntrada($entrada);
        } catch (ChaveAcessoInvalidaException $e) {
            return ResultadoIngestao::rejeitado('chave_malformada');
        }

        // 2. Escopo da onda: só SP (UF 35) e NFC-e (modelo 65) — validado só pela chave.
        if ($chave->uf() !== '35') {
            return ResultadoIngestao::rejeitado('fora_de_escopo_uf');
        }
        if ($chave->modelo() !== '65') {
            return ResultadoIngestao::rejeitado('modelo_invalido');
        }

        // 3. Dedup idempotente: a unicidade é garantida pelo banco (à prova de corrida).
        [$cupom, $novo] = $this->persistirPendente($chave, $origem);

        if (! $novo && $cupom->status !== Cupom::STATUS_FALHA) {
            // Já recebido antes (e não está em falha reprocessável) → no-op.
            return ResultadoIngestao::duplicado($cupom);
        }

        // 4. Extração + normalização (síncrona no spike; Job enfileirado em produção).
        return $this->extrair($cupom, $chave);
    }

    /**
     * Captura + handoff (STORY-009): valida a chave, aplica escopo e persiste o cupom
     * `pendente` de forma idempotente — SEM extrair (a validação SEFAZ/normalização é a
     * STORY-010, disparada por Job na fila, ADR-002). É a porta que a camada web usa.
     *
     * Retorna CAPTURADO (novo, aguardando validação), DUPLICADO (já recebido) ou
     * REJEITADO (chave malformada / fora de escopo) — o web mapeia para a confirmação.
     */
    public function capturar(string $entrada, string $origem = 'scan'): ResultadoIngestao
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

        [$cupom, $novo] = $this->persistirPendente($chave, $origem);

        // Aqui é onde a STORY-010 vai despachar o ExtrairCupomJob para o novo cupom.

        return $novo
            ? ResultadoIngestao::capturado($cupom)
            : ResultadoIngestao::duplicado($cupom);
    }

    /** Reprocessa um cupom que ficou em `falha` (ADR-002) — idempotente, não duplica. */
    public function reprocessar(string $chaveAcesso): ResultadoIngestao
    {
        $cupom = Cupom::where('chave_acesso', $chaveAcesso)->firstOrFail();

        if ($cupom->status !== Cupom::STATUS_FALHA) {
            return ResultadoIngestao::duplicado($cupom);
        }

        return $this->extrair($cupom, ChaveAcesso::deEntrada($cupom->chave_acesso));
    }

    /**
     * Insere o cupom em estado `pendente` de forma idempotente.
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

    private function extrair(Cupom $cupom, ChaveAcesso $chave): ResultadoIngestao
    {
        $adaptador = $this->adaptadores[$chave->uf()] ?? null;
        if ($adaptador === null) {
            $cupom->update([
                'status' => Cupom::STATUS_FALHA,
                'motivo_falha' => 'sem_adaptador_uf',
            ]);

            return ResultadoIngestao::falhaExtracao($cupom, SefazExtracaoException::ESTRUTURAL);
        }

        $cupom->update(['status' => Cupom::STATUS_EXTRAINDO, 'motivo_falha' => null]);

        try {
            $extraido = $adaptador->extrair($chave);
        } catch (SefazExtracaoException $e) {
            $cupom->update([
                'status' => $e->tipo === SefazExtracaoException::NEGOCIO
                    ? Cupom::STATUS_REJEITADO
                    : Cupom::STATUS_FALHA,   // transitória/estrutural = reprocessável (ADR-002)
                'motivo_falha' => $e->tipo,
            ]);

            return ResultadoIngestao::falhaExtracao($cupom, $e->tipo);
        }

        $this->normalizarEpersistir($cupom, $extraido);

        return ResultadoIngestao::aceito($cupom->fresh('itens'));
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
