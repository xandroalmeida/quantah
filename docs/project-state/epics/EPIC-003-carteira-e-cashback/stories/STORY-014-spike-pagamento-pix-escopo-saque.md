---
story_id: STORY-014
slug: spike-pagamento-pix-escopo-saque
title: Spike de arquitetura — pagamento/PIX + KYC mínimo e escopo do saque (ADR-005)
epic_id: EPIC-003
sprint_id: null
type: spike
target_role: arquiteto
requires_design: false
design_screen_id: null
status: done
owner_agent: claude-arquiteto-story014
created_at: 2026-07-03
updated_at: 2026-07-03
status_note: concluída — ADR-005 accepted por Alexandro (2026-07-03)
estimated_session_size: L
---

# STORY-014 — Spike de pagamento/PIX e escopo do saque

> **Para o agente que vai executar (arquiteto):** este é um spike de decisão. O produto é o
> **ADR-005** e uma recomendação de escopo do saque para o MVP — não código de produção.

## Contexto (por que esta estória existe)

O EPIC-003 credita saldo em reais e precisa de um **caminho de resgate**. O custo e a complexidade
da camada de pagamento (PIX + KYC de saque) são o maior risco da onda. A `docs/visao.md` §5.1/§8.1
trata cashback como remuneração livre (sem gate), mas o **saque** envolve pagamento a terceiros e
identidade. Antes de implementar carteira e resgate, precisamos decidir a arquitetura e o escopo
mínimo viável do saque.

## Objetivo do spike

Produzir o **ADR-005** decidindo: provedor/abordagem de PIX (ex.: PSP vs. banco parceiro), nível de
KYC mínimo para saque, e o **escopo do resgate no MVP** — automatizado vs. **PIX assistido/manual**
(a visão já admite o simplificado). A recomendação define o contorno da STORY-017.

## Critérios de aceite (alto nível — refinar na execução)

- ADR-005 `accepted` cobrindo: opções consideradas de PIX/PSP, custo estimado por saque, KYC
  mínimo exigido, riscos (fraude, chargeback, compliance) e decisão de escopo do saque no MVP.
- Recomendação explícita de escopo para a STORY-017 (automatizado x assistido) com gatilho para
  revisitar na Onda 2.
- Modelo de dados mínimo da carteira/transações esboçado o suficiente para destravar STORY-015/016.

## Fora de escopo

- Implementação da integração de pagamento (vai para STORY-017 conforme o ADR).
- KYC completo/automação de saque, se o ADR indicar custo alto para a onda.

## Dependências

- **Bloqueado por:** — (pode rodar em paralelo ao início do épico).
- **Bloqueia:** STORY-017 (resgate/saque depende do escopo decidido aqui).

## Definição de pronto

ADR-005 `accepted` e indexado; escopo do saque recomendado; `index.json` = `done`; Notas do agente
preenchidas.

## Notas do agente

**2026-07-03 — Arquiteto (claude-arquiteto-story014):** produzido o **ADR-005** em `proposed`
(`decisions/adr/ADR-005-escopo-saque-pix-assistido-kyc-carteira.md`) e indexado no `index.json`.

- **Decisão recomendada:** saque no MVP via **PIX assistido** (solicitação no app → operação manual no
  backoffice com KYC mínimo: CPF + chave PIX do tipo CPF + comprovante), com a **automação via PSP
  deferida para a Onda 2** sob gatilho explícito (volume > ~30 saques/semana ou north-star validada).
  Custo ~zero, sem lead time de PSP, reversível para automatizar sem retrabalho.
- **KYC mínimo:** CPF do titular + chave PIX do tipo CPF (titularidade por construção) + base
  legal/consentimento registrados + valor mínimo de saque. PII de pagamento na **base segregada**
  (ADR-006), nunca na base analítica.
- **Modelo mínimo esboçado** (destrava STORY-015/016): `carteiras` (saldo em centavos, cache
  reconciliável, ≥ 0) + `carteira_transacoes` (ledger append-only, fonte da verdade do saldo, crédito
  idempotente por cupom) + `saques` (máquina de estados solicitado→…→pago/rejeitado; estorno no ledger).
- **Contorno da STORY-017:** resgate = solicitação (reserva via ledger) + tela de backoffice de operação
  + máquina de estados + estorno. Nenhuma integração de PSP nesta onda.
- **2026-07-03 — ADR-005 aceito** por Alexandro → `accepted`. STORY-014 fechada (`done`). Destrava a
  STORY-017 (resgate/saque) e entrega o modelo de carteira/ledger para STORY-015/016.
