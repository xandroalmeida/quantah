---
id: WISH-006
slug: dashboard-de-observabilidade-dedicado
title: Dashboard de observabilidade dedicado (RED) além do /up
status: triaged
origin: Validação EPIC-005 (relatório, ressalva R4 não-bloqueante) — 2026-07-05
tags: ["debito", "observabilidade", "operacao", "qualidade"]
spec_link: null
rejected_reason: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# WISH-006 — Dashboard de observabilidade dedicado (RED) além do /up

## One-liner

A saúde da aplicação hoje é observável só por endpoint básico (`/up`) e por um controller interno de
métricas; falta um **dashboard dedicado** (taxa/erros/latência — RED) para operar o piloto com visão.

## Problema / necessidade

A validação do EPIC-005 confirmou saúde básica via `/up` e `Interno/MetricasController`, mas **sem painel
de observabilidade dedicado** nesta fase. Para rodar o **piloto** da WAVE-2026-02 com usuários reais, operar
às cegas (sem visão de taxa de requisições, erros e latência ao longo do tempo) é risco: uma regressão de
disponibilidade ou um pico de erro na jornada B2C passaria despercebido até um usuário reclamar. Esta é a
dívida transversal de "observabilidade RED" já listada como risco da onda.

## Valor esperado

Dá visão operacional para sustentar o piloto e detectar problemas antes do usuário — condição para colher um
**primeiro baseline** confiável da north-star. Liga ao princípio de qualidade/automação e ao objetivo da
onda (habilitar o piloto), não a uma métrica de produto direta.

## Referências

- Origem: `docs/project-state/epics/EPIC-005-portas-de-entrada/validation/report.md` (ressalva R4).
- Saúde atual: endpoint `/up`; `Interno/MetricasController` (EPIC-002/003).
- Onda: `docs/project-state/roadmap/current-wave.md` — "Riscos da onda" (observabilidade RED).
- Padrões: `docs/skills/po/references/quality-standards.md` (automação/observabilidade).

## Restrições conhecidas

- LGPD: painel operacional não deve expor PII (métricas agregadas, não dados de usuário).
- Integração externa: escolha da ferramenta (stack de métricas/log/tracing, hospedagem) é decisão de
  Arquiteto/Programador na promoção; preferir o que integra com a IaC em `infra/gcp/`.
- Escopo a decidir na triagem/spec: RED mínimo (taxa, erros, latência) para a jornada B2C e o loop de
  coleta/carteira/saque; alertas ficam para uma iteração seguinte se necessário.

## Notas / histórico

- `2026-07-05` — Captura inicial. Origem: validação EPIC-005 (R4, não-bloqueante). Status `triaged`:
  contexto entendido, sem compromisso de sprint. Reforça o risco de observabilidade RED da WAVE-2026-02.
