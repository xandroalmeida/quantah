<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Nome do estabelecimento (razão social/nome fantasia do emitente) capturado na extração
 * SEFAZ (STORY-034). Estende o modelo canônico (ADR-001) — não é PII do consumidor (ADR-006),
 * é dado público do emitente. Nullable: cupons antigos e fontes sem o nome degradam com
 * fallback na UI; sem backfill nesta onda.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cupons', function (Blueprint $table) {
            $table->string('nome_emitente')->nullable()->after('cnpj_emitente');
        });
    }

    public function down(): void
    {
        Schema::table('cupons', function (Blueprint $table) {
            $table->dropColumn('nome_emitente');
        });
    }
};
