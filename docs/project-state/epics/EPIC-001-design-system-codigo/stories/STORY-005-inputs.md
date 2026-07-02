---
story_id: STORY-005
slug: inputs
title: Inputs do DS (text/masked/datetime/select/checkbox/radio/switch) com estados e spec
epic_id: EPIC-001
sprint_id: SPRINT-2026-W27
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: in_progress
owner_agent: claude-programador-story005
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-005 — Inputs do DS

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. Se algo
> estiver ambíguo, registre a dúvida em "Notas do agente" e pause. `requires_design: true` — o
> Designer valida a fidelidade em paralelo (PDR-002).

## Contexto (por que esta estória existe)

Coleta e Carteira dependem de captura de dados (ex.: QR/chave da NFC-e, dados de cadastro). Os
campos de formulário precisam existir como componentes do DS, com estados e validação visual
consistentes, antes que as telas de produto nasçam.

- Épico: `epics/EPIC-001-design-system-codigo/epic.md`
- Documentos a ler ANTES:
  - `docs/especificacao/design-system.md` (inputs e mensagens de erro)
  - `docs/project-state/design/system/components.md` (spec de `input.*`)
  - `docs/project-state/design/system/tokens.md`
  - `docs/skills/stacks/inertia-react/SKILL.md`

## O quê (objetivo desta estória)

Implementar os componentes de entrada do DS em React: **text, masked, datetime, select, checkbox,
radio e switch** — cada um com **label, hint, estados (default/focus/disabled/error) e mensagem de
erro**, tudo via tokens (zero valor cru).

## Por quê (valor para o usuário)

Formulários consistentes e acessíveis reduzem erro do usuário na coleta do cupom — o ponto de maior
fricção da Onda 1. Sem inputs do DS, cada tela reinventaria campo e validação visual.

## Critérios de aceite

- [ ] **CA-1:** Existem componentes reutilizáveis para text, masked, datetime, select, checkbox,
      radio e switch, todos compondo tokens do DS (sem valor cru).
- [ ] **CA-2:** Cada input suporta **label**, **hint/ajuda** e estado de **erro** com mensagem
      associada acessível (`aria-describedby`/`aria-invalid`).
- [ ] **CA-3:** Estados default/focus/disabled/error cobertos; **foco visível** por teclado; alvo
      de toque **≥48px**; contraste AA em texto, borda e mensagem de erro.
- [ ] **CA-4:** `masked` aceita ao menos uma máscara relevante à Onda 1 (ex.: chave de acesso da
      NFC-e / campos numéricos) sem acoplar regra de negócio no componente.
- [ ] **CA-5:** Todos os inputs aparecem na vitrine com seus estados (a página é finalizada na
      STORY-006, mas os componentes já são plugáveis nela).

## Fora de escopo

- Validação de regra de negócio (ex.: validar/deduplicar cupom) — é dos épicos de Coleta.
- Botões, cards e demais componentes — outras estórias.

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. Cobertura ≥80% no código novo; testes de
estado/erro por componente; a11y mínima (foco, contraste, `aria-*`) verificada; sem valor cru.

## Dependências

- **Bloqueada por:** STORY-002 (tema/tokens) — `done`. Pode correr em paralelo com STORY-004.
- **Bloqueia:** STORY-006 (vitrine), STORY-007 (validação do épico).
- **Pré-requisitos:** ambiente/homologação da fundação.

## Decisões já tomadas (não as reabra)

- PDR-001, DDR-001, IDR-001. Regras de ouro do DS. Não redefina token — falta/conflito → exceção de
  spec do Designer ou novo DDR: **pare e registre**.

## Liberdade técnica do agente

Você decide a API de props, a lib de máscara (dentro das decisões vigentes), a estrutura de pastas
e o design dos testes. Você **não** redefine tokens nem critérios de aceite.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; **Designer confirmou fidelidade** (spec de `input.*`).
- [ ] Testes escritos e passando na cobertura exigida; a11y mínima verificada.
- [ ] Pipeline verde; deploy de homologação verificado.
- [ ] IDR se houve decisão técnica relevante.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. `requires_design: true` → alinhe cedo com o
Designer. Falta de token/conflito → `blocked` + escalar.

## Notas do agente (preenchido durante/após execução)

> **Owner:** `claude-programador-story005`. Trabalho direto na `main` (decisão do dono).
> Ambiente: stack Sail `quantah` (no ar: app :8000, selenium, pgsql :5442). Segue IDR-002
> (contrato-em-fonte + Dusk) e as regras de ouro do DS.

### 2026-07-02 — Leitura inicial completa
- **Documentos lidos:** STORY-005 (inteira); `design/system/components.md` (§Inputs & formulários:
  `input.text` = fundo `canvas`, texto `ink`, borda 1px `ink`, raio `md` 12px, padding `md lg`,
  `body-md`, label flutuante obrigatório, helper opcional, erro via `aria-describedby`, estados
  default/focus/disabled/error/readonly; `input.masked` = variante de text, máscara é UX e persiste
  valor **sem máscara** — validação é do servidor; `input.datetime` = **por seletor**, nunca digitação
  livre, valor canônico **ISO 8601**; `select/checkbox/radio/switch` herdam tokens, selecionado usa
  `primary` como **indicador**, não preenchimento de área grande); `tokens.md` (raio `md`=12px p/
  inputs, `elev.1` hairline `ink`, toque ≥48px, contraste AA, foco visível, feedback nunca só cor);
  `stacks/inertia-react/SKILL.md` (§Estilo/DS, §Máscaras = `react-imask` default + valor unmasked no
  useForm; §Seletor de data = `react-day-picker` OU **nativo `<input type=date>` como fallback simples**,
  valor ISO; §Acessibilidade = label associado, erro textual não só cor, ícone tem label; §Testes =
  Vitest+RTL para componente + Dusk browser real); `programador/SKILL.md` + IDR-001/IDR-002; código
  existente (`Button.jsx`, `Buttons.jsx`, `ButtonTokenContractTest`, `ButtonTest` Dusk, `tailwind.config.js`).
- **Entendimento consolidado:** implementar **7 componentes** de entrada em `Components/`:
  `TextField` (`input.text`), `MaskedField` (`input.masked`), `DateTimeField` (`input.datetime`),
  `SelectField`, `Checkbox`, `Radio`, `Switch`. Todos compõem **só tokens** (zero valor cru): chrome de
  input = fundo `canvas`, texto `ink`, borda `ink` (elev.1 hairline), raio `md` (12px), padding
  `md lg`, `body-md`. Cada um com **label**, **hint** e **erro** com wiring a11y (`aria-invalid`,
  `aria-describedby` ligando hint + mensagem de erro; `role="alert"` na mensagem). Estados
  default/focus/disabled/error; **foco visível** por teclado; alvo de toque **≥48px**; contraste AA.
  `MaskedField` usa `react-imask` e guarda **valor unmasked** (`onAccept`/`unmask`) — máscara da chave
  de acesso da NFC-e (44 dígitos) como exemplo, sem regra de negócio. `DateTimeField` usa input nativo
  (`type="date"`/`datetime-local`) — seletor real do browser, valor **ISO 8601** — o fallback simples
  que a sub-skill autoriza (evita `react-day-picker` num story de DS sem tela de produto ainda).
  Selecionado (checkbox/radio/switch) usa `primary` como **indicador**. Host de E2E: vitrine `/ds/inputs`.
- **Fora de escopo:** validação de regra de negócio (Coleta); botões/cards/demais (outras stories);
  kitchen-sink completa (STORY-006 finaliza a vitrine — aqui os componentes já são plugáveis).
- **Dúvidas:** nenhuma bloqueante. Não redefino token; falta/conflito → `blocked` + Designer.

### Decisões (registradas antes de codar)
1. **`react-imask` como lib de máscara** (default da sub-skill inertia-react) — lib focada, testada,
   resolve caret/paste/delete que máscara caseira erra; guarda valor **unmasked** no consumidor. Vira
   **IDR-003** (lib transversal do projeto p/ campos formatados). Único dep novo desta story.
2. **`input.datetime` por input nativo** (`type="date"`/`datetime-local`), não `react-day-picker` —
   o nativo já é seletor real do browser e entrega valor ISO 8601; é o fallback simples que a sub-skill
   autoriza p/ caso simples. Evita dep pesada num componente de DS sem tela de produto. Se uma tela de
   Coleta pedir calendário estilizado, aí `react-day-picker` entra por ADR/IDR.
3. **Estratégia de teste = IDR-002 mantida (contrato-em-fonte Feature + Dusk); Vitest/RTL segue
   adiado.** Motivo: a lógica de cliente "rica" foi **delegada a peça testada** (`react-imask`) e a
   **controle nativo** (datetime), então o que resta nos componentes é wiring fino (variante→classe,
   aria, valor unmasked/ISO fluindo p/ o consumidor) — tudo **observável em browser real** via Dusk
   (digitar no campo, ler o valor canônico num display de debug da vitrine). Sem jsdom que se pague vs.
   risco de toolchain contra Vite 8. IDR-003 registra a reavaliação e mantém o gatilho: quando um
   componente tiver lógica pura de cliente sem lib que a cubra, adota-se Vitest/RTL. (Supersede o ponto
   de decisão da IDR-002.)
4. **Label flutuante** em `TextField`/`MaskedField` (spec: "label flutuante obrigatório") via técnica
   CSS `peer`/`placeholder-shown` (sem JS). `DateTimeField`/`SelectField` usam **label estático no topo**
   (input de data/select não têm `:placeholder-shown` útil); `Checkbox`/`Radio`/`Switch` usam label inline.

### Plano (5 bullets)
1. **RED contrato:** `InputTokenContractTest` (Feature) varrendo os 7 `.jsx` — tokens (raio `md`,
   `border-ink`, `bg-canvas`, `text-body-md`), zero valor cru (hex/arbitrário/paleta neutra), wiring
   a11y (`aria-invalid`, `aria-describedby`, `role="alert"`), estado de erro por token (`border-negative`).
2. **RED Dusk:** `InputTest` (browser real) na vitrine `/ds/inputs` — label/hint/erro com aria ligado;
   foco visível por teclado; alvo ≥48px; contraste AA (texto/borda/erro); máscara guarda **unmasked**;
   datetime guarda **ISO**; disabled bloqueia; error expõe `aria-invalid`. Categorias (a) feliz (b)
   inválido (c) exceção (d) borda cobertas.
3. **GREEN:** implementar os 7 componentes via tokens + página `/ds/inputs` (com displays de valor
   canônico p/ provar unmasked/ISO) + rota.
4. Suíte completa verde (sail), Dusk verde, Pint limpo, cobertura ≥80%.
5. IDR-003, notas finais, gate do Designer (fidelidade `input.*`), `done` + `index.json`, deploy
   homolog verificado, roteiro de teste ao usuário.

### Mapeamento CA → testes (planejado)
- **CA-1** (7 componentes reutilizáveis compondo tokens, sem valor cru):
  `InputTokenContractTest::test_defines_seven_input_components`,
  `::test_input_chrome_uses_ds_tokens` (raio md, border-ink, bg-canvas, body-md),
  `::test_inputs_have_no_raw_hex_color`, `::test_inputs_have_no_arbitrary_or_neutral_color`.
- **CA-2** (label + hint + erro com aria acessível):
  `InputTokenContractTest::test_error_wires_aria_invalid_and_describedby`;
  Dusk `InputTest::test_error_shows_message_wired_by_aria` (a),
  `::test_hint_is_linked_by_aria_describedby` (a), `::test_label_is_associated_with_control` (a).
- **CA-3** (estados default/focus/disabled/error; foco visível; ≥48px; contraste AA):
  Dusk `::test_all_controls_have_visible_keyboard_focus` (d),
  `::test_text_control_touch_target_is_at_least_48px` (d),
  `::test_text_border_and_error_pass_aa_contrast` (b),
  `::test_disabled_control_is_not_editable` (b).
- **CA-4** (masked guarda valor unmasked, sem regra de negócio):
  Dusk `::test_masked_field_formats_and_stores_unmasked_value` (a),
  `::test_masked_field_ignores_non_digits` (b — inválido/encoding),
  `::test_masked_field_empty_stays_empty` (d — borda);
  `DateTimeField` complementar: `::test_datetime_field_stores_iso_value` (a).
- **CA-5** (todos na vitrine com estados):
  Dusk `::test_showcase_lists_all_input_components_and_states`.
- Categorias (testing-discipline): (a) feliz, (b) inválido, (c) exceção — *disabled/leitura bloqueada e
  erro submetido*, (d) borda — *vazio, foco por teclado, ≥48px* — todas cobertas nos 7 componentes.

### Decisões tomadas
- (ver "Decisões (registradas antes de codar)" acima; IDR-003 formaliza react-imask + datetime nativo +
  manutenção da estratégia de teste da IDR-002)

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
