<?php

namespace Tests\Feature\Coleta;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * Modelo canônico do cupom (ADR-001) — schema e reversibilidade da migração.
 * O esquema é criado pela suíte (RefreshDatabase); aqui checamos as invariantes
 * estruturais e que a migração é reversível (`down()` derruba as duas tabelas).
 */
class MigracaoCupomTest extends TestCase
{
    use RefreshDatabase;

    public function test_tabelas_do_modelo_canonico_existem(): void
    {
        $this->assertTrue(Schema::hasTable('cupons'));
        $this->assertTrue(Schema::hasTable('cupom_itens'));
    }

    /** ADR-003: a dedup depende do índice único na chave. */
    public function test_chave_acesso_tem_coluna_e_e_unica(): void
    {
        $this->assertTrue(Schema::hasColumn('cupons', 'chave_acesso'));

        $indices = collect(Schema::getIndexes('cupons'));
        $this->assertTrue(
            $indices->contains(fn ($i) => $i['unique'] && in_array('chave_acesso', $i['columns'], true)),
            'cupons.chave_acesso deve ter índice único (dedup, ADR-003).'
        );
    }

    /** ADR-006: nenhuma coluna de CPF no modelo analítico. */
    public function test_nenhuma_coluna_de_cpf(): void
    {
        foreach (Schema::getColumnListing('cupons') as $coluna) {
            $this->assertStringNotContainsString('cpf', strtolower($coluna));
        }
    }

    /** Migração reversível (checklist do épico): `down()` derruba as duas tabelas. */
    public function test_migracao_e_reversivel(): void
    {
        $src = file_get_contents(base_path('database/migrations/2026_07_02_000001_create_cupons_table.php'));

        $this->assertStringContainsString('public function down', $src);
        $this->assertStringContainsString("dropIfExists('cupom_itens')", $src);
        $this->assertStringContainsString("dropIfExists('cupons')", $src);
    }
}
