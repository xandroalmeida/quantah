<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Ponte entre o cupom (base analítica, sem PII) e o Colaborador que o coletou (base de
 * pagamento) — ADR-005/006. Existe porque `cupons` não tem `user_id` por design de LGPD.
 *
 * Gravada na coleta autenticada, apenas para o **1º** coletor de cada chave (dedup, ADR-003):
 * `cupom_id` é UNIQUE. É a origem de "para quem creditar" quando o cupom fica válido — o
 * crédito acontece de forma assíncrona, fora do contexto de request.
 */
class CupomAtribuicao extends Model
{
    use HasUuids;

    protected $table = 'cupom_atribuicoes';

    protected $fillable = [
        'cupom_id',
        'user_id',
    ];

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
