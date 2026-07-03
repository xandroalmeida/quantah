<?php

namespace Tests\Feature\Coleta;

use App\Domain\Coleta\Metricas\MetricasColeta;
use App\Models\ColetaEvento;
use App\Models\Cupom;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

/**
 * North-star do MVP (STORY-012): cupons válidos, únicos e novos por semana + taxa de
 * sucesso de envio. A contagem deriva do modelo canônico (ADR-001/003): "único" e
 * "novo" são garantidos por construção — `chave_acesso` é UNIQUE, então todo cupom
 * `validado` é, por definição, único e de primeira ocorrência.
 *
 * A semana é a de COLETA (created_at), no fuso de SP (só SP nesta onda).
 *
 * Mapa CA → teste:
 *  - CA-1 (contagem por semana): test_conta_validos_por_semana_de_coleta
 *  - CA-4 (ignora inválidos / não conta em dobro): test_ignora_nao_validados,
 *          test_dedup_nao_conta_em_dobro
 *  - CA-2 (taxa de sucesso): test_taxa_de_sucesso_por_semana, test_taxa_sem_envios_nao_divide_por_zero
 *  - resumo geral: test_resumo_agrega_totais
 */
class MetricasColetaTest extends TestCase
{
    use RefreshDatabase;

    /** Quarta-feira da semana ISO de 12/01/2026 (segunda) — fuso SP. */
    private const SEMANA_A = '2026-01-14 12:00:00';

    private const SEMANA_A_LABEL = '2026-01-12';

    /** Quarta-feira da semana seguinte (segunda 19/01/2026). */
    private const SEMANA_B = '2026-01-21 12:00:00';

    private const SEMANA_B_LABEL = '2026-01-19';

    private int $seq = 0;

    private function metricas(): MetricasColeta
    {
        return new MetricasColeta;
    }

    /** Cria um cupom com chave única e created_at fixo (sem passar pela extração). */
    private function cupom(string $status, string $criadoEm): Cupom
    {
        $chave = str_pad((string) (++$this->seq), 44, '3', STR_PAD_LEFT);

        $cupom = new Cupom([
            'chave_acesso' => $chave,
            'uf' => '35',
            'ano_mes' => '2601',
            'cnpj_emitente' => '12345678000195',
            'modelo' => '65',
            'status' => $status,
            'origem' => 'scan',
        ]);
        $cupom->forceFill(['created_at' => $criadoEm, 'updated_at' => $criadoEm])->save();

        return $cupom;
    }

    private function evento(string $situacao, string $criadoEm, ?string $motivo = null): void
    {
        $evento = new ColetaEvento(['situacao' => $situacao, 'motivo' => $motivo]);
        $evento->forceFill(['created_at' => $criadoEm, 'updated_at' => $criadoEm])->save();
    }

    /** Localiza a linha de uma semana no retorno de porSemana(). */
    private function semana(array $porSemana, string $label): ?array
    {
        foreach ($porSemana as $linha) {
            if ($linha['semana'] === $label) {
                return $linha;
            }
        }

        return null;
    }

    /** CA-1: conta cupons validados agrupados pela semana de coleta. */
    public function test_conta_validos_por_semana_de_coleta(): void
    {
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_A);
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_A);
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_A);
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_B);

        $porSemana = $this->metricas()->porSemana();

        $this->assertSame(3, $this->semana($porSemana, self::SEMANA_A_LABEL)['validos']);
        $this->assertSame(1, $this->semana($porSemana, self::SEMANA_B_LABEL)['validos']);
    }

    /** CA-4: só `validado` conta como válido-único-novo — os demais status são ignorados. */
    public function test_ignora_nao_validados(): void
    {
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_A);
        $this->cupom(Cupom::STATUS_FALHA, self::SEMANA_A);
        $this->cupom(Cupom::STATUS_REJEITADO, self::SEMANA_A);
        $this->cupom(Cupom::STATUS_PENDENTE, self::SEMANA_A);
        $this->cupom(Cupom::STATUS_EXTRAINDO, self::SEMANA_A);

        $porSemana = $this->metricas()->porSemana();

        $this->assertSame(1, $this->semana($porSemana, self::SEMANA_A_LABEL)['validos']);
    }

    /**
     * CA-4: a dedup (chave UNIQUE) garante que reenviar o mesmo cupom não infla a
     * contagem — há um só cupom validado, mesmo com vários eventos de envio.
     */
    public function test_dedup_nao_conta_em_dobro(): void
    {
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_A);
        // 2 reenvios da mesma chave viram eventos "duplicado", não novos validados.
        $this->evento('capturado', self::SEMANA_A);
        $this->evento('duplicado', self::SEMANA_A);
        $this->evento('duplicado', self::SEMANA_A);

        $semana = $this->semana($this->metricas()->porSemana(), self::SEMANA_A_LABEL);

        $this->assertSame(1, $semana['validos']);
        $this->assertSame(3, $semana['enviados']);
    }

    /** CA-2: taxa de sucesso = validados / enviados, por semana. */
    public function test_taxa_de_sucesso_por_semana(): void
    {
        // 3 validados, 5 envios (2 falharam/duplicaram) → 60%.
        for ($i = 0; $i < 3; $i++) {
            $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_A);
            $this->evento('aceito', self::SEMANA_A);
        }
        $this->evento('duplicado', self::SEMANA_A);
        $this->evento('rejeitado', self::SEMANA_A, 'fora_de_escopo_uf');

        $semana = $this->semana($this->metricas()->porSemana(), self::SEMANA_A_LABEL);

        $this->assertSame(3, $semana['validos']);
        $this->assertSame(5, $semana['enviados']);
        $this->assertEqualsWithDelta(0.6, $semana['taxa'], 0.0001);
    }

    /** CA-2 borda: semana com validados mas sem eventos de envio não divide por zero. */
    public function test_taxa_sem_envios_nao_divide_por_zero(): void
    {
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_A);

        $semana = $this->semana($this->metricas()->porSemana(), self::SEMANA_A_LABEL);

        $this->assertSame(0, $semana['enviados']);
        $this->assertNull($semana['taxa']);
    }

    /** Resumo geral agrega todas as semanas (números do topo do painel). */
    public function test_resumo_agrega_totais(): void
    {
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_A);
        $this->cupom(Cupom::STATUS_VALIDADO, self::SEMANA_B);
        $this->cupom(Cupom::STATUS_FALHA, self::SEMANA_A);
        $this->evento('aceito', self::SEMANA_A);
        $this->evento('aceito', self::SEMANA_B);
        $this->evento('falha_extracao', self::SEMANA_A, 'estrutural');
        $this->evento('rejeitado', self::SEMANA_B, 'chave_malformada');

        $resumo = $this->metricas()->resumo();

        $this->assertSame(2, $resumo['validos_total']);
        $this->assertSame(4, $resumo['enviados_total']);
        $this->assertEqualsWithDelta(0.5, $resumo['taxa_geral'], 0.0001);
    }

    /** Borda: base vazia → sem semanas e resumo zerado, sem erro. */
    public function test_base_vazia_retorna_estrutura_zerada(): void
    {
        $resumo = $this->metricas()->resumo();

        $this->assertSame([], $this->metricas()->porSemana());
        $this->assertSame(0, $resumo['validos_total']);
        $this->assertSame(0, $resumo['enviados_total']);
        $this->assertNull($resumo['taxa_geral']);
    }

    /** CA-5: o modelo de telemetria não guarda dado pessoal — só sinais de funil/saúde. */
    public function test_evento_de_telemetria_nao_tem_pii(): void
    {
        $colunas = Schema::getColumnListing('coleta_eventos');

        foreach ($colunas as $coluna) {
            $this->assertStringNotContainsString('cpf', strtolower($coluna));
        }
        $this->assertEqualsCanonicalizing(
            ['id', 'situacao', 'motivo', 'cupom_id', 'created_at', 'updated_at'],
            $colunas,
        );
    }
}
