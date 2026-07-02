<?php

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

Route::get('/dashboard', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
