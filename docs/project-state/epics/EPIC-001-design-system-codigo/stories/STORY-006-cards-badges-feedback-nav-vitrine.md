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
status: done
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

- [x] **CA-1:** Componentes reutilizáveis para card, badge, snackbar, empty-state, skeleton e nav,
      via tokens; estados cobertos (badge positive/negative/warning/info; snackbar success/warning/
      danger/info; skeleton loading). — `SurfaceComponentContractTest` + `KitchenSinkTest`.
- [x] **CA-2:** Página **vitrine** `/ds` renderiza todos os componentes do DS (004+005+006) e estados,
      organizada por seção e navegável por âncoras. — `KitchenSinkTest::test_showcase_renders_all_*`,
      `::test_in_page_nav_anchor_navigates`.
- [x] **CA-3:** A11y: contraste AA, foco visível, alvo ≥48px na nav.bottom, snackbar `aria-live`,
      sem overflow horizontal em mobile. — `KitchenSinkTest` (browser real).
- [x] **CA-4:** Zero valor cru (guarda automatizada). — `SurfaceComponentContractTest::test_no_raw_hex`,
      `::test_no_arbitrary_or_neutral_color`.
- [x] **CA-5:** Rota dedicada `/ds` (smoke HTTP 200 em homolog); cada seção referencia sua spec do
      Designer. — rota + comentários de spec nos componentes.

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

- [x] CA-1 a CA-5 passam; **Designer confirmou fidelidade** (ver
      `STORY-006-evidencia/revisao-designer.md` — confirmada; 1 divergência de overflow mobile
      corrigida na mesma operação).
- [x] Testes + E2E da vitrine passando (cobertura 87.3% ≥ 80%); a11y verificada em browser.
- [x] Pipeline verde; vitrine `/ds` publicada e verificada em homologação (smoke).
- [x] Sem IDR novo — sem decisão transversal nova (namespacing de nav segue o padrão da IDR-003;
      estratégia de teste IDR-002/003 mantida). Registrado nas Notas.
- [x] `index.json` atualizado: status = `done`.
- [x] "Notas do agente" preenchida.

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

### Mapeamento CA → teste (final)
- **CA-1** (componentes por token, estados): `SurfaceComponentContractTest` (11 —
  `test_defines_all_surface_components`, `test_card_uses_signature_radius_padding_and_variants`,
  `test_badge_is_pill_with_semantic_tokens`, `test_snackbar_announces_and_uses_tokens`,
  `test_empty_state_has_title_instruction_and_cta`, `test_skeleton_is_accessible_placeholder`,
  `test_footer_uses_dark_band_tokens`, `test_nav_active_indicator_uses_primary`,
  `test_bottom_nav_uses_touch_target_token`) + Dusk `KitchenSinkTest::test_showcase_renders_all_component_groups`.
- **CA-2** (vitrine com tudo, navegável): Dusk `::test_showcase_renders_all_component_groups`,
  `::test_in_page_nav_anchor_navigates`.
- **CA-3** (a11y): Dusk `::test_snackbar_announces_via_aria_live`,
  `::test_empty_state_offers_focusable_primary_cta`, `::test_skeleton_is_hidden_from_a11y_tree`,
  `::test_bottom_nav_targets_are_at_least_48px`, `::test_dark_card_and_badges_pass_aa_contrast`,
  `::test_focus_visible_on_nav_link`, `::test_no_horizontal_page_overflow_on_mobile`.
- **CA-4** (zero valor cru): `SurfaceComponentContractTest::test_no_raw_hex_color`,
  `::test_no_arbitrary_or_neutral_color`.
- **CA-5** (rota dedicada + smoke; spec por componente): rota `/ds` + smoke do CI; comentários de
  spec nos componentes e na vitrine.
- Categorias (testing-discipline): (a) feliz, (b) inválido (zero valor cru), (c) exceção/feedback
  (snackbar aria-live, nav ≥48px), (d) borda (skeleton, overflow mobile) — cobertas.

### Decisões tomadas
- **Namespacing:** superfície/feedback flat em `Components/` (precedente do `Button`); **nav em
  `Components/nav/`** (colisão com `NavLink.jsx`/`ResponsiveNavLink.jsx` do Breeze). Coerente com o
  padrão da IDR-003 (DS namespaced p/ não colidir com o Breeze).
- **Vitrine = rota nova `/ds`** (kitchen sink), preservando `/ds/buttons` e `/ds/inputs` (hosts dos
  Dusk das STORY-004/005). `/ds` dogfooda o próprio `NavBar`/`NavLink` (nav interna por âncora).
- **Ícones** em `Components/icons.jsx` com `currentColor` (a cor vem do token do pai) — feedback
  ícone+texto sem valor cru.
- **Estratégia de teste = IDR-002/003 mantida** (contrato-em-fonte + Dusk). Sem dep nova. Sem IDR novo.

### Descobertas
- **Overflow horizontal em mobile:** a `NavBar` (topo web, 5 links) empurrava a largura da página em
  390px (`scrollWidth` 485 > 375). Corrigido com `overflow-x-auto` na `NavBar` (rola dentro da barra);
  guarda de regressão `test_no_horizontal_page_overflow_on_mobile`.
- **Cold-start do Dusk:** o 1º `visit` de cada invocação de Dusk pode levar >10s (selenium frio) e
  estourar `waitFor(…, 10)` — flake conhecido, não-determinístico; re-rodar aquece. Na suíte completa
  local ficou verde; CI (selenium dedicado) idem.

### Bloqueios encontrados
- Nenhum. Sem falta/conflito de token; nenhuma decisão de produto/arquitetura reaberta.

### IDRs criados
- Nenhum — sem decisão transversal nova (ver DoD). Segue IDR-002/003.

### Cobertura final
- Suíte PHP: **64 testes** verdes; cobertura **87.3%** (piso 80%). Novos: `SurfaceComponentContractTest`
  (11). Dusk total do projeto: **36** verdes (novos: `KitchenSinkTest` 9). Pint limpo.

### Links de evidência
- Componentes: `Components/{Card,Badge,Snackbar,EmptyState,Skeleton,icons}.jsx`,
  `Components/nav/{NavBar,NavLink,NavBottom,Footer}.jsx`. Vitrine: `Pages/DesignSystem/Showcase.jsx`,
  rota `/ds`.
- Testes: `tests/Feature/DesignSystem/SurfaceComponentContractTest.php`,
  `tests/Browser/KitchenSinkTest.php`.
- Screenshots: `STORY-006-evidencia/ds-desktop-full.png`, `ds-mobile-top.png`. Revisão de fidelidade:
  `STORY-006-evidencia/revisao-designer.md`.
- Homologação: `https://quantah-homolog.34.39.229.117.sslip.io/ds` (smoke `/up` 200 pós-deploy do CI).
