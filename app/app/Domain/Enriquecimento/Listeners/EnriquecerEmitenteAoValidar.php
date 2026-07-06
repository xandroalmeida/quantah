<?php

namespace App\Domain\Enriquecimento\Listeners;

use App\Domain\Coleta\Events\CupomValidado;
use App\Domain\Enriquecimento\EnriquecimentoService;
use App\Models\Cupom;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Dispara o enriquecimento do emitente quando um cupom é validado (EPIC-009, ADR-013).
 *
 * **Enfileirado** (`ShouldQueue`): roda no worker, fora do request — a latência do envio
 * do cupom pelo Colaborador não muda (CA-1). Coleta só anuncia o fato `CupomValidado`;
 * este contexto reage, sem acoplar Coleta a Enriquecimento (padrão IDR-008). Delega ao
 * serviço, que é cache-first: emitente já no cache dentro do TTL ⇒ nenhuma chamada
 * externa (CA-2). Recebe só o uuid do cupom (sem PII); recarrega o CNPJ.
 */
class EnriquecerEmitenteAoValidar implements ShouldQueue
{
    public function __construct(private readonly EnriquecimentoService $service) {}

    public function handle(CupomValidado $event): void
    {
        $cupom = Cupom::find($event->cupomId);
        if ($cupom === null) {
            return; // cupom sumiu entre o evento e o processamento — nada a fazer
        }

        $this->service->solicitar($cupom->cnpj_emitente);
    }
}
