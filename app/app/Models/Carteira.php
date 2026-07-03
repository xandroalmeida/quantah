<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Carteira do Colaborador (ADR-005) — base de pagamento segregada (ADR-006). Uma por
 * usuário. `saldo_centavos` é **cache reconciliável** do `SUM(carteira_transacoes)` e
 * NUNCA fica negativo (invariante garantida por CHECK no banco). Dinheiro em centavos.
 */
class Carteira extends Model
{
    use HasUuids;

    protected $table = 'carteiras';

    protected $fillable = [
        'user_id',
        'saldo_centavos',
    ];

    protected function casts(): array
    {
        return [
            'saldo_centavos' => 'integer',
        ];
    }

    /** @return BelongsTo<User, $this> */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /** @return HasMany<CarteiraTransacao, $this> */
    public function transacoes(): HasMany
    {
        return $this->hasMany(CarteiraTransacao::class);
    }
}
