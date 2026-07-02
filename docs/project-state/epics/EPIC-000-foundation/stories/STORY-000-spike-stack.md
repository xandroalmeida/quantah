---
story_id: STORY-000
slug: spike-stack
title: Spike de stack — validar o esqueleto rodando e a viabilidade de leitura do QR da NFC-e
epic_id: EPIC-000
sprint_id: null
type: spike
target_role: arquiteto
requires_design: false
status: ready
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-000 — Spike de stack

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. É um
> **spike** — o entregável é conhecimento + evidência, não feature. Registre achados nas "Notas
> do agente" e, se descobrir uma decisão arquitetural durável, registre-a em ADR.

## Contexto (por que esta estória existe)

O ADR-000 ratificou a stack (Laravel + Inertia/React + PostgreSQL, PWA) e o `app/` já foi
scaffoldado. Antes de investir nas estórias de pipeline e nas de coleta, precisamos **provar que
o esqueleto sobe ponta a ponta** e que a peça de maior risco do produto — **ler os dados do
cupom a partir do QR/URL da NFC-e da SEFAZ-SP** — é viável na prática. Isso reduz o risco de
descobrir tarde que o scraping é inviável.

- Épico: `epics/EPIC-000-foundation/epic.md`
- Documentos a ler ANTES:
  - `docs/project-state/decisions/adr/ADR-000-stack-default.md`
  - `docs/visao.md` §6 (mecânica de coleta, chave de acesso de 44 dígitos, URL do QR)
  - `docs/skills/stacks/laravel/SKILL.md` e `docs/skills/stacks/inertia-react/SKILL.md`

## O quê (objetivo desta estória)

Validar que a stack scaffoldada roda 100% local ponta a ponta e produzir uma **prova de conceito
descartável** que, dada a URL de uma NFC-e de SP, obtém os dados estruturados do cupom
(estabelecimento, itens, preços, chave de acesso).

## Por quê (valor para o usuário)

De-risca o núcleo da north-star: sem leitura confiável do cupom, não há coleta nem base de
preços. Confirmar viabilidade agora evita retrabalho caro no EPIC-002.

## Critérios de aceite

Spike: os CAs são de **evidência e recomendação**, não de código de produção.

- [ ] **CA-1:** A stack sobe 100% local com um comando (app + Postgres) e serve uma rota Inertia
      no navegador — evidência (print/log) nas Notas do agente.
- [ ] **CA-2:** A partir da URL do QR de ao menos uma NFC-e real de SP, a PoC extrai:
      chave de acesso (44 dígitos), estabelecimento, ao menos um item com descrição e preço —
      evidência anexada.
- [ ] **CA-3:** Documentado o grau de fragilidade observado (captcha, layout, bloqueio) e o
      caminho recomendado para o EPIC-002 (o que vira ADR-001/002/003).
- [ ] **CA-4:** Se a extração se mostrar inviável no prazo, isso está explicitado com alternativas
      (ex.: fonte oficial/credenciamento) — para o PO reavaliar escopo (PDR-002 § sinais de revisão).

## Fora de escopo

- Construir o módulo de ingestão de produção (é EPIC-002).
- Deduplicação, anti-fraude, anonimização definitivos (EPIC-002).
- Qualquer UI de coleta.

## Padrões de qualidade exigidos

Spike não entrega código de produção; o código da PoC é **descartável** e não precisa das metas
de cobertura. Mas: nenhum segredo commitado, nenhum dado pessoal real persistido, e a PoC roda
contra dados de teste. Padrões plenos em `docs/skills/po/references/quality-standards.md`.

## Dependências

- **Bloqueada por:** nada (é a primeira estória).
- **Bloqueia:** STORY-001, STORY-002 e todo o EPIC-002.
- **Pré-requisitos de ambiente:** `app/` scaffoldado (já existe); Docker/Postgres local.

## Decisões já tomadas (não as reabra)

- ADR-000 (stack) → `decisions/adr/ADR-000-stack-default.md`
- PDR-002 (escopo da onda) → `decisions/pdr/PDR-002-escopo-onda-1.md`

## Liberdade técnica do agente

Como Arquiteto/spike: você decide a abordagem da PoC de extração e o que virá a ser ADR. Você
**não** decide escopo de produto (PO) nem fecha os ADRs de ingestão como aceitos sem aprovação
humana — registre-os como `proposed` se avançar a decisão.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-4 evidenciados nas Notas do agente.
- [ ] Recomendação clara para o EPIC-002 (esboço de ADR-001/002/003, status `proposed` se aplicável).
- [ ] `index.json` atualizado: status = `done` (spike não passa por validador de épico).
- [ ] Notas do agente preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`: ao iniciar, `status: in_progress` +
`owner_agent` + `index.json`; ao terminar, preencher Notas do agente, `status: done`, atualizar
`index.json`. Decisão arquitetural durável → ADR (`decisions/adr/`), não IDR.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 

### Descobertas
- 

### Bloqueios encontrados
- 

### Evidências
- Stack local:
- Extração do cupom:
