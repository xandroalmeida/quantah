<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Lead B2B (STORY-026). Interessado no Quantah Intelligence capturado na landing pública.
 * O e-mail é único (deduplicação idempotente). A captação/normalização é responsabilidade da
 * ação de domínio `App\Domain\Lead\CapturarLead` — o Model é só a persistência.
 */
class Lead extends Model
{
    protected $fillable = ['nome', 'email', 'empresa'];
}
