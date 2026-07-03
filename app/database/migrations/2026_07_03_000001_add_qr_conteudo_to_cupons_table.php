<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Guarda o conteúdo bruto do QR da NFC-e (STORY-010). A consulta ao portal de SP exige
 * o **QR completo assinado** (`p=chave|versao|amb|token|hash`), não só a chave de 44
 * dígitos — então a extração assíncrona precisa do QR original persistido. Sem PII: o
 * `p` carrega chave + assinatura, não dado pessoal.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cupons', function (Blueprint $table) {
            $table->text('qr_conteudo')->nullable()->after('origem');
        });
    }

    public function down(): void
    {
        Schema::table('cupons', function (Blueprint $table) {
            $table->dropColumn('qr_conteudo');
        });
    }
};
