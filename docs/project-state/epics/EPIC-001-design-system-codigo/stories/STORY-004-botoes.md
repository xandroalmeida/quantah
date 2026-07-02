---
story_id: STORY-004
slug: botoes
title: Botões do DS (primary/secondary/tertiary/danger/icon) com estados e spec
epic_id: EPIC-001
sprint_id: SPRINT-2026-W27
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: in_review
owner_agent: claude-programador-story004
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-004 — Botões do DS

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. Se algo
> estiver ambíguo, registre a dúvida em "Notas do agente" e pause em vez de adivinhar.
> `requires_design: true` — o Designer valida a fidelidade em paralelo (PDR-002).

## Contexto (por que esta estória existe)

O tema Tailwind com os tokens do DS já está de pé (STORY-002), mas os componentes ainda não vivem
em código. O botão é o componente mais usado e o portador da regra de ouro do DS (verde `primary`
só como CTA). Implementá-lo primeiro destrava a vitrine e serve de molde para os demais.

- Épico: `epics/EPIC-001-design-system-codigo/epic.md`
- Documentos a ler ANTES:
  - `docs/especificacao/design-system.md` (botões e regras de ouro)
  - `docs/project-state/design/system/components.md` (spec de `button.*`)
  - `docs/project-state/design/system/tokens.md` (valores canônicos)
  - `docs/skills/stacks/inertia-react/SKILL.md` (§ componente→React, token→Tailwind)

## O quê (objetivo desta estória)

Implementar o componente `Button` do DS em React (`Components/`) com as variantes
**primary, secondary, tertiary, danger e icon**, cada uma com os estados
**default / hover / focus / pressed / disabled / loading**, tudo via tokens (zero valor cru).

## Por quê (valor para o usuário)

Botão consistente é o que faz cada tela do Quantah parecer o mesmo produto e evita dívida de
design. Sem ele, Coleta e Carteira reinventariam o CTA — quebrando a regra de ouro do DS.

## Critérios de aceite

- [ ] **CA-1:** Existe `Button` reutilizável com as 5 variantes (primary/secondary/tertiary/danger/
      icon); a variante `primary` usa o verde `primary` + texto `on-primary` (é o único CTA).
- [ ] **CA-2:** Cada variante cobre os estados default/hover/focus/pressed/disabled/loading, todos
      derivados de tokens — **nenhum valor cru** de cor/spacing/raio no componente.
- [ ] **CA-3:** A11y mínima: contraste AA (≥4.5:1) em cada variante, **foco visível** por teclado,
      e alvo de toque **≥48px** de altura (exceto onde a spec do Designer definir diferente).
- [ ] **CA-4:** Estado `loading` desabilita o clique e expõe estado acessível (ex.: `aria-busy`);
      `disabled` não dispara `onClick`.
- [ ] **CA-5:** O componente aparece na vitrine (kitchen sink) exibindo todas as variantes e estados
      — mesmo que a página da vitrine seja finalizada na STORY-006, o `Button` já é plugável nela.

## Fora de escopo

- Demais componentes (inputs, cards etc.) — outras estórias do épico.
- Telas de produto (Coleta, Carteira).
- Ilustração autoral de marca (DDR-002).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. Cobertura ≥80% no código novo; testes de
render/estado por variante; checagem a11y (contraste AA + foco) automatizada onde viável; sem valor
cru fora dos tokens.

## Dependências

- **Bloqueada por:** STORY-002 (tema Tailwind com tokens) — já `done`.
- **Bloqueia:** STORY-006 (vitrine consome os componentes), STORY-007 (validação do épico).
- **Pré-requisitos:** ambiente e homologação da fundação (EPIC-000).

## Decisões já tomadas (não as reabra)

- PDR-001 (adoção do DS), DDR-001 (Inter), IDR-001 (tokens no `theme.extend`).
- Regras de ouro do DS: verde só como CTA; raio 24px; sem 2º accent. Não redefina token — falta/
  conflito de token vira exceção de spec do Designer ou novo DDR: **pare e registre**.

## Liberdade técnica do agente

Você decide a API de props do `Button`, a estrutura de pastas em `Components/`, e o design dos
testes. Você **não** redefine tokens nem os critérios de aceite.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; **Designer confirmou fidelidade** (spec de `button.*`).
- [ ] Testes escritos e passando na cobertura exigida; a11y mínima verificada.
- [ ] Pipeline de CI verde; deploy de homologação realizado e verificado.
- [ ] IDR registrado se houve decisão técnica relevante.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. `requires_design: true` → alinhe cedo com o
Designer. Falta de token/conflito → `blocked` + escalar ao Designer.

## Notas do agente (preenchido durante/após execução)

> **Owner:** `claude-programador-story004`. Trabalho direto na `main` (decisão do dono).
> Ambiente: stack Sail `quantah` (já no ar: app :8000, selenium, pgsql :5442).

### 2026-07-02 — Leitura inicial completa
- **Documentos lidos:** STORY-004 (inteira); `epic.md` (EPIC-001); `design/system/components.md`
  (§Botões: primary/secondary/tertiary/danger/icon-circular, estados, "usar/não usar quando");
  `design/system/tokens.md` (cor, tipografia `button-md`, spacing base-4, raio `xl`=24px,
  elevação `elev.1` hairline, motion, toque ≥48px, contraste AA e regra "nunca branco sobre
  primary"); `stacks/inertia-react/SKILL.md` (§Estilo/DS na prática, §Acessibilidade, §Testes);
  `programador/SKILL.md` + `reading-discipline.md`; código existente (`Hello.jsx`, `PrimaryButton`
  do starter, testes `NoRawColorInHelloTest`, `ThemeTest`, `DuskTestCase`, `routes/web.php`).
- **Entendimento consolidado:** implementar `Components/Button.jsx` — 5 variantes
  (primary/secondary/tertiary/danger/icon) com estados default/hover/focus/pressed/disabled/loading,
  **tudo via tokens do DS (zero valor cru de cor/spacing)**. `primary` é o único CTA (verde `primary`
  + `on-primary`). A11y: contraste AA por variante, foco visível por teclado, alvo ≥48px; `loading`
  bloqueia clique e expõe `aria-busy`; `disabled` não dispara `onClick`. Demonstrar numa vitrine
  (`/ds/buttons`) com todas as variantes/estados — base plugável para a vitrine completa (STORY-006).
- **Fora de escopo:** demais componentes (inputs/cards/etc.), telas de produto, ilustração (DDR-002).
- **Dúvidas:** nenhuma bloqueante. Não redefino token; se faltar/conflitar → `blocked` + Designer.

### Plano (5 bullets)
1. **Estratégia de teste** (IDR-002): seguir o padrão já estabelecido no projeto — teste **Feature
   (PHP)** varrendo o JSX para o **contrato de tokens** (como `NoRawColorInHelloTest`) + **Dusk**
   (browser real) para comportamento/a11y (cor computada, contraste, foco, clique). **Não** introduzo
   Vitest/RTL agora (risco de toolchain vs Vite 8; Dusk cobre comportamento+a11y em browser de verdade).
2. **RED:** commitar `ButtonTokenContractTest` (Feature) + `ButtonTest` (Dusk) antes do código.
3. **GREEN:** `Components/Button.jsx` (variantes+estados via tokens), `Pages/DesignSystem/Buttons.jsx`
   (vitrine com contadores de clique para provar onClick/disabled/loading), rota `/ds/buttons`.
4. Suíte completa verde (sail), Dusk verde em browser real, Pint limpo, cobertura ≥80%.
5. IDR-002, notas finais, `done` + `index.json`, deploy homolog verificado, roteiro de teste.

### Mapeamento CA → testes (planejado)
- **CA-1** (5 variantes; primary = verde `primary` + `on-primary`, único CTA):
  `ButtonTokenContractTest::test_button_defines_five_variants`,
  `::test_primary_variant_uses_brand_tokens`; Dusk `ButtonTest::test_primary_renders_brand_tokens`
  (bg rgb(159,232,112), color rgb(14,15,12), raio 24px).
- **CA-2** (estados default/hover/focus/pressed/disabled/loading via tokens; **zero valor cru**):
  `ButtonTokenContractTest::test_button_has_no_raw_color`,
  `::test_button_has_no_arbitrary_or_neutral_color`,
  `::test_each_variant_maps_to_ds_tokens`,
  `::test_button_covers_hover_focus_pressed_disabled_loading_states`.
- **CA-3** (a11y: contraste AA por variante, foco visível, alvo ≥48px):
  Dusk `ButtonTest::test_all_variants_pass_AA_contrast` (WCAG real por variante),
  `::test_keyboard_focus_is_visible`, `::test_touch_target_is_at_least_48px`.
- **CA-4** (loading bloqueia clique + `aria-busy`; disabled não dispara onClick):
  Dusk `ButtonTest::test_enabled_primary_fires_onclick` (feliz),
  `::test_disabled_does_not_fire_onclick` (inválido),
  `::test_loading_blocks_click_and_sets_aria_busy` (exceção — inclui spinner visível/label oculto).
- **CA-5** (aparece na vitrine com todas as variantes e estados):
  Dusk `ButtonTest::test_showcase_lists_all_variants_and_states`.

### Categorias de teste cobertas (testing-discipline)
- (a) feliz: primary render + onClick dispara. (b) inválido: disabled não dispara; contraste por
  variante. (c) exceção: loading bloqueia clique + aria-busy. (d) borda: alvo ≥48px, foco por teclado,
  todas as 5 variantes (não só primary).

### Mapeamento CA → teste (final)
- **CA-1** (5 variantes; primary = verde `primary` + `on-primary`, único CTA):
  `ButtonTokenContractTest::test_button_defines_five_variants`,
  `::test_primary_variant_uses_brand_tokens`;
  Dusk `ButtonTest::test_primary_renders_brand_tokens` (bg rgb(159,232,112), color rgb(14,15,12), raio 24px).
- **CA-2** (estados via tokens; zero valor cru):
  `ButtonTokenContractTest::test_each_variant_maps_to_ds_tokens`,
  `::test_button_uses_signature_radius_label_and_touch_target`,
  `::test_button_covers_all_interaction_states`,
  `::test_button_has_no_raw_hex_color`, `::test_button_has_no_arbitrary_or_neutral_color`.
- **CA-3** (a11y: contraste AA por variante, foco visível, alvo ≥48px):
  Dusk `ButtonTest::test_all_variants_pass_aa_contrast` (WCAG real nas 5 variantes),
  `::test_keyboard_focus_is_visible`, `::test_touch_target_is_at_least_48px`.
- **CA-4** (loading bloqueia clique + aria-busy; disabled não dispara onClick):
  Dusk `ButtonTest::test_enabled_primary_fires_onclick` (a: feliz),
  `::test_disabled_does_not_fire_onclick` (b: inválido),
  `::test_loading_blocks_click_and_sets_aria_busy` (c: exceção — spinner visível + label oculto).
- **CA-5** (aparece na vitrine com variantes e estados):
  Dusk `ButtonTest::test_showcase_lists_all_variants_and_states`.
- Categorias (testing-discipline): (a) feliz, (b) inválido, (c) exceção, (d) borda — todas cobertas;
  a11y verificada nas **5** variantes (não só a primary).

### Decisões tomadas
- **IDR-002** — teste de componente do DS por contrato-em-fonte (Feature) + Dusk; **Vitest/RTL
  adiado** para a STORY-005 (inputs, lógica de máscara/data). Segue o padrão já estabelecido (STORY-002)
  e evita toolchain novo contra Vite 8.
- **API do `Button`**: `variant` (primary/secondary/tertiary/danger/icon), `loading`, `disabled`,
  `type`, `className`, `...props` (onClick, aria-label). `loading`/`disabled` usam o `disabled`
  nativo do `<button>` (bloqueio de clique correto e sem JS extra); `loading` adiciona `aria-busy`
  e spinner sobre o rótulo (label `invisible`, sem shift de layout).
- **Tailwind**: `minHeight`/`minWidth` passam a herdar a escala de spacing do DS, para `min-h-3xl`
  (48px) valer como token — necessário para o alvo de toque (o icon-button sem isso mediria ~34px).
- **Estados por tokens**: hover/pressed via tokens do DS (`primary-active`/`primary-neutral`,
  `negative-deep`/`negative-darkest`, `primary-pale`); disabled = `opacity-[0.38]` (38% da spec de
  `components.md` — opacidade não é token de cor/spacing; único arbitrário, e é o valor literal da spec).
- **Convenção de lint**: `pint.json` fixa `test_snake_case` (convenção do projeto) + passo **Lint
  (Pint)** no CI. Limpou débito pré-existente em `bootstrap/app.php`.

### Descobertas
- Sem `pint.json`, o preset default do Pint tentava recasear os 60 métodos `test_snake_case` do
  projeto (inclusive a `ThemeTest` já mergeada) — por isso a config. CI não rodava Pint; agora roda.
- Locks de git obsoletos (`index.lock`/`HEAD.lock`, 0 bytes) na raiz do repo — removidos após
  confirmar que não havia processo git ativo (há agentes concorrentes na máquina, mas em stacks `app-*`).

### Bloqueios encontrados
- Nenhum.

### IDRs criados
- **IDR-002** (accepted) — `decisions/idr/IDR-002-teste-de-componente-ds-feature-contract-mais-dusk.md`.

### Cobertura final
- Unit+feature: **45 testes, 169 assertions, verde**; cobertura PHP **87.3%** (gate `--min=80`).
  A estória não adiciona código PHP de produção (só JSX/config/rota-closure + testes), então a
  cobertura de núcleo permanece intacta; o contrato do componente é coberto por `ButtonTokenContractTest`.
- Dusk (browser real): **15 testes verde** (8 `ButtonTest` + 5 `ThemeTest` + 2 `HelloWorldTest`),
  56 assertions.
- Pint: **limpo** (`sail pint --test` → passed).

### Estado de entrega
- Implementação **verde localmente** (suíte completa + Dusk + Pint + cobertura). Commits na `main`:
  `34ab37d` (testes vermelhos), `9040d97` (feat Button+vitrine), `cf45afb` (pint.json + gate CI),
  + docs (IDR-002/notas/index). `status: in_review`.
- **Pendente (aguarda push):** CI verde + deploy de homologação verificado — só faço push quando o
  dono pedir. **Gate de design (`requires_design: true`):** confirmação de fidelidade do Designer
  (validação em paralelo, PDR-002).

### Links de evidência
- Componente: `app/resources/js/Components/Button.jsx`. Vitrine: `app/resources/js/Pages/DesignSystem/Buttons.jsx` (rota `/ds/buttons`).
- Testes: `app/tests/Feature/DesignSystem/ButtonTokenContractTest.php`, `app/tests/Browser/ButtonTest.php`.
- Smoke local: `GET http://localhost:8000/ds/buttons` → HTTP 200.
