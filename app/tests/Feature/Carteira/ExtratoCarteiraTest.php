<?php

namespace Tests\Feature\Carteira;

use App\Domain\Cashback\ExtratoCarteira;
use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Read-model da tela de carteira (STORY-016): saldo + histórico de créditos, cada crédito
 * juntado ao seu cupom (valor/data) via `cupom_id` — referência lógica, sem cruzar a
 * segregação de bases (ADR-006). Formatação de reais/data na borda de apresentação.
 */
class ExtratoCarteiraTest extends TestCase
{
    use RefreshDatabase;

    private ExtratoCarteira $extrato;

    protected function setUp(): void
    {
        parent::setUp();
        $this->extrato = app(ExtratoCarteira::class);
    }

    private function cupom(string $valorReais, string $dataEmissao, string $chave): Cupom
    {
        return Cupom::create([
            'chave_acesso' => $chave,
            'uf' => '35', 'ano_mes' => '2601', 'cnpj_emitente' => '12345678000195',
            'modelo' => '65', 'valor_total' => $valorReais, 'data_emissao' => $dataEmissao,
            'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ]);
    }

    private const CHAVE_A = '35260112345678000195650010001234561000000019';

    private const CHAVE_B = '35260112345678000195650010001234561000000028';

    public function test_usuario_sem_carteira_tem_saldo_zero_e_extrato_vazio(): void
    {
        $user = User::factory()->create();

        $dados = $this->extrato->para($user);

        $this->assertSame(0, $dados['saldo']['centavos']);
        $this->assertSame('0,00', $dados['saldo']['reais']);
        $this->assertSame([], $dados['extrato']);
    }

    public function test_saldo_formatado_em_reais(): void
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1247]);

        $dados = $this->extrato->para($user);

        $this->assertSame(1247, $dados['saldo']['centavos']);
        $this->assertSame('12,47', $dados['saldo']['reais']);
    }

    public function test_saldo_grande_usa_separador_de_milhar(): void
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 123456]); // R$ 1.234,56

        $this->assertSame('1.234,56', $this->extrato->para($user)['saldo']['reais']);
    }

    public function test_extrato_junta_credito_ao_cupom_e_formata(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 9]);
        $cupom = $this->cupom('87.90', '2026-01-15 10:00:00', self::CHAVE_A);
        CarteiraTransacao::create([
            'carteira_id' => $carteira->id,
            'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
            'valor_centavos' => 9,
            'cupom_id' => $cupom->id,
        ]);

        $extrato = $this->extrato->para($user)['extrato'];

        $this->assertCount(1, $extrato);
        $this->assertSame('87,90', $extrato[0]['cupom_valor']);
        $this->assertSame('15 jan 2026', $extrato[0]['data']);
        $this->assertSame('0,09', $extrato[0]['credito']);
    }

    /** STORY-034 CA-2/CA-3 — o extrato expõe estabelecimento e o cupom_id (para abrir o detalhe). */
    public function test_extrato_expoe_estabelecimento_e_cupom_id(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 9]);
        $cupom = $this->cupom('87.90', '2026-01-15 10:00:00', self::CHAVE_A);
        $cupom->update(['nome_emitente' => 'Supermercado Bom Preço']);
        CarteiraTransacao::create([
            'carteira_id' => $carteira->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
            'valor_centavos' => 9, 'cupom_id' => $cupom->id,
        ]);

        $item = $this->extrato->para($user)['extrato'][0];

        $this->assertSame('Supermercado Bom Preço', $item['estabelecimento']);
        $this->assertSame($cupom->id, $item['cupom_id']);
    }

    /** STORY-034 CA-1 — sem nome do emitente, a listagem degrada com fallback (não quebra). */
    public function test_extrato_usa_fallback_de_estabelecimento_sem_nome(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 9]);
        $cupom = $this->cupom('50.00', '2026-01-15 10:00:00', self::CHAVE_A); // sem nome_emitente
        CarteiraTransacao::create([
            'carteira_id' => $carteira->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
            'valor_centavos' => 9, 'cupom_id' => $cupom->id,
        ]);

        $item = $this->extrato->para($user)['extrato'][0];

        $this->assertSame('Estabelecimento não identificado', $item['estabelecimento']);
    }

    public function test_extrato_ordena_do_mais_recente_para_o_mais_antigo(): void
    {
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 33]);
        $antigo = $this->cupom('100.00', '2026-01-10 10:00:00', self::CHAVE_A);
        $novo = $this->cupom('235.43', '2026-01-14 10:00:00', self::CHAVE_B);

        // Insere o antigo primeiro, o novo depois (created_at do lançamento decide a ordem).
        CarteiraTransacao::create(['carteira_id' => $carteira->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK, 'valor_centavos' => 10, 'cupom_id' => $antigo->id, 'created_at' => now()->subDay()]);
        CarteiraTransacao::create(['carteira_id' => $carteira->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK, 'valor_centavos' => 24, 'cupom_id' => $novo->id, 'created_at' => now()]);

        $extrato = $this->extrato->para($user)['extrato'];

        $this->assertSame('0,24', $extrato[0]['credito']); // novo no topo
        $this->assertSame('0,10', $extrato[1]['credito']);
    }

    public function test_credito_sem_cupom_correspondente_usa_fallback(): void
    {
        // Borda: crédito aponta para um cupom ausente (referência lógica, sem FK dura —
        // ADR-006). A tela não quebra: valor "0,00" e data vazia, mas o crédito aparece.
        $user = User::factory()->create();
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 5]);
        CarteiraTransacao::create([
            'carteira_id' => $carteira->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
            'valor_centavos' => 5, 'cupom_id' => '019f0000-0000-7000-8000-000000000000',
        ]);

        $extrato = $this->extrato->para($user)['extrato'];

        $this->assertCount(1, $extrato);
        $this->assertSame('0,00', $extrato[0]['cupom_valor']);
        $this->assertSame('', $extrato[0]['data']);
        $this->assertSame('0,05', $extrato[0]['credito']);
    }

    public function test_extrato_nao_inclui_transacoes_de_outra_carteira(): void
    {
        $user = User::factory()->create();
        $outro = User::factory()->create();
        $carteiraOutro = Carteira::create(['user_id' => $outro->id, 'saldo_centavos' => 100]);
        $cupom = $this->cupom('1000.00', '2026-01-10 10:00:00', self::CHAVE_A);
        CarteiraTransacao::create(['carteira_id' => $carteiraOutro->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK, 'valor_centavos' => 100, 'cupom_id' => $cupom->id]);

        // $user não tem carteira → não vê o crédito do outro.
        $this->assertSame([], $this->extrato->para($user)['extrato']);
    }

    // --- Cupons recentes (em processamento / não aceitos) — visibilidade pós-scan -----------

    private function cupomAtribuido(User $user, string $status, string $chave, ?string $motivo = null): Cupom
    {
        $cupom = Cupom::create([
            'chave_acesso' => $chave, 'uf' => '35', 'ano_mes' => '2601',
            'cnpj_emitente' => '12345678000195', 'modelo' => '65',
            'status' => $status, 'motivo_falha' => $motivo, 'origem' => 'scan',
        ]);
        CupomAtribuicao::create(['cupom_id' => $cupom->id, 'user_id' => $user->id]);

        return $cupom;
    }

    public function test_cupom_em_processamento_aparece_para_o_colaborador(): void
    {
        $user = User::factory()->create();
        $this->cupomAtribuido($user, Cupom::STATUS_PENDENTE, self::CHAVE_A);
        $this->cupomAtribuido($user, Cupom::STATUS_EXTRAINDO, self::CHAVE_B);

        $cupons = $this->extrato->para($user)['cupons'];

        $this->assertCount(2, $cupons);
        $this->assertSame('processando', $cupons[0]['status']);
        $this->assertSame('Cupom recebido', $cupons[0]['titulo']);
    }

    public function test_falha_transitoria_continua_como_em_processamento(): void
    {
        // SEFAZ instável: falha transitória NÃO é "não aceito" — o Job reprocessa até ela voltar.
        $user = User::factory()->create();
        $this->cupomAtribuido($user, Cupom::STATUS_FALHA, self::CHAVE_A, 'transitoria');

        $cupons = $this->extrato->para($user)['cupons'];

        $this->assertCount(1, $cupons);
        $this->assertSame('processando', $cupons[0]['status']);
    }

    public function test_cupom_rejeitado_aparece_como_nao_aceito_com_motivo(): void
    {
        $user = User::factory()->create();
        $this->cupomAtribuido($user, Cupom::STATUS_REJEITADO, self::CHAVE_A, 'cupom_expirado');

        $cupons = $this->extrato->para($user)['cupons'];

        $this->assertCount(1, $cupons);
        $this->assertSame('nao_aceito', $cupons[0]['status']);
        $this->assertSame('Fora do prazo de validade.', $cupons[0]['detalhe']);
    }

    public function test_cupom_validado_nao_fica_na_lista_de_recentes(): void
    {
        // Validado já aparece no `extrato` (com cashback) — não se repete em "cupons recentes".
        $user = User::factory()->create();
        $this->cupomAtribuido($user, Cupom::STATUS_VALIDADO, self::CHAVE_A);

        $this->assertSame([], $this->extrato->para($user)['cupons']);
    }

    public function test_cupons_de_outro_colaborador_nao_vazam(): void
    {
        $user = User::factory()->create();
        $outro = User::factory()->create();
        $this->cupomAtribuido($outro, Cupom::STATUS_PENDENTE, self::CHAVE_A);

        $this->assertSame([], $this->extrato->para($user)['cupons']);
    }
}
