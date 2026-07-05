<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Leads B2B (STORY-026 · EPIC-005). Captação de interessados no Quantah Intelligence: nome,
 * e-mail e empresa. O e-mail é a chave natural de deduplicação (único) — a captação é
 * idempotente por e-mail normalizado (minúsculas/trim). Base de captação B2B, sem relação com
 * a base analítica de cupons nem com a base de pagamento (ADR-006). Visível no Backoffice sob o
 * papel operacional (STORY-027).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('leads', function (Blueprint $table) {
            $table->id();
            $table->string('nome');
            $table->string('email')->unique(); // chave de deduplicação idempotente
            $table->string('empresa');
            $table->timestampsTz();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('leads');
    }
};
