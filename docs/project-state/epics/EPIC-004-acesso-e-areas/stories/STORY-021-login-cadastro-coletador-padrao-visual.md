---
story_id: STORY-021
slug: login-cadastro-coletador-padrao-visual
title: Login/cadastro do Coletador no padrão visual (e-mail/senha), em pt-BR
epic_id: EPIC-004
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: SCREEN-STORY-021-login-cadastro-coletador
status: in_progress
owner_agent: Programador
created_at: 2026-07-04
updated_at: 2026-07-04
estimated_session_size: M
---

# STORY-021 — Login/cadastro do Coletador no padrão visual

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Esta estória tem UI nova
> (`requires_design: true`) — o Designer entrega a spec/protótipo em paralelo (PDR-002). Use o DS; não
> invente cor/fonte/raio/spacing fora dos tokens.

## Contexto (por que esta estória existe)

A porta de entrada do Coletador é hoje a tela de login do Breeze com o **logo do Laravel** — parece
scaffolding, não o Quantah. O e-mail/senha já funciona (Breeze), mas sem identidade de marca. Esta
estória substitui a apresentação por uma **tela de login e cadastro no padrão visual do DS**, em pt-BR,
mantendo o fluxo de e-mail/senha (cadastro, entrada, recuperação de senha).

- Épico: `epics/EPIC-004-acesso-e-areas/epic.md`
- Ler antes: spec do Designer (`design/screens/SCREEN-STORY-021-login-cadastro-coletador/`); ADR de
  acesso (STORY-019); `design/system/` (tokens/componentes/voz); PDR-003; PDR-001 (DS).

## O quê (objetivo desta estória)

Entregar a tela de **login e cadastro do Coletador** no DS (sem logo do Laravel), em pt-BR, com o fluxo
de **e-mail/senha** (cadastro → entrada → recuperação/reset de senha → verificação de e-mail) funcionando
em homologação. A tela **reserva o lugar do botão "Entrar com Google"** (implementado na STORY-022).

## Por quê (valor para o usuário)

O Coletador entra por uma porta que parece o Quantah, com baixa fricção e em português — primeira
impressão de produto e condição para o funil B2C (métrica primária do épico).

## Critérios de aceite

- [ ] **CA-1:** A tela de login/cadastro usa o DS (tokens, componentes de input/botão) e **não exibe o
      logo do Laravel** (`ApplicationLogo` do scaffolding substituído pela identidade Quantah); todo texto
      em pt-BR via o mecanismo de i18n (STORY-020).
- [ ] **CA-2:** Dado um visitante, quando se cadastra com nome, e-mail e senha válidos, então uma conta
      de Coletador é criada e ele fica autenticado — em homologação.
- [ ] **CA-3:** Dado um Coletador cadastrado, quando entra com e-mail/senha corretos, então é autenticado;
      credencial inválida mostra erro em pt-BR sem vazar qual campo falhou.
- [ ] **CA-4:** Recuperação e redefinição de senha funcionam e estão em pt-BR (e-mail de reset incluído).
- [ ] **CA-5:** A tela apresenta o **espaço do botão "Entrar com Google"** conforme a spec do Designer
      (placeholder desabilitado/oculto por flag até a STORY-022), sem quebrar o layout.
- [ ] **CA-6:** E2E em browser real (mobile) percorre cadastro → logout → login por e-mail/senha, com
      asserções de texto em pt-BR.

## Fora de escopo

- **Login com Google** (STORY-022) — aqui só o lugar do botão.
- Segmentação de áreas/guardas e destino pós-login definitivo (STORY-023 e EPIC-006). Ao entrar, o
  Coletador segue para o destino atual até a home-hub (EPIC-006) existir.
- Verificação/localização das demais telas (coberto pela STORY-020).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`: cobertura ≥ 80% no código novo; **E2E em browser
real** do fluxo de acesso (CA-6); a11y AA (contraste dos tokens, alvo ~48px, foco visível); **pt-BR**
(§5.1). Sem código não testado.

## Dependências

- **Bloqueada por:** STORY-019 (ADR de acesso), STORY-020 (mecanismo de i18n).
- **Bloqueia:** STORY-022 (o botão Google vive nesta tela).
- **Pré-requisitos de ambiente:** homologação operante; spec/protótipo do Designer para a tela.

## Decisões já tomadas (não as reabra)

- PDR-001 (DS) e PDR-003 (login Google + e-mail/senha). ADR de acesso (STORY-019). ADR-000 (stack).

## Liberdade técnica do agente

Você decide a estrutura de código/testes e refatorações locais das telas de auth do Breeze. Você **não**
decide stack/framework, critérios de aceite, nem a spec visual (é do Designer). Se faltar decisão
arquitetural, **pare e registre**.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam; unitários + E2E verdes; cobertura exigida.
- [ ] Entrada em `design.screens[]` (`SCREEN-STORY-021-...`) existe antes de `in_review` (invariante v2).
- [ ] Pipeline verde; deploy de homologação verificado (tela de marca viva).
- [ ] IDR registrado se houve decisão técnica relevante.
- [ ] `index.json` = `done`; "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. Coordene com o Designer (PDR-002) para a spec.

## Notas do agente (preenchido durante/após execução)

### Handoff de Design (2026-07-04)

Design entregue e **validado por Alexandro** ("aprovado") — o Programador pode implementar contra o spec.

- **Spec:** `design/screens/STORY-021-login-cadastro-coletador/screen-spec.md` (`ready`).
- **Protótipo fiel:** `design/screens/STORY-021-login-cadastro-coletador/index.html` (5 telas, estados
  `padrão/erro/carregando/sem-google/enviado/reenviado`, mobile+desktop).
- **DDR-004** (`accepted`): marca Quantah no lugar do `ApplicationLogo`; split-hero escuro no desktop;
  Google acima (neutro) → "ou" → e-mail/senha → CTA verde único.
- **DS atualizado:** `brand.mark`, `brand.lockup`, `brand.google-btn` (components.md) e `pattern.auth`
  (patterns.md).
- **Para o Programador:** trocar `ApplicationLogo`/`GuestLayout` do Breeze pelo `pattern.auth`; microcopy
  pt-BR via i18n (chaves em `lang/pt_BR`, §5 do spec); `data-testid` = identificadores lógicos (§7) para
  ancorar o E2E do CA-6; placeholder do Google desabilitado/oculto por flag (CA-5).

### Decisões tomadas
### Descobertas
### IDRs criados
### Cobertura final
### Links de evidência
