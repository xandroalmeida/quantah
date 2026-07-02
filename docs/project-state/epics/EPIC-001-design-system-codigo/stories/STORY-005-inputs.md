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
status: ready
owner_agent: null
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

### Decisões tomadas
- 

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
