<?php

namespace Tests\Unit\Lead;

use App\Domain\Lead\CapturarLead;
use App\Models\Lead;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * STORY-026 — regra de captação/persistência do lead B2B (núcleo de negócio, ≥ 98%).
 * Cobre as 4 categorias: feliz (persiste), normalização (e-mail), duplicado idempotente
 * (não cria 2º, não sobrescreve, sem vazar), e bordas de caixa/espaço no e-mail.
 */
class CapturarLeadTest extends TestCase
{
    use RefreshDatabase;

    private function capturar(array $dados): Lead
    {
        return app(CapturarLead::class)($dados);
    }

    /** (feliz) captura um lead novo e persiste com os dados informados. */
    public function test_captura_persiste_lead_novo(): void
    {
        $lead = $this->capturar(['nome' => 'Ana Souza', 'email' => 'ana@acme.com.br', 'empresa' => 'Acme']);

        $this->assertInstanceOf(Lead::class, $lead);
        $this->assertTrue($lead->exists);
        $this->assertDatabaseCount('leads', 1);
        $this->assertDatabaseHas('leads', [
            'nome' => 'Ana Souza',
            'email' => 'ana@acme.com.br',
            'empresa' => 'Acme',
        ]);
    }

    /** (borda) e-mail é normalizado (minúsculas + trim) antes de persistir/deduplicar. */
    public function test_email_normalizado_minusculas_e_trim(): void
    {
        $lead = $this->capturar(['nome' => 'Bia', 'email' => '  Bia@Empresa.COM  ', 'empresa' => 'Empresa']);

        $this->assertSame('bia@empresa.com', $lead->email);
        $this->assertDatabaseHas('leads', ['email' => 'bia@empresa.com']);
    }

    /** (duplicado) reenvio do mesmo e-mail é idempotente — não cria um 2º lead. */
    public function test_duplicado_e_idempotente(): void
    {
        $primeiro = $this->capturar(['nome' => 'Caio', 'email' => 'caio@x.com', 'empresa' => 'X']);
        $segundo = $this->capturar(['nome' => 'Caio', 'email' => 'caio@x.com', 'empresa' => 'X']);

        $this->assertSame($primeiro->id, $segundo->id);
        $this->assertDatabaseCount('leads', 1);
    }

    /** (duplicado) a deduplicação ignora caixa e espaços no e-mail. */
    public function test_duplicado_ignora_caixa_e_espaco(): void
    {
        $this->capturar(['nome' => 'Dara', 'email' => 'dara@y.com', 'empresa' => 'Y']);
        $this->capturar(['nome' => 'Dara', 'email' => '  DARA@Y.com ', 'empresa' => 'Y']);

        $this->assertDatabaseCount('leads', 1);
    }

    /** (duplicado) não sobrescreve o dado do primeiro lead nem vaza que já existia. */
    public function test_duplicado_nao_sobrescreve_o_primeiro(): void
    {
        $this->capturar(['nome' => 'Eva Antiga', 'email' => 'eva@z.com', 'empresa' => 'Z Antiga']);
        $retorno = $this->capturar(['nome' => 'Eva Nova', 'email' => 'eva@z.com', 'empresa' => 'Z Nova']);

        // O lead permanece com os dados originais (idempotência); o retorno é o registro existente.
        $this->assertSame('Eva Antiga', $retorno->nome);
        $this->assertDatabaseHas('leads', ['email' => 'eva@z.com', 'nome' => 'Eva Antiga', 'empresa' => 'Z Antiga']);
        $this->assertDatabaseMissing('leads', ['nome' => 'Eva Nova']);
    }
}
