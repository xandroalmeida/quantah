---
story_id: STORY-042
slug: validacao-epic-009
title: Validação final do EPIC-009 — enriquecimento cadastral do emitente
epic_id: EPIC-009
sprint_id: null
type: validation
target_role: validador
requires_design: false
status: ready
owner_agent: null
created_at: 2026-07-06
updated_at: 2026-07-06
estimated_session_size: M
---

# STORY-042 — Validação final do EPIC-009

> **Para o agente que vai executar:** você atua com a skill `validador`
> (`docs/skills/validador/SKILL.md`). Execute o checklist em
> `epics/EPIC-009-enriquecimento-cadastral-emitente/validation/checklist.md` e produza
> `validation/report.md`. O épico só fecha com o seu relatório.

## Contexto

Última estória do EPIC-009. As STORY-040/041 entregaram o enriquecimento CNPJ (fila + cache) integrado
ao pipeline do cupom e visível no Backoffice. Valide contra o `epic.md` e o checklist.

## Critérios de aceite

- [ ] **CA-1:** Checklist executado item a item, com evidência por item (log, screenshot, saída de teste).
- [ ] **CA-2:** Suíte completa (unit/feature + E2E Dusk) verde sobre o sha deployado em homologação.
- [ ] **CA-3:** Coberturas verificadas (≥ 80% novo; ≥ 98% em regra de cache/fallback).
- [ ] **CA-4:** Demonstração em homologação: cupom novo → emitente enriquecido no Backoffice; segundo
      cupom do mesmo CNPJ → sem chamada externa (evidência de cache).
- [ ] **CA-5:** `validation/report.md` com veredito (`approved` / `approved_with_findings` /
      `rejected`), achados classificados (bloqueante × ressalva) e `index.json` atualizado.

## Protocolo

Siga `docs/skills/po/references/agent-task-format.md`. Achados não-bloqueantes viram WISH/BUG conforme
`references/wishlist.md` e `references/bugs.md`. Reprovação → estórias de correção (PO), épico fica
`in_review`.

## Notas do agente

-
