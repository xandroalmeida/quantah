<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Item do cupom (ADR-001). `gtin`/`codigo_loja` são apenas armazenados;
 * a reconciliação de produtos entre lojas é ADR-004, fora desta onda.
 */
class CupomItem extends Model
{
    use HasUuids;

    protected $table = 'cupom_itens';

    protected $fillable = [
        'cupom_id',
        'sequencia',
        'descricao',
        'codigo_loja',
        'gtin',
        'quantidade',
        'unidade',
        'valor_unitario',
        'valor_total',
    ];

    protected function casts(): array
    {
        return [
            'quantidade' => 'decimal:4',
            'valor_unitario' => 'decimal:4',
            'valor_total' => 'decimal:2',
        ];
    }

    /** @return BelongsTo<Cupom, $this> */
    public function cupom(): BelongsTo
    {
        return $this->belongsTo(Cupom::class);
    }
}
