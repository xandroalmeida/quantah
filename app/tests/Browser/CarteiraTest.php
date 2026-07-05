<?php

namespace Tests\Browser;

use App\Models\Carteira;
use App\Models\CarteiraTransacao;
use App\Models\Cupom;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da Carteira do Colaborador (STORY-016), mobile-first. Cobre os
 * caminhos alcançáveis pela UI: saldo + histórico (preenchido); primeira vez (vazio) com
 * CTA para coletar; e anônimo barrado para o login.
 *
 * Roda contra o banco de dev (`.env.dusk.local` → quantah); por isso é auto-limpo
 * (remove usuário/cupom de teste antes/depois) em vez de migrar o banco.
 */
class CarteiraTest extends DuskTestCase
{
    private const EMAIL = 'dusk-carteira@quantah.test';

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
        // Apagar o usuário cascateia carteira → transações (FK onDelete cascade).
        User::where('email', self::EMAIL)->delete();
        Cupom::where('chave_acesso', self::CHAVE)->delete();
    }

    private function usuario(): User
    {
        return User::factory()->create(['email' => self::EMAIL]);
    }

    /** Semeia carteira com um crédito de cashback ligado a um cupom válido. */
    private function comCredito(User $user): void
    {
        $carteira = Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 9]);
        $cupom = Cupom::create([
            'chave_acesso' => self::CHAVE, 'uf' => '35', 'ano_mes' => '2601',
            'cnpj_emitente' => '12345678000195', 'modelo' => '65', 'valor_total' => '87.90',
            'data_emissao' => '2026-01-15 10:00:00', 'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ]);
        CarteiraTransacao::create([
            'carteira_id' => $carteira->id, 'tipo' => CarteiraTransacao::TIPO_CREDITO_CASHBACK,
            'valor_centavos' => 9, 'cupom_id' => $cupom->id,
        ]);
    }

    public function test_preenchido_mostra_saldo_e_historico(): void
    {
        $user = $this->usuario();
        $this->comCredito($user);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/carteira')
                ->waitFor('[data-testid=screen-carteira-saldo]', 10)
                ->assertSeeIn('[data-testid=screen-carteira-saldo]', 'R$ 0,09')
                ->assertSee('Cada nota conta.')
                ->assertPresent('[data-testid=screen-carteira-historico]')
                ->assertSeeIn('[data-testid=screen-carteira-item]', 'Cupom de R$ 87,90')
                ->assertSeeIn('[data-testid=screen-carteira-item-credito]', '+R$ 0,09')
                ->assertVisible('[data-testid=app-nav]');
        });
    }

    public function test_vazio_mostra_cta_que_leva_a_coleta(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/carteira')
                ->waitFor('[data-testid=screen-carteira-vazio]', 10)
                ->assertSeeIn('[data-testid=screen-carteira-saldo]', 'R$ 0,00')
                ->assertSee('Seu saldo vai aparecer aqui')
                ->click('[data-testid=screen-carteira-vazio-cta]')
                ->waitForLocation('/coletar', 10)
                ->assertPathIs('/coletar');
        });
    }

    public function test_anonimo_e_barrado_para_o_login(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->logout()
                ->visit('/carteira')
                ->waitForLocation('/login', 10)
                ->assertPathIs('/login');
        });
    }
}
