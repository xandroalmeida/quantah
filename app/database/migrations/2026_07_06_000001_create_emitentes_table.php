<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tabela `emitentes` (ADR-014): cache + registro canônico do emitente enriquecido.
 * Chave natural `cnpj` (14 dígitos, UNIQUE). Dado público de empresa (ADR-006).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('emitentes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->char('cnpj', 14)->unique();          // chave natural
            $table->text('razao_social')->nullable();
            $table->text('nome_fantasia')->nullable();
            $table->char('cnae_principal_codigo', 7)->nullable();
            $table->text('cnae_principal_descricao')->nullable();
            $table->jsonb('cnaes_secundarios')->nullable();
            $table->text('situacao_cadastral')->nullable();
            $table->text('municipio')->nullable();
            $table->char('uf', 2)->nullable();
            // enriquecido | sem_cnae | nao_encontrado | nao_enriquecido (ADR-014)
            $table->text('status_enriquecimento');
            $table->text('fonte')->nullable();
            $table->timestampTz('enriquecido_em')->nullable();  // base do TTL
            $table->timestampsTz();

            $table->index('enriquecido_em');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('emitentes');
    }
};
