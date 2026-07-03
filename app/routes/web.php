<?php

use App\Http\Controllers\ColetaController;
use App\Http\Controllers\Interno\MetricasController;
use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

Route::get('/', function () {
    return Inertia::render('Hello', [
        'appName' => config('app.name'),
        'environment' => app()->environment(),
        'laravelVersion' => Application::VERSION,
        'phpVersion' => PHP_VERSION,
    ]);
})->name('home');

// Vitrine do Design System (EPIC-001). Pública para inspeção/E2E; virada à
// kitchen sink completa na STORY-006.
Route::get('/ds/buttons', function () {
    return Inertia::render('DesignSystem/Buttons');
})->name('ds.buttons');

Route::get('/ds/inputs', function () {
    return Inertia::render('DesignSystem/Inputs');
})->name('ds.inputs');

// Vitrine kitchen-sink: todos os componentes do DS (STORY-006). Rota dedicada,
// pública para inspeção/E2E e smoke de homologação.
Route::get('/ds', function () {
    return Inertia::render('DesignSystem/Showcase');
})->name('ds.showcase');

// Captura do cupom (STORY-009). Tela mobile de scan/colar o QR da NFC-e. Aberta
// (a atribuição ao Colaborador/cashback vem com a Carteira, EPIC-003); o POST é
// limitado por throttle. A validação/persistência canônica é a STORY-010.
Route::get('/coletar', [ColetaController::class, 'create'])->name('coleta.create');
Route::post('/coletar', [ColetaController::class, 'store'])
    ->middleware('throttle:30,1')
    ->name('coleta.store');

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

// Painel interno da north-star (STORY-012). Atrás de `auth` — mostra a contagem de
// cupons válidos-únicos-novos por semana e a taxa de sucesso de envio.
Route::get('/interno/metricas', [MetricasController::class, 'index'])
    ->middleware('auth')
    ->name('interno.metricas');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
