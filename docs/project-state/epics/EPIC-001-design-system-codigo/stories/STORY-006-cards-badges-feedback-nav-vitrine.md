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
status: draft
owner_agent: null
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
