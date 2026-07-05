<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Endereço do estabelecimento (logradouro/nº/bairro), município e UF do emitente,
 * capturados na extração SEFAZ (extensão da STORY-034). Como `nome_emitente`, é dado
 * público do emitente — não é PII do consumidor (ADR-006). Nullable: cupons antigos e
 * fontes sem o bloco de endereço degradam com fallback na UI; sem backfill nesta onda.
 *
 * `uf` (numérico "35", derivado da chave) já existe; `uf_emitente` guarda a sigla legível
 * ("SP") extraída do DANFE — são coisas distintas.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cupons', function (Blueprint $table) {
            $table->string('endereco_emitente')->nullable()->after('nome_emitente');
            $table->string('municipio_emitente')->nullable()->after('endereco_emitente');
            $table->string('uf_emitente', 2)->nullable()->after('municipio_emitente');
        });
    }

    public function down(): void
    {
        Schema::table('cupons', function (Blueprint $table) {
            $table->dropColumn(['endereco_emitente', 'municipio_emitente', 'uf_emitente']);
        });
    }
};
