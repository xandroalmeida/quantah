<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modelo canônico do cupom (ADR-001), com deduplicação por chave (ADR-003).
 * SEM coluna de CPF por decisão de LGPD (ADR-006) — a base analítica é livre de PII.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cupons', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Chave natural (44 dígitos) — identidade e salvaguarda de dedup (ADR-003).
            $table->char('chave_acesso', 44)->unique();

            // Campos derivados da própria chave (visao §6.1).
            $table->char('uf', 2);
            $table->char('ano_mes', 4);
            $table->char('cnpj_emitente', 14);
            $table->char('modelo', 2);

            // Campos preenchidos pela extração (ADR-002) — nulos até validar.
            $table->unsignedBigInteger('numero')->nullable();
            $table->unsignedInteger('serie')->nullable();
            $table->timestampTz('data_emissao')->nullable();
            $table->decimal('valor_total', 12, 2)->nullable();

            $table->string('status')->default('pendente');
            $table->string('origem')->default('scan');
            $table->text('motivo_falha')->nullable();
            $table->timestampTz('extraido_em')->nullable();

            $table->timestampsTz();

            $table->index(['uf', 'ano_mes']);
            $table->index('status');
        });

        Schema::create('cupom_itens', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('cupom_id')->constrained('cupons')->cascadeOnDelete();
            $table->unsignedInteger('sequencia');
            $table->text('descricao');
            $table->string('codigo_loja')->nullable();
            $table->string('gtin')->nullable();
            $table->decimal('quantidade', 14, 4);
            $table->string('unidade');
            $table->decimal('valor_unitario', 14, 4);
            $table->decimal('valor_total', 12, 2);
            $table->timestampsTz();

            $table->unique(['cupom_id', 'sequencia']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupom_itens');
        Schema::dropIfExists('cupons');
    }
};
