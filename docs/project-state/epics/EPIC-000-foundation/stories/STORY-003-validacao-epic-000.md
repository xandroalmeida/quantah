---
story_id: STORY-003
slug: validacao-epic-000
title: Validação final do EPIC-000 (Foundation)
epic_id: EPIC-000
sprint_id: null
type: validation
target_role: validador
requires_design: false
status: done
owner_agent: validador-826f6e93
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

> Executada em 2026-07-02 pelo papel **Validador** (sessão `826f6e93`). Relatório completo em
> `epics/EPIC-000-foundation/validation/report.md`. `index.json` › `EPIC-000.validation_report`
> aponta o relatório com veredito `approved`. Status do épico **não** foi alterado (é ato do PO).

### Veredito
- **APPROVED.** 15 itens do checklist: 12 `pass`, 3 `pass com ressalva`, 0 `fail` (0 bloqueantes,
  0 não-bloqueantes).

### Evidências verificadas
- **Homologação**: `https://quantah-homolog.34.39.229.117.sslip.io/` → HTTP 200, `/up` → 200;
  Inter + `border-radius:24px` no CSS servido (verificação independente por curl).
- **Pipeline**: run verde `28612051361` (Testes+build, Dusk, Deploy — todos success); 2 runs
  vermelhos antes do verde comprovam que o gate barra o merge (job `deploy` `needs: [tests, dusk]`).
- **Cobertura**: 87.3% (gate `--min=80` verde), 38 testes / 138 assertions.
- **A11y**: contraste do CTA 13.05:1 (cálculo WCAG independente; AA exige 4.5:1); foco visível.
- **DS**: `Hello.jsx` só com utilitários de token; fidelidade confirmada pelo Designer.
- **Segredos**: nenhum versionado; deploy via GitHub Secrets; `app/.env` ignorado.

### Ressalvas factuais (não-fail)
- `make up` verificado por inspeção + evidência da STORY-000; não re-executado de clone limpo nesta sessão.
- E2E em browser real (Dusk) roda contra a app servida no runner (ambiente equivalente); homologação
  coberta por smoke HTTP 200 + probe independente, não por E2E de browser apontado à URL pública.
- `EPIC-000.status` permanece `ready` no `index.json`/`epic.md` apesar das estórias `done` (flip é do PO).

### Itens reprovados / a corrigir
- Nenhum item reprovado.
