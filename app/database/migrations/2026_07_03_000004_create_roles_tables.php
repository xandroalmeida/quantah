<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Autorização por papéis (ADR-009) — RBAC mínimo e extensível. Um usuário tem N papéis;
 * o acesso a recursos protegidos (ex.: backoffice de saque, STORY-017) é concedido por
 * papel via Gate. Sem matriz de permissões (YAGNI no MVP; evolui para papel→permissões
 * quando houver demanda).
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->uuid('id')->primary();
            $table->string('nome')->unique(); // ex.: 'operador'
            $table->timestampsTz();
        });

        Schema::create('role_user', function (Blueprint $table) {
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignUuid('role_id')->constrained('roles')->cascadeOnDelete();
            $table->primary(['user_id', 'role_id']);
            $table->index('role_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('role_user');
        Schema::dropIfExists('roles');
    }
};
