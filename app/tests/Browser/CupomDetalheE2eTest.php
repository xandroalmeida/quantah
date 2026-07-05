<?php

namespace Tests\Browser;

use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real do cupom com contexto (STORY-034 · EPIC-007), mobile-first (390px).
 *
 *  - CA-2/CA-3: a listagem mostra estabelecimento + data e abre o detalhe em 1 toque.
 *  - CA-4: o detalhe mostra cabeçalho (estabelecimento, total, status) e a lista de itens.
 *  - CA-5: sem overflow horizontal na tela de detalhe.
 */
class CupomDetalheE2eTest extends DuskTestCase
{
    private const EMAIL = 'dusk-cupom@quantah.test';

    private const CHAVE = '35260112345678000195650010001234561000000019';

    protected function setUp(): void
    {
        parent::setUp();
        $this->limpar();
    }

    protected function tearDown(): void
    {
        $this->limpar();
        parent::tearDown();
    }

    private function limpar(): void
    {
        User::where('email', self::EMAIL)->delete();
        Cupom::where('chave_acesso', self::CHAVE)->delete();
    }

    private function usuarioComCupom(): User
    {
        $user = User::factory()->create(['email' => self::EMAIL, 'name' => 'Ana Coletadora']);
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 9]);

        $cupom = Cupom::create([
            'chave_acesso' => self::CHAVE, 'uf' => '35', 'ano_mes' => '2601',
            'cnpj_emitente' => '43259548002883', 'nome_emitente' => 'Supermercados Cavicchiolli Ltda',
            'modelo' => '65', 'valor_total' => '235.43', 'data_emissao' => '2026-07-01 16:43:54',
            'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ]);
        $cupom->itens()->createMany([
            ['sequencia' => 1, 'descricao' => 'SALSICHA HOT DOG SADIA 500G', 'quantidade' => '1.0000', 'unidade' => 'UN', 'valor_unitario' => '14.85', 'valor_total' => '14.85'],
            ['sequencia' => 2, 'descricao' => 'BANANA NANICA', 'quantidade' => '0.5060', 'unidade' => 'KG', 'valor_unitario' => '6.99', 'valor_total' => '3.54'],
        ]);
        CupomAtribuicao::create(['cupom_id' => $cupom->id, 'user_id' => $user->id]);
        CarteiraTransacao::create([
            'carteira_id' => $carteira->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
            'valor_centavos' => 9, 'cupom_id' => $cupom->id,
        ]);

        return $user;
    }

    /** CA-2/CA-3/CA-4 — da listagem ao detalhe com itens em 1 toque. */
    public function test_da_listagem_ao_detalhe_com_itens_em_um_toque(): void
    {
        $user = $this->usuarioComCupom();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)->resize(390, 1400)
                ->visit('/carteira')
                ->waitFor('[data-testid=screen-carteira-item]', 10)
                ->assertSeeIn('[data-testid=screen-carteira-item]', 'Supermercados Cavicchiolli Ltda')
                ->click('[data-testid=screen-carteira-item]')
                ->waitFor('[data-testid=screen-cupom-detalhe]', 10)
                // Cabeçalho
                ->assertSeeIn('[data-testid=screen-cupom-cabecalho]', 'Supermercados Cavicchiolli Ltda')
                ->assertSeeIn('[data-testid=screen-cupom-total]', 'R$ 235,43')
                ->assertSeeIn('[data-testid=screen-cupom-status]', 'Validado')
                // Itens
                ->assertSee('SALSICHA HOT DOG SADIA 500G')
                ->assertSee('BANANA NANICA');

            // CA-5 — sem overflow horizontal no detalhe.
            $overflow = (int) $browser->script('return document.body.scrollWidth - window.innerWidth;')[0];
            $this->assertLessThanOrEqual(1, $overflow, 'overflow horizontal no detalhe do cupom');

            // CA-3 — retorno à listagem.
            $browser->click('[data-testid=screen-cupom-voltar]')
                ->waitForLocation('/carteira', 10)
                ->assertPathIs('/carteira');
        });
    }
}
