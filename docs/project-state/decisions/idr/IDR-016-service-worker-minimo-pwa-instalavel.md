---
idr_id: IDR-016
slug: service-worker-minimo-pwa-instalavel
title: Service worker mínimo (sem cache) para PWA instalável — captura do beforeinstallprompt, versionWatcher segue como atualizador
status: accepted  # proposed | accepted | superseded
decided_at: 2026-07-05
decided_by: programador
owner_agent: claude-programador
related_story: STORY-038
related_adrs: []
related_idrs: [IDR-012]
related_ddrs: [DDR-007]
supersedes: null
superseded_by: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# IDR-016 — Service worker mínimo (sem cache) para PWA instalável

> Implementation Decision Record. Registra **como** a área do Coletador virou uma PWA
> **instalável de verdade** (STORY-038): com um service worker MÍNIMO, sem cache, para satisfazer
> o critério de instalabilidade do Chrome e capturar o convite de instalação — sem reintroduzir as
> armadilhas de cache que o [[IDR-012]] evitava e mantendo o `versionWatcher` como único atualizador.

## Contexto

O [[IDR-012]] (STORY-033) habilitou o "app-like" **standalone** (manifest + metatags iOS), mas
**adiou o service worker de propósito**: "offline está fora de escopo e SW mal gerido prende assets
em cache; entra quando o piloto pedir (vira estória própria)". Esta é essa estória.

O que faltava para ser um app instalável de fato: o Chrome/Android **não** oferece "Instalar app" nem
dispara `beforeinstallprompt` sem um service worker com `fetch` handler; e não havia nenhum convite/
instrução de instalação na UI. Escopo desta onda continua **sem offline** e **sem push** (o push vira
épico próprio).

## Decisão

> **Decidi: (1) adicionar um service worker MÍNIMO em `public/sw.js` — `install`→`skipWaiting`,
> `activate`→`clients.claim`, e um `fetch` handler PASSIVO (sem `respondWith`) — que NÃO cacheia nada,
> apenas satisfaz o critério de instalabilidade; (2) registrá-lo só em contexto seguro e com build real
> (`app-asset` != 'dev'), espelhando o guard do `versionWatcher`; (3) capturar o `beforeinstallprompt`
> no boot (antes do render) e oferecer um convite dispensável na home (ramo Android com botão de
> instalar; ramo iOS com instrução de "Adicionar à Tela de Início"). O `versionWatcher` permanece o
> ÚNICO mecanismo de atualização.**

## Por quê

- **SW passivo, sem cache:** o único requisito do Chrome que faltava era "existe um SW com `fetch`
  handler". Um handler que **não** chama `respondWith` deixa toda requisição seguir pela rede — zero
  cache, zero "cache preso". Assim o risco que o [[IDR-012]] temia é eliminado por construção, não por
  disciplina de versionamento.
- **`versionWatcher` intocado:** ele já resolve atualização (recarrega quando o `/version` diverge do
  boot, priorizando o retorno ao primeiro plano no mobile). Cachear assets no SW competiria com esse
  fluxo. Como o SW não guarda nada, os dois não colidem.
- **Captura no boot + convite na UI:** `beforeinstallprompt` pode chegar antes do React montar, então a
  captura mora num módulo iniciado em `app.jsx`; a UI (hook + componente) só decide se mostra. O iOS não
  expõe a API — lá o caminho honesto é coach-ar o A2HS manual.

## Alternativas consideradas

- **Continuar sem SW (manter o [[IDR-012]]):** descartada — sem SW o Android nunca promove a instalação;
  o app ficaria "instalável" só no iOS (via A2HS), perdendo o público Android.
- **SW com cache/offline (Workbox / vite-plugin-pwa):** descartada nesta onda — reintroduz invalidação de
  cache e passaria a competir/substituir o `versionWatcher`; offline não é requisito agora. Vira estória
  se o piloto pedir uso sem rede.
- **Web Push junto:** descartada deste escopo — exige tabela de subscriptions, VAPID, canal de
  notificação e UX de permissão; é o próximo épico. No iOS só funciona depois de instalado (16.4+).

## Consequências

### Para outros agentes
- **Existe um service worker agora** (`public/sw.js`), mas ele é PROPOSITADAMENTE sem cache. Qualquer
  necessidade de offline/precache é estória nova — **não** adicionar cache a este SW sem reabrir a decisão.
- Atualização do app continua sendo do `versionWatcher`; não introduzir cache de assets que o contorne.
- A captura do convite vive em `resources/js/pwa/installPrompt.js` (store externo) + `useInstallPrompt.js`;
  a UI em `Components/pwa/InstallPrompt.jsx` (só na home, dispensa persistida em `localStorage`).
- Registro guardado por `window.isSecureContext` e `app-asset != 'dev'` — por isso o SW **não** registra
  no E2E (http://laravel.test é inseguro) nem no `vite dev`.

### Para o projeto
- +`public/sw.js`, +`resources/js/pwa/*`, +`Components/pwa/InstallPrompt.jsx`; `manifest.json` ganhou
  `id`, `shortcuts` e ícones com `purpose` separado (`any` + `maskable`). Sem dependência nova.

### Trade-offs aceitos
- A **ativação real do SW** e o **ramo iOS** (A2HS) não são reproduzíveis no Chrome headless sobre
  `http://laravel.test` — os E2E cobrem a UX do convite via evento sintético; a instalação real valida-se
  no device/DevTools (Lighthouse/Application), como o `CascaMobileTest` faz com o comportamento do Safari.
- Sem offline e sem push nesta onda (aceito; cada um vira estória/épico próprio).

## Como verificar

- `tests/Feature/PwaAssetsTest.php` — `sw.js` presente, com os handlers e SEM cache/`respondWith`; manifest
  com `id`, `standalone`, `start_url`, ícones 192/512, `maskable` dedicado e atalho `/coletar`.
- `tests/Browser/PwaInstalavelTest.php` — convite só aparece após `beforeinstallprompt` (sintético) e a
  dispensa persiste entre visitas.
- Device/DevTools (manual): Application → Service Workers mostra `sw.js` ativo; Manifest "Installable";
  Lighthouse PWA verde; instalar em Android (botão/banner) e iOS (A2HS), abrir standalone e conferir que a
  **câmera/coleta** funciona e que o auto-update (`versionWatcher`) segue vivo.

## Tipo

- [x] **Convenção interna**: existe um SW, mas sem cache; atualização segue no `versionWatcher`.
- [x] **Workaround**: SW passivo apenas para destravar a instalabilidade do Chrome.

---

## Histórico

- 2026-07-05 — criada como `accepted` por programador (sessão claude-programador) durante STORY-038;
  complementa o [[IDR-012]] (que adiara o service worker).
