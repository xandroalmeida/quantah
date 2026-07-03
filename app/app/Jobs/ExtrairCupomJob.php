<?php

namespace App\Jobs;

use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\ResultadoIngestao;
use App\Domain\Coleta\Sefaz\SefazExtracaoException;
use App\Models\Cupom;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Extração assíncrona do cupom na SEFAZ-SP (ADR-002) — roda no worker, fora do request.
 *
 * Política de falha (a classificação vem do adaptador, via ResultadoIngestao):
 *  - TRANSITÓRIA (timeout, 5xx, rate limit) → **relança** para a fila retentar com backoff;
 *  - ESTRUTURAL (quebra de layout / captcha) → cupom fica `falha`, **sem retry** (alerta);
 *  - NEGÓCIO (cupom inexistente/cancelado) → cupom `rejeitado`, sem retry.
 * Esgotado o retry, `failed()` deixa o cupom `falha` (dead-letter, reprocessável).
 */
class ExtrairCupomJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $cupomId) {}

    /** Backoff crescente entre as tentativas (segundos). */
    public function backoff(): array
    {
        return [10, 60, 300];
    }

    public function handle(IngestaoCupomService $ingestao): void
    {
        $cupom = Cupom::find($this->cupomId);

        // Idempotência: cupom sumiu ou já está em estado terminal → nada a fazer.
        if ($cupom === null || in_array($cupom->status, [
            Cupom::STATUS_VALIDADO,
            Cupom::STATUS_REJEITADO,
        ], true)) {
            return;
        }

        $resultado = $ingestao->processarExtracao($cupom);

        // Só a falha TRANSITÓRIA justifica retry — relança para a fila cuidar do backoff.
        if ($resultado->situacao === ResultadoIngestao::FALHA_EXTRACAO
            && $resultado->motivo === SefazExtracaoException::TRANSITORIA) {
            throw SefazExtracaoException::transitoria('Extração transitória falhou; reprocessando.');
        }
    }

    /** Retry esgotado → garante o cupom em `falha` (dead-letter, reprocessável). */
    public function failed(Throwable $e): void
    {
        $cupom = Cupom::find($this->cupomId);
        if ($cupom !== null && ! in_array($cupom->status, [
            Cupom::STATUS_VALIDADO,
            Cupom::STATUS_REJEITADO,
        ], true)) {
            $cupom->update([
                'status' => Cupom::STATUS_FALHA,
                'motivo_falha' => SefazExtracaoException::TRANSITORIA,
            ]);
        }

        Log::warning('Extração de cupom esgotou as tentativas', [
            'cupom_id' => $this->cupomId,
            'erro' => $e->getMessage(),
        ]);
    }
}
