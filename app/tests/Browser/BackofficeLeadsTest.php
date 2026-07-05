<?php

namespace Tests\Browser;

use App\Models\Lead;
use App\Models\Role;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real da lista de leads no Backoffice (STORY-027 · CA-5). Caminhos:
 *  (a) operador vê um lead recém-capturado na lista;
 *  (b) usuário sem o papel operacional é barrado (403 branded em pt-BR).
 * Roda contra o banco de dev — limpa usuário e lead de teste.
 */
class BackofficeLeadsTest extends DuskTestCase
{
    private const OPERADOR = 'dusk-op-leads@quantah.test';

    private const COLETADOR = 'dusk-col-leads@quantah.test';

    private const LEAD = 'dusk-lead-backoffice@quantah.test';

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
        User::whereIn('email', [self::OPERADOR, self::COLETADOR])->delete();
        Lead::where('email', self::LEAD)->delete();
    }

    private function operador(): User
    {
        $user = User::factory()->create(['email' => self::OPERADOR]);
        $user->roles()->attach(Role::firstOrCreate(['nome' => Role::OPERADOR]));

        return $user;
    }

    /** (a) operador vê o lead recém-capturado na lista, em pt-BR. */
    public function test_operador_ve_lead_na_lista(): void
    {
        $op = $this->operador();
        Lead::create(['nome' => 'Ana Souza', 'email' => self::LEAD, 'empresa' => 'Acme QA']);

        $this->browse(function (Browser $browser) use ($op) {
            $browser->loginAs($op)
                ->visit('/backoffice/leads')
                ->waitFor('[data-testid=backoffice-leads]', 10)
                ->assertSee('Leads')
                ->assertSee('Ana Souza')
                ->assertSee(self::LEAD)
                ->assertSee('Acme QA')
                ->logout();
        });
    }

    /** (b) usuário sem o papel operacional é barrado (403 branded em pt-BR). */
    public function test_sem_papel_e_barrado(): void
    {
        $col = User::factory()->create(['email' => self::COLETADOR]);

        $this->browse(function (Browser $browser) use ($col) {
            $browser->loginAs($col)
                ->visit('/backoffice/leads')
                ->waitFor('[data-testid=barreira-403]', 10)
                ->assertSee('Acesso restrito')
                ->assertSee('não tem permissão')
                ->logout();
        });
    }
}
