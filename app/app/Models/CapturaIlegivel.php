<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro de diagnóstico de uma captura ilegível (QR não decodificado no cliente).
 *
 * Append-only, para diagnóstico futuro (frequência de falhas e, quando o OCR pega, a
 * chave). Sem dado do consumidor (ADR-006) — a chave é a identidade do documento/emitente.
 */
class CapturaIlegivel extends Model
{
    use HasUuids;

    protected $table = 'coleta_capturas_ilegiveis';

    protected $fillable = [
        'user_id',
        'chave',
    ];
}
