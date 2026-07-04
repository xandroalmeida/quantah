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
status: ready
owner_agent: null
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
- <data> — <ADR-XXX: decisão>

### Descobertas
- <data> — <gotcha / item para o PO saber>

### ADRs criados
- ADR-XXX — <título> — `decisions/adr/ADR-XXX-<slug>.md`
