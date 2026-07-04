<?php

namespace Tests\Browser;

use App\Models\Carteira;
use App\Models\Saque;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real do saque do Colaborador (STORY-017). Cobre os caminhos alcançáveis:
 * solicitar com sucesso (saldo reservado), valor acima do saldo (erro no campo), CPF inválido.
 *
 * Roda contra o banco de dev (auto-limpo). O saque é PIX assistido — só cria a solicitação.
 */
class SolicitarSaqueTest extends DuskTestCase
{
    private const EMAIL = 'dusk-saque@quantah.test';

    private const CPF = '111.444.777-35';

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
        User::where('email', self::EMAIL)->delete(); // cascateia carteira/saques
    }

    private function usuarioComSaldo(int $centavos): User
    {
        $user = User::factory()->create(['email' => self::EMAIL]);
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => $centavos]);

        return $user;
    }

    public function test_solicita_saque_com_sucesso(): void
    {
        $user = $this->usuarioComSaldo(3000); // R$ 30,00

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/carteira')
                ->waitFor('[data-testid=screen-carteira-sacar]', 10)
                ->click('[data-testid=screen-carteira-sacar]')
                ->waitForLocation('/carteira/saque', 10)
                ->waitFor('[data-testid=screen-saque-valor]', 10)
                ->type('[data-testid=screen-saque-valor]', '20')
                ->type('[data-testid=screen-saque-cpf]', self::CPF)
                ->click('[data-testid=screen-saque-submit]')
                ->waitForLocation('/carteira', 10)
                ->waitFor('[data-testid=screen-carteira-saque-ok]', 10)
                ->assertSee('Saque solicitado');
        });

        $this->assertDatabaseHas('saques', [
            'cpf' => '11144477735',
            'valor_centavos' => 2000,
            'status' => Saque::STATUS_SOLICITADO,
        ]);
        $this->assertSame(1000, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
    }

    public function test_valor_acima_do_saldo_mostra_erro(): void
    {
        $user = $this->usuarioComSaldo(3000);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/carteira/saque')
                ->waitFor('[data-testid=screen-saque-valor]', 10)
                ->type('[data-testid=screen-saque-valor]', '50')
                ->type('[data-testid=screen-saque-cpf]', self::CPF)
                ->click('[data-testid=screen-saque-submit]')
                ->waitFor('[data-testid=screen-saque-erro-valor]', 10)
                ->assertSee('Saldo insuficiente');
        });

        $this->assertSame(3000, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
    }

    public function test_cpf_invalido_mostra_erro(): void
    {
        $user = $this->usuarioComSaldo(3000);

        $this->browse(function (Browser $browser) use ($user) {
            $browser->loginAs($user)
                ->resize(390, 1400)
                ->visit('/carteira/saque')
                ->waitFor('[data-testid=screen-saque-cpf]', 10)
                ->type('[data-testid=screen-saque-valor]', '20')
                ->type('[data-testid=screen-saque-cpf]', '111.444.777-00')
                ->click('[data-testid=screen-saque-submit]')
                ->waitFor('[data-testid=screen-saque-erro-cpf]', 10)
                ->assertSee('CPF inválido');
        });
    }
}
