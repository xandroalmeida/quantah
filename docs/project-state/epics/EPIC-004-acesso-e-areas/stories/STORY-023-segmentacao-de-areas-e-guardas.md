---
story_id: STORY-023
slug: segmentacao-de-areas-e-guardas
title: Segmentação das 3 áreas (B2C/B2B/Backoffice) e guardas de acesso
epic_id: EPIC-004
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: ready
owner_agent: null
created_at: 2026-07-04
updated_at: 2026-07-04
estimated_session_size: M
---

# STORY-023 — Segmentação das 3 áreas e guardas

> **Para o agente que vai executar:** leia a estória inteira antes de começar. O contorno das áreas e das
> guardas foi decidido no ADR de acesso (STORY-019) — implemente conforme ele, reusando o RBAC (ADR-009).

## Contexto (por que esta estória existe)

Os três públicos compartilham hoje a mesma porta. O produto precisa de **áreas segmentadas**: o Coletador
(B2C, autenticado), o Backoffice Operacional (autenticado + RBAC, **sem CTA público**) e a área B2B
**reservada** (Quantah Intelligence — sem login nesta onda, mas com o lugar arquitetural pronto para a
captação de lead do EPIC-005 e para o futuro login B2B). Sem isso, não dá para ligar landing pages a
destinos certos nem evoluir cada face sem retrabalho.

- Épico: `epics/EPIC-004-acesso-e-areas/epic.md`
- Ler antes: ADR de acesso (STORY-019) — segmentação e guardas; ADR-009 (RBAC); PDR-003.

## O quê (objetivo desta estória)

Implementar a **segmentação das 3 áreas** com pontos de entrada distintos e **guardas** que isolam cada
uma: Coletador só acessa a área B2C; Backoffice tem entrada própria não anunciada, atrás de `auth` +
papel operacional; a área B2B fica reservada (sem login).

## Por quê (valor para o usuário)

Cada público entra pela porta certa e não vê o que não é dele — base de confiança e de organização do
produto, e pré-condição para as landings (EPIC-005) e a jornada (EPIC-006).

## Critérios de aceite

- [ ] **CA-1:** Existe separação clara de áreas no roteamento (B2C / B2B / Backoffice) conforme o ADR;
      cada área tem seu ponto de entrada.
- [ ] **CA-2:** Dado um Coletador autenticado (B2C), quando tenta acessar uma rota de Backoffice, então é
      barrado (403/redirect), reusando o RBAC do ADR-009 (`can:operar-saques` e afins).
- [ ] **CA-3:** O Backoffice tem **entrada própria, não anunciada** (sem CTA/link público); um usuário sem
      papel operacional não a alcança.
- [ ] **CA-4:** A área **B2B está reservada** (rota/namespace existe, sem login e sem features) — pronta
      para receber a captação de lead do EPIC-005 sem retrabalho estrutural.
- [ ] **CA-5:** E2E em browser real cobre a **barreira entre áreas**: usuário de uma área é barrado da
      outra (incl. o 403 do Backoffice para não-operador), com mensagens em pt-BR.

## Fora de escopo

- **Login/conta B2B** (fora da onda). A **captação de lead** B2B (formulário) — EPIC-005.
- A **home-hub / destino pós-login** do Coletador — EPIC-006 (aqui só a fronteira de áreas).
- Rebrand das telas de auth (STORY-021) e i18n (STORY-020).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`: cobertura ≥ 80%; as **guardas/autorização** no
padrão de núcleo (≥ 98%); **E2E** da barreira entre áreas (CA-5); **pt-BR**. Sem código não testado.

## Dependências

- **Bloqueada por:** STORY-019 (ADR de acesso/segmentação).
- **Bloqueia:** — (habilita EPIC-005 e EPIC-006; STORY-024 valida). Pode correr em paralelo a 020–022.
- **Pré-requisitos de ambiente:** homologação operante; RBAC (ADR-009) já vigente da Onda 1.

## Decisões já tomadas (não as reabra)

- ADR-009 (RBAC). ADR de acesso (STORY-019) — segmentação e guardas. PDR-003 (3 áreas; B2B sem login).

## Liberdade técnica do agente

Você decide a estrutura de rotas/middleware/testes dentro do ADR. Não decide o desenho das áreas (é do
ADR) nem os CAs. Se faltar decisão arquitetural, **pare e registre**.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam; unitários + E2E verdes; coberturas exigidas.
- [ ] Pipeline verde; deploy de homologação verificado (barreiras de área ativas).
- [ ] IDR registrado se houve decisão técnica relevante.
- [ ] `index.json` = `done`; "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
### Descobertas
### IDRs criados
### Cobertura final
### Links de evidência
