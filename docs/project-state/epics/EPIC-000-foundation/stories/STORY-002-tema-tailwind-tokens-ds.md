---
story_id: STORY-002
slug: tema-tailwind-tokens-ds
title: Tema Tailwind a partir dos tokens do Design System (com Inter)
epic_id: EPIC-000
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: done
owner_agent: claude-programador-story002
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-002 — Tema Tailwind a partir dos tokens do DS

> **Para o agente que vai executar:** leia por inteiro antes de começar. Esta estória tem
> `requires_design: true` — o Designer valida a fidelidade do mapeamento em paralelo (PDR-002).

## Contexto (por que esta estória existe)

O design system canônico está documentado em tokens (cor, tipografia, spacing, raio, elevação,
motion, breakpoints), mas ainda não vive no código. Para que a vitrine (EPIC-001) e todas as
telas nasçam consistentes, os tokens precisam virar a configuração de tema do Tailwind, e a
fonte precisa ser a Inter (DDR-001). Esta estória aplica a fundação visual no hello-world.

- Épico: `epics/EPIC-000-foundation/epic.md`
- Documentos a ler ANTES:
  - `docs/especificacao/design-system.md` (referência canônica)
  - `docs/project-state/design/system/tokens.md` (valores exatos)
  - `docs/project-state/decisions/ddr/DDR-001-substituicao-fonte-display.md` (Inter)
  - `docs/skills/stacks/inertia-react/SKILL.md` (§ "Estilo / Design System na prática")

## O quê (objetivo desta estória)

Mapear os tokens do DS para `tailwind.config.js` (+ CSS variables quando fizer sentido), carregar
a fonte **Inter** (400/600/900), e aplicar a paleta/tipografia na página hello-world.

## Por quê (valor para o usuário)

A fundação visual consistente é o que faz o produto parecer Quantah e evita dívida de design.
Sem os tokens no tema, cada tela reinventaria valores crus — proibido pelo DS.

## Critérios de aceite

- [x] **CA-1:** Os tokens de cor do DS (primary, on-primary, canvas, canvas-soft, ink, body,
      mute, semânticas) existem como utilitários Tailwind e **nenhum valor cru** de cor aparece
      no JSX da hello-world.
- [x] **CA-2:** A escala tipográfica do DS está mapeada e a fonte **Inter** (400/600/900) é
      carregada; o display usa peso 900 (DDR-001).
- [x] **CA-3:** Spacing, raio (com `xl` = 24px), elevação e breakpoints do DS estão no tema.
- [x] **CA-4:** A hello-world exibe: um título display (Inter 900), um parágrafo em corpo, e um
      `button.primary` (verde `#9fe870`, texto `on-primary`, raio 24px) — todos via tokens.
- [x] **CA-5:** Contraste do botão passa AA (texto `on-primary` sobre `primary`), foco visível.

## Fora de escopo

- Construir a biblioteca de componentes React do DS (é EPIC-001) — aqui só o tema + demonstração
  mínima no hello-world.
- Ilustração autoral de marca (DDR-002).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. E2E/checagem visual do hello-world com
tema aplicado; a11y mínima (contraste AA, foco) verificada; sem valor cru fora dos tokens.

## Dependências

- **Bloqueada por:** STORY-001 (hello-world + pipeline de pé).
- **Bloqueia:** EPIC-001 (a biblioteca de componentes consome este tema).
- **Pré-requisitos:** ambiente e homologação da STORY-001.

## Decisões já tomadas (não as reabra)

- PDR-001 (adoção do DS) e DDR-001 (Inter) e DDR-002 (diferenciação de marca).
- Regras de ouro do DS (verde só como CTA; raio 24px; sem 2º accent) — ver `design-system.md`.

## Liberdade técnica do agente

Você decide a organização do `tailwind.config.js`, uso de CSS variables e como carregar a fonte.
Você **não** redefine tokens — se algo do DS parecer faltar ou conflitar, é exceção de spec do
Designer ou novo DDR; **pare e registre**, não invente token.

## Definição de Pronto (DoD)

- [x] CA-1 a CA-5 passam; **Designer confirmou fidelidade do mapeamento** (ver
      `STORY-002-evidencia/revisao-designer.md` — 100% fiel, sem divergências).
- [x] Testes/checagens passando; a11y mínima verificada (contraste AA + foco por teclado em Dusk).
- [x] Pipeline verde; hello-world com tema em homologação (evidência: smoke em `/` e `/up`).
- [x] IDR se houve decisão técnica relevante — **IDR-001** (estratégia de tokens no tema / CSS vars).
- [x] `index.json` atualizado (status acompanhou in_progress → in_review → done).
- [x] Notas do agente preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. `requires_design: true` → alinhe cedo com
o Designer antes de cristalizar. Falta de token/conflito → `blocked` + escalar ao Designer.

## Notas do agente (preenchido durante/após execução)

**Owner:** `claude-programador-story002` · Branch: `story-002-tema-tailwind-tokens`

### Documentos lidos
- STORY-002 (inteira), `design/system/tokens.md` (valores canônicos), `DDR-001` (Inter),
  `stacks/inertia-react/SKILL.md` (§ Estilo/DS na prática), `design/system/components.md`
  (`button.primary`), `patterns.md` (`surface-rhythm`), `programador/SKILL.md`, `_project.md`.

### Entendimento consolidado (minhas palavras)
- Transformar os tokens do DS na configuração de tema do Tailwind (cores, tipografia com Inter,
  spacing, raio com `xl`=24px, elevação, motion, breakpoints) e demonstrar no hello-world com um
  título display (Inter 900), um parágrafo de corpo e um `button.primary` — **tudo via tokens,
  zero valor cru**. Contraste AA (`on-primary` sobre `primary`) e foco visível.
- Fora de escopo: biblioteca de componentes React do DS (EPIC-001) e ilustração de marca (DDR-002).
- Não redefino token; se faltar/conflitar → `blocked` + escalar Designer (`requires_design: true`).

### Plano (3–5 bullets)
1. Mapear tokens → `tailwind.config.js` (`theme.extend`) + CSS vars/`font-feature-settings` em
   `app.css`; carregar Inter 400/600/900 em `app.blade.php` (troca de Figtree por Inter, DDR-001).
2. TDD: escrever testes vermelhos antes do código (guardas de config + Dusk em browser real).
3. Reconstruir `Hello.jsx` só com utilitários de token (canvas sage, display 900, corpo,
   `button.primary`).
4. Verde: unit+feature + Dusk; suíte completa; Pint. Evidência de E2E por cenário.

### Mapeamento CA → testes (planejado)
- **CA-1** (tokens de cor existem; sem valor cru no JSX) →
  `Unit/DesignSystem/NoRawColorInHelloTest` (varre `Hello.jsx`: sem hex, sem `gray-`, `bg-black`,
  `[#...]`) + `Unit/DesignSystem/TailwindThemeTokensTest` (config expõe as cores do DS).
- **CA-2** (escala tipográfica + Inter 400/600/900; display 900) →
  Dusk `ThemeTest::test_display_title_uses_inter_900` (font-family contém Inter, weight 900) +
  guarda: `app.blade.php` carrega Inter 400,600,900.
- **CA-3** (spacing, raio `xl`=24px, elevação, breakpoints no tema) →
  `Unit/DesignSystem/TailwindThemeTokensTest` (borderRadius.xl=24px, spacing, screens md/lg,
  boxShadow) + provado em browser pelo raio real do botão (CA-4).
- **CA-4** (título display, parágrafo, `button.primary` verde/on-primary/raio 24px via tokens) →
  Dusk `ThemeTest::test_primary_button_renders_with_brand_tokens` (bg rgb(159,232,112),
  color rgb(14,15,12), border-radius 24px) + `test_hello_shows_display_title_and_body`.
- **CA-5** (contraste AA on-primary/primary; foco visível) →
  Dusk `ThemeTest::test_primary_button_contrast_passes_AA` (calcula ratio ≥ 4.5 dos rgb reais) +
  `test_primary_button_focus_is_visible` (foco por teclado muda outline/box-shadow).

### Dúvidas
- Nenhuma bloqueante. `requires_design: true`: implemento o mapeamento 1:1 dos valores canônicos
  de `tokens.md`; fidelidade fica pendente de confirmação do Designer (registrado no PR).

### Decisões tomadas
- **IDR-001**: tokens vivem no `theme.extend` do Tailwind; camada de CSS variables adiada até
  haver runtime theming real (dark mode/multi-marca). Fonte Inter via bunny.net (como o starter).
- Raio/spacing/breakpoints do DS mapeados; `borderRadius.xl=24px` é a assinatura. Breakpoints
  postos em `extend.screens` (md/lg) para não remover `sm/xl/2xl` das telas de auth do starter.
- `button.primary` demonstrado com `focus-visible:ring` (indicador só no teclado, a11y correta).

### Mapeamento CA → teste (final)
- **CA-1** (tokens de cor; sem valor cru no JSX):
  `NoRawColorInHelloTest::test_hello_has_no_raw_hex_color`,
  `::test_hello_has_no_tailwind_arbitrary_color`,
  `::test_hello_does_not_use_raw_neutral_palette`;
  `TailwindThemeTokensTest::test_theme_maps_design_system_colors`.
- **CA-2** (escala tipográfica + Inter 400/600/900; display 900):
  `ThemeTest::test_display_title_uses_inter_900` (browser real),
  `TailwindThemeTokensTest::test_theme_sets_inter_as_display_and_sans`,
  `::test_document_loads_inter_weights`.
- **CA-3** (spacing, raio xl=24px, elevação, breakpoints):
  `TailwindThemeTokensTest::test_theme_defines_signature_radius_xl_24px`,
  `::test_theme_defines_design_system_breakpoints`,
  `::test_theme_defines_spacing_and_elevation`; raio provado em browser por CA-4.
- **CA-4** (título display, corpo, button.primary via tokens):
  `ThemeTest::test_hello_shows_display_title_and_body`,
  `::test_primary_button_renders_with_brand_tokens` (bg rgb(159,232,112), texto rgb(14,15,12),
  raio 24px — computados no browser real).
- **CA-5** (contraste AA + foco visível):
  `ThemeTest::test_primary_button_contrast_passes_AA` (ratio real ≥ 4.5),
  `::test_primary_button_focus_is_visible` (Tab por teclado → CTA com ring visível).

### Cenários E2E em browser real (Dusk) — desfechos cobertos
- Este é um hello-world de demonstração (sem ramificações/erros de usuário): o fluxo é "abrir a
  página e ver o tema aplicado". Coberto ponta a ponta pelos 5 cenários do `ThemeTest` + os 2 do
  `HelloWorldTest` (render + hidratação). Não há caminhos alternativos/erro mapeados nesta estória.

### Descobertas
- Flake de cold-start: o **primeiro** `visit` de um run Dusk podia estourar o `waitFor` de 5s
  (warm-up do Chromium + primeiro serve de assets). Endurecido para 10s (ThemeTest + HelloWorldTest).
  Full Dusk rodado 2× seguidas: 7/7 verde e determinístico.

### Bloqueios encontrados
- Nenhum.

### IDRs criados
- `IDR-001` — Tokens no `theme.extend` do Tailwind; CSS vars adiadas (accepted).

### Cobertura final
- Suíte unit+feature: **38 testes, 138 assertions, verde**; cobertura PHP **87.3%** (≥ 80% do gate).
  A estória não adiciona código PHP de produção (só tema/JSX/config/blade + testes), então a
  cobertura de núcleo permanece intacta.
- Dusk (browser real): **7 testes verde** (5 ThemeTest + 2 HelloWorldTest), rodado 2× sem flake.
- Pint: limpo.

### Pendências de gate (não são do Programador)
- `requires_design: true`: a **fidelidade do mapeamento** dos tokens foi feita 1:1 a partir dos
  valores canônicos de `tokens.md`, mas a **confirmação do Designer** fica pendente (validação em
  paralelo, PDR-002). CI verde e smoke em homologação seguem o merge do PR.

### Links de evidência
- Branch: `story-002-tema-tailwind-tokens`. Commits (TDD): `4388db6` (testes vermelhos) →
  `7532288` (tema/tokens) → `ddf1e8d` (hello-world verde + Dusk).
- PR: https://github.com/xandroalmeida/quantah/pull/1 — **CI verde** ("Testes + build" ✓,
  "E2E (Dusk)" ✓). Aprovado por Alexandro e **mergeado** na `main` (merge `e6f2539`).
- **Homologação verde:** pipeline de push na `main` fez o deploy e o smoke automático passou;
  smoke manual confirmado em `https://quantah-homolog.34.39.229.117.sslip.io/` (`/up` 200, `/`
  200 servindo Inter 400/600/900 + "Quantah"). Estória `done`.
- **Revisão do Designer (fidelidade):** `STORY-002-evidencia/revisao-designer.md` +
  capturas `tema-mobile.png` / `tema-desktop.png`. Veredito: mapeamento **100% fiel** aos tokens
  canônicos, regras de ouro respeitadas em mobile e desktop, **sem divergências**.
