---
story_id: STORY-030
slug: navegacao-coesa-b2c
title: Navegação coesa da área B2C (home ↔ coleta ↔ carteira/extrato ↔ saque)
epic_id: EPIC-006
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: SCREEN-STORY-030-navegacao-b2c   # rabisco em draft (design/screens/STORY-030-navegacao-b2c/)
status: ready
owner_agent: null
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-030 — Navegação coesa da área B2C

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Esta estória tem UI nova
> (`requires_design: true`) — o Designer entrega o padrão de navegação em paralelo (PDR-002). Use o DS.
> Se algo estiver ambíguo, registre em "Notas do agente" e pause em vez de adivinhar.

## Contexto (por que esta estória existe)

Com a home-hub existindo (STORY-029), falta a **costura de navegação** que liga o Coletador às telas da
Onda 1 sem passar por página genérica. Hoje as telas de **extrato/histórico** (EPIC-003), **saque/prêmios**
(EPIC-003) e **coleta** (EPIC-002) existem soltas. Esta estória entrega os **atalhos a partir da home-hub**
e uma navegação consistente entre elas, e **remove qualquer página genérica remanescente** da área logada.

- Épico: `epics/EPIC-006-jornada-b2c/epic.md`
- Handoff de design: `epics/EPIC-006-jornada-b2c/design-handoff.md`
- Ler antes de codificar: spec do Designer (`design/screens/STORY-030-navegacao-b2c/`); `design/system/`
  (`patterns.md` — navegação/retorno; `components.md`); telas reusadas
  `design/screens/STORY-016-carteira-saldo-historico`, `design/screens/STORY-017-solicitar-saque`,
  `design/screens/STORY-009-captura-qr-confirmacao`; DDR-005 (casca compartilhada); PDR-003.

## O quê (objetivo desta estória)

Entregar a **navegação coesa da área B2C** a partir da home-hub: atalhos para **histórico/extrato** e
**prêmios/saque** (EPIC-003) e para **coletar** (EPIC-002), com retorno consistente à home — de modo que o
Coletador alcance coleta e saldo/histórico em **≤ 2 toques** cada, e **nenhuma rota logada** caia mais numa
página genérica/scaffolding. Viva em homologação, mobile-first, no DS e em pt-BR.

## Por quê (valor para o usuário)

É o que faz a jornada "andar": sem navegação coesa, as telas da Onda 1 continuam ilhadas e o Coletador se
perde. A meta de ≤ 2 toques (métrica de sucesso do épico) só existe se os atalhos e o retorno forem claros.
Remover a página genérica é o critério visível de que a experiência logada virou produto, não scaffolding.

## Critérios de aceite

Cada item é uma asserção testável. O agente DEVE escrever testes que cubram cada um.

- [ ] **CA-1:** Dado um Coletador na home-hub, quando aciona o atalho de **histórico/extrato**, então abre a
      tela de extrato da carteira (EPIC-003) em **≤ 2 toques** a partir da home.
- [ ] **CA-2:** Dado um Coletador na home-hub, quando aciona o atalho de **prêmios/saque**, então abre a
      tela de solicitar saque (EPIC-003) em **≤ 2 toques** a partir da home.
- [ ] **CA-3:** Dado um Coletador em qualquer tela da jornada (coleta, extrato, saque), quando aciona o
      retorno, então volta à **home-hub** de forma consistente (sem beco sem saída).
- [ ] **CA-4:** **Nenhuma rota da área B2C autenticada** exibe página genérica/scaffolding (sem
      dashboard genérico do EPIC-004, sem logo do Laravel) — a home-hub é o centro.
- [ ] **CA-5:** A navegação usa o DS e é **mobile-first**; todo o texto em **pt-BR** via i18n (STORY-020).
- [ ] **CA-6:** Os elementos de navegação atendem a11y AA (foco por teclado visível, alvos toucáveis,
      contraste ≥ 4.5:1).

## Fora de escopo

- A home-hub em si (saldo + CTA de coleta) — é a **STORY-029** (esta estória depende dela).
- Atualização do saldo refletindo uma coleta recém-feita e o E2E ponta a ponta do loop — é a **STORY-031**.
- Novas regras de coleta, carteira ou saque — reusa as da Onda 1 como estão.

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. Resumo aplicável:

- **Cobertura unitária:** ≥ 80% no código novo; ≥ 98% em núcleo/regra (roteamento, guardas de navegação).
- **E2E:** browser real, viewport mobile, cobrindo home → extrato e home → saque (contagem de toques) e o
  retorno à home; asserção explícita de que nenhuma rota logada renderiza página genérica.
- **pt-BR (§5.1)** e **a11y AA (§5)** verificados. Sem código não testado.

## Dependências

- **Bloqueada por:** STORY-029 (a home-hub é o ponto de partida da navegação).
- **Bloqueia:** STORY-031, STORY-032.
- **Pré-requisitos de ambiente:** homologação operante; Coletador de teste com extrato e saldo para saque.

## Decisões já tomadas (não as reabra)

- PDR-003 (jornada B2C mobile-first); PDR-001/PDR-002 (DS, modelo paralelo).
- DDR-005 (casca pública compartilhada) e guardas de área do EPIC-004 — reusar.

## Liberdade técnica do agente

Você decide estrutura de código, componentes de navegação e design dos testes, dentro das decisões acima.
Você **não** decide stack, qualidade nem CAs. Necessidade de ADR ausente → **pare e registre**.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam.
- [ ] Unitários e E2E mobile (browser real) escritos e passando em homologação.
- [ ] Entrada em `design.screens[]` com `story: STORY-030` e `status: ready` antes de `in_review`
      (invariante v2 nº 9).
- [ ] CI verde; deploy de homologação automatizado e verificado.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. `in_progress` ao iniciar (frontmatter + índice);
`in_review` ao terminar, com "Notas do agente" preenchidas e PR aberto. Fecha após o validador (STORY-032).

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- <data> — <decisão>

### Descobertas
- <data> — <gotcha>

### Cobertura final
- Unitários: <%> · E2E: <cenários / evidência>

### Links de evidência
- PR: <url> · Pipeline: <url> · Deploy de homologação: <url>
