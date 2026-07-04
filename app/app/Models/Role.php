<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * Papel de autorização (ADR-009) — RBAC por papéis. `nome` é a chave (ex.: `operador`).
 * Acesso a recursos protegidos é concedido por papel via Gate.
 */
class Role extends Model
{
    use HasUuids;

    /** Operador do backoffice de saque (STORY-017). */
    public const OPERADOR = 'operador';

    protected $fillable = ['nome'];

    /** @return BelongsToMany<User, $this> */
    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class);
    }
}
