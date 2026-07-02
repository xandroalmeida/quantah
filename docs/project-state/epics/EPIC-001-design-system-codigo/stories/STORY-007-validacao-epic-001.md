---
story_id: STORY-007
slug: validacao-epic-001
title: Validação final do EPIC-001 (Design System em código)
epic_id: EPIC-001
sprint_id: null
type: validation
target_role: validador
requires_design: false
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-007 — Validação final do EPIC-001

> **Para o validador:** valide o épico de forma independente contra o checklist. Não confie apenas
> em relatos das estórias — verifique a vitrine em homologação por evidência própria.

## Contexto (por que esta estória existe)

Fecha o EPIC-001: confirmar, com evidência independente, que a biblioteca de componentes do DS
existe em código, com estados e a11y mínima, e que a vitrine está no ar em homologação.

- Épico: `epics/EPIC-001-design-system-codigo/epic.md`
- Checklist: `epics/EPIC-001-design-system-codigo/validation/checklist.md` (a criar antes da validação)
- Critérios de veredito: `docs/skills/validador/references/verdict-criteria.md`

## O quê (objetivo desta estória)

Executar a validação final do EPIC-001 e emitir um relatório com veredito (`approved` / `rejected`),
seguindo o mesmo rigor da validação do EPIC-000.

## Por quê (valor para o usuário)

Garante que Coleta e Carteira vão nascer sobre uma base de componentes de fato pronta e consistente,
não sobre uma promessa.

## Critérios de aceite

- [ ] **CA-1:** Todos os componentes da lista mínima existem, com estados, e aparecem na vitrine.
- [ ] **CA-2:** Vitrine acessível em homologação por HTTPS (HTTP 200), verificada de forma independente.
- [ ] **CA-3:** A11y mínima confirmada (contraste AA, foco visível, alvo ≥48px) e zero valor cru
      fora dos tokens (guarda verde).
- [ ] **CA-4:** Cobertura conforme `quality-standards.md`; pipeline verde no merge que publicou.
- [ ] **CA-5:** Relatório emitido em `validation/report.md` e `index.json` aponta o relatório com o
      veredito; transição de status do épico fica a cargo do PO.

## Fora de escopo

- Corrigir defeitos encontrados — isso vira estória de correção; a validação apenas relata.

## Padrões de qualidade exigidos

Segue `docs/skills/validador/*` e `quality-standards.md`. Evidência independente obrigatória
(não apenas relato das estórias).

## Dependências

- **Bloqueada por:** STORY-004, STORY-005, STORY-006 (todas `done`).
- **Bloqueia:** fechamento do EPIC-001.

## Decisões já tomadas (não as reabra)

- PDR-001, PDR-002 (paralelismo Designer↔Programador), DDR-001.

## Definição de Pronto (DoD)

- [ ] Checklist do épico executado item a item com evidência.
- [ ] Relatório com veredito em `validation/report.md`.
- [ ] `index.json` atualizado: `epics[EPIC-001].validation_report` aponta o relatório; story `done`.
- [ ] "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `docs/skills/validador/*` e `agent-task-format.md`. Veredito segue `verdict-criteria.md`.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 

### Descobertas
- 

### Bloqueios encontrados
- 

### Links de evidência
- 
