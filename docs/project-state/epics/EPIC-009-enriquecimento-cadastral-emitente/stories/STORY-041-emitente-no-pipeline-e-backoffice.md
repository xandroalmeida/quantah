---
story_id: STORY-041
slug: emitente-no-pipeline-e-backoffice
title: Emitente enriquecido no pipeline do cupom e visível no Backoffice
epic_id: EPIC-009
sprint_id: null
type: implementation
target_role: programador
requires_design: false
status: ready
owner_agent: null
created_at: 2026-07-06
updated_at: 2026-07-06
estimated_session_size: M
---

# STORY-041 — Emitente enriquecido no pipeline do cupom e visível no Backoffice

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. Se algo estiver
> ambíguo, registre em "Notas do agente" e pause em vez de adivinhar.

## Contexto (por que esta estória existe)

A STORY-040 entregou o serviço de enriquecimento CNPJ. Agora ele precisa entrar no fluxo real: todo
cupom validado/deduplicado dispara o enriquecimento do seu emitente, e o resultado fica visível no
Backoffice — a evidência observável do épico.

- Épico: `epics/EPIC-009-enriquecimento-cadastral-emitente/epic.md`
- Documentos canônicos a ler ANTES de codificar:
  - ADRs da STORY-039; `decisions/adr/ADR-001` (pipeline canônico do cupom), `ADR-003` (dedup)
  - `decisions/adr/ADR-009` (RBAC — Backoffice)
  - `docs/especificacao/design-system.md` (padrão visual das telas)

## O quê (objetivo desta estória)

Integrar o enriquecimento ao pipeline do cupom (disparo assíncrono pós-validação) e exibir os dados do
emitente no detalhe do cupom no Backoffice, incluindo o estado do enriquecimento (pendente / enriquecido
/ indisponível).

## Por quê (valor para o usuário)

Fecha o outcome do épico: o operador enxerga a categoria de cada estabelecimento, e o EPIC-010 passa a
ter o insumo fluindo automaticamente para todo cupom novo.

## Critérios de aceite

- [ ] **CA-1:** Dado um cupom que completa validação/deduplicação, então o enriquecimento do emitente é
      disparado automaticamente via fila, sem alterar a latência percebida do envio pelo Colaborador.
- [ ] **CA-2:** Dado um cupom cujo emitente já está no cache, então o vínculo é imediato, sem chamada
      externa.
- [ ] **CA-3:** No Backoffice, o detalhe do cupom exibe razão social, CNAE (código + descrição),
      município/UF e situação cadastral do emitente — em pt-BR, no padrão visual do DS.
- [ ] **CA-4:** Cupom com enriquecimento pendente ou indisponível exibe o estado correspondente de
      forma clara (sem erro, sem campo vazio mudo).
- [ ] **CA-5:** Cupons de um mesmo CNPJ compartilham o mesmo registro de emitente (sem duplicação).
- [ ] **CA-6:** E2E: enviar cupom → cupom listado no Backoffice com emitente enriquecido (ou estado
      pendente que resolve após processamento da fila).

## Fora de escopo

- Pontuação (EPIC-010); telas do Colaborador; tela de configuração do TTL (EPIC-012).
- Reprocessamento em massa de cupons antigos (se necessário, decisão do PO na abertura do EPIC-011).

## Padrões de qualidade exigidos

`docs/skills/po/references/quality-standards.md`: cobertura ≥ 80% (novo) / ≥ 98% (regra de negócio);
E2E do fluxo (CA-6) obrigatório; sem passos manuais.

## Dependências

- **Bloqueada por:** STORY-040.
- **Bloqueia:** STORY-042 (validação) e a decomposição do EPIC-010.
- **Pré-requisitos de ambiente:** homologação com fila operante.

## Decisões já tomadas (não as reabra)

- PDR-004 (regra 2); ADRs da STORY-039; ADR-001/003/009.

## Liberdade técnica do agente

Como na STORY-040: estrutura de código, testes e refatorações locais são suas; fonte/fila/cache e
critérios de aceite não. Sem ADR para algo necessário → pare e registre.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam, com testes (incluindo E2E CA-6).
- [ ] Coberturas atingidas; CI verde; deploy em homologação verificado.
- [ ] IDR registrado se aplicável; `index.json` e esta estória atualizados.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
-

### Descobertas
-

### Bloqueios encontrados
-

### IDRs criados
-

### Cobertura final
-

### Links de evidência
-
