---
epic_id: EPIC-002
slug: coleta-de-cupom
title: Coleta de cupom — do QR da NFC-e ao dado válido, único e novo
wave: WAVE-2026-01
status: ready
owner_role: po
created_at: 2026-07-02
updated_at: 2026-07-02
target_completion: 2026-09-15
---

# EPIC-002 — Coleta de cupom

## Por que existimos (problema do usuário)

Este é o **coração do Quantah**. O Colaborador recebe uma NFC-e a cada compra e hoje esse dado se
perde. Ele quer enviar o cupom em segundos e ter certeza de que "contou". Sem uma coleta de
fricção mínima que capture, valide e consolide o cupom, não existe base de preços — e a
north-star (cupons válidos, únicos e novos por semana) não sai do zero.

## Resultado esperado (outcome)

Ao fim deste épico, o **Colaborador escaneia ou compartilha o QR Code da NFC-e**, vê a
confirmação do cupom capturado e recebe o feedback "cupom aceito" — e esse cupom entra na base
como dado **válido, único e novo** (deduplicado por chave de acesso, validado contra a SEFAZ-SP).

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária (north-star): cupons válidos, únicos e novos coletados por semana > 0 em
  homologação, com um piloto fechado gerando baseline.
- Métrica de apoio: taxa de sucesso de envio (enviados → válidos-únicos-novos) medida e visível.
- Métrica de qualidade: deduplicação sem falso-positivo/negativo nos testes; extração com fila de
  reprocessamento; anonimização de CPF na coleta (LGPD).

## Entregável visível no fim do épico

- [ ] Fluxo de coleta (escanear/compartilhar → confirmar → "cupom aceito") em homologação, mobile.
- [ ] Cupom enviado é validado contra a SEFAZ-SP, deduplicado por chave de 44 dígitos e persistido
      no modelo canônico.
- [ ] CPF, quando presente na nota, é anonimizado na coleta.
- [ ] Painel/rota interna que mostra a contagem da north-star (cupons válidos-únicos-novos/semana).

## Fora de escopo (explicitamente)

- Cashback/crédito de saldo — isso é EPIC-003 (aqui o cupom entra; o crédito vem no próximo).
- Adaptadores de outros estados (só SP nesta onda).
- Matching de produtos entre lojas / GTIN (ADR-004) — evolução, fora da Onda 1.
- Gancho "você pagou mais/menos que a média" — Onda 2.
- Sorteio (gate desligado, fora desta onda).

## Referências da especificação

- `docs/visao.md` §6 (mecânica de coleta), §7 (LGPD) — fonte enquanto a spec durável não existe.
- `docs/especificacao/` — a consolidar (glossário, domínio do cupom, fluxo de coleta).
- ADRs a criar via spike (ver Dependências).

## Dependências

- **Bloqueia:** EPIC-003 (cashback credita sobre o cupom válido).
- **Bloqueado por:** EPIC-000 (ambiente) e EPIC-001 (componentes de UI).
- **Decisões arquiteturais necessárias (abrir spikes no início):**
  - ADR-001 — módulo de ingestão + modelo canônico do cupom.
  - ADR-002 — extração resiliente SEFAZ-SP (fila de reprocessamento, captcha, monitoramento).
  - ADR-003 — deduplicação e validação por chave de acesso.
  - ADR-006 — anonimização de CPF e segregação de bases (LGPD).

## Estórias

Decomposto em 2026-07-02 (PO). Começa pelo spike de arquitetura (produz as ADRs) antes das estórias
de implementação. Checklist de validação (`validation/checklist.md`) autorado já nesta decomposição.

- [ ] **STORY-008** (spike) — ingestão + modelo canônico + extração SP + dedup + LGPD → ADR-001/002/003/006
      (target_role: arquiteto) · `in_review` (spike + 4 ADRs `proposed`; aguardando aceite humano p/ `done`)
- [ ] **STORY-009** — captura do QR (scan + compartilhar) e confirmação, mobile (`requires_design`) · `draft`
      (bloqueada por STORY-008)
- [ ] **STORY-010** — validação SEFAZ-SP + deduplicação por chave + persistência canônica · `draft`
      (bloqueada por STORY-008; núcleo de regra, cobertura ≥98%)
- [ ] **STORY-011** — anonimização de CPF na coleta (LGPD, ADR-006) · `draft` (bloqueada por STORY-008/010)
- [ ] **STORY-012** — instrumentação da north-star (válidos-únicos-novos/semana) · `draft` (bloqueada por STORY-010)
- [ ] **STORY-013** (validação) — validação final do épico · `draft` (bloqueada por STORY-008..012)

## Validação final

Critérios em `validation/checklist.md`. Relatório em `validation/report.md`.

**Definição de épico concluído:** fluxo de coleta demonstrável em homologação com cupom real de
SP virando dado válido-único-novo; north-star instrumentada; validação `approved`.

## Histórico

- 2026-07-02 — criado por PO (Fluxo A, WAVE-2026-01).
