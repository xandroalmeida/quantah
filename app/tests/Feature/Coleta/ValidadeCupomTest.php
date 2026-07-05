<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\AnonimizadorCpf;
use App\Domain\Coleta\Events\CupomValidado;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\ResultadoIngestao;
use App\Domain\Coleta\Sefaz\SpSefazAdapter;
use App\Models\Cupom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Tests\Support\Coleta\FakeSefazSpFetcher;
use Tests\TestCase;

/**
 * Regra de validade parametrizável da coleta (STORY-035 · EPIC-007).
 *
 * A janela é aplicada na EXTRAÇÃO (`processarExtracao`), único ponto onde `data_emissao` é
 * conhecida (ADR-002 — extração assíncrona). Semântica (IDR-013): o cupom é válido se sua
 * idade ≤ N dias em America/Sao_Paulo; estritamente mais velho → rejeitado (`cupom_expirado`),
 * motivo distinto de dedup/chave/negócio. N vem de `config('coleta.janela_dias')` (default 7).
 *
 * CA-1 config/default; CA-2 rejeição com motivo próprio; CA-3 dentro da janela segue normal;
 * CA-5 fronteira exata + fuso.
 */
class ValidadeCupomTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_SP = '35260112345678000195650010001234561000000019';

    protected function tearDown(): void
    {
        Carbon::setTestNow();
        parent::tearDown();
    }

    private function servico(FakeSefazSpFetcher $fetcher): IngestaoCupomService
    {
        return new IngestaoCupomService(new SpSefazAdapter($fetcher, new AnonimizadorCpf));
    }

    /** Payload de SP válido (com itens) numa data de emissão específica. */
    private function fetcherComEmissao(string $dataEmissao): FakeSefazSpFetcher
    {
        return (new FakeSefazSpFetcher)->comPayload([
            'data_emissao' => $dataEmissao,
            'valor_total' => '50.00',
            'numero' => 999,
            'serie' => 1,
            'itens' => [[
                'descricao' => 'CAFE 500G',
                'gtin' => '7890000000001',
                'quantidade' => '1.0000',
                'unidade' => 'UN',
                'valor_unitario' => '50.00',
                'valor_total' => '50.00',
            ]],
        ]);
    }

    /** CA-1 — o limite vem de config com default 7 (nenhum número mágico). */
    public function test_janela_default_e_sete_dias_via_config(): void
    {
        $this->assertSame(7, config('coleta.janela_dias'));
    }

    /** CA-3 — cupom dentro da janela segue o fluxo normal (validado + cashback). */
    public function test_cupom_dentro_da_janela_e_validado(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-05 12:00:00', 'America/Sao_Paulo'));
        Event::fake([CupomValidado::class]);

        $resultado = $this->servico($this->fetcherComEmissao('2026-07-02 09:00:00'))->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::ACEITO, $resultado->situacao);
        $this->assertSame(Cupom::STATUS_VALIDADO, $resultado->cupom->status);
        Event::assertDispatched(CupomValidado::class);
    }

    /** CA-2 — cupom mais antigo que a janela é rejeitado com motivo próprio, sem cashback. */
    public function test_cupom_fora_da_janela_e_rejeitado_com_motivo_proprio(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-05 12:00:00', 'America/Sao_Paulo'));
        Event::fake([CupomValidado::class]);

        $resultado = $this->servico($this->fetcherComEmissao('2026-06-01 09:00:00'))->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::REJEITADO, $resultado->situacao);
        $this->assertSame('cupom_expirado', $resultado->motivo);
        $this->assertSame(Cupom::STATUS_REJEITADO, $resultado->cupom->status);
        $this->assertSame('cupom_expirado', $resultado->cupom->motivo_falha);

        // Não vira dado de cashback nem persiste itens (rejeitado antes de normalizar).
        Event::assertNotDispatched(CupomValidado::class);
        $this->assertDatabaseCount('cupom_itens', 0);
    }

    /** CA-2 — o motivo de prazo é distinto de dedup/chave/negócio. */
    public function test_motivo_expirado_e_distinto_de_outras_rejeicoes(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-05 12:00:00', 'America/Sao_Paulo'));

        $resultado = $this->servico($this->fetcherComEmissao('2026-01-01 09:00:00'))->ingerir(self::CHAVE_SP);

        $this->assertSame('cupom_expirado', $resultado->motivo);
        $this->assertNotSame('chave_malformada', $resultado->motivo);
        $this->assertNotSame('fora_de_escopo_uf', $resultado->motivo);
    }

    /** CA-5 — fronteira: exatamente N dias ainda é válido (limite inclusivo). */
    public function test_limite_exatamente_na_janela_ainda_e_valido(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 12:00:00', 'America/Sao_Paulo'));

        // Exatamente 7 dias antes → idade == janela → válido.
        $resultado = $this->servico($this->fetcherComEmissao('2026-07-01 12:00:00'))->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::ACEITO, $resultado->situacao);
    }

    /** CA-5 — fronteira: um segundo além de N dias é rejeitado. */
    public function test_um_segundo_alem_da_janela_e_rejeitado(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-08 12:00:00', 'America/Sao_Paulo'));

        $resultado = $this->servico($this->fetcherComEmissao('2026-07-01 11:59:59'))->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::REJEITADO, $resultado->situacao);
        $this->assertSame('cupom_expirado', $resultado->motivo);
    }

    /** CA-1 — o limite é sobreponível por config (sem número mágico no código). */
    public function test_janela_configuravel_muda_o_desfecho(): void
    {
        Carbon::setTestNow(Carbon::parse('2026-07-05 12:00:00', 'America/Sao_Paulo'));
        $fetcher = fn () => $this->fetcherComEmissao('2026-06-25 12:00:00'); // 10 dias atrás

        config(['coleta.janela_dias' => 7]);
        $this->assertSame(ResultadoIngestao::REJEITADO, $this->servico($fetcher())->ingerir(self::CHAVE_SP)->situacao);

        Cupom::query()->delete(); // reingerir a mesma chave
        config(['coleta.janela_dias' => 30]);
        $this->assertSame(ResultadoIngestao::ACEITO, $this->servico($fetcher())->ingerir(self::CHAVE_SP)->situacao);
    }

    /** CA-5 — fuso America/Sao_Paulo: a comparação usa SP, não UTC. */
    public function test_fuso_sao_paulo_respeitado_na_fronteira(): void
    {
        // "Agora" = 2026-07-08 01:00 em SP (= 04:00 UTC). Emissão 2026-07-01 02:00 SP.
        Carbon::setTestNow(Carbon::parse('2026-07-08 01:00:00', 'America/Sao_Paulo'));

        // 2026-07-01 02:00 SP → idade < 7 dias em SP → válido. (Em UTC ingênuo daria diferente.)
        $resultado = $this->servico($this->fetcherComEmissao('2026-07-01 02:00:00'))->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::ACEITO, $resultado->situacao);
    }

    /** Borda — sem data de emissão não há como aferir prazo → não rejeita por validade. */
    public function test_sem_data_emissao_nao_rejeita_por_prazo(): void
    {
        $fetcher = (new FakeSefazSpFetcher)->comPayload([
            'data_emissao' => null,
            'valor_total' => '50.00',
            'numero' => 999,
            'serie' => 1,
            'itens' => [[
                'descricao' => 'CAFE 500G', 'quantidade' => '1.0000',
                'unidade' => 'UN', 'valor_unitario' => '50.00', 'valor_total' => '50.00',
            ]],
        ]);

        $resultado = $this->servico($fetcher)->ingerir(self::CHAVE_SP);

        $this->assertSame(ResultadoIngestao::ACEITO, $resultado->situacao);
    }
}
