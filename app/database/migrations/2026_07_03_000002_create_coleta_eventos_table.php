<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Telemetria de coleta (STORY-012) — um evento append-only por tentativa de envio.
 *
 * É o denominador da taxa de sucesso da north-star: "enviados" inclui duplicatas e
 * inválidos, que NÃO viram linha em `cupons` (dedup/parse descartam). O válido-único-novo
 * (numerador) segue vindo de `cupons` (ADR-003). Sem PII (ADR-006): só situação, motivo
 * e a referência técnica ao cupom (uuid) — nada do consumidor.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coleta_eventos', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Desfecho do envio (espelha ResultadoIngestao): capturado|aceito|duplicado|
            // rejeitado|falha_extracao.
            $table->string('situacao');

            // Sub-sinal do funil quando há recusa/falha (chave_malformada, fora_de_escopo_uf,
            // modelo_invalido, transitoria, estrutural, negocio). Nulo no caminho feliz.
            $table->string('motivo')->nullable();

            // Referência técnica ao cupom quando ele existe (uuid, não é PII). Nulo nas
            // rejeições de parse, que não chegam a persistir cupom.
            $table->uuid('cupom_id')->nullable();

            $table->timestampsTz();

            $table->index('created_at');
            $table->index('situacao');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coleta_eventos');
    }
};
