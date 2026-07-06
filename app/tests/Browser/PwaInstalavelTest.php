<?php

namespace Tests\Browser;

use App\Models\Carteira;
use App\Models\User;
use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

/**
 * E2E do convite de instalação da PWA (EPIC-008 · STORY-038), mobile-first.
 *
 * O `beforeinstallprompt` real depende de contexto seguro (HTTPS) + service worker +
 * heurística de engajamento — nada disso ocorre no Chrome headless sobre http://laravel.test.
 * Então injetamos um evento SINTÉTICO para exercitar o ramo Android (captura → banner →
 * botão → dispensa), que é o comportamento da nossa UI. A ativação real do SW e o ramo iOS
 * (instrução de "Adicionar à Tela de Início") são validados no device/DevTools (roteiro nas
 * Notas do agente), como o CascaMobileTest faz com o comportamento específico do Safari.
 */
class PwaInstalavelTest extends DuskTestCase
{
    private const EMAIL = 'dusk-pwa@quantah.test';

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
    }

    private function usuario(): User
    {
        $user = User::factory()->create(['email' => self::EMAIL, 'name' => 'Ana Coletadora']);
        Carteira::create(['user_id' => $user->id, 'saldo_centavos' => 1247]);

        return $user;
    }

    /** Abre a home com o localStorage limpo (sem "dispensado" de um teste anterior). */
    private function abrirHome(Browser $browser, User $user): void
    {
        $browser->loginAs($user)->resize(390, 1400)
            ->visit('/inicio')->waitFor('[data-testid=screen-home-saldo]', 10)
            ->script('window.localStorage.clear();');
        // Remonta com o storage já limpo, para o convite não nascer dispensado.
        $browser->visit('/inicio')->waitFor('[data-testid=screen-home-saldo]', 10);
    }

    /** Dispara um beforeinstallprompt sintético (ramo Android/Chrome). */
    private function dispararConvite(Browser $browser): void
    {
        $browser->script(
            "var e=new Event('beforeinstallprompt',{cancelable:true});".
            'e.prompt=function(){return Promise.resolve();};'.
            "e.userChoice=Promise.resolve({outcome:'dismissed'});".
            'window.dispatchEvent(e);'
        );
    }

    /** O convite só aparece depois de o browser sinalizar instalabilidade. */
    public function test_convite_so_aparece_apos_beforeinstallprompt(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $this->abrirHome($browser, $user);
            $browser->assertMissing('[data-testid=pwa-install-prompt]');

            $this->dispararConvite($browser);
            $browser->waitFor('[data-testid=pwa-install-prompt]', 5)
                ->assertSeeIn('[data-testid=pwa-install-btn]', 'Instalar');
        });
    }

    /** "Dispensar" esconde e persiste — não reaparece nem com um novo convite. */
    public function test_dispensar_persiste_entre_visitas(): void
    {
        $user = $this->usuario();

        $this->browse(function (Browser $browser) use ($user) {
            $this->abrirHome($browser, $user);
            $this->dispararConvite($browser);
            $browser->waitFor('[data-testid=pwa-install-prompt]', 5)
                ->click('[data-testid=pwa-install-dismiss]')
                ->waitUntilMissing('[data-testid=pwa-install-prompt]', 5);

            // Nova visita + novo convite: continua dispensado (persistido em localStorage).
            $browser->visit('/inicio')->waitFor('[data-testid=screen-home-saldo]', 10);
            $this->dispararConvite($browser);
            $browser->pause(500)->assertMissing('[data-testid=pwa-install-prompt]');
        });
    }
}
