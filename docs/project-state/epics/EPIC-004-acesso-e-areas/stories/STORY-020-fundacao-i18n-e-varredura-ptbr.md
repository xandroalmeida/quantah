---
story_id: STORY-020
slug: fundacao-i18n-e-varredura-ptbr
title: Fundação de i18n (pt-BR) e varredura das superfícies existentes
epic_id: EPIC-004
sprint_id: null
type: enablement
target_role: programador
requires_design: false
design_screen_id: null
status: ready
owner_agent: null
created_at: 2026-07-04
updated_at: 2026-07-04
estimated_session_size: M
---

# STORY-020 — Fundação de i18n e varredura pt-BR

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Esta é uma estória
> **enablement** (horizontal, justificada): estabelece o mecanismo de localização e paga a dívida de
> idioma das telas atuais, destravando as telas novas do épico.

## Contexto (por que esta estória existe)

O produto tem strings de scaffolding em inglês (login/registro do Breeze, "Whoops!", "Log in",
"Remember me") e o mecanismo de localização ainda não está estabelecido. O requisito transversal pt-BR
(quality-standards §5.1) exige **todo texto visível em português do Brasil**, sem hardcode fora do
mecanismo de i18n. Estabelecer isso agora faz as telas novas (login de marca, áreas) já nascerem em
pt-BR.

**Por que é enablement (horizontal):** o mecanismo de i18n é infraestrutura transversal; sozinho não é
um fluxo de usuário. Justificativa: destrava STORY-021 (login de marca em pt-BR) e todas as telas
seguintes, e entrega valor observável imediato — as telas atuais em pt-BR.

- Épico: `epics/EPIC-004-acesso-e-areas/epic.md`
- Ler antes: ADR de i18n produzido na STORY-019; `docs/project-state/design/system/voice-and-tone.md`
  (voz/tom); quality-standards §5.1.

## O quê (objetivo desta estória)

Estabelecer o mecanismo de i18n (pt-BR) conforme o ADR e **varrer todas as superfícies já existentes**
para pt-BR, sem resíduo de inglês nem strings hardcoded fora do mecanismo.

## Por quê (valor para o usuário)

O Coletador (e o operador) veem um produto coeso em português — condição para parecer produto, não
scaffolding — e sustenta a métrica de qualidade da onda (100% pt-BR).

## Critérios de aceite

- [ ] **CA-1:** Mecanismo de i18n do ADR configurado; existe um único lugar canônico para as strings de
      interface (nada de texto de UI hardcoded fora dele nas telas tocadas).
- [ ] **CA-2:** Superfícies existentes **varridas para pt-BR**, sem resíduo de inglês: telas de auth do
      Breeze (login, registro, recuperação/reset de senha, verificação de e-mail), a página `/dashboard`
      atual, vitrine `/ds`, carteira, e o backoffice de saques.
- [ ] **CA-3:** Mensagens de validação e erro exibidas ao usuário estão em pt-BR (incl. as do
      back-end/validações do Breeze).
- [ ] **CA-4:** Formatos brasileiros aplicados onde há moeda/data: `R$ 1.234,56`, `dd/mm/aaaa`, fuso
      `America/Sao_Paulo`.
- [ ] **CA-5:** E2E cobre ao menos uma asserção de texto em pt-BR numa superfície pública (ex.: `/login`
      exibe rótulos em português) e a ausência de uma string de scaffolding conhecida (ex.: não há
      "Log in"/"Remember me"/"Whoops!").

## Fora de escopo

- Rebrand visual da tela de login (substituir o logo do Laravel, DS) — isso é a STORY-021.
- Tradução de conteúdo das landing pages (não existem ainda — EPIC-005).
- i18n multi-idioma (só pt-BR nesta fase).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`, em particular **§5.1 (pt-BR)**. Cobertura ≥ 80%
no código novo; E2E cobrindo a asserção de idioma (CA-5). Sem código não testado.

## Dependências

- **Bloqueada por:** STORY-019 (ADR de i18n).
- **Bloqueia:** STORY-021 (login de marca deve usar o mecanismo).
- **Pré-requisitos de ambiente:** homologação operante (herdada da Onda 1).

## Decisões já tomadas (não as reabra)

- ADR de i18n (STORY-019) — mecanismo de localização e formatos. Quality-standards §5.1 — o resultado exigido.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam; unitários + E2E (CA-5) verdes; cobertura exigida atingida.
- [ ] Pipeline verde; deploy de homologação verificado (telas atuais em pt-BR ao vivo).
- [ ] IDR registrado se houve decisão técnica relevante (ex.: organização das chaves de tradução).
- [ ] `index.json` = `done`; "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. Ao iniciar, marque `in_progress` e assuma a
estória no `index.json`. Decisões de baixo nível relevantes viram IDR.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
### Descobertas
### IDRs criados
### Cobertura final
### Links de evidência
