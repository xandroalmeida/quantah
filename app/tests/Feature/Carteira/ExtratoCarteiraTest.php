<?php

namespace Tests\Feature\Carteira;

use App\Domain\Cashback\ExtratoCarteira;
use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
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
}
