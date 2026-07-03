---
story_id: STORY-015
slug: calculo-e-credito-de-cashback
title: Cálculo e crédito automático de cashback (0,1%) sobre cupom válido
epic_id: EPIC-003
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-03
updated_at: 2026-07-03
estimated_session_size: L
---

# STORY-015 — Cálculo e crédito de cashback

> **Para o agente que vai executar (programador):** o foco é a **regra de negócio correta e
> auditável**, com cobertura alta (a `quality-standards.md` pede 98% no núcleo de cálculo).

## Contexto (por que esta estória existe)

É a **fundação do saldo**: quando um cupom entra na base como válido-único-novo (EPIC-002), o
Colaborador deve receber **R$ 1 a cada R$ 1.000** (0,1% do valor do cupom) creditado na carteira.
Sem este crédito, a carteira (STORY-016) e o saque (STORY-017) não têm o que exibir/movimentar.

## Critérios de aceite (alto nível — refinar na execução)

- Ao um cupom ser marcado válido-único-novo, um crédito de **0,1% do valor** é lançado na carteira
  do Colaborador, **idempotente por chave de acesso** (nunca credita o mesmo cupom duas vezes).
- Modelo de carteira/transações append-only (saldo derivado do extrato, reconciliável).
- Arredondamento e moeda definidos e testados (centavos; sem perda/dobro por arredondamento).
- **Reconciliação saldo × cupons sem divergência** (métrica de qualidade do épico).
- Cobertura ≥ 98% no núcleo da regra de cálculo/crédito.
- Telemetria: nº de Colaboradores com saldo > 0 (métrica de apoio) mensurável.

## Fora de escopo

- Tela da carteira (STORY-016) e saque (STORY-017).
- Estorno/ajuste manual de crédito (Onda 2, salvo decisão em contrário).

## Dependências

- **Bloqueado por:** EPIC-002 (crédito incide sobre cupom válido) — done.
- **Bloqueia:** STORY-016 (exibe saldo/histórico), STORY-017 (movimenta saldo), STORY-018.

## Definição de pronto

Regra implementada e coberta (≥98%); reconciliação sem divergência; `index.json` = `done`; IDR se
houve decisão técnica; Notas do agente preenchidas.
