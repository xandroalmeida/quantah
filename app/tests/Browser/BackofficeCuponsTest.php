<?php

namespace Tests\Browser;

use App\Models\Cupom;
use App\Models\Emitente;
use App\Models\Role;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E em browser real do Backoffice de cupons + emitente enriquecido (STORY-041 ·
 * EPIC-009). Cobre: operador vê o cupom na lista e abre o detalhe com os dados
 * cadastrais (caminho enriquecido); cupom sem enriquecimento mostra o estado pendente
 * (CA-4); não-operador é barrado (403, RBAC ADR-009).
 *
 * Roda contra o banco de dev (auto-limpo). A fila do Dusk NÃO é `sync`, então semeamos
 * o estado final do emitente diretamente (convenção Dusk do projeto).
 */
class BackofficeCuponsTest extends DuskTestCase
{
    private const OP = 'dusk-operador-cupom@quantah.test';

    private const OUTRO = 'dusk-naoop-cupom@quantah.test';

    private const CNPJ = '43259548002883';

    private const CHAVE = '35260743259548002883652030000666061954634872';

    private const CHAVE_PEND = '35260743259548002883652030000666061954634899';

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
        User::whereIn('email', [self::OP, self::OUTRO])->delete();
        Cupom::whereIn('chave_acesso', [self::CHAVE, self::CHAVE_PEND])->delete();
        Emitente::where('cnpj', self::CNPJ)->delete();
    }

    private function operador(): User
    {
        $u = User::factory()->create(['email' => self::OP]);
        $u->roles()->attach(Role::firstOrCreate(['nome' => Role::OPERADOR]));

        return $u;
    }

    private function cupom(string $chave): Cupom
    {
        return Cupom::create([
            'chave_acesso' => $chave,
            'uf' => '35', 'ano_mes' => '2607', 'cnpj_emitente' => self::CNPJ,
            'nome_emitente' => 'Supermercados Cavicchiolli',
            'modelo' => '65', 'status' => Cupom::STATUS_VALIDADO,
            'valor_total' => '235.43', 'extraido_em' => now(),
        ]);
    }

    public function test_operador_ve_cupom_e_emitente_enriquecido(): void
    {
        $cupom = $this->cupom(self::CHAVE);
        Emitente::factory()->enriquecido()->create([
            'cnpj' => self::CNPJ,
            'razao_social' => 'SUPERMERCADOS CAVICCHIOLLI LTDA',
            'cnae_principal_codigo' => '4711302',
            'cnae_principal_descricao' => 'Comércio varejista — supermercados',
            'situacao_cadastral' => 'ATIVA', 'municipio' => 'ITU', 'uf' => 'SP',
        ]);
        $op = $this->operador();

        // A lista do Backoffice traz todos os cupons validados (inclusive dados de dev),
        // então provamos "listado com o estado" na lista e abrimos o detalhe por id —
        // determinístico, sem depender da ordem da lista.
        $this->browse(function (Browser $browser) use ($op, $cupom) {
            $browser->loginAs($op)
                ->visit('/backoffice/cupons')
                ->waitFor('[data-testid=backoffice-cupons]', 10)
                ->assertSee('SUPERMERCADOS CAVICCHIOLLI LTDA')
                ->assertSee('Enriquecido')
                ->visit('/backoffice/cupons/'.$cupom->id)
                ->waitFor('[data-testid=backoffice-cupom-detalhe]', 10)
                ->assertSeeIn('[data-testid=emitente-razao]', 'SUPERMERCADOS CAVICCHIOLLI LTDA')
                ->assertSeeIn('[data-testid=emitente-cnae]', '4711-3/02')
                ->assertSeeIn('[data-testid=emitente-estado]', 'Enriquecido')
                ->logout();
        });
    }

    public function test_cupom_sem_enriquecimento_mostra_pendente(): void
    {
        $cupom = $this->cupom(self::CHAVE_PEND); // sem Emitente semeado
        $op = $this->operador();

        $this->browse(function (Browser $browser) use ($op, $cupom) {
            $browser->loginAs($op)
                ->visit('/backoffice/cupons/'.$cupom->id)
                ->waitFor('[data-testid=backoffice-cupom-detalhe]', 10)
                ->assertSeeIn('[data-testid=emitente-estado]', 'Enriquecimento pendente')
                ->assertPresent('[data-testid=emitente-sem-dados]')
                ->logout();
        });
    }

    public function test_nao_operador_e_barrado(): void
    {
        $outro = User::factory()->create(['email' => self::OUTRO]);

        $this->browse(function (Browser $browser) use ($outro) {
            $browser->loginAs($outro)
                ->visit('/backoffice/cupons')
                ->waitFor('[data-testid=barreira-403]', 10)
                ->assertSee('Acesso restrito')
                ->logout();
        });
    }
}
