<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Agregado raiz do cupom (ADR-001). Chave natural = `chave_acesso` (44 dígitos, UNIQUE).
 * NÃO possui coluna de CPF — a base analítica nasce livre de PII (ADR-006).
 */
class Cupom extends Model
{
    use HasUuids;

    // Ciclo de vida da extração (ADR-002).
    public const STATUS_PENDENTE = 'pendente';

    public const STATUS_EXTRAINDO = 'extraindo';

    public const STATUS_VALIDADO = 'validado';

    public const STATUS_FALHA = 'falha';

    public const STATUS_REJEITADO = 'rejeitado';

    protected $table = 'cupons';

    protected $fillable = [
        'chave_acesso',
        'uf',
        'ano_mes',
        'cnpj_emitente',
        'nome_emitente',
        'endereco_emitente',
        'municipio_emitente',
        'uf_emitente',
        'modelo',
        'numero',
        'serie',
        'data_emissao',
        'valor_total',
        'status',
        'origem',
        'qr_conteudo',
        'motivo_falha',
        'extraido_em',
    ];

    protected function casts(): array
    {
        return [
            'data_emissao' => 'datetime',
            'extraido_em' => 'datetime',
            'valor_total' => 'decimal:2',
        ];
    }

    /** @return HasMany<CupomItem, $this> */
    public function itens(): HasMany
    {
        return $this->hasMany(CupomItem::class);
    }

    public function validado(): bool
    {
        return $this->status === self::STATUS_VALIDADO;
    }

    /**
     * Cupons "válidos, únicos e novos" (base da north-star — STORY-012). A unicidade e o
     * "novo" são garantidos por construção: `chave_acesso` é UNIQUE (ADR-003), então todo
     * cupom `validado` é, por definição, único e de primeira ocorrência.
     *
     * @param  Builder<Cupom>  $query
     */
    public function scopeValidosUnicosNovos($query)
    {
        return $query->where('status', self::STATUS_VALIDADO);
    }
}
