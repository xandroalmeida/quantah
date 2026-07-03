<?php

namespace App\Providers;

use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\Sefaz\HttpSefazSpFetcher;
use App\Domain\Coleta\Sefaz\SefazSpFetcher;
use App\Domain\Coleta\Sefaz\SpSefazAdapter;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Extração SEFAZ-SP (ADR-002): o fetcher real bate no portal; testes trocam por
        // um fake no container. O adaptador de SP é a ACL (ADR-001) por trás da fronteira.
        $this->app->bind(SefazSpFetcher::class, HttpSefazSpFetcher::class);

        $this->app->bind(IngestaoCupomService::class, fn ($app) => new IngestaoCupomService(
            $app->make(SpSefazAdapter::class),
        ));
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Vite::prefetch(concurrency: 3);
    }
}
