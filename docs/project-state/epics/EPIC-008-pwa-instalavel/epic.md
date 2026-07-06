---
epic_id: EPIC-008
slug: pwa-instalavel
title: PWA instalável — service worker mínimo e convite de instalação (Opção A)
wave: WAVE-2026-02
status: done
created_at: 2026-07-05
updated_at: 2026-07-05
---

# EPIC-008 — PWA instalável (Opção A)

## Objetivo

Fechar o último passo para a área do Coletador ser um **app instalável de verdade**: o Chrome/Android
passar a oferecer "Instalar app" e o usuário ter um convite/instrução clara para instalar — **sem push**
(épico próprio) e **sem quebrar** a estratégia deliberada de "sem cache" do `versionWatcher`.

Contexto de origem: análise das opções para "virar app mobile" concluiu que a área B2C **já é** uma PWA
mobile-first (manifest standalone, metatags iOS, casca app-like, câmera/QR, auto-update). Faltavam apenas
o **service worker** e a **captura do `beforeinstallprompt`**. O PO escolheu a **Opção A** (só instalável)
com **service worker mínimo, sem cache**.

## Escopo

- Service worker MÍNIMO (`public/sw.js`), passivo, sem cache — satisfaz a instalabilidade do Chrome.
- Registro guardado (contexto seguro + build real), sem interferir no HMR nem no `versionWatcher`.
- Captura do `beforeinstallprompt` e convite dispensável na home (ramo Android; ramo iOS com A2HS).
- Polimento do `manifest.json` (`id`, `shortcuts`, ícones `any`/`maskable` separados).
- i18n pt-BR das strings novas.

## Fora de escopo (wishlist / próximos épicos)

- **Web Push** (cashback creditado, saque pago/rejeitado) — exige subscriptions/VAPID/canal/UX de permissão.
- **Offline/precache** — manteria o conflito com o `versionWatcher`; só se o piloto exigir uso sem rede.
- **Ícone maskable dedicado** com safe-zone real (os atuais são simples) — follow-up de designer.
- Screenshots no manifest (diálogo de instalação mais rico).

## Estórias

- **STORY-038** — PWA instalável (SW mínimo + convite de instalação). `done`.

## Decisões registradas

- **IDR-016** — Service worker mínimo (sem cache) para PWA instalável; complementa o IDR-012 (que adiara
  o SW). O `versionWatcher` segue como único mecanismo de atualização.

## Estado / verificação

Automação **verde**: 340/340 unit+feature (inclui `PwaAssetsTest`) e Dusk de Home/Casca/PWA
(`HomeHubTest`, `CascaMobileTest`, `PwaInstalavelTest`) 11/11. Assets servidos e conferidos
(`/sw.js` 200 JS, `/manifest.json` 200 JSON com `id`/`shortcuts`/`purpose`), e o HTML servido carrega
`app-asset` real (o registro do SW ativa em contexto seguro).

**Gate manual restante antes de fechar em homologação (device/DevTools):** Lighthouse "Installable" verde;
SW ativo em Application → Service Workers; instalar em **Android** (botão/banner) e **iOS** (A2HS), abrir
standalone e confirmar **câmera/coleta** sem regressão + auto-update (`versionWatcher`) ainda funcionando.
Motivo de ser manual: `beforeinstallprompt`/registro real exigem HTTPS e não são reproduzíveis no Chrome
headless sobre `http://laravel.test` (ver IDR-016 e o roteiro na STORY-038).
