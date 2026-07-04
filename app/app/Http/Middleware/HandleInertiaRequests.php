<?php

namespace App\Http\Middleware;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Inertia\Middleware;

class HandleInertiaRequests extends Middleware
{
    /**
     * The root template that is loaded on the first page visit.
     *
     * @var string
     */
    protected $rootView = 'app';

    /**
     * Determine the current asset version.
     */
    public function version(Request $request): ?string
    {
        return parent::version($request);
    }

    /**
     * Define the props that are shared by default.
     *
     * @return array<string, mixed>
     */
    public function share(Request $request): array
    {
        return [
            ...parent::share($request),
            'auth' => [
                'user' => $request->user(),
            ],
            // Flash de resultado da coleta (STORY-009) e do saque (STORY-017) — sem PII.
            'flash' => [
                'coleta' => fn () => $request->session()->get('coleta'),
                'saque' => fn () => $request->session()->get('saque'),
            ],
            // Fundação de i18n (ADR-011): o dicionário do locale ativo (`lang/<locale>.json`) é a
            // fonte única de strings de UI, consumido pelo helper `t()` no React. Monolíngue
            // (pt-BR); o mapa é idêntico entre páginas, então o custo é um bundle pequeno (~13KB).
            'locale' => App::getLocale(),
            'translations' => fn () => $this->translations(),
        ];
    }

    /**
     * Dicionário de traduções do locale ativo (mapa string-fonte → pt-BR), lido de
     * `lang/<locale>.json`. Memoizado no ciclo da requisição.
     *
     * @return array<string, string>
     */
    protected function translations(): array
    {
        static $cache = [];
        $locale = App::getLocale();

        if (! array_key_exists($locale, $cache)) {
            $path = base_path("lang/{$locale}.json");
            $cache[$locale] = is_file($path)
                ? (json_decode((string) file_get_contents($path), true) ?: [])
                : [];
        }

        return $cache[$locale];
    }
}
