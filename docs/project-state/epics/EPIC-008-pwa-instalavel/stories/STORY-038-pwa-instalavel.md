---
story_id: STORY-038
slug: pwa-instalavel
title: PWA instalável — service worker mínimo e convite de instalação
epic_id: EPIC-008
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: done
owner_agent: claude-programador
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-038 — PWA instalável (SW mínimo + convite de instalação)

> **Para o agente que vai executar:** leia por inteiro. Torna a área do Coletador uma PWA instalável de
> verdade, sem push e sem offline. O ponto sensível é o service worker: adicioná-lo **sem** reintroduzir
> cache (o `versionWatcher` segue como atualizador).

## Contexto (por que esta estória existe)

A área B2C já é PWA mobile-first (manifest standalone + metatags iOS — IDR-012), mas **não** havia service
worker nem captura de `beforeinstallprompt`. Sem isso, o Chrome/Android não oferece "Instalar app" e não há
convite/instrução ao usuário. O IDR-012 adiara o SW de propósito ("entra quando o piloto pedir — vira
estória própria"); esta é essa estória. PO escolheu a **Opção A** (só instalável) com **SW mínimo, sem cache**.

- Documentos a ler ANTES: IDR-012 (casca/PWA sem SW), IDR-010 (i18n), DDR-007 (casca logada).
- Arquivos-âncora: `resources/js/app.jsx`, `resources/js/versionWatcher.js`, `resources/js/Pages/Home/Hub.jsx`,
  `public/manifest.json`, `resources/views/app.blade.php`, `lang/pt_BR.json`.

## O quê (objetivo)

1. Service worker MÍNIMO em `public/sw.js` (passivo, sem cache) e seu registro guardado.
2. Captura do `beforeinstallprompt` no boot + convite dispensável na home (Android instala; iOS coach A2HS).
3. Polir o `manifest.json` (`id`, `shortcuts`, ícones `any`/`maskable` separados). i18n pt-BR das strings.

## Por quê (valor para o usuário)

App na tela inicial, abrindo em tela cheia com um toque — coleta mais à mão, mais "app", sem loja.

## Critérios de aceite

- [x] **CA-1:** `public/sw.js` existe, registra em contexto seguro/build real, e **não** cacheia nada
      (fetch passivo, sem `respondWith`); o `versionWatcher` continua o único atualizador.
- [x] **CA-2:** `manifest.json` habilita instalação: `id`, `display: standalone`, `start_url`, ícones
      192/512 com `maskable` dedicado, e atalho para `/coletar`.
- [x] **CA-3:** Na home, um convite dispensável de instalar aparece quando o browser sinaliza
      instalabilidade (Android, botão "Instalar") ou no iOS (instrução "Adicionar à Tela de Início");
      dispensar persiste (não reaparece).
- [x] **CA-4:** 100% pt-BR (i18n IDR-010), a11y AA (alvos ≥48px, foco, aria), sobre o DS (verde único
      accent), mobile-first sem overflow. Não some/instala quando já rodando standalone.

## Fora de escopo

- Web Push; offline/precache; ícone maskable dedicado com safe-zone; screenshots no manifest.

## Definição de Pronto (DoD)

- [x] CA-1 a CA-4 cobertos por teste (feature + E2E) — verdes.
- [x] Decisão registrada (IDR-016); `index.json` atualizado; "Notas do agente" preenchidas.
- [ ] Gate manual de device/DevTools (Lighthouse + install Android/iOS) — pendente com o PO.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas

- **SW mínimo sem cache (IDR-016):** `install`→`skipWaiting`, `activate`→`clients.claim`, `fetch` passivo
  (sem `respondWith`). Registro em `pwa/registerServiceWorker.js` guardado por `window.isSecureContext` e
  `app-asset != 'dev'` (espelha o `versionWatcher`) → **não** registra no E2E (http inseguro) nem no vite dev.
- **Captura no boot:** `pwa/installPrompt.js` (store externo, `initInstallCapture()` em `app.jsx` antes do
  render) + hook `pwa/useInstallPrompt.js`. UI em `Components/pwa/InstallPrompt.jsx`, só na home; dispensa
  persistida em `localStorage` (`quantah:pwa-dismiss`). iOS detectado por UA (sem API) → coach A2HS.
- **Manifest:** `id: "/"`, `shortcuts` (Coletar → `/coletar`), ícones com `purpose` separado (`any` +
  `maskable`) em vez do combinado (evita recorte). i18n: 7 chaves-fonte novas no `lang/pt_BR.json` (re-sort
  alfabético, diff só de adições).

### Descobertas

- O E2E roda sobre `http://laravel.test` (contexto **inseguro**): SW real e `beforeinstallprompt` não
  disparam ali. Cobri a UX do convite injetando um `beforeinstallprompt` sintético; a instalação real fica
  no roteiro manual de device (como o `CascaMobileTest` faz com o Safari). Registrado no IDR-016.
- `caches`/`respondWith` apareciam nos comentários do `sw.js` → o teste checa as **chamadas**
  (`caches.open(`, `.respondWith(`), não o token solto.

### Mapeamento CA → teste (todos verdes)

- **CA-1/CA-2** → `tests/Feature/PwaAssetsTest.php` (sw.js sem cache/respondWith; manifest com
  id/standalone/start_url/ícones/maskable/atalho).
- **CA-3** → `tests/Browser/PwaInstalavelTest.php` (`test_convite_so_aparece_apos_beforeinstallprompt`,
  `test_dispensar_persiste_entre_visitas`).
- **CA-4** → coberto pela UX (DS/pt-BR/alvos ≥48px no `InstallPrompt`) + `mostrar=false` quando standalone.

### Bloqueios encontrados

Nenhum. Gate de device/Lighthouse pendente com o PO (não automatizável em headless/http).

### Links de evidência

- Feature: `PwaAssetsTest` (2/2). Unit+feature suíte: **340/340**.
- E2E: `PwaInstalavelTest` (2/2); regressão Home/Casca: `HomeHubTest`+`CascaMobileTest`+`PwaInstalavelTest`
  **11/11**.
- Assets servidos: `GET /sw.js` → 200 `application/javascript`; `GET /manifest.json` → 200
  `application/json` (`id=/`, atalho `/coletar`, `purpose` any+maskable).
- Decisão: `decisions/idr/IDR-016-service-worker-minimo-pwa-instalavel.md`.
- Arquivos: `public/sw.js`, `resources/js/pwa/{installPrompt,useInstallPrompt,registerServiceWorker}.js`,
  `Components/pwa/InstallPrompt.jsx`, `Components/icons.jsx` (Download/Share), `app.jsx`, `Pages/Home/Hub.jsx`,
  `public/manifest.json`, `lang/pt_BR.json`.
