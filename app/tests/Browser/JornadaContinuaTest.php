<?php

namespace Tests\Browser;

use App\Domain\Cashback\CreditarCashbackService;
use App\Models\Carteira;
use App\Models\Cupom;
use App\Models\CupomAtribuicao;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E ponta a ponta da jornada do Coletador (STORY-031 · EPIC-006), browser real, mobile 390px.
 *
 * Percorre o loop completo: entrar → home-hub → coletar (a partir da home, 1 toque) → saldo atualiza
 * → extrato (1 toque) mostra o crédito → iniciar saque (1 toque). É o E2E que a validação do épico
 * (STORY-032) referencia. Cobre CA-1..CA-6.
 *
 * A validação assíncrona do cupom (SEFAZ, EPIC-002) não roda no browser (a coleta pela UI para em
 * `pendente`); por isso a "coleta válida que gera crédito" é representada, no meio do teste, por um
 * cupom `validado` + o serviço real de crédito (EPIC-003), gravado no banco de dev que o app lê.
 *
 * Roda contra o banco de dev; auto-limpo (remove usuário + cupom de teste antes/depois).
 */
class JornadaContinuaTest extends DuskTestCase
{
    private const EMAIL = 'dusk-jornada@quantah.test';

    private const CHAVE = '35260112345678000195650010008888881000000019';

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
        User::where('email', self::EMAIL)->delete(); // cascateia carteira/transações
        Cupom::where('chave_acesso', self::CHAVE)->delete();
    }

    /** Simula o resultado de uma coleta válida do usuário: cupom validado + crédito real (EPIC-003). */
    private function coletaValidaCredita(User $user, string $valorReais): void
    {
        $cupom = Cupom::create([
            'chave_acesso' => self::CHAVE, 'uf' => '35', 'ano_mes' => '2601',
            'cnpj_emitente' => '12345678000195', 'nome_emitente' => 'Mercado da Jornada',
            'modelo' => '65', 'valor_total' => $valorReais,
            'data_emissao' => '2026-01-15 10:00:00', 'status' => Cupom::STATUS_VALIDADO, 'origem' => 'scan',
        ]);
        CupomAtribuicao::create(['cupom_id' => $cupom->id, 'user_id' => $user->id]);
        app(CreditarCashbackService::class)->creditarPorCupom($cupom);
    }

    /** CA-1..CA-6 — jornada contínua: entrar → home → coletar → saldo atualiza → extrato → saque. */
    public function test_jornada_continua_do_coletador(): void
    {
        $user = User::factory()->create(['email' => self::EMAIL, 'name' => 'Ana Coletadora']);
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 0]);

        $this->browse(function (Browser $browser) use ($user) {
            // Entrar → home-hub; antes de coletar, saldo zero.
            $browser->loginAs($user)
                ->resize(390, 1600)
                ->visit('/inicio')
                ->waitFor('[data-testid=screen-home-saldo]', 10)
                ->assertSeeIn('[data-testid=screen-home-saldo]', 'R$ 0,00')
                // Coletar a partir da home em 1 toque (CA-4).
                ->click('[data-testid=screen-home-cta]')
                ->waitForLocation('/coletar', 10)
                ->assertPathIs('/coletar');

            // Coleta válida que gera crédito (validação SEFAZ é assíncrona/EPIC-002 — representada aqui).
            $this->coletaValidaCredita($user, '4890.00'); // 0,1% = R$ 4,89

            $browser
                // Voltar à home: o saldo reflete a coleta, sem novo login (CA-1).
                ->visit('/inicio')
                ->waitFor('[data-testid=screen-home-saldo]', 10)
                ->assertSeeIn('[data-testid=screen-home-saldo]', 'R$ 4,89')
                // Extrato em 1 toque; o crédito aparece no histórico (CA-2/CA-4).
                ->click('[data-testid=screen-home-atalho-historico]')
                ->waitForLocation('/carteira', 10)
                ->assertSeeIn('[data-testid=screen-carteira-item]', 'Mercado da Jornada')
                ->assertSeeIn('[data-testid=screen-carteira-item-credito]', '+R$ 4,89')
                // Iniciar saque a partir da jornada (CA-3).
                ->visit('/inicio')
                ->click('[data-testid=screen-home-atalho-premios]')
                ->waitForLocation('/carteira/saque', 10)
                // Tela de saque pronta para prosseguir, com o saldo em pt-BR (CA-3/CA-6).
                ->assertPresent('[data-testid=screen-saque-submit]')
                ->assertSeeIn('[data-testid=screen-saque-saldo]', 'R$ 4,89');
        });
    }
}
