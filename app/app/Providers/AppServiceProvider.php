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
use App\Support\Auth\FakeGoogleProvider;
use Carbon\CarbonImmutable;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;
use Laravel\Socialite\Facades\Socialite;

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

        // Localização pt-BR (ADR-011): nomes de mês/dia e `translatedFormat` do Carbon saem em
        // português. A persistência segue em UTC; o fuso America/Sao_Paulo é só de exibição
        // (App\Support\Formato). Data canônica continua ISO 8601.
        Carbon::setLocale('pt_BR');
        CarbonImmutable::setLocale('pt_BR');

        // Cupom válido-único-novo → crédito de cashback ao coletor (STORY-015). Registrado
        // explicitamente porque o listener vive fora de `app/Listeners` (auto-discovery não
        // o alcança). Listener enfileirado, idempotente — ver CreditarCashbackAoValidar.
        Event::listen(CupomValidado::class, CreditarCashbackAoValidar::class);

        // Acesso ao backoffice de saque (ADR-009 · RBAC): concedido a quem tem o papel
        // `operador`. Rotas administrativas ficam atrás de `can:operar-saques`.
        Gate::define('operar-saques', fn (User $user) => $user->hasRole(Role::OPERADOR));

        // Login Google simulado (STORY-022 · ADR-010) — só em dev/CI/E2E, via flag. Em
        // homolog/prod (flag off) vale o driver real do Socialite, com credencial via secret.
        if (config('services.google.fake')) {
            Socialite::extend('google', fn () => new FakeGoogleProvider);
        }
    }
}
