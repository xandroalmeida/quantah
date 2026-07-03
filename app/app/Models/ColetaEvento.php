<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Evento de telemetria da coleta (STORY-012) — um por tentativa de envio, append-only.
 *
 * É a fonte do "enviados" da taxa de sucesso (inclui duplicata/inválido, que não viram
 * cupom). Sem PII (ADR-006): só situação, motivo e o uuid técnico do cupom.
 */
class ColetaEvento extends Model
{
    use HasUuids;

    protected $table = 'coleta_eventos';

    protected $fillable = [
        'situacao',
        'motivo',
        'cupom_id',
    ];
}
