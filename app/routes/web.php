<?php

use App\Http\Controllers\Backoffice\LeadsController;
use App\Http\Controllers\Backoffice\SaquesController;
use App\Http\Controllers\CarteiraController;
use App\Http\Controllers\ColetaController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\Intelligence\LeadController;
use App\Http\Controllers\Interno\MetricasController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SaqueController;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

/*
|--------------------------------------------------------------------------
| Segmentação das 3 áreas (STORY-023 · ADR-010 §3, reusando o RBAC ADR-009)
|--------------------------------------------------------------------------
| O produto tem três públicos com portas distintas. Cada área é um grupo de
| rota com guard explícito; rota nova nasce dentro de um grupo (fail-secure):
|   • B2C — Coletador ......... `auth` (todo autenticado é Coletador)
|   • Backoffice — Operação ... `auth` + `can:operar-saques` (sem CTA público)
|   • B2B — Intelligence ...... pública e reservada (sem login, sem features)
| A entrada de cada área está anotada no cabeçalho do respectivo grupo.
*/

// ---------------------------------------------------------------------------
// Área pública — landing B2C / home (sem segmentação de público)
// Entrada do funil B2C (STORY-025): "Cada nota conta." Pública, sem login; o CTA
// primário leva ao /login do Coletador (EPIC-004) e o secundário à landing B2B
// (/intelligence). Substitui a hello-world de scaffolding do EPIC-000.
// ---------------------------------------------------------------------------

Route::get('/', function () {
    return Inertia::render('LandingB2C');
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

// ---------------------------------------------------------------------------
// Área B2B — Quantah Intelligence (PÚBLICA · ADR-010 §3 / PDR-003)
// Entrada: /intelligence. Pública, SEM login (não há conta B2B nesta onda). A
// landing apresenta o produto e capta lead (STORY-026); a confirmação é uma tela
// dedicada de agradecimento (DDR-006, PRG). Nenhuma rota aqui carrega `auth`.
// ---------------------------------------------------------------------------

Route::get('/intelligence', [LeadController::class, 'landing'])->name('intelligence.landing');
Route::post('/intelligence/leads', [LeadController::class, 'store'])
    ->middleware('throttle:20,1')
    ->name('intelligence.leads.store');
Route::get('/intelligence/obrigado', [LeadController::class, 'obrigado'])->name('intelligence.obrigado');

// Política de Privacidade (LGPD) — página pública de texto; destino do aviso de consentimento
// do formulário de lead (STORY-026). Sem login.
Route::get('/privacidade', function () {
    return Inertia::render('Privacidade');
})->name('privacidade');

// ---------------------------------------------------------------------------
// Área B2C — Coletador (AUTENTICADO · ADR-010 §3)
// Entrada: /login (STORY-021/022) → destino do Coletador logado. Todo usuário
// autenticado sem papel administrativo é Coletador; o guard é a sessão `auth`.
// ---------------------------------------------------------------------------

Route::middleware('auth')->group(function () {
    // Captura do cupom (STORY-009/015). A coleta atribui o cupom ao Colaborador
    // logado, que recebe o cashback quando o cupom valida. POST limitado por throttle.
    Route::get('/coletar', [ColetaController::class, 'create'])->name('coleta.create');
    Route::post('/coletar', [ColetaController::class, 'store'])
        ->middleware('throttle:30,1')
        ->name('coleta.store');

    // Carteira do Colaborador (STORY-016): saldo em reais + histórico de cashback.
    Route::get('/carteira', [CarteiraController::class, 'index'])->name('carteira.index');

    // Solicitação de saque — PIX assistido (STORY-017, ADR-005).
    Route::get('/carteira/saque', [SaqueController::class, 'create'])->name('saque.create');
    Route::post('/carteira/saque', [SaqueController::class, 'store'])->name('saque.store');

    // Painel interno da north-star (STORY-012): cupons válidos-únicos-novos por semana.
    Route::get('/interno/metricas', [MetricasController::class, 'index'])->name('interno.metricas');

    // Perfil do Coletador (Breeze).
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

// Home-hub do Coletador (STORY-029 · EPIC-006) — DESTINO PÓS-LOGIN da área B2C.
// Substitui a página genérica de scaffolding (Breeze Dashboard) pelo centro da jornada:
// saldo da carteira (EPIC-003) + CTA de coleta (EPIC-002). Mantém o nome `dashboard` e a
// guarda `verified` (STORY-022) — é para onde todos os fluxos de acesso já apontam. A
// navegação coesa (renomear a URL, atalhos, ≤2 toques) é a STORY-030.
Route::get('/dashboard', [HomeController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

// ---------------------------------------------------------------------------
// Área Backoffice — Operação interna (AUTENTICADO + RBAC · ADR-009/ADR-010 §3)
// Entrada: /backoffice/* — URL direta, NÃO anunciada (sem CTA/link público). O
// guard é por grupo (nunca `can:` solto por rota): não-operador recebe 403.
// ---------------------------------------------------------------------------

Route::middleware(['auth', 'can:operar-saques'])->prefix('backoffice')->name('backoffice.')->group(function () {
    // Leads B2B capturados na landing (STORY-026) — visíveis à operação (STORY-027).
    Route::get('/leads', [LeadsController::class, 'index'])->name('leads.index');

    Route::get('/saques', [SaquesController::class, 'index'])->name('saques.index');
    Route::get('/saques/{saque}', [SaquesController::class, 'show'])->name('saques.show');
    Route::post('/saques/{saque}/assumir', [SaquesController::class, 'assumir'])->name('saques.assumir');
    Route::post('/saques/{saque}/aprovar', [SaquesController::class, 'aprovar'])->name('saques.aprovar');
    Route::post('/saques/{saque}/pagar', [SaquesController::class, 'pagar'])->name('saques.pagar');
    Route::post('/saques/{saque}/rejeitar', [SaquesController::class, 'rejeitar'])->name('saques.rejeitar');
});

require __DIR__.'/auth.php';
