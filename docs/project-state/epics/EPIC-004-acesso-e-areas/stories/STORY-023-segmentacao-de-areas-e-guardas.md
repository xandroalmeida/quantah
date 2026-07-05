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
status: in_progress
owner_agent: claude-code/programador
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

### Plano inicial (antes de codar)

**Documentos lidos:** STORY-023 (inteira), ADR-010 (acesso/OAuth/segmentação — eixo 3), ADR-009 (RBAC),
`agent-task-format.md`, skill `programador`, `_project.md`. Código lido: `routes/web.php`, `routes/auth.php`,
`AppServiceProvider` (Gate `operar-saques`), `User`/`Role`, `HandleInertiaRequests`, `AuthenticatedLayout.jsx`,
testes existentes (`GateOperadorTest`, `BackofficeSaquesHttpTest`, `BackofficeSaquesTest`, `AcessoColetadorTest`).

**Entendimento consolidado (minhas palavras):** o roteamento já isola o Backoffice (`/backoffice` atrás de
`auth` + `can:operar-saques`, ADR-009) e a nav do Coletador **não** aponta para lá (sem CTA público). O delta
desta estória é: (1) tornar a segmentação das 3 áreas **explícita** no roteamento (grupos de área comentados,
fail-secure); (2) **reservar** a área B2B `/intelligence` (pública, sem login, sem features), pronta para o lead
do EPIC-005; (3) fazer a barreira 403 do Backoffice renderizar em **pt-BR** (hoje cai na página default em inglês);
(4) cobrir a barreira entre áreas com Feature + E2E em browser real, mensagens pt-BR.

**Dúvidas:** nenhuma — o eixo 3 do ADR-010 fixa as guardas; escolhas abaixo são locais do Programador.

**Decisões locais (dentro do ADR-010, não o reabrem):**
- `/intelligence` = página Inertia **pública** placeholder (`Intelligence/Reservado`), sem auth e sem feature,
  reservando o namespace para a captação de lead do EPIC-005 (ADR-010 §3 B2B). Alternativa (redirect p/ landing)
  descartada: a landing do EPIC-005 ainda não existe.
- 403 pt-BR via `resources/views/errors/403.blade.php` (KISS, local, não transversal). Página de erro fora do
  Inertia é padrão aceitável do Laravel; evita mexer no handler global de exceções.

**Plano (bullets):**
1. Reservar B2B `/intelligence` (rota pública + página Inertia Reservado pt-BR).
2. 403 em pt-BR na barreira do Backoffice (view `errors/403`).
3. Agrupar rotas B2C numa área `auth` explícita; comentar as 3 áreas em `web.php`.
4. Testes: Feature (barreiras, B2B reservado, guarda fail-secure por grupo) + E2E Dusk (barreira entre áreas, pt-BR).

**Mapeamento CA → testes planejados** (TDD — teste vermelho antes do código):
- **CA-1** (separação/entrada por área): `SegmentacaoAreasTest::test_toda_rota_backoffice_esta_atras_do_guard`
  (arquitetural, fail-secure) + entradas por área exercitadas nos testes abaixo.
- **CA-2** (Coletador barrado no Backoffice): `test_coletador_autenticado_recebe_403_no_backoffice` (inválido/autz)
  + E2E `test_coletador_logado_barrado_no_backoffice_ve_403_ptbr` (exceção/erro).
- **CA-3** (entrada não anunciada, sem CTA): `test_guest_no_backoffice_redireciona_para_login` (borda) +
  `test_navegacao_do_coletador_nao_expoe_backoffice` (E2E, alternativo).
- **CA-4** (B2B reservado): `test_area_b2b_intelligence_e_publica_sem_login` (feliz) +
  `test_area_b2b_nao_tem_rota_autenticada_nem_feature` (borda) + E2E `test_b2b_intelligence_acessivel_sem_login`.
- **CA-5** (E2E barreira pt-BR): os cenários E2E acima, todos com texto pt-BR; 403 em pt-BR verificado também no
  Feature `test_pagina_403_esta_em_ptbr`.

### Decisões tomadas
### Descobertas
### IDRs criados
### Cobertura final
### Links de evidência
