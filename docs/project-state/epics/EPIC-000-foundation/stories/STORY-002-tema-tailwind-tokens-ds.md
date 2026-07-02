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
status: in_progress
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

- [ ] **CA-1:** Os tokens de cor do DS (primary, on-primary, canvas, canvas-soft, ink, body,
      mute, semânticas) existem como utilitários Tailwind e **nenhum valor cru** de cor aparece
      no JSX da hello-world.
- [ ] **CA-2:** A escala tipográfica do DS está mapeada e a fonte **Inter** (400/600/900) é
      carregada; o display usa peso 900 (DDR-001).
- [ ] **CA-3:** Spacing, raio (com `xl` = 24px), elevação e breakpoints do DS estão no tema.
- [ ] **CA-4:** A hello-world exibe: um título display (Inter 900), um parágrafo em corpo, e um
      `button.primary` (verde `#9fe870`, texto `on-primary`, raio 24px) — todos via tokens.
- [ ] **CA-5:** Contraste do botão passa AA (texto `on-primary` sobre `primary`), foco visível.

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

- [ ] CA-1 a CA-5 passam; Designer confirmou fidelidade do mapeamento.
- [ ] Testes/checagens passando; a11y mínima verificada.
- [ ] Pipeline verde; hello-world com tema em homologação (evidência).
- [ ] IDR se houve decisão técnica relevante (ex.: estratégia de CSS vars).
- [ ] `index.json` atualizado: status = `in_review` ao abrir PR.
- [ ] Notas do agente preenchidas.

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
- (em progresso)
### Descobertas
- (em progresso)
### Bloqueios encontrados
- Nenhum até agora.
### IDRs criados
- (avaliar ao fim — estratégia de CSS vars / troca de fonte)
### Cobertura final
- (preencher ao fim)
### Links de evidência
- (preencher ao fim)
