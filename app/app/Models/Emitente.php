<?php

namespace App\Models;

use Database\Factories\EmitenteFactory;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Registro cadastral do emitente enriquecido (ADR-014). É, ao mesmo tempo, o **cache**
 * do CNPJ e o **registro canônico** do emitente — chave natural `cnpj` (14 dígitos).
 * Dado público de empresa (não é PII do consumidor — ADR-006).
 *
 * `enriquecido_em` é a base do TTL: presente ⇒ resposta definitiva cacheável; nulo
 * (status `nao_enriquecido`) ⇒ falha transitória, reconsultável.
 */
class Emitente extends Model
{
    use HasFactory, HasUuids;

    // Desfechos do enriquecimento (ADR-014).
    public const STATUS_ENRIQUECIDO = 'enriquecido';

    public const STATUS_SEM_CNAE = 'sem_cnae';

    public const STATUS_NAO_ENCONTRADO = 'nao_encontrado';

    public const STATUS_NAO_ENRIQUECIDO = 'nao_enriquecido';

    protected $fillable = [
        'cnpj',
        'razao_social',
        'nome_fantasia',
        'cnae_principal_codigo',
        'cnae_principal_descricao',
        'cnaes_secundarios',
        'situacao_cadastral',
        'municipio',
        'uf',
        'status_enriquecimento',
        'fonte',
        'enriquecido_em',
    ];

    protected function casts(): array
    {
        return [
            'cnaes_secundarios' => 'array',
            'enriquecido_em' => 'datetime',
        ];
    }

    /**
     * Cache-hit: tem resposta definitiva (`enriquecido_em` preenchido) dentro do TTL.
     * `nao_enriquecido` (falha transitória) tem `enriquecido_em` nulo → nunca é fresco.
     */
    public function estaFresco(int $ttlDias): bool
    {
        return $this->enriquecido_em !== null
            && $this->enriquecido_em->greaterThanOrEqualTo(now()->subDays($ttlDias));
    }

    protected static function newFactory(): EmitenteFactory
    {
        return EmitenteFactory::new();
    }
}
