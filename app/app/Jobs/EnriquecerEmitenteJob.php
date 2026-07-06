<?php

namespace App\Jobs;

use App\Domain\Enriquecimento\EnriquecimentoException;
use App\Domain\Enriquecimento\EnriquecimentoService;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Enriquecimento assíncrono do CNPJ do emitente na fila (ADR-013) — roda no worker,
 * fora do request. Cache-first (o serviço só bate na fonte em miss/vencido).
 *
 * Política de falha (espelha ADR-002):
 *  - TRANSITÓRIA (timeout/5xx/429) → relança para a fila retentar com backoff;
 *  - ESTRUTURAL (contrato mudou) → alerta e marca `nao_enriquecido`, sem retry;
 *  - esgotado o retry, `failed()` marca `nao_enriquecido`.
 * Em nenhum caminho a falha vira erro ao Colaborador nem perde a solicitação (CA-5).
 */
class EnriquecerEmitenteJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(public string $cnpj) {}

    /** Backoff crescente — respeita a fonte pública (ADR-013). */
    public function backoff(): array
    {
        return [30, 120, 300];
    }

    public function handle(EnriquecimentoService $service): void
    {
        try {
            $service->enriquecer($this->cnpj);
        } catch (EnriquecimentoException $e) {
            if ($e->tipo === EnriquecimentoException::TRANSITORIA) {
                throw $e; // deixa a fila retentar com backoff
            }

            // Estrutural: contrato mudou → alerta e degrada (não trava, não insiste).
            Log::warning('Enriquecimento CNPJ: falha estrutural (contrato da fonte).', [
                'cnpj' => $this->cnpj,
                'erro' => $e->getMessage(),
            ]);
            $service->registrarNaoEnriquecido($this->cnpj);
        }
    }

    /** Retry esgotado → registra `nao_enriquecido` (reconsultável); nunca perde a solicitação. */
    public function failed(Throwable $e): void
    {
        app(EnriquecimentoService::class)->registrarNaoEnriquecido($this->cnpj);

        Log::warning('Enriquecimento CNPJ esgotou as tentativas.', [
            'cnpj' => $this->cnpj,
            'erro' => $e->getMessage(),
        ]);
    }
}
