<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Saque (resgate PIX assistido — ADR-005). Base de pagamento segregada (ADR-006): guarda o
 * mínimo de PII (CPF + chave PIX do titular). Máquina de estados:
 * solicitado → em_analise → aprovado → pago; em_analise → rejeitado (estorno).
 */
class Saque extends Model
{
    use HasUuids;

    public const STATUS_SOLICITADO = 'solicitado';

    public const STATUS_EM_ANALISE = 'em_analise';

    public const STATUS_APROVADO = 'aprovado';

    public const STATUS_PAGO = 'pago';

    public const STATUS_REJEITADO = 'rejeitado';

    protected $table = 'saques';

    protected $fillable = [
        'carteira_id',
        'valor_centavos',
        'cpf',
        'chave_pix',
        'status',
        'comprovante',
        'processado_por',
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
