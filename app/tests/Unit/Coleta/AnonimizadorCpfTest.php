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
}
