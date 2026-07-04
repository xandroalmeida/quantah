<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Lançamento no ledger append-only da carteira (ADR-005) — a **fonte da verdade** do saldo.
 * Sinal do valor: crédito/estorno `> 0`, débito `< 0`. `cupom_id` é referência **lógica**
 * (uuid, sem FK dura) à base analítica, preenchida no `credito_cashback`.
 *
 * Idempotência do crédito: índice único parcial `(cupom_id) WHERE tipo='credito_cashback'`
 * garante no máximo um crédito por cupom (amarra à dedup por chave, ADR-003).
 */
class CarteiraTransacao extends Model
{
    use HasUuids;

    /** Crédito de cashback sobre cupom válido-único-novo (STORY-015). */
    public const TIPO_CREDITO_CASHBACK = 'credito_cashback';

    /** Débito por reserva/pagamento de saque (STORY-017). */
    public const TIPO_DEBITO_SAQUE = 'debito_saque';

    /** Estorno de saque rejeitado, devolve o valor ao saldo (STORY-017). */
    public const TIPO_ESTORNO_SAQUE = 'estorno_saque';

    protected $table = 'carteira_transacoes';

    protected $fillable = [
        'carteira_id',
        'tipo',
        'valor_centavos',
        'cupom_id',
        'saque_id',
    ];

    protected function casts(): array
    {
        return [
            'valor_centavos' => 'integer',
        ];
    }

    /** @return BelongsTo<Carteira, $this> */
    public function carteira(): BelongsTo
    {
        return $this->belongsTo(Carteira::class);
    }
}
