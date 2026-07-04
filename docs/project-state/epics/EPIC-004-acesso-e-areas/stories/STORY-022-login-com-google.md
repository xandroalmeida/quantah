---
story_id: STORY-022
slug: login-com-google
title: Login com Google para o Coletador
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

# STORY-022 — Login com Google

> **Para o agente que vai executar:** leia a estória inteira antes de começar. O botão já foi desenhado
> na tela de login (STORY-021); esta estória entrega o **fluxo OAuth** e o modelo de contas por trás dele.

## Contexto (por que esta estória existe)

O Colaborador Casual — a maioria — precisa entrar em segundos. O login social **Google** é o caminho de
menor fricção decidido no PDR-003. A tela de login de marca (STORY-021) já reserva o botão "Entrar com
Google"; falta implementar o fluxo OAuth e a coexistência com contas de e-mail/senha, conforme o ADR de
acesso (STORY-019).

- Épico: `epics/EPIC-004-acesso-e-areas/epic.md`
- Ler antes: ADR de acesso (STORY-019) — fluxo OAuth e modelo de contas; STORY-021 (tela); PDR-003.

## O quê (objetivo desta estória)

Entregar **login/cadastro com Google** para o Coletador: o botão da tela de login inicia o fluxo OAuth,
que **cria ou vincula** a conta do Coletador e o autentica — em homologação.

## Por quê (valor para o usuário)

Reduz a fricção de entrada ao mínimo, aumentando a conversão do funil B2C (métrica primária do épico).

## Critérios de aceite

- [ ] **CA-1:** Dado um visitante sem conta, quando entra com Google e autoriza, então uma conta de
      Coletador é criada a partir do perfil Google (nome, e-mail) e ele fica autenticado.
- [ ] **CA-2:** Dado um Coletador que já tem conta de **e-mail/senha** com o mesmo e-mail (verificado),
      quando entra com Google, então a identidade Google é **vinculada à conta existente** (não cria
      duplicada), conforme o modelo do ADR.
- [ ] **CA-3:** Falha/cancelamento no provedor Google retorna à tela de login com mensagem em pt-BR, sem
      derrubar a sessão nem expor detalhes técnicos.
- [ ] **CA-4:** Segredos do OAuth (client id/secret) vêm de configuração/secrets, **nunca versionados**
      (quality-standards §4).
- [ ] **CA-5:** E2E cobre o fluxo de login Google (provedor simulado/stub em teste) até o Coletador
      autenticado, incl. o caminho de vínculo (CA-2) e o de erro (CA-3).

## Fora de escopo

- Outros provedores sociais. Login/conta B2B (fora da onda). Destino pós-login definitivo (EPIC-006).
- Redesenho do botão (já é da STORY-021).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`: cobertura ≥ 80% (o **núcleo de vínculo/criação de
conta** no padrão de núcleo, ≥ 98%); E2E do fluxo (CA-5); **segurança** (segredos fora do versionamento);
**pt-BR** nas mensagens. Sem código não testado.

## Dependências

- **Bloqueada por:** STORY-019 (ADR de acesso), STORY-021 (tela de login que hospeda o botão).
- **Bloqueia:** — (STORY-024 valida).
- **Pré-requisitos de ambiente:** credenciais OAuth Google de teste/homologação disponíveis via secrets.

## Decisões já tomadas (não as reabra)

- PDR-003 (Google + e-mail/senha). ADR de acesso (STORY-019) — fluxo OAuth e modelo de contas. ADR-000.

## Liberdade técnica do agente

Você decide a estrutura de código/testes e a forma de stub do provedor em teste, dentro do ADR. Não
decide o modelo de contas (é do ADR) nem os CAs. Se faltar decisão arquitetural, **pare e registre**.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam; unitários + E2E verdes; coberturas exigidas (núcleo de contas ≥ 98%).
- [ ] Nenhum segredo versionado; segredos por secrets do CI/ambiente.
- [ ] Pipeline verde; deploy de homologação verificado (login Google ao vivo).
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
