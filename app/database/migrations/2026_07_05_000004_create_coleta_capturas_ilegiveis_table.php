<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Diagnóstico de captura ilegível (reforço da coleta pós-campo).
 *
 * Quando o cliente NÃO consegue ler o QR da nota (impressão térmica danificada), nada
 * chega ao `/coletar`. Aqui registramos a tentativa para diagnóstico futuro: quantas
 * falham e, quando o OCR consegue, a chave de 44 dígitos (identidade do documento/emitente,
 * não é dado do consumidor — ADR-006). Para o usuário, segue "não foi possível ler".
 *
 * Tabela separada da telemetria `coleta_eventos` (que é o denominador da taxa de sucesso e
 * não pode ser contaminada por este caminho que nunca vira cupom).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('coleta_capturas_ilegiveis', function (Blueprint $table) {
            $table->uuid('id')->primary();

            // Quem tentou (Colaborador logado), para correlação. Nulo se anônimo.
            $table->foreignId('user_id')->nullable()->index();

            // Chave de acesso lida por OCR quando possível (44 dígitos, DV válido). Em
            // geral nula: a chave costuma ficar ACIMA do QR, fora do quadro capturado.
            $table->string('chave', 44)->nullable();

            $table->timestampsTz();

            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('coleta_capturas_ilegiveis');
    }
};
