<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Saque (resgate) — PIX assistido no MVP (ADR-005). Base de pagamento SEGREGADA (ADR-006):
 * guarda o mínimo de PII (CPF + chave PIX do titular), nunca cruza para a base analítica.
 *
 * Máquina de estados (ADR-005): solicitado → em_analise → aprovado → pago; em_analise →
 * rejeitado (estorno). A **reserva** (débito no ledger) acontece na solicitação; o estorno
 * devolve ao saldo. O elo com o ledger é `carteira_transacoes.saque_id`.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('saques', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carteira_id')->constrained('carteiras')->cascadeOnDelete();
            $table->bigInteger('valor_centavos');

            // KYC mínimo (PII de pagamento — base segregada, ADR-006). Canônico sem máscara.
            $table->char('cpf', 11);
            $table->string('chave_pix'); // tipo CPF no MVP (titularidade por construção)

            $table->string('status')->default('solicitado');
            $table->text('comprovante')->nullable();     // e2e/id do PIX; preenchido em `pago`
            $table->foreignId('processado_por')->nullable()->constrained('users')->nullOnDelete();

            $table->timestampsTz();

            $table->index('carteira_id');
            $table->index('status');
        });

        DB::statement(
            "ALTER TABLE saques ADD CONSTRAINT saques_status_valido
             CHECK (status IN ('solicitado', 'em_analise', 'aprovado', 'pago', 'rejeitado'))"
        );

        DB::statement('ALTER TABLE saques ADD CONSTRAINT saques_valor_positivo CHECK (valor_centavos > 0)');

        // Elo do ledger com o saque (débito/estorno) — mesma base de pagamento, FK legítima.
        Schema::table('carteira_transacoes', function (Blueprint $table) {
            $table->foreignUuid('saque_id')->nullable()->after('cupom_id')
                ->constrained('saques')->nullOnDelete();
            $table->index('saque_id');
        });
    }

    public function down(): void
    {
        Schema::table('carteira_transacoes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('saque_id');
        });
        Schema::dropIfExists('saques');
    }
};
