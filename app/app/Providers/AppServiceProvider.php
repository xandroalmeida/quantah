<?php

namespace App\Providers;

use App\Domain\Cashback\Listeners\CreditarCashbackAoValidar;
use App\Domain\Coleta\Events\CupomValidado;
use App\Domain\Coleta\IngestaoCupomService;
use App\Domain\Coleta\Sefaz\HttpSefazSpFetcher;
use App\Domain\Coleta\Sefaz\SefazSpFetcher;
use App\Domain\Coleta\Sefaz\SpSefazAdapter;
use App\Models\Role;
use App\Models\User;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
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

        // Cupom válido-único-novo → crédito de cashback ao coletor (STORY-015). Registrado
        // explicitamente porque o listener vive fora de `app/Listeners` (auto-discovery não
        // o alcança). Listener enfileirado, idempotente — ver CreditarCashbackAoValidar.
        Event::listen(CupomValidado::class, CreditarCashbackAoValidar::class);

        // Acesso ao backoffice de saque (ADR-009 · RBAC): concedido a quem tem o papel
        // `operador`. Rotas administrativas ficam atrás de `can:operar-saques`.
        Gate::define('operar-saques', fn (User $user) => $user->hasRole(Role::OPERADOR));
    }
}
