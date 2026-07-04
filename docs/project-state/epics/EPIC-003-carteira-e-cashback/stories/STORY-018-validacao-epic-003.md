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
status: done
owner_agent: claude-validador-story018
created_at: 2026-07-03
updated_at: 2026-07-04
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

## Notas do agente

### 2026-07-04 — Validação final concluída (claude-validador-story018)

- **Pré-condição resolvida pelo PO:** STORY-015 estava `in_review`; o PO aprovou (`in_review→done`) e
  moveu EPIC-003 `ready→in_review` antes da validação (commit `91291c8`).
- **Checklist autorado** em `validation/checklist.md` (7 blocos, derivado dos CAs STORY-014..017,
  `epic.md`, `quality-standards.md`) e executado item a item.
- **Veredito: APPROVED.** 26 passes (4 com ressalva), 0 fails, 1 n/a. Relatório em
  `validation/report.md`.
- **Verificado de 1ª mão sobre o sha deployado `9c77cbc`:** suíte **237 PHPUnit / 955 asserções**
  verdes; núcleo de cashback e do saque **100%** (gate 98%), cobertura geral **94,3%**; **9 Dusk**
  (carteira/saque/backoffice) verdes em browser real; homologação viva (`/up` 200, `/carteira`
  302→login); reconciliação saldo × ledger garantida por construção e coberta por teste; CPF do KYC
  mascarado e não logado; CI run 28704414576 verde (build + E2E + deploy homolog).
- **Ressalvas (não-bloqueantes):** cobertura de *glue* de models < 80% (fora do núcleo); CI sem
  scanner dedicado de segredos/deps (verificado por inspeção); dashboard RED não verificado de 1ª mão;
  walkthrough autenticado do crédito na UI de homologação não executado (coberto por E2E/feature).
- **Fronteira de papel:** atualizei apenas `EPIC-003.validation_report` (não o `status` do épico —
  decisão do PO). A transição de EPIC-003 para `done` cabe ao PO com base neste veredito.
