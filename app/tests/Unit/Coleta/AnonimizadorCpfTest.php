<?php

namespace Tests\Unit\Coleta;

use App\Domain\Coleta\AnonimizadorCpf;
use PHPUnit\Framework\TestCase;

/** Anonimização de CPF na coleta (ADR-006): descartar chaves de CPF e escovar valores com cara de CPF. */
class AnonimizadorCpfTest extends TestCase
{
    public function test_remove_a_chave_de_cpf_do_payload(): void
    {
        $limpo = (new AnonimizadorCpf)->limpar([
            'valor_total' => '10.00',
            'cpf_consumidor' => '390.533.447-05',
        ]);

        $this->assertArrayNotHasKey('cpf_consumidor', $limpo);
        $this->assertSame('10.00', $limpo['valor_total']);
    }

    public function test_escova_cpf_embutido_em_texto_livre(): void
    {
        $limpo = (new AnonimizadorCpf)->limpar([
            'observacao' => 'Consumidor informou CPF 390.533.447-05 na nota',
        ]);

        $this->assertStringNotContainsString('390.533.447-05', $limpo['observacao']);
        $this->assertStringContainsString('[CPF-REMOVIDO]', $limpo['observacao']);
    }

    public function test_escova_cpf_sem_formatacao(): void
    {
        $limpo = (new AnonimizadorCpf)->limpar(['nota' => 'doc 39053344705 informado']);

        $this->assertFalse(AnonimizadorCpf::contemCpf($limpo['nota']));
    }

    public function test_limpa_recursivamente_estruturas_aninhadas(): void
    {
        $limpo = (new AnonimizadorCpf)->limpar([
            'itens' => [
                ['descricao' => 'ARROZ', 'cpf' => '390.533.447-05'],
            ],
        ]);

        $this->assertArrayNotHasKey('cpf', $limpo['itens'][0]);
        $this->assertSame('ARROZ', $limpo['itens'][0]['descricao']);
    }

    /** Borda: mais de um CPF no mesmo texto — todos saem, não só o primeiro. */
    public function test_escova_multiplos_cpfs_no_mesmo_texto(): void
    {
        $limpo = (new AnonimizadorCpf)->limpar([
            'nota' => 'CPFs 390.533.447-05 e 39053344705 na mesma linha',
        ]);

        $this->assertFalse(AnonimizadorCpf::contemCpf($limpo['nota']));
        $this->assertSame(2, substr_count($limpo['nota'], '[CPF-REMOVIDO]'));
    }

    /** Borda: a chave de CPF é reconhecida sem depender de caixa (CPF, Cpf, cpf). */
    public function test_remove_chave_de_cpf_ignorando_caixa(): void
    {
        $limpo = (new AnonimizadorCpf)->limpar(['CPF_Consumidor' => '390.533.447-05']);

        $this->assertArrayNotHasKey('CPF_Consumidor', $limpo);
    }

    /** Borda: valores não-string (int, bool, null) passam intactos — nada a escovar. */
    public function test_preserva_valores_nao_string(): void
    {
        $limpo = (new AnonimizadorCpf)->limpar([
            'numero' => 123456,
            'validado' => true,
            'extraido_em' => null,
        ]);

        $this->assertSame(123456, $limpo['numero']);
        $this->assertTrue($limpo['validado']);
        $this->assertNull($limpo['extraido_em']);
    }

    /** Borda: dado legítimo parecido com número não é confundido com CPF (sem falso positivo). */
    public function test_nao_escova_chave_de_44_digitos_nem_gtin(): void
    {
        $chave = '35260112345678000195650010001234561000000019'; // 44 dígitos
        $limpo = (new AnonimizadorCpf)->limpar([
            'chave' => $chave,
            'gtin' => '7891234567895', // 13 dígitos
        ]);

        $this->assertSame($chave, $limpo['chave']);
        $this->assertSame('7891234567895', $limpo['gtin']);
    }

    /** Borda: texto vazio e texto que é só um CPF. */
    public function test_limpar_texto_em_bordas(): void
    {
        $this->assertSame('', AnonimizadorCpf::limparTexto(''));
        $this->assertSame('[CPF-REMOVIDO]', AnonimizadorCpf::limparTexto('390.533.447-05'));
    }
}
