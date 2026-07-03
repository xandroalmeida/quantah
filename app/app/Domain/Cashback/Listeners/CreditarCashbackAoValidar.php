<?php

namespace App\Domain\Cashback\Listeners;

use App\Domain\Cashback\CreditarCashbackService;
use App\Domain\Coleta\Events\CupomValidado;
use App\Models\Cupom;
use Illuminate\Contracts\Queue\ShouldQueue;

/**
 * Credita o cashback quando um cupom é validado (STORY-015).
 *
 * **Enfileirado** (`ShouldQueue`): o crédito roda no worker, desacoplado do sucesso da
 * extração — assim uma falha de crédito não derruba a extração, e o retry da fila é
 * seguro porque `CreditarCashbackService` é idempotente por cupom. Em teste a fila é
 * `sync`, então roda inline. Sem PII: recebe só o uuid do cupom.
 */
class CreditarCashbackAoValidar implements ShouldQueue
{
    public function __construct(private readonly CreditarCashbackService $service) {}

    public function handle(CupomValidado $event): void
    {
        $cupom = Cupom::find($event->cupomId);
        if ($cupom === null) {
            return; // cupom sumiu entre o evento e o processamento — nada a fazer
        }

        $this->service->creditarPorCupom($cupom);
    }
}
