<?php

namespace Tests\Feature\Coleta;

use App\Models\CapturaIlegivel;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

/**
 * Diagnóstico de captura ilegível: quando o cliente não lê o QR, registra a tentativa
 * (e a chave, se o OCR pegou 44 dígitos com DV válido) para diagnóstico futuro. Para o
 * usuário, segue "não foi possível ler" — este caminho é só backend/telemetria.
 */
class CapturaIlegivelTest extends TestCase
{
    use RefreshDatabase;

    private const CHAVE_VALIDA = '35260112345678000195650010001234561000000019';

    public function test_registra_a_chave_quando_o_ocr_entrega_44_digitos_validos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/coleta/ilegivel', ['chave' => self::CHAVE_VALIDA])
            ->assertNoContent();

        $this->assertDatabaseHas('coleta_capturas_ilegiveis', [
            'user_id' => $user->id,
            'chave' => self::CHAVE_VALIDA,
        ]);
    }

    public function test_aceita_a_chave_com_a_url_do_qr_e_normaliza_para_44_digitos(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->postJson('/coleta/ilegivel', ['chave' => 'https://x/?p='.self::CHAVE_VALIDA.'|2|1|...'])
            ->assertNoContent();

        $this->assertDatabaseHas('coleta_capturas_ilegiveis', ['chave' => self::CHAVE_VALIDA]);
    }

    public function test_registra_a_falha_sem_chave_quando_o_ocr_falha_ou_e_invalido(): void
    {
        $user = User::factory()->create();

        foreach ([['chave' => null], ['chave' => '123-lixo-do-ocr'], []] as $payload) {
            $this->actingAs($user)->postJson('/coleta/ilegivel', $payload)->assertNoContent();
        }

        $this->assertSame(3, CapturaIlegivel::whereNull('chave')->count());
    }

    public function test_exige_autenticacao(): void
    {
        // Rota do grupo `web`/`auth`: anônimo é barrado (redireciona ao login) e nada grava.
        $this->post('/coleta/ilegivel', ['chave' => self::CHAVE_VALIDA])->assertRedirect('/login');

        $this->assertDatabaseCount('coleta_capturas_ilegiveis', 0);
    }
}
