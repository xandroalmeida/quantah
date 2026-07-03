---
story_id: STORY-018
slug: validacao-epic-003
title: Validação final do EPIC-003 (Carteira e cashback)
epic_id: EPIC-003
sprint_id: null
type: validation
target_role: validador
requires_design: false
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-03
updated_at: 2026-07-03
estimated_session_size: M
---

# STORY-018 — Validação final do EPIC-003

> **Para o agente que vai executar (validador):** verificação de 1ª mão sobre o sha deployado.
> O produto é um **veredito** e um relatório; a transição de status do épico é do PO.

## Contexto (por que esta estória existe)

Fecha o épico com evidência independente de que o loop de recompensa funciona: crédito correto,
saldo visível, resgate existente — tudo em homologação, sem confiar apenas nos testes dos autores.

## Critérios de aceite (alto nível — autorar checklist na execução)

- Checklist em `validation/checklist.md` e relatório em `validation/report.md`.
- Verificado de 1ª mão sobre o sha deployado: crédito de 0,1% correto e idempotente;
  **reconciliação saldo × cupons sem divergência**; carteira visível em homologação; caminho de
  resgate existente no escopo do ADR-005.
- Cobertura ≥ 98% no núcleo de cálculo de cashback (gate de qualidade do épico).
- CI verde; sem segredos versionados; LGPD mantida.
- Veredito registrado; `EPIC-003.validation_report` apontando o relatório.

## Fora de escopo

- Recomendar/decidir a transição de status do épico (é do PO).

## Dependências

- **Bloqueado por:** STORY-014, STORY-015, STORY-016, STORY-017.
- **Bloqueia:** —

## Definição de pronto

Relatório publicado com veredito; estado atualizado; `index.json` = `done`; Notas do agente
preenchidas.
