---
story_id: STORY-035
slug: validade-cupom-parametrizavel
title: Validade parametrizável na coleta — rejeitar cupom com mais de N dias (default 7)
epic_id: EPIC-007
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: S
---

# STORY-035 — Validade parametrizável do cupom

> **Para o agente que vai executar:** leia por inteiro. Regra de negócio na **ingestão** (não é tela nova).
> O limite deve ser **parametrizável** (config), não um número mágico no código.

## Contexto (por que esta estória existe)

A coleta hoje aceita cupons independentemente de quão antigos são. O negócio quer priorizar dado **fresco**
(a north-star é sobre cupons novos) e evitar coleta de notas muito antigas. Precisamos **rejeitar cupons com
mais de N dias** desde a emissão — com **N configurável** (default **7**), para ajustar sem deploy de código.
O cupom já guarda `data_emissao` (ADR-001) e a ingestão já tem ponto de rejeição com motivo
(`ResultadoIngestao`/`IngestaoCupomService`, ver STORY-009/010).

- Épico: `epics/EPIC-007-refinamento-experiencia-b2c-mobile/epic.md`
- Documentos a ler ANTES: ADR-001 (modelo/`data_emissao`), STORY-010 (validação SEFAZ/dedup/persistência e
  motivos de rejeição), `docs/skills/stacks/laravel/SKILL.md`.
- Arquivos-âncora: `app/Domain/Coleta/IngestaoCupomService.php`, `app/Domain/Coleta/ResultadoIngestao.php`,
  `app/Http/Controllers/ColetaController.php`, `config/` (novo `config/coleta.php`), `.env`/`config('...')`.

## O quê (objetivo desta estória)

1. Introduzir um **parâmetro de janela de validade** — ex.: `config('coleta.janela_dias')`, default `7`,
   sobreponível por env — em vez de valor fixo.
2. Na **ingestão/validação**, comparar `data_emissao` com "agora" (America/Sao_Paulo): se exceder a janela,
   **rejeitar** o cupom com um **motivo específico** (ex.: `cupom_expirado`/"Cupom fora do prazo de N dias").
3. Superfície o motivo na confirmação da coleta (microcopy pt-BR do DS), sem quebrar o fluxo.

## Por quê (valor para o usuário)

Deixa claro o "prazo para valer" (expectativa honesta) e mantém a base **fresca** — qualidade do dado que
sustenta a north-star.

## Critérios de aceite

- [ ] **CA-1:** O limite de dias vem de **configuração** (`config('coleta.janela_dias')`), com **default 7**
      e sobreponível por env; **nenhum número mágico** no código de regra.
- [ ] **CA-2:** Cupom com `data_emissao` **mais antigo** que a janela é **rejeitado** na ingestão, com
      **motivo** próprio persistido/retornado (não confundível com dedup ou chave inválida).
- [ ] **CA-3:** Cupom **dentro** da janela segue o fluxo normal (validação/persistência/cashback como hoje).
- [ ] **CA-4:** A borda de coleta mostra **mensagem pt-BR** clara ao rejeitar por prazo (microcopy do DS),
      ancorada como as demais rejeições (STORY-009), sem banner global quebrado.
- [ ] **CA-5:** Fronteira testada (limite exatamente em N dias) e comportamento definido (documentar se N
      dias é inclusivo/exclusivo); fuso America/Sao_Paulo respeitado.

## Fora de escopo

- Tela de configuração/admin do parâmetro (é config/env nesta fase).
- Mudar dedup, validação de chave ou regras de cashback.
- Reprocessar cupons já coletados sob a regra nova.

## Padrões de qualidade exigidos

Segue `quality-standards.md`. Testes de unidade/feature cobrindo dentro/fora da janela e a fronteira; motivo
de rejeição distinto; parâmetro lido de config. Cobertura ≥80% no código novo.

## Dependências

- **Bloqueada por:** EPIC-002 (ingestão/validação) — já entregue.
- **Bloqueia:** STORY-037 (validação do épico).
- **Decisões:** possível **IDR** curto se a semântica da janela (inclusiva/exclusiva, base horária) ou o
  motivo de rejeição merecer registro. Sem ADR novo.

## Decisões já tomadas (não as reabra)

- ADR-001 (`data_emissao` no modelo) e o pipeline de ingestão/rejeição (STORY-010) valem. Fuso do projeto:
  America/Sao_Paulo.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; testes de fronteira verdes.
- [ ] Parâmetro em `config/coleta.php` (default 7) documentado; motivo de rejeição pt-BR.
- [ ] Pipeline verde; IDR se houve decisão relevante; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Sem tela nova. Decisão de semântica da regra → IDR.

## Notas do agente (preenchido durante/após execução)

> _(a preencher)_

### Decisões tomadas
### Descobertas
### Bloqueios encontrados
### Links de evidência
