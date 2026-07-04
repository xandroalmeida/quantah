---
story_id: STORY-017
slug: resgate-saque
title: Resgate/saque do saldo (escopo conforme ADR-005)
epic_id: EPIC-003
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: in_progress
owner_agent: claude-story017
created_at: 2026-07-03
updated_at: 2026-07-03
estimated_session_size: M
---

# STORY-017 — Resgate/saque do saldo

> **Para o agente que vai executar:** o **escopo depende do ADR-005** (STORY-014). No MVP pode ser
> **PIX assistido/manual**, não necessariamente saque automatizado com KYC completo.

## Contexto (por que esta estória existe)

O loop só fecha se houver um **caminho para o dinheiro sair**. A visão admite resgate simplificado
no MVP (`docs/visao.md` §5.1). O contorno exato (automatizado x assistido, KYC mínimo) vem do
ADR-005. Sem resgate, o saldo é número na tela sem promessa cumprida.

## Critérios de aceite (alto nível — refinar após ADR-005)

- Caminho de resgate existente em homologação, no escopo decidido pelo ADR-005.
- Débito do saldo **idempotente e reconciliável** (extrato bate com carteira; sem duplo saque).
- Estados de erro/pendência tratados (ex.: saque em processamento, falha de PIX).
- Se automatizado: KYC mínimo do ADR-005 aplicado. Se assistido: fluxo operacional documentado.

## Design (requires_design)

Tela/fluxo do Colaborador → exige artefato de Designer (mesmo critério da STORY-016). A profundidade
depende do escopo do ADR-005 (assistido pode ser fluxo enxuto).

## Fora de escopo

- KYC completo e automação total, se o ADR-005 empurrar para a Onda 2.

## Dependências

- **Bloqueado por:** STORY-014 (ADR-005 define o escopo), STORY-015 (saldo a movimentar).
- **Bloqueia:** STORY-018.

## Definição de pronto

Resgate no escopo do ADR-005 demonstrável em homologação; débito reconciliável; artefato de
Designer presente; testes verdes; `index.json` = `done`; Notas do agente preenchidas.
