---
story_id: STORY-010
slug: validacao-sefaz-dedup-persistencia
title: Validação SEFAZ-SP, deduplicação por chave e persistência no modelo canônico
epic_id: EPIC-002
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: L
---

# STORY-010 — Validação SEFAZ + deduplicação + persistência

> **Para o agente que vai executar:** leia por inteiro. Esta é a estória de **núcleo/regra de negócio**
> do épico — cobertura de teste mais alta se aplica (ver Padrões). Siga as ADRs da STORY-008.

## Contexto (por que esta estória existe)

Capturar a chave não basta: o cupom precisa ser **validado** contra a SEFAZ-SP, **deduplicado** pela
chave de 44 dígitos e **persistido** no modelo canônico. É o que transforma um envio em dado
**válido, único e novo** — a definição da north-star.

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- Documentos a ler ANTES: `docs/visao.md` §6 (extração, adaptador SP, dedup §6.4); ADR-001 (modelo),
  ADR-002 (extração resiliente), ADR-003 (dedup/validação).

## O quê (objetivo desta estória)

Implementar o pipeline servidor: **extrair** o cupom da SEFAZ-SP (adaptador SP, com fila de
reprocessamento em falha) → **validar** → **deduplicar** por chave → **persistir** no modelo canônico,
marcando o cupom como válido/único/novo.

## Por quê (valor para o usuário)

Sem validação e dedup confiáveis, a base tem lixo e contagem dupla — o dado perde valor e o incentivo
vira fraude. Esta estória é o que garante qualidade do dado.

## Critérios de aceite

- [ ] **CA-1:** Dada uma chave válida de SP, o sistema extrai o cupom via adaptador SP e persiste os
      campos do modelo canônico (ADR-001).
- [ ] **CA-2:** **Deduplicação idempotente:** reenviar a mesma chave **não** cria segundo registro nem
      conta em dobro; o resultado sinaliza "já existente" (sem falso-positivo/negativo nos testes).
- [ ] **CA-3:** Falha/instabilidade da SEFAZ não perde o envio: entra em **fila de reprocessamento**
      (ADR-002) e é retomada; estados do cupom (pendente/válido/falho) são persistidos.
- [ ] **CA-4:** Validação rejeita chave malformada/não-SP com erro tratado (sem persistir lixo).
- [ ] **CA-5:** Um cupom persistido é classificável como **válido, único e novo** — a base para a
      contagem da north-star (instrumentada na STORY-012).

## Fora de escopo

- UI de captura (STORY-009); anonimização de CPF (STORY-011 — mas **não persista CPF em claro** aqui);
  painel da north-star (STORY-012); cashback (EPIC-003).
- Adaptadores de outros estados; GTIN/matching (ADR-004).

## Padrões de qualidade exigidos

Segue `quality-standards.md`. **Núcleo de regra de negócio:** cobertura ≥ **98%** na lógica de
validação/dedup; ≥80% no restante novo. Testes cobrem feliz + duplicado + falha de extração +
malformado. Migrações reversíveis e testadas. Nenhum segredo/credencial de acesso versionado.

## Dependências

- **Bloqueada por:** STORY-008 (ADR-001/002/003).
- **Bloqueia:** STORY-011 (anonimização atua sobre a persistência), STORY-012 (conta o resultado),
  STORY-013 (validação).

## Decisões já tomadas (não as reabra)

- ADR-000/007/008; ADR-001/002/003. Dedup por chave de 44 dígitos (visao §6.4). Só SP.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; cobertura de núcleo ≥98% comprovada.
- [ ] Migrações reversíveis testadas em homologação; fila de reprocessamento exercida em teste.
- [ ] Pipeline verde; comportamento verificável em homologação.
- [ ] IDR se houve decisão técnica; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Falta/conflito de ADR → `blocked` + escalar ao Arquiteto/PO.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 

### Descobertas
- 

### Bloqueios encontrados
- 

### Links de evidência
- 
