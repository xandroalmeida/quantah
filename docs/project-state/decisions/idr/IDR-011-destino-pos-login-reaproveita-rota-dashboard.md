---
idr_id: IDR-011
slug: destino-pos-login-reaproveita-rota-dashboard
title: Destino pós-login — home-hub reaproveitando a rota `dashboard` (sem introduzir `/inicio`)
status: accepted  # proposed | accepted | superseded
decided_at: 2026-07-05
decided_by: programador
owner_agent: claude-story029
related_story: STORY-029
related_adrs: [ADR-010]
related_idrs: []
related_ddrs: [DDR-007]
supersedes: null
superseded_by: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# IDR-011 — Destino pós-login: home-hub reaproveitando a rota `dashboard`

> Implementation Decision Record. Registra **como** a home-hub (STORY-029) passou a ser o destino
> pós-login, para que a **STORY-030** (navegação coesa) e outros agentes saibam por que a home vive
> hoje em `/dashboard` e o que ainda falta (renomear a URL, extrair a casca compartilhada).

## Contexto

O destino pós-login era a rota Breeze `dashboard` (`GET /dashboard`, guarda `['auth','verified']`,
STORY-022), que renderizava a página genérica de scaffolding (`Pages/Dashboard.jsx`, "Você está
logado!"). **Oito** pontos do fluxo de acesso já apontam para `route('dashboard')`:
`AuthenticatedSessionController`, `GoogleAuthController`, `RegisteredUserController`,
`EmailVerificationPromptController`, `VerifyEmailController` (×2), `EmailVerificationNotificationController`,
`ConfirmablePasswordController`. A STORY-029 pede que esse destino seja a **home-hub** (saldo + CTA de
coleta), sem página genérica, preservando a verificação de e-mail do EPIC-004.

## Decisão

> **Decidi reaproveitar a rota `dashboard`** — passei o `GET /dashboard` a resolver por
> `HomeController@index` (renderiza `Home/Hub`), mantendo o **nome** `dashboard`, o path `/dashboard` e a
> guarda `['auth','verified']`. Removi a página genérica `Pages/Dashboard.jsx`. **Não** introduzi uma rota
> `/inicio` nesta estória.

## Por quê

- **Um ponto de troca, zero churn nos fluxos de acesso:** como os 8 controllers e o fluxo de verificação
  de e-mail já redirecionam para `route('dashboard')`, trocar o que a rota **renderiza** move o destino de
  todos os caminhos de login de uma vez, sem tocar em 8 controllers nem em seus testes. A regra da
  simplicidade (menos superfície alterada = menos risco) e o foco no escopo da estória.
- **Preserva a verificação de e-mail (STORY-022):** manter a guarda `verified` na landing mantém o
  comportamento do EPIC-004 (não verificado → aviso de verificação) — a home-hub não afrouxa nada.
- **Sem página órfã:** a genérica foi **substituída** pela home-hub (intenção literal do épico:
  "o Coletador não cai mais numa página genérica"), em vez de coexistir como rota morta.

## Alternativas consideradas

- **Nova rota `/inicio` (auth+verified) + repontar os 8 controllers + testes + redirects de verificação:**
  mais mudança, sem ganho funcional nesta estória, e invade o escopo da STORY-030 (coesão/naming de
  navegação). **Adiada para a STORY-030**, que já mexe na casca de navegação.
- **Manter `/dashboard` genérica E adicionar `/inicio`:** deixa a página de scaffolding viva como rota
  órfã — contraria o objetivo do épico. Descartada.

## Consequências

### Para outros agentes (sobretudo STORY-030)
- A home-hub vive hoje em **`/dashboard`** (nome de rota `dashboard`). Ao extrair a **casca B2C
  compartilhada** e unificar a navegação, a STORY-030 **pode renomear** a URL para `/inicio` e apontar
  todo "Início" para ela — atualizando, no mesmo passe, os 8 redirects, os `waitForLocation('/dashboard')`
  dos testes Dusk (`AcessoGoogleTest`, `ConfirmacaoEmailTest`) e os rótulos de nav.
- `Pages/Dashboard.jsx` foi **removida**. O `Layouts/AuthenticatedLayout.jsx` (Breeze) ainda tem um link
  "Dashboard" → `route('dashboard')` usado **só pela tela de Perfil** — a STORY-030 deve reconciliar esse
  chrome ao unificar a navegação da área logada.
- Os **rótulos de nav** ('Início/Cupons/Carteira/Perfil') seguem **hardcoded** na `Home/Hub` (como nas
  telas irmãs Carteira/Privacidade/Showcase); a STORY-030 centraliza e passa esses rótulos pelo `t()`
  (IDR-010) ao criar a casca compartilhada.

### Para o projeto
- `verified` continua **só na landing** (`/dashboard`); o resto da área B2C (`/coletar`, `/carteira`, …)
  segue `auth`-only — comportamento pré-existente, não alterado por esta estória.

### Trade-offs aceitos
- A URL `/dashboard` é temporariamente um nome pouco "de marca" para a home do Coletador; aceito por ser
  interno (o usuário vê "Início" na nav, não a URL) e por deixar o rename para quem é dono da navegação
  (STORY-030).

## Como verificar

- Todos os caminhos de acesso (e-mail/senha, Google, verificação de e-mail) caem na home-hub: coberto por
  `HomeHubTest` (Feature) + `AcessoGoogleTest`/`ConfirmacaoEmailTest` (Dusk asserta "Seu saldo").
- Se a STORY-030 renomear a URL, este IDR deve ser atualizado (ou superseded) e os 8 redirects + testes
  Dusk movidos em conjunto.

## Tipo

- [x] **Convenção interna**: o destino pós-login é a home-hub via a rota `dashboard` (até a STORY-030).
- [x] **Refatoração estrutural**: substituição da página genérica de scaffolding pela home-hub.

---

## Histórico

- 2026-07-05 — criada como `accepted` por programador (sessão claude-story029) durante a STORY-029.
- 2026-07-05 — **STORY-030 renomeou** a rota e a URL de `dashboard`/`/dashboard` para **`inicio`/`/inicio`**
  ao criar a casca de navegação (DDR-007), atualizando os 8 redirects de acesso e os
  `waitForLocation('/dashboard')` dos Dusk num só passe. O destino pós-login segue o mesmo (a home-hub);
  só o nome/URL ficou "de marca". A decisão original (reaproveitar a rota em vez de introduzir uma paralela
  na STORY-029) permanece válida historicamente.
