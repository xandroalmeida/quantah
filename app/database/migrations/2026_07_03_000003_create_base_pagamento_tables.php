<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Base de pagamento SEGREGADA da base analítica (ADR-005 + ADR-006) — carteira, ledger e
 * a ponte de atribuição do cupom ao Colaborador. Aqui vive o mínimo de PII de pagamento;
 * a base analítica (`cupons`/itens) permanece livre de PII. O elo com o cupom é uma
 * **referência lógica** (`cupom_id` uuid, SEM FK dura) para não cruzar as duas bases.
 *
 * Princípios (ADR-005): dinheiro em **centavos inteiros**; ledger **append-only** é a fonte
 * da verdade do saldo (`saldo_centavos` é cache reconciliável, sempre `>= 0`); crédito de
 * cashback **idempotente por cupom** (no máximo um `credito_cashback` por `cupom_id`).
 */
return new class extends Migration
{
    public function up(): void
    {
        // Carteira: uma por Colaborador. `saldo_centavos` é cache do SUM(ledger), nunca negativo.
        Schema::create('carteiras', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignId('user_id')->unique()->constrained()->cascadeOnDelete();
            $table->bigInteger('saldo_centavos')->default(0);
            $table->timestampsTz();
        });

        DB::statement(
            'ALTER TABLE carteiras ADD CONSTRAINT carteiras_saldo_nao_negativo CHECK (saldo_centavos >= 0)'
        );

        // Ledger append-only — fonte da verdade do saldo. Sinal: crédito/estorno > 0, débito < 0.
        Schema::create('carteira_transacoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->foreignUuid('carteira_id')->constrained('carteiras')->cascadeOnDelete();
            $table->string('tipo'); // credito_cashback | debito_saque | estorno_saque
            $table->bigInteger('valor_centavos');

            // Referência LÓGICA ao cupom (base analítica) — uuid, sem FK dura (segregação, ADR-006).
            $table->uuid('cupom_id')->nullable();

            $table->timestampsTz();

            $table->index('carteira_id');
        });

        DB::statement(
            "ALTER TABLE carteira_transacoes ADD CONSTRAINT carteira_transacoes_tipo_valido
             CHECK (tipo IN ('credito_cashback', 'debito_saque', 'estorno_saque'))"
        );

        // Idempotência do crédito: no máximo UM credito_cashback por cupom (amarra à dedup, ADR-003).
        DB::statement(
            "CREATE UNIQUE INDEX carteira_transacoes_credito_por_cupom_unique
             ON carteira_transacoes (cupom_id) WHERE tipo = 'credito_cashback'"
        );

        // Ponte de atribuição: quem coletou (1º) o cupom recebe o crédito. Um cupom = um coletor.
        Schema::create('cupom_atribuicoes', function (Blueprint $table) {
            $table->uuid('id')->primary();
            // Referência lógica ao cupom (uuid, sem FK dura — segregação de bases).
            $table->uuid('cupom_id')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->timestampsTz();

            $table->index('user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cupom_atribuicoes');
        Schema::dropIfExists('carteira_transacoes');
        Schema::dropIfExists('carteiras');
    }
};
