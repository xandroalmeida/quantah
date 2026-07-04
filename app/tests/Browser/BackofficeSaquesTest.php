<?php

namespace Tests\Browser;

use App\Domain\Saque\SolicitarSaqueService;
use App\Models\Carteira;
use App\Models\Role;
use App\Models\Saque;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real do backoffice de saques (STORY-017, ADR-005/009). Cobre: operador
 * conduz o fluxo até pago; rejeição estorna; não-operador é barrado (403).
 *
 * Roda contra o banco de dev (auto-limpo).
 */
class BackofficeSaquesTest extends DuskTestCase
{
    private const OP = 'dusk-operador@quantah.test';

    private const COL = 'dusk-colab-saque@quantah.test';

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
        User::whereIn('email', [self::OP, self::COL])->delete();
    }

    private function operador(): User
    {
        $u = User::factory()->create(['email' => self::OP]);
        $u->roles()->attach(Role::firstOrCreate(['nome' => Role::OPERADOR]));

        return $u;
    }

    private function saqueSolicitado(): Saque
    {
        $col = User::factory()->create(['email' => self::COL]);
        Carteira::create(['user_id' => $col->id, 'saldo_centavos' => 3000]);

        return app(SolicitarSaqueService::class)->solicitar($col, 2000, self::CPF, self::CPF);
    }

    public function test_operador_conduz_ate_pago(): void
    {
        $op = $this->operador();
        $saque = $this->saqueSolicitado();

        $this->browse(function (Browser $browser) use ($op) {
            $browser->loginAs($op)
                ->visit('/backoffice/saques')
                ->waitFor('[data-testid=backoffice-saques]', 10)
                ->assertSee('R$ 20,00')
                ->assertSeeIn('[data-testid=backoffice-saque-status]', 'solicitado')
                ->click('[data-testid=backoffice-acao-assumir]')
                ->waitFor('[data-testid=backoffice-acao-aprovar]', 10)
                ->click('[data-testid=backoffice-acao-aprovar]')
                ->waitFor('[data-testid=backoffice-comprovante]', 10)
                ->type('[data-testid=backoffice-comprovante]', 'E2E-PIX-777')
                ->click('[data-testid=backoffice-acao-pagar]')
                ->waitFor('[data-testid=backoffice-saque-status]', 10)
                ->assertSeeIn('[data-testid=backoffice-saque-status]', 'pago');
        });

        $this->assertDatabaseHas('saques', ['status' => Saque::STATUS_PAGO, 'comprovante' => 'E2E-PIX-777']);
    }

    public function test_rejeitar_estorna_o_saldo(): void
    {
        $op = $this->operador();
        $saque = $this->saqueSolicitado();

        $this->browse(function (Browser $browser) use ($op, $saque) {
            $browser->loginAs($op)
                ->visit("/backoffice/saques/{$saque->id}")
                ->waitFor('[data-testid=backoffice-acao-assumir]', 10)
                ->click('[data-testid=backoffice-acao-assumir]')
                ->waitFor('[data-testid=backoffice-acao-rejeitar]', 10)
                ->click('[data-testid=backoffice-acao-rejeitar]')
                ->acceptDialog()
                ->waitForText('rejeitado', 10)
                ->assertSeeIn('[data-testid=backoffice-saque-status]', 'rejeitado');
        });

        $this->assertSame(3000, Carteira::find($saque->carteira_id)->saldo_centavos); // estornado
    }

    public function test_nao_operador_e_barrado(): void
    {
        $col = User::factory()->create(['email' => self::COL]);

        $this->browse(function (Browser $browser) use ($col) {
            $browser->loginAs($col)
                ->visit('/backoffice/saques')
                ->assertSee('403');
        });
    }
}
