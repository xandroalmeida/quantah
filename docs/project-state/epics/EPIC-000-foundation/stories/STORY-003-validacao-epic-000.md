---
story_id: STORY-003
slug: validacao-epic-000
title: Validação final do EPIC-000 (Foundation)
epic_id: EPIC-000
sprint_id: null
type: validation
target_role: validador
requires_design: false
status: ready
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-003 — Validação final do EPIC-000

> **Para o agente validador:** esta é a última estória do épico. Emita um veredito independente
> em ambiente real (homologação), não confie em "passou no meu ambiente". Preencha o relatório
> em `epics/EPIC-000-foundation/validation/report.md`.

## Contexto (por que esta estória existe)

O EPIC-000 só é considerado concluído após validação independente de que a fundação está de pé:
hello-world em homologação com o tema do DS, pipeline verde e ambiente automatizado. Esta
estória fecha o épico.

- Épico: `epics/EPIC-000-foundation/epic.md`
- Checklist: `epics/EPIC-000-foundation/validation/checklist.md`
- Estórias a validar: STORY-000, STORY-001, STORY-002.

## O quê (objetivo desta estória)

Executar a bateria de validação do EPIC-000 em homologação e produzir o relatório com veredito
`approved` ou `rejected` (com motivos acionáveis).

## Por quê (valor para o usuário)

Garante que a base sobre a qual os épicos de valor sobem é real e observável — evita "fundação de
papel" que desmorona no primeiro épico de coleta.

## Critérios de aceite

- [ ] **CA-1:** Hello-world acessível na URL de homologação, respondendo 200, com paleta e
      tipografia (Inter) do DS aplicadas.
- [ ] **CA-2:** Pipeline CI/CD verde e deploy automatizado confirmados (sem passo manual).
- [ ] **CA-3:** Ambiente de dev sobe com um comando a partir de clone limpo.
- [ ] **CA-4:** Cobertura e E2E das estórias de implementação atendem `quality-standards.md`.
- [ ] **CA-5:** Relatório de validação preenchido com veredito e evidências.

## Fora de escopo

- Validar features de produto (não existem ainda neste épico).

## Padrões de qualidade exigidos

Aplica integralmente `docs/skills/po/references/quality-standards.md`. O validador não "conserta"
— ele reporta; correções voltam às estórias de origem como `in_progress`.

## Dependências

- **Bloqueada por:** STORY-000, STORY-001, STORY-002 (todas `done`/`in_review`).
- **Bloqueia:** fechamento do EPIC-000 e início efetivo do EPIC-001.

## Decisões já tomadas (não as reabra)

- PDR-002 (escopo), ADR-000 (stack), PDR-001/DDR-001/DDR-002 (design).

## Definição de Pronto (DoD)

- [ ] Checklist do épico percorrido; relatório `approved` (ou `rejected` com motivos).
- [ ] `epics/EPIC-000-foundation/validation/report.md` preenchido.
- [ ] `index.json` atualizado: EPIC-000 → `done` se aprovado; `epics[].validation_report` apontando o relatório.
- [ ] Notas do agente preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md` e a skill `validador`. Veredito independente
em ambiente real; nada de aprovar por confiança.

## Notas do agente (preenchido durante/após execução)

### Veredito
- 
### Evidências verificadas
- 
### Itens reprovados / a corrigir
- 
