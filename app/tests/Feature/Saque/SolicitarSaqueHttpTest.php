<?php

namespace Tests\Feature\Saque;

use App\Models\Carteira;
use App\Models\Saque;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Inertia\Testing\AssertableInertia as Assert;
use Tests\TestCase;

/**
 * Fluxo HTTP do Colaborador solicitar saque (STORY-017). Contrato: form envia `valor`
 * (reais) + `cpf` (= chave PIX); o servidor converte para centavos e delega ao domínio.
 * Auth exigida; erros de regra voltam por campo.
 */
class SolicitarSaqueHttpTest extends TestCase
{
    use RefreshDatabase;

    private const CPF = '111.444.777-35';

    private function userComSaldo(int $centavos): User
    {
        $user = User::factory()->create();
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => $centavos]);

        return $user;
    }

    public function test_exige_autenticacao(): void
    {
        $this->get('/carteira/saque')->assertRedirect('/login');
        $this->post('/carteira/saque', [])->assertRedirect('/login');
    }

    public function test_renderiza_form_com_saldo(): void
    {
        $user = $this->userComSaldo(1247);

        $this->actingAs($user)->get('/carteira/saque')
            ->assertOk()
            ->assertInertia(fn (Assert $page) => $page
                ->component('Saque/Solicitar')
                ->where('saldo.reais', '12,47')
                ->where('saldo.centavos', 1247));
    }

    public function test_solicita_saque_valido_reserva_e_redireciona(): void
    {
        $user = $this->userComSaldo(3000); // R$ 30,00

        $this->actingAs($user)
            ->from('/carteira/saque')
            ->post('/carteira/saque', ['valor' => '20', 'cpf' => self::CPF])
            ->assertRedirect('/carteira')
            ->assertSessionHas('saque');

        $this->assertDatabaseHas('saques', [
            'valor_centavos' => 2000,
            'cpf' => '11144477735',
            'chave_pix' => '11144477735',
            'status' => Saque::STATUS_SOLICITADO,
        ]);
        $this->assertSame(1000, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
    }

    public function test_bloqueia_abaixo_do_minimo(): void
    {
        $user = $this->userComSaldo(3000);

        $this->actingAs($user)->from('/carteira/saque')
            ->post('/carteira/saque', ['valor' => '3', 'cpf' => self::CPF])
            ->assertSessionHasErrors('valor');

        $this->assertDatabaseCount('saques', 0);
    }

    public function test_bloqueia_acima_do_saldo(): void
    {
        $user = $this->userComSaldo(3000); // R$ 30,00

        $this->actingAs($user)->from('/carteira/saque')
            ->post('/carteira/saque', ['valor' => '50', 'cpf' => self::CPF])
            ->assertSessionHasErrors('valor');

        $this->assertDatabaseCount('saques', 0);
        $this->assertSame(3000, Carteira::where('user_id', $user->id)->first()->saldo_centavos);
    }

    public function test_bloqueia_cpf_invalido(): void
    {
        $user = $this->userComSaldo(3000);

        $this->actingAs($user)->from('/carteira/saque')
            ->post('/carteira/saque', ['valor' => '20', 'cpf' => '111.444.777-00'])
            ->assertSessionHasErrors('cpf');

        $this->assertDatabaseCount('saques', 0);
    }
}
