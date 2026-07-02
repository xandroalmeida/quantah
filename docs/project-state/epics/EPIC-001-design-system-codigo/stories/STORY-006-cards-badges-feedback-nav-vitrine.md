---
story_id: STORY-006
slug: cards-badges-feedback-nav-vitrine
title: Cards, badges, snackbar, empty-state, skeleton, nav + página vitrine
epic_id: EPIC-001
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: in_progress
owner_agent: claude-programador-story006
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: L
---

# STORY-006 — Cards, badges, feedback, nav + vitrine

> **Para o agente que vai executar:** leia por inteiro antes de começar. `requires_design: true` —
> Designer valida em paralelo (PDR-002). Estória `L`: se ficar grande demais, sinalize ao PO para
> quebrar (ex.: componentes numa estória, vitrine noutra).

## Contexto (por que esta estória existe)

Com botões (STORY-004) e inputs (STORY-005) prontos, faltam os componentes de superfície, status e
feedback — e a **vitrine**: a página de referência em homologação que prova o DS em código e vira
o entregável visível do épico.

- Épico: `epics/EPIC-001-design-system-codigo/epic.md`
- Documentos a ler ANTES:
  - `docs/especificacao/design-system.md`
  - `docs/project-state/design/system/components.md` (spec de cards/badges/snackbar/empty-state/skeleton/nav)
  - `docs/project-state/design/system/patterns.md` (ritmo de superfície)
  - `docs/skills/stacks/inertia-react/SKILL.md`

## O quê (objetivo desta estória)

Implementar **cards, badges, snackbar, empty-state, skeleton e nav** (via tokens), e construir a
**página vitrine (kitchen sink)** que exibe **todos** os componentes do DS — botões, inputs e os
desta estória — com seus estados, publicada em homologação.

## Por quê (valor para o usuário)

A vitrine é a prova viva de que o DS existe em código: o Programador passa a compor telas a partir
dela em vez de reinventar markup. É o entregável observável que fecha o épico.

## Critérios de aceite

- [ ] **CA-1:** Existem componentes reutilizáveis para card, badge, snackbar, empty-state, skeleton
      e nav, todos via tokens (sem valor cru); estados relevantes cobertos (ex.: badge por status;
      snackbar success/error/info; skeleton em loading).
- [ ] **CA-2:** Existe uma página **vitrine** em homologação que renderiza **todos** os componentes
      do DS (STORY-004 + STORY-005 + esta) e seus estados, organizada e navegável.
- [ ] **CA-3:** A11y mínima em todos: contraste AA, foco visível, alvo ≥48px onde aplicável;
      snackbar anuncia via `aria-live`.
- [ ] **CA-4:** Nenhum componente da vitrine usa valor cru fora dos tokens (guarda automatizada,
      como na STORY-002).
- [ ] **CA-5:** A vitrine é acessível por rota dedicada em homologação (smoke HTTP 200) e cada
      componente referencia sua spec do Designer.

## Fora de escopo

- Telas de produto (Coleta, Carteira).
- Componentes fora da lista mínima da Onda 1 (ex.: tabelas B2B do Quantah Intelligence).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. Cobertura ≥80% no código novo; E2E em
browser real cobrindo a vitrine em homologação; a11y mínima verificada; sem valor cru.

## Dependências

- **Bloqueada por:** STORY-004 (botões) e STORY-005 (inputs) — a vitrine os consome.
- **Bloqueia:** STORY-007 (validação do épico).
- **Pré-requisitos:** ambiente/homologação da fundação.

## Decisões já tomadas (não as reabra)

- PDR-001, DDR-001, IDR-001. Regras de ouro do DS. Não redefina token — falta/conflito → exceção de
  spec do Designer ou novo DDR: **pare e registre**.

## Liberdade técnica do agente

Você decide a estrutura da vitrine (organização, rota), a API dos componentes e o design dos testes.
Você **não** redefine tokens nem critérios de aceite.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; **Designer confirmou fidelidade** das specs.
- [ ] Testes + E2E da vitrine passando na cobertura exigida; a11y mínima verificada.
- [ ] Pipeline verde; vitrine publicada e verificada em homologação (smoke).
- [ ] IDR se houve decisão técnica relevante.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. `requires_design: true` → alinhe cedo. Se a
estória crescer além de `L`, sinalize ao PO para quebrar antes de continuar.

## Notas do agente (preenchido durante/após execução)

> **Owner:** `claude-programador-story006`. Trabalho direto na `main`. Ambiente Sail `quantah` no ar.
> Segue IDR-002/003 (contrato-em-fonte + Dusk) e as regras de ouro do DS.

### 2026-07-02 — Leitura inicial completa
- **Documentos lidos:** STORY-006 (inteira); `components.md` (§Cards: raio `xl` 24px, padding `xl`;
  content/feature-sage/feature-green/feature-dark; §Navegação: nav.bar canvas/ink sticky, nav.link
  `body-sm-strong` ativo=indicador primary, nav.bottom ≥48px, footer ink/canvas-soft `3xl xl`;
  §Feedback: badge.positive primary-pale/positive-deep pill, badge.negative negative-bg/branco pill,
  snackbar canvas/xl/`md lg` success/warning/danger/info ícone+texto `aria-live=polite`, empty-state
  ícone+`display-xs`+`body-md`+button.primary "sempre instrui o próximo passo", skeleton placeholder
  nunca spinner em tela vazia); `patterns.md` (surface-rhythm sage→branco; pattern.empty/error);
  `README`/`design-system.md` (regras de ouro); código existente (Button, inputs, tokens, Breeze).
- **Entendimento consolidado:** implementar os componentes de superfície/status/feedback/nav por token
  e montar a **vitrine kitchen-sink** em `/ds` que renderiza TODOS os componentes do DS (STORY-004 +
  005 + estes) com estados. Componentes:
  - **Card** — variantes `content` (canvas/ink), `feature-sage` (canvas-soft), `feature-green`
    (primary-pale), `feature-dark` (ink/primary) — raio `xl`, padding `xl`.
  - **Badge** — `positive` (primary-pale/positive-deep), `negative` (negative-bg/branco→canvas),
    `warning`, `info` — pill, `body-sm-strong`, ícone+texto (feedback nunca só cor).
  - **Snackbar** — success/warning/danger/info; canvas, raio `xl`, `md lg`, ícone+texto,
    `aria-live="polite"` (mudança dinâmica anunciada).
  - **EmptyState** — ícone + título `display-xs` + instrução `body-md` + `button.primary`; **sempre
    instrui o próximo passo**.
  - **Skeleton** — placeholder de carregamento (linha/bloco/círculo), `aria-hidden`, animate-pulse.
  - **Nav** — `NavBar` (sticky, canvas/ink) + `NavLink` (ativo=indicador primary) + `NavBottom`
    (mobile ≥48px) + `Footer` (ink/canvas-soft).
- **Fora de escopo:** telas de produto; componentes fora da lista mínima da Onda 1 (tabelas B2B, bands
  de marketing/hero).
- **Dúvidas:** nenhuma bloqueante. Não redefino token; falta/conflito → `blocked` + Designer.

### Decisões (registradas antes de codar)
1. **Namespacing:** componentes flat em `Components/` quando não colidem com o Breeze
   (Card/Badge/Snackbar/EmptyState/Skeleton) — seguindo o precedente do `Button`. **Nav em
   `Components/nav/`** porque `NavLink.jsx`/`ResponsiveNavLink.jsx` do Breeze colidem (débito pré-DS,
   usado por Auth — fora de escopo mexer).
2. **Vitrine = rota nova `/ds`** (kitchen sink), preservando `/ds/buttons` e `/ds/inputs` (hosts dos
   Dusk das STORY-004/005 — não quebrar). `/ds` dogfooda o próprio `NavBar`/`NavLink` como navegação
   interna por âncoras (prova a navegabilidade do CA-2).
3. **Estratégia de teste = IDR-002/003 mantida** (contrato-em-fonte Feature + Dusk). Componentes
   presentacionais; sem lógica de cliente que peça Vitest. Sem dep nova prevista.

### Plano (5 bullets)
1. **RED contrato:** `SurfaceComponentContractTest` (Feature) varre os novos `.jsx` — existência,
   tokens (card raio `xl`, badge `rounded-pill`, snackbar `aria-live`, empty-state com CTA + `display-xs`,
   footer ink/canvas-soft, nav.link indicador primary), zero valor cru.
2. **RED Dusk:** `KitchenSinkTest` na vitrine `/ds` — renderiza todos os grupos; snackbar `aria-live`;
   empty-state tem `button.primary`; skeleton presente; nav ativo com indicador; footer; contraste AA;
   foco visível; alvo ≥48px na nav.bottom; âncoras navegáveis. Categorias (a)(b)(c)(d).
3. **GREEN:** componentes + `Pages/DesignSystem/Showcase.jsx` (`/ds`) + rota.
4. Suíte completa verde, Dusk verde, Pint limpo, cobertura ≥80%.
5. Evidência (screenshots), revisão Designer, IDR se houver, `done` + `index.json`, homolog verificado,
   roteiro ao usuário.

### Mapeamento CA → testes (planejado)
- **CA-1** (componentes reutilizáveis por token, estados): `SurfaceComponentContractTest::*`
  (existência + tokens por componente) + Dusk render de cada grupo com estados.
- **CA-2** (vitrine `/ds` com TODOS os componentes, navegável): Dusk
  `KitchenSinkTest::test_showcase_renders_all_component_groups`, `::test_in_page_nav_anchors_navigate`.
- **CA-3** (a11y: contraste, foco, ≥48px, snackbar aria-live): Dusk `::test_snackbar_announces_via_aria_live`,
  `::test_bottom_nav_targets_are_at_least_48px`, `::test_focus_visible_on_nav_link`,
  `::test_dark_card_and_badges_pass_aa_contrast`.
- **CA-4** (zero valor cru — guarda automatizada): `SurfaceComponentContractTest::test_no_raw_hex`,
  `::test_no_arbitrary_or_neutral_color`.
- **CA-5** (rota dedicada em homolog, smoke 200; cada componente referencia spec): rota `/ds` +
  smoke do CI; `::test_showcase_route_responds` + comentários de spec nos componentes.

### Decisões tomadas
- (ver "Decisões (registradas antes de codar)")

### Descobertas
- 

### Bloqueios encontrados
- 

### IDRs criados
- 

### Cobertura final
- 

### Links de evidência
- 
