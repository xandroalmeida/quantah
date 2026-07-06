<?php

namespace Database\Factories;

use App\Models\Emitente;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Str;

/**
 * @extends Factory<Emitente>
 */
class EmitenteFactory extends Factory
{
    protected $model = Emitente::class;

    public function definition(): array
    {
        return [
            'id' => (string) Str::uuid(),
            'cnpj' => (string) $this->faker->numerify('##############'),
            'razao_social' => strtoupper($this->faker->company()).' LTDA',
            'nome_fantasia' => null,
            'cnae_principal_codigo' => '4711302',
            'cnae_principal_descricao' => 'Comércio varejista de mercadorias em geral — supermercados',
            'cnaes_secundarios' => [],
            'situacao_cadastral' => 'ATIVA',
            'municipio' => 'SAO PAULO',
            'uf' => 'SP',
            'status_enriquecimento' => Emitente::STATUS_ENRIQUECIDO,
            'fonte' => 'brasilapi',
            'enriquecido_em' => now(),
        ];
    }

    /** Registro definitivo e cacheável (enriquecido, com `enriquecido_em`). */
    public function enriquecido(): static
    {
        return $this->state(fn () => [
            'status_enriquecimento' => Emitente::STATUS_ENRIQUECIDO,
            'enriquecido_em' => now(),
        ]);
    }

    /** Falha transitória: reconsultável, sem `enriquecido_em`. */
    public function naoEnriquecido(): static
    {
        return $this->state(fn () => [
            'status_enriquecimento' => Emitente::STATUS_NAO_ENRIQUECIDO,
            'cnae_principal_codigo' => null,
            'cnae_principal_descricao' => null,
            'enriquecido_em' => null,
        ]);
    }
}
