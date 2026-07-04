<?php

namespace Tests\Unit\Saque;

use App\Domain\Saque\Cpf;
use PHPUnit\Framework\TestCase;

/**
 * CPF do KYC mínimo do saque (STORY-017, ADR-005). Value object puro: normaliza (só
 * dígitos, sem máscara — ver database-method.md) e valida os dígitos verificadores
 * (mod 11). É a base da titularidade (chave PIX do tipo CPF).
 */
class CpfTest extends TestCase
{
    public function test_aceita_cpf_valido_sem_mascara(): void
    {
        $this->assertTrue(Cpf::ehValido('11144477735'));
    }

    public function test_aceita_cpf_valido_com_mascara(): void
    {
        $this->assertTrue(Cpf::ehValido('111.444.777-35'));
    }

    public function test_rejeita_digito_verificador_invalido(): void
    {
        $this->assertFalse(Cpf::ehValido('11144477700'));
    }

    public function test_rejeita_todos_os_digitos_iguais(): void
    {
        // DV "fecha" para repetidos, mas são CPFs inválidos por definição.
        $this->assertFalse(Cpf::ehValido('11111111111'));
        $this->assertFalse(Cpf::ehValido('00000000000'));
    }

    public function test_rejeita_tamanho_errado(): void
    {
        $this->assertFalse(Cpf::ehValido('123'));
        $this->assertFalse(Cpf::ehValido('111444777351')); // 12 dígitos
    }

    public function test_rejeita_lixo_e_vazio(): void
    {
        $this->assertFalse(Cpf::ehValido('abcdefghijk'));
        $this->assertFalse(Cpf::ehValido(''));
    }

    public function test_normaliza_removendo_mascara(): void
    {
        $this->assertSame('11144477735', Cpf::apenasDigitos('111.444.777-35'));
        $this->assertSame('11144477735', Cpf::apenasDigitos('111 444 777 35'));
    }
}
