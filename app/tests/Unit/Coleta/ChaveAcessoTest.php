<?php

namespace Tests\Unit\Coleta;

use App\Domain\Coleta\ChaveAcesso;
use App\Domain\Coleta\ChaveAcessoInvalidaException;
use PHPUnit\Framework\TestCase;

/**
 * Value object da chave de acesso (ADR-003): valida formato/DV e deriva campos só
 * pela chave, antes de qualquer acesso ao portal (visao §6.1).
 */
class ChaveAcessoTest extends TestCase
{
    /** Chave de SP válida (DV mod 11 correto) usada em todo o spike. */
    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    public function test_aceita_chave_de_sp_valida_e_deriva_os_campos(): void
    {
        $chave = ChaveAcesso::deEntrada(self::CHAVE_SP);

        $this->assertSame(self::CHAVE_SP, $chave->valor());
        $this->assertSame('35', $chave->uf());          // SP
        $this->assertSame('2601', $chave->anoMes());
        $this->assertSame('12345678000195', $chave->cnpjEmitente());
        $this->assertSame('65', $chave->modelo());        // NFC-e
        $this->assertSame(1, $chave->serie());
        $this->assertSame(123456, $chave->numero());
    }

    public function test_extrai_a_chave_da_url_do_qr_da_sefaz(): void
    {
        $url = 'https://www.nfce.fazenda.sp.gov.br/qrcode?p='.self::CHAVE_SP.'|2|1|1|ABCDEF';

        $this->assertSame(self::CHAVE_SP, ChaveAcesso::deEntrada($url)->valor());
    }

    public function test_rejeita_chave_com_menos_de_44_digitos(): void
    {
        $this->expectException(ChaveAcessoInvalidaException::class);
        ChaveAcesso::deEntrada('3526011234567800019565001000123456');
    }

    public function test_rejeita_chave_com_digito_verificador_invalido(): void
    {
        // Mesma chave, DV trocado de 9 para 0.
        $this->expectException(ChaveAcessoInvalidaException::class);
        ChaveAcesso::deEntrada('35260112345678000195650010001234561000000010');
    }

    public function test_calcula_o_digito_verificador_mod_11(): void
    {
        $this->assertSame(9, ChaveAcesso::digitoVerificador(substr(self::CHAVE_SP, 0, 43)));
    }
}
