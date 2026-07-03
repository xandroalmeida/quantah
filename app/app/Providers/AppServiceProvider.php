<?php

namespace App\Providers;

use App\Domain\Coleta\IngestaoCupomService;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Captura/handoff (STORY-009) não usa adaptador de extração — ele é injetado
        // pela STORY-010 (SpSefazAdapter real, atrás de SefazAdapter, ADR-002). Aqui a
        // fronteira sobe sem adaptador; o container não precisa resolver a interface.
        $this->app->bind(IngestaoCupomService::class, fn () => new IngestaoCupomService);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
