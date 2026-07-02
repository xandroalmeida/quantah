---
story_id: STORY-008
slug: spike-ingestao-modelo-extracao
title: Spike de arquitetura — ingestão, modelo canônico do cupom, extração SEFAZ-SP, dedup e LGPD
epic_id: EPIC-002
sprint_id: null
type: spike
target_role: arquiteto
requires_design: false
design_screen_id: null
status: ready
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: L
---

# STORY-008 — Spike de arquitetura da coleta

> **Para o agente que vai executar (arquiteto):** este é um spike de decisão. O produto é
> **ADRs + um esqueleto verticalmente fino** que prova o caminho, não a feature completa. Registre
> as decisões como ADRs no índice; não implemente a coleta inteira aqui.

## Contexto (por que esta estória existe)

O EPIC-002 é o coração do Quantah, mas as decisões arquiteturais da coleta ainda não existem. Antes
de implementar captura, validação e persistência, o arquiteto precisa fixar: como o cupom é ingerido
e modelado, como extrair da SEFAZ-SP de forma resiliente, como deduplicar/validar pela chave de
44 dígitos, e como tratar CPF (LGPD). Sem isso, as estórias de implementação decidiriam arquitetura
sozinhas — proibido.

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- Documentos a ler ANTES: `docs/visao.md` §6 (mecânica de coleta e qualidade do dado) e §7 (LGPD);
  `docs/project-state/decisions/adr/ADR-000-stack-default.md` (stack vigente); `docs/skills/arquiteto/*`.

## O quê (objetivo desta estória)

Produzir as decisões arquiteturais da coleta como ADRs e um **spike vertical fino** (uma chave de
acesso de exemplo percorre ingestão → extração → modelo canônico → dedup) que comprove viabilidade:

- **ADR-001** — módulo de ingestão + **modelo canônico do cupom** (entidades/campos, chave natural).
- **ADR-002** — **extração resiliente SEFAZ-SP** (scraping do portal público; fila de reprocessamento,
  tratamento de captcha/instabilidade, monitoramento) com desenho de **adaptador por estado** (só SP na onda).
- **ADR-003** — **deduplicação e validação por chave de acesso** (44 dígitos; o que dá para validar só
  pela URL/chave — UF, ano/mês, CNPJ, modelo — antes de acessar o portal).
- **ADR-006** — **anonimização de CPF e segregação de bases** (LGPD): o que se guarda, o que se
  descarta/anonimiza, base legal.

## Por quê (valor para o usuário)

Decisões erradas aqui contaminam todo o épico (dado duplicado, extração frágil, exposição de dado
pessoal). O spike derisca o coração do produto antes do investimento de implementação.

## Critérios de aceite

- [ ] **CA-1:** ADR-001, ADR-002, ADR-003 e ADR-006 criados, com contexto/decisão/consequências, e
      **indexados** em `index.json` (`decisions.adr`), status `accepted` (ou `proposed` + o que falta).
- [ ] **CA-2:** Existe o **modelo canônico do cupom** documentado (entidades, campos mínimos, chave
      natural = chave de acesso), suficiente para as estórias 010/011 implementarem sem reabrir decisão.
- [ ] **CA-3:** Spike vertical demonstra, de ponta a ponta e para **uma** chave de exemplo de SP:
      parse da chave/URL → extração de ao menos os campos-âncora → normalização no modelo canônico →
      deduplicação idempotente (reenvio da mesma chave não duplica).
- [ ] **CA-4:** A estratégia de extração cobre **falha e reprocessamento** (o que acontece quando o
      portal falha/rate-limita) — desenhada na ADR-002 e exercida minimamente no spike.
- [ ] **CA-5:** Tratamento de CPF definido (ADR-006): o spike **não persiste CPF em claro**.

## Fora de escopo

- UI de coleta (STORY-009), fluxo completo de validação/persistência de produção (STORY-010),
  anonimização end-to-end (STORY-011), instrumentação (STORY-012).
- Adaptadores de outros estados; matching de produtos/GTIN (ADR-004, fora da onda).

## Padrões de qualidade exigidos

Segue `quality-standards.md`. O spike pode ser fino, mas o que ficar versionado tem teste; nenhuma
credencial/segredo commitado; nenhum CPF em claro persistido.

## Dependências

- **Bloqueada por:** EPIC-000 (ambiente) e EPIC-001 (UI) — `done`.
- **Bloqueia:** STORY-009, STORY-010, STORY-011, STORY-012 (todas dependem das ADRs/modelo).

## Decisões já tomadas (não as reabra)

- ADR-000 (stack Laravel + Inertia/React + PostgreSQL), ADR-007 (infra), ADR-008 (Dusk).
- Escopo: só SP nesta onda; dedup por chave de 44 dígitos é a salvaguarda principal (visao §6.4).

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 atendidos; ADRs criados e indexados.
- [ ] Spike vertical roda com teste; sem segredo/CPF em claro versionado.
- [ ] `index.json` atualizado: story `done`; ADRs em `decisions.adr`.
- [ ] "Notas do agente" preenchida (incluindo o que ficou como recomendação para 009–012).

## Protocolo do agente (obrigatório)

Siga `docs/skills/arquiteto/*` e `agent-task-format.md`. Decisão arquitetural → ADR (não IDR).
Se uma decisão for de produto (não técnica), **pare e escale ao PO**.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas (ADRs)
- 

### Descobertas
- 

### Bloqueios encontrados
- 

### Links de evidência
- 
