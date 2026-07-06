<?php

namespace Tests\Unit\Enriquecimento;

use App\Domain\Enriquecimento\ApresentacaoEmitente;
use App\Models\Emitente;
use PHPUnit\Framework\TestCase;

/**
 * Read-model do emitente para o Backoffice (STORY-041): traduz o registro de
 * enriquecimento num view-model claro — nunca campo vazio mudo (CA-4). Puro, sem DB.
 */
class ApresentacaoEmitenteTest extends TestCase
{
    private const CNPJ = '43259548002883';

    private function emitente(array $attrs): Emitente
    {
        return new Emitente(array_merge(['cnpj' => self::CNPJ], $attrs));
    }

    public function test_sem_registro_e_pendente(): void // CA-4
    {
        $vm = ApresentacaoEmitente::montar(null, self::CNPJ);

        $this->assertSame('pendente', $vm['estado']);
        $this->assertSame('warning', $vm['badge_variante']);
        $this->assertSame('43.259.548/0028-83', $vm['cnpj']);
        $this->assertNull($vm['razao_social']);
    }

    public function test_enriquecido_mostra_todos_os_campos(): void // CA-3
    {
        $vm = ApresentacaoEmitente::montar($this->emitente([
            'razao_social' => 'SUPERMERCADOS CAVICCHIOLLI LTDA',
            'cnae_principal_codigo' => '4711302',
            'cnae_principal_descricao' => 'Comércio varejista — supermercados',
            'situacao_cadastral' => 'ATIVA',
            'municipio' => 'ITU',
            'uf' => 'SP',
            'status_enriquecimento' => Emitente::STATUS_ENRIQUECIDO,
        ]), self::CNPJ);

        $this->assertSame('enriquecido', $vm['estado']);
        $this->assertSame('positive', $vm['badge_variante']);
        $this->assertSame('SUPERMERCADOS CAVICCHIOLLI LTDA', $vm['razao_social']);
        $this->assertSame('4711-3/02 — Comércio varejista — supermercados', $vm['cnae']);
        $this->assertSame('ATIVA', $vm['situacao_cadastral']);
        $this->assertSame('ITU/SP', $vm['localizacao']);
    }

    public function test_sem_cnae_mostra_razao_mas_sinaliza_cnae_ausente(): void // CA-4
    {
        $vm = ApresentacaoEmitente::montar($this->emitente([
            'razao_social' => 'EMPRESA X LTDA',
            'situacao_cadastral' => 'ATIVA',
            'municipio' => 'SAO PAULO', 'uf' => 'SP',
            'status_enriquecimento' => Emitente::STATUS_SEM_CNAE,
        ]), self::CNPJ);

        $this->assertSame('sem_cnae', $vm['estado']);
        $this->assertSame('warning', $vm['badge_variante']);
        $this->assertSame('EMPRESA X LTDA', $vm['razao_social']);
        $this->assertNull($vm['cnae']);
    }

    public function test_nao_encontrado(): void // CA-4
    {
        $vm = ApresentacaoEmitente::montar($this->emitente([
            'status_enriquecimento' => Emitente::STATUS_NAO_ENCONTRADO,
        ]), self::CNPJ);

        $this->assertSame('nao_encontrado', $vm['estado']);
        $this->assertSame('negative', $vm['badge_variante']);
        $this->assertNull($vm['razao_social']);
    }

    public function test_nao_enriquecido_e_indisponivel(): void // CA-4
    {
        $vm = ApresentacaoEmitente::montar($this->emitente([
            'status_enriquecimento' => Emitente::STATUS_NAO_ENRIQUECIDO,
        ]), self::CNPJ);

        $this->assertSame('indisponivel', $vm['estado']);
        $this->assertSame('negative', $vm['badge_variante']);
    }

    public function test_estado_sempre_tem_rotulo_legivel(): void // CA-4 (nunca campo mudo)
    {
        foreach ([null, Emitente::STATUS_ENRIQUECIDO, Emitente::STATUS_SEM_CNAE, Emitente::STATUS_NAO_ENCONTRADO, Emitente::STATUS_NAO_ENRIQUECIDO] as $status) {
            $emitente = $status === null ? null : $this->emitente(['status_enriquecimento' => $status]);
            $vm = ApresentacaoEmitente::montar($emitente, self::CNPJ);
            $this->assertNotEmpty($vm['estado_rotulo']);
        }
    }
}
