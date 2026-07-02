<?php

namespace Tests\Browser;

use Laravel\Dusk\Browser;
use Tests\DuskTestCase;

class HelloWorldTest extends DuskTestCase
{
    /**
     * CA-5 — caminho feliz em browser real: um visitante abre a raiz e vê a
     * hello-world do Quantah renderizada (Inertia → página React).
     */
    public function test_visitor_sees_quantah_hello_world(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('[data-testid="hello-world"]')
                ->assertSee('Quantah')
                ->assertSeeIn('[data-testid="hello-world"]', 'Olá do Quantah');
        });
    }

    /**
     * CA-5 — hidratação: a página React monta e exibe o ambiente vindo do backend
     * (prova que Inertia/React hidratou de verdade no navegador, não só HTML estático).
     */
    public function test_hello_world_hydrates_react_and_shows_environment(): void
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/')
                ->waitFor('[data-testid="hello-environment"]')
                ->assertSeeIn('[data-testid="hello-environment"]', 'ambiente:');
        });
    }
}
