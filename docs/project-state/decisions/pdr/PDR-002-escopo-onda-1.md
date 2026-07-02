---
pdr_id: PDR-002
slug: escopo-onda-1
title: Escopo da Onda 1 — Coleta + incentivo (provar a coleta em SP)
status: accepted
decided_at: 2026-07-02
decided_by: PO (Alexandro / Claude)
supersedes: null
superseded_by: null
related_epics: [EPIC-000, EPIC-001, EPIC-002, EPIC-003]
related_adrs: [ADR-000]
---

# PDR-002 — Escopo da Onda 1: Coleta + incentivo

## Contexto

Com o Fluxo 0 (visão, personas, north-star) pronto, o ADR-000 (stack) e o design system
(PDR-001 + DDR-001/002) aceitos, abre-se a **primeira onda de implementação**. A north-star do
MVP é **cupons NFC-e válidos, únicos e novos por semana** — a hipótese central é que
consumidores em SP enviam cupons em volume suficiente **quando há recompensa**. Precisamos
definir o escopo mínimo da onda que testa essa hipótese sem inflar o plano (princípio de
planejamento em ondas).

## Opções consideradas

### Opção 1 — Enxuta (só coleta técnica)
Foundation + Design System + Coleta. Prova que o cupom entra e vira dado válido/único/novo.
- Prós: mais rápido; valida o pipeline técnico de coleta.
- Contras: **não testa a hipótese da north-star** (volume puxado por incentivo) — sem cashback,
  não há razão para o Colaborador enviar.

### Opção 2 — Coleta + incentivo (escolhida)
Foundation + Design System + Coleta + Carteira/cashback.
- Prós: testa a hipótese real (coleta motivada por recompensa); entrega um loop completo
  Colaborador → cupom → crédito; ainda enxuta (4 épicos).
- Contras: inclui integração de pagamento/saque (mitigado: saque pode ficar parcial/simplificado
  no MVP; o crédito de saldo é o essencial).

### Opção 3 — Completa
Acima + Gamificação + gancho "você pagou mais/menos que a média".
- Contras: onda grande; gamificação e gancho são aceleradores, não o teste mínimo da hipótese —
  cabem melhor na Onda 2, replanejados com aprendizado real da Onda 1.

## Decisão

> **Optamos pela Opção 2 — Coleta + incentivo.**

A Onda 1 (WAVE-2026-01) entrega, em homologação, o loop **Colaborador escaneia/compartilha o QR
da NFC-e → cupom é validado, deduplicado e vira dado → saldo em cashback é creditado**, sobre a
stack ratificada e o design system adotado. Quatro épicos: **EPIC-000 Foundation**, **EPIC-001
Design System em código**, **EPIC-002 Coleta de cupom**, **EPIC-003 Carteira e cashback**.

## Justificativa

É o menor escopo que efetivamente **testa a hipótese da north-star**: sem incentivo, medir
volume de coleta seria enganoso. O loop coleta→crédito é o coração do produto; gamificação e
gancho de valor (Opção 3) são alavancas que só fazem sentido depois de o loop básico funcionar e
ser medível — vão para a Onda 2 com replanejamento à luz do que a Onda 1 ensinar.

## Consequências

### Positivas
- Onda focada e testável; entrega um loop de valor completo e demonstrável em homologação.
- Instrumenta a north-star desde a Onda 1 (base para metas reais).

### Negativas / trade-offs aceitos
- Traz a camada de pagamento para a Onda 1. Mitigação: no MVP o **crédito de saldo** é o
  essencial; **saque/resgate** pode entrar simplificado (ex.: PIX manual/assistido) e evoluir —
  decisão fina fica no EPIC-003 e no ADR-005.
- Gamificação fica fora — risco de menor recorrência no piloto; aceitável para a Onda 1.

### Para o time técnico
- ADRs que esta decisão demanda (abrem via spike no início dos épicos): ADR-001 (ingestão/modelo
  canônico), ADR-002 (extração SEFAZ-SP), ADR-003 (deduplicação), ADR-005 (pagamento/PIX),
  ADR-006 (LGPD/anonimização de CPF).
- Impacto em épicos: EPIC-000 a EPIC-003 (ver `roadmap/current-wave.md`).

## Sinais de revisão

- Se o spike de extração (ADR-002) revelar que o scraping SEFAZ-SP é inviável no prazo da onda,
  reavaliar escopo (a coleta é o núcleo — sem ela a onda não fecha).
- Se a camada de pagamento se mostrar cara demais para a onda, mover saque para a Onda 2 mantendo
  só o crédito de saldo.
