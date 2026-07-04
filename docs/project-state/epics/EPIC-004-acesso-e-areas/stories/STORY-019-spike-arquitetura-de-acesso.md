---
story_id: STORY-019
slug: spike-arquitetura-de-acesso
title: Spike de arquitetura — acesso (OAuth Google + contas), segmentação de áreas e i18n
epic_id: EPIC-004
sprint_id: null
type: spike
target_role: arquiteto
requires_design: false
design_screen_id: null
status: in_progress
owner_agent: arquiteto
created_at: 2026-07-04
updated_at: 2026-07-04
estimated_session_size: L
---

# STORY-019 — Spike de arquitetura de acesso

> **Para o agente que vai executar (arquiteto):** este é um spike de decisão. O produto são **ADRs** e
> um esboço de contorno para as estórias de implementação — **não** código de produção. Se algo for
> ambíguo, registre em "Notas do agente" e pause.

## Contexto (por que esta estória existe)

O EPIC-004 dá identidade e acesso ao Coletador e segmenta os três públicos. Hoje o app usa o
scaffolding do Laravel Breeze (e-mail/senha) com o logo do Laravel e uma página genérica `/dashboard`
pós-login; **não há login social** nem separação de áreas. Antes de implementar, precisamos decidir a
arquitetura de: (a) login social Google, (b) modelo de contas e verificação, (c) segmentação das 3
áreas (B2C / B2B / Backoffice) e suas guardas, e (d) o mecanismo de localização (pt-BR).

- Épico: `epics/EPIC-004-acesso-e-areas/epic.md`
- Ler antes: `docs/visao.md` §4 (personas), §11.3 (marca app + B2B); PDR-003
  (`decisions/pdr/PDR-003-escopo-onda-2-de-poc-a-produto.md`); ADR-009 (RBAC) e ADR-000 (stack).

## Objetivo do spike

Produzir os ADRs que destravam a implementação:

- **ADR de acesso** (ex.: ADR-010): abordagem de **OAuth Google** (biblioteca/fluxo), **modelo de
  contas** (como coexistem Google e e-mail/senha para o mesmo Coletador, vínculo por e-mail,
  verificação de e-mail), e a **segmentação das 3 áreas** com guardas (papéis/rotas/middleware,
  reusando o RBAC do ADR-009) — incluindo a entrada **não anunciada** do Backoffice e a área B2B
  **reservada** (sem login nesta onda).
- **ADR de i18n** (ex.: ADR-011): mecanismo de localização **pt-BR** (onde vivem as strings, formatação
  de moeda/data/fuso `America/Sao_Paulo`), de modo que nenhum texto de interface fique hardcoded.

## Critérios de aceite (alto nível — refinar na execução)

- [ ] **CA-1:** ADR de acesso `accepted` cobrindo OAuth Google (fluxo + biblioteca), modelo de contas
      (Google ↔ e-mail/senha, vínculo por e-mail, verificação) e riscos (conflito de conta, e-mail não
      verificado, revogação).
- [ ] **CA-2:** O mesmo ADR decide a **segmentação das 3 áreas** e as guardas: como um usuário de uma
      área é barrado de outra, como o Backoffice tem entrada própria sem CTA público, e como a área B2B
      fica reservada sem login. Reusa/estende ADR-009 (RBAC).
- [ ] **CA-3:** ADR de i18n `accepted` decidindo o mecanismo de localização pt-BR e formatos brasileiros.
- [ ] **CA-4:** Contorno explícito para as estórias de implementação (020–023): o que cada uma assume
      como decidido; modelo de dados/rotas mínimo esboçado o suficiente para destravá-las.

## Fora de escopo

- Implementar login, telas, guardas ou i18n (vai para STORY-020..023 conforme os ADRs).
- Escolher outros provedores sociais além do Google.

## Padrões de qualidade exigidos

Spike de decisão: **sem exigência de E2E/cobertura**. O produto são ADRs indexados e um contorno. Segue
`docs/skills/po/references/quality-standards.md` no que se aplica a decisões arquiteturais.

## Dependências

- **Bloqueada por:** — (primeira estória do épico).
- **Bloqueia:** STORY-020, STORY-021, STORY-022, STORY-023.
- **Pré-requisitos de ambiente:** nenhum (decisão).

## Decisões já tomadas (não as reabra)

- PDR-003 — login B2C = Google **+** e-mail/senha; B2B = captação de lead (sem login); 3 áreas.
- ADR-009 — RBAC do backoffice (base das guardas). ADR-000 — stack (Laravel + Inertia/React + Postgres).

## Definição de Pronto (DoD)

- [ ] ADRs (`accepted` após aprovação do Alexandro) criados e **indexados no `index.json`**.
- [ ] Contorno das estórias de implementação registrado (nas Notas do agente ou no ADR).
- [ ] `index.json` = `done`; frontmatter e "Notas do agente" preenchidos.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. ADRs vão em `decisions/adr/` e são indexados. Se
uma decisão de produto (não arquitetural) faltar, **pare e registre** — não decida como arquiteto.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 2026-07-04 — **ADR-010** (proposed): OAuth Google via **Laravel Socialite** (fluxo Authorization Code por
  redirect, **sem** guardar tokens); **modelo de contas por colunas** no `users` (`google_id` unique nullable,
  `avatar` nullable, `password` nullable), com **vínculo por e-mail verificado** e regra fail-secure; **3 áreas**
  com **host único + RBAC do ADR-009** — B2C=`auth`, Backoffice=`can:operar-saques` sob `/backoffice` sem CTA,
  B2B=namespace `/intelligence` **reservado** sem login. Segregação por subdomínio do Backoffice fica **`deferred`**
  com gatilho (go-live / Backoffice ≥2 domínios / B2B autenticado / incidente).
- 2026-07-04 — **ADR-011** (proposed): i18n via **localização nativa do Laravel** (`lang/`) como **fonte única**,
  exposta ao React por **prop compartilhada do Inertia + helper `t()` fino**; `app.locale=pt_BR`,
  **`laravel-lang/lang`** para matar o inglês do Breeze; formatos BR (moeda `R$`, data `dd/mm/aaaa`), fuso de
  **exibição** `America/Sao_Paulo` mantendo persistência **UTC/ISO 8601** (`app.timezone` continua `UTC`).
  Monolíngue (sem seletor); 2º locale seria ADR aditiva.

> **Status:** ambos os ADRs em `proposed`, **aguardando aprovação de Alexandro**. A estória só vai a `done`
> quando os ADRs forem `accepted` (DoD). Enquanto isso, permanece `in_progress`.

### Contorno para as estórias de implementação (CA-4)

O que cada estória pode assumir como **decidido** e o modelo mínimo esboçado:

- **STORY-020 (fundação i18n + varredura pt-BR)** — materializa **ADR-011**: configura `app.locale=pt_BR`/
  `faker_locale`; instala `laravel-lang/lang`; cria `HandleInertiaRequests::share(['translations' => ...])` +
  helper `t()` no React; define helpers de moeda/data (BRL, `America/Sao_Paulo`, mantendo UTC/ISO na
  persistência). **Varre para pt-BR** as superfícies existentes: login/Breeze, vitrine `/ds`, carteira,
  backoffice de saques. É a base das telas novas (nasce cedo, como o épico pede). **Não** mexe em auth/áreas.

- **STORY-021 (login/cadastro de marca — e-mail/senha, `requires_design`)** — assume **ADR-010** (sessão `web`
  Breeze mantida; `users` com `password` nullable) e **ADR-011** (strings em `lang/`). Substitui o logo do
  Laravel pelo padrão visual do DS, em pt-BR. **Hospeda o botão "Entrar com Google"** (a implementação do fluxo
  é a 022). Modelo mínimo: rotas `guest` de login/register/forgot-password já existem (Breeze) — reskin + i18n.

- **STORY-022 (login com Google)** — implementa o **eixo OAuth do ADR-010**: config `services.google`
  (`.env`: `GOOGLE_CLIENT_ID/SECRET/REDIRECT`); rotas `GET /auth/google/redirect` e `GET /auth/google/callback`
  (grupo `guest`); Socialite driver `google`; **migração**: `users.google_id` (unique, nullable),
  `users.avatar` (nullable), `users.password` → nullable. Regra de contas do ADR-010 (criar / login / vincular /
  recusar sem `verified_email`). **Sem** persistir tokens. Audit log de login/vínculo. E2E com Google **simulado**.

- **STORY-023 (segmentação das 3 áreas + guardas)** — implementa o **eixo áreas do ADR-010**: organiza rotas em
  **grupos por área** (B2C `auth`; Backoffice `auth`+`can:operar-saques` sob `/backoffice`, já vigente, sem CTA
  público; B2B namespace `/intelligence` **reservado** → resolve para landing pública, sem rota autenticada);
  redirect pós-login por papel; **fail-secure** (rota nova nasce dentro de um grupo). E2E de **barreira de área**
  (Coletador → 403 no `/backoffice`). Papel `analista_b2b` **não** é criado agora (só quando a área B2B ativar).

**Modelo de dados mínimo (ADR-010):** `users` += `google_id` (unique, nullable), `avatar` (nullable),
`password` (nullable). Reusa `roles`/`role_user`/Gate `operar-saques` do ADR-009 — **nada de tabela nova**.

### Descobertas
- 2026-07-04 — O app já roda **Laravel Breeze** (sessão server-side, e-mail/senha) e o Backoffice já vive atrás
  do Gate `operar-saques` sob `/backoffice` (ADR-009) — a segmentação **estende** o que existe, não recria.
- 2026-07-04 — `security-architecture.md` recomenda segregar a superfície admin por **subdomínio**; optamos por
  path-based agora e registramos o subdomínio como `deferred` (não ignorar a recomendação nem pagar por ela
  cedo demais). **Item para o PO/Alexandro:** ciente do trade-off aceito no ADR-010.
- 2026-07-04 — Persistência de data/hora **permanece UTC/ISO 8601**; a localização pt-BR é de **exibição**.
  Cuidado a passar ao Programador na STORY-020: **não** trocar `app.timezone` para `America/Sao_Paulo`.

### ADRs criados
- **ADR-010** — Acesso: login Google (OAuth) + modelo de contas e segmentação das 3 áreas —
  `decisions/adr/ADR-010-acesso-oauth-google-contas-e-areas.md` (`proposed`).
- **ADR-011** — i18n: mecanismo de localização pt-BR (strings e formatos brasileiros) —
  `decisions/adr/ADR-011-i18n-mecanismo-localizacao-ptbr.md` (`proposed`).
