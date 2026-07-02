---
story_id: STORY-012
slug: instrumentacao-north-star
title: Instrumentação da north-star — cupons válidos, únicos e novos por semana
epic_id: EPIC-002
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-012 — Instrumentação da north-star

> **Para o agente que vai executar:** leia por inteiro. O painel é interno (`requires_design: true`
> leve — compõe DS existente). O foco é a **métrica correta**, não uma tela sofisticada.

## Contexto (por que esta estória existe)

A onda existe para gerar o **primeiro baseline** da north-star: cupons **válidos, únicos e novos por
semana**. Sem instrumentar a contagem e mostrá-la, não sabemos se a coleta está funcionando nem
fechamos o piloto.

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- Documentos a ler ANTES: `docs/project-state/product/north-star.md`; `docs/visao.md` §6.4 (dedup);
  ADR-001/003 (modelo e dedup — base da contagem); componentes do DS.

## O quê (objetivo desta estória)

Instrumentar e expor a métrica: uma **rota/painel interno** que mostra a contagem de cupons
válidos-únicos-novos por semana, além da **taxa de sucesso de envio** (enviados → válidos-únicos-novos).

## Por quê (valor para o usuário)

É o instrumento de medição da onda — o que transforma "coletamos cupons" em "sabemos quanto e com que
qualidade", permitindo decidir o futuro do produto com dado.

## Critérios de aceite

- [ ] **CA-1:** Existe uma contagem, por semana, de cupons **válidos, únicos e novos** derivada do
      modelo canônico (dedup por chave), correta em testes com cenários conhecidos.
- [ ] **CA-2:** Existe a **taxa de sucesso de envio** (enviados → válidos-únicos-novos) calculada e visível.
- [ ] **CA-3:** Rota/painel **interno** em homologação exibe as métricas, compondo componentes do DS.
- [ ] **CA-4:** A contagem não conta em dobro (idempotente com a dedup da STORY-010) e ignora inválidos.
- [ ] **CA-5:** Métricas básicas de saúde/uso ficam registradas (log/telemetria) sem vazar dado pessoal.

## Fora de escopo

- Dashboards para clientes B2B (Quantah Intelligence) — evolução.
- Gancho "pagou mais/menos que a média" (Onda 2); cashback (EPIC-003).

## Padrões de qualidade exigidos

Segue `quality-standards.md`. Testes da lógica de contagem (feliz + duplicado + inválido); a11y mínima
no painel; sem valor cru fora dos tokens; sem vazamento de dado pessoal em métricas/logs.

## Dependências

- **Bloqueada por:** STORY-010 (persistência/classificação válido-único-novo).
- **Bloqueia:** STORY-013 (validação — item da métrica).

## Decisões já tomadas (não as reabra)

- ADR-001/003; definição da north-star (`product/north-star.md`). Só SP.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; lógica de contagem testada em cenários conhecidos.
- [ ] Painel interno acessível em homologação; pipeline verde.
- [ ] IDR se houve decisão técnica; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Dúvida sobre a **definição** da métrica → `blocked` + escalar ao PO
(é decisão de produto).

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 

### Descobertas
- 

### Bloqueios encontrados
- 

### Links de evidência
- 
