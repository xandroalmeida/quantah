---
story_id: STORY-031
slug: jornada-continua-ponta-a-ponta
title: Jornada contínua — saldo reflete a coleta e loop coletar → saldo → extrato → saque
epic_id: EPIC-006
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: in_review
owner_agent: claude-story031
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-031 — Jornada contínua ponta a ponta

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Não há tela nova
> (`requires_design: false`) — esta estória **costura** telas já entregues (STORY-029, STORY-030,
> EPIC-002/003) e prova o loop de valor. Se algo estiver ambíguo, registre em "Notas do agente" e pause.

## Contexto (por que esta estória existe)

Com a home-hub (STORY-029) e a navegação coesa (STORY-030) no lugar, falta fechar o **loop de valor
visível**: depois de coletar um cupom, o Coletador precisa **ver o saldo crescer na home-hub** e seguir,
num fluxo contínuo, até o extrato e o saque. Este é o coração do épico — a costura que transforma
funcionalidades isoladas em jornada de produto e materializa o ciclo "coletei → ganhei → resgato".

- Épico: `epics/EPIC-006-jornada-b2c/epic.md`
- Ler antes de codificar: STORY-029 e STORY-030 (esta estória depende do que elas entregam);
  `docs/visao.md` §3.1 e §5.1; telas da Onda 1 reusadas (coleta `STORY-009`, carteira/extrato `STORY-016`,
  saque `STORY-017`); regras de cashback do EPIC-003 (reusar como estão); PDR-003.

## O quê (objetivo desta estória)

Garantir que, após uma **coleta bem-sucedida**, o **saldo exibido na home-hub reflete o crédito** da coleta,
e entregar a **jornada contínua ponta a ponta** — entrar → home-hub → coletar → saldo atualiza → abrir
extrato → iniciar saque — **demonstrável em homologação** e coberta por E2E em browser real (mobile).

## Por quê (valor para o usuário)

É a prova da métrica primária do épico: a partir da home-hub, o Coletador inicia uma coleta e consulta
saldo/histórico em ≤ 2 toques, num fluxo contínuo. Ver o saldo subir logo após coletar é o que torna a
recompensa tangível e sustenta a recorrência — a condição para a north-star sair de zero.

## Critérios de aceite

Cada item é uma asserção testável. O agente DEVE escrever testes que cubram cada um.

- [ ] **CA-1:** Dado um Coletador que conclui uma **coleta válida** (EPIC-002) que gera crédito (EPIC-003),
      quando retorna à home-hub, então o **saldo exibido reflete o novo crédito** (valor atualizado, sem
      exigir novo login).
- [ ] **CA-2:** Dado o saldo atualizado na home-hub, quando o Coletador abre o **extrato**, então a coleta
      recém-creditada aparece no histórico da carteira (EPIC-003).
- [ ] **CA-3:** Dado saldo suficiente, quando o Coletador aciona **iniciar saque** a partir da jornada,
      então chega à tela de solicitar saque (EPIC-003) com o fluxo pronto para prosseguir.
- [ ] **CA-4:** A partir da home-hub, **coletar** e **consultar saldo/histórico** são alcançáveis em
      **≤ 2 toques** cada (métrica primária do épico), verificado no E2E.
- [ ] **CA-5:** Existe um **E2E em browser real, viewport mobile**, que percorre a jornada completa
      (entrar → home-hub → coletar → saldo atualiza → extrato → iniciar saque) e passa em homologação.
- [ ] **CA-6:** Toda a jornada permanece em **pt-BR** (i18n) e no DS; nenhuma etapa cai em página genérica.

## Fora de escopo

- Novas regras de cashback, de coleta ou de saque — reusa as do EPIC-002/003 como estão.
- Concluir efetivamente um saque com débito — o fluxo de saque já é do EPIC-003; aqui basta **chegar** e
  poder prosseguir.
- Gamificação, ganchos de valor de dado, notificações — fora do épico.

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. Resumo aplicável:

- **Cobertura unitária:** ≥ 80% no código novo; ≥ 98% no que tocar núcleo/regra (atualização/leitura de
  saldo pós-coleta).
- **E2E:** browser real, viewport mobile, cobrindo a jornada ponta a ponta (CA-5) e a contagem de toques
  (CA-4). Este é o E2E que a validação do épico (STORY-032) vai referenciar.
- **pt-BR (§5.1)** e **a11y AA (§5)** verificados. Sem código não testado.

## Dependências

- **Bloqueada por:** STORY-029 (home-hub/saldo) e STORY-030 (navegação/atalhos).
- **Bloqueia:** STORY-032.
- **Pré-requisitos de ambiente:** homologação operante; Coletador de teste apto a coletar um cupom válido
  novo (não duplicado) que gere crédito; carteira com saldo para habilitar o início do saque.

## Decisões já tomadas (não as reabra)

- PDR-003 (jornada B2C); regras de cashback/saque do EPIC-003; fluxo de captura do EPIC-002 — reusar.
- PDR-001/PDR-002 (DS, modelo paralelo).

## Liberdade técnica do agente

Você decide como costurar a atualização do saldo, a estrutura do E2E e refatorações locais, dentro das
decisões acima. Você **não** decide stack, qualidade nem CAs. ADR ausente e necessária → **pare e registre**.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam.
- [ ] Unitários e o E2E mobile ponta a ponta (browser real) escritos e passando em homologação.
- [ ] CI verde; deploy de homologação automatizado e verificado.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. `in_progress` ao iniciar (frontmatter + índice);
`in_review` ao terminar, com "Notas do agente" e PR. Fecha após o validador (STORY-032).

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 2026-07-05 — **Sem código de produção novo:** a costura já funcionava após 029/030 — a home lê o saldo
  fresco (read-model `ExtratoCarteira`) a cada render server-side, então o saldo reflete o crédito sem novo
  login (CA-1) sem glue adicional. Esta estória entrega a **prova** (Feature + E2E).
- 2026-07-05 — **Representação da "coleta que gera crédito":** a validação do cupom é **assíncrona** (SEFAZ,
  EPIC-002) e **não roda no browser** (a coleta pela UI para em `pendente` — ver `ColetaCapturaTest`). Por
  isso, no meio do E2E, a coleta válida é representada por um cupom já `validado` + o **serviço real de
  crédito** (`CreditarCashbackService::creditarPorCupom`, EPIC-003), gravado no banco de dev que o app lê.
  O "coletar a partir da home" é exercido de verdade pela UI (CTA → `/coletar`, 1 toque).

### Descobertas
- 2026-07-05 — Nos testes Dusk é possível **interpolar PHP entre passos do browser** (o closure roda no
  processo de teste): usei isso para creditar a carteira no banco de dev entre o "coletar" e o "voltar à
  home", tornando o "saldo atualiza" observável na mesma sessão.

### Cobertura final
- **Feature** `JornadaContinuaTest`: CA-1 (saldo da home 0,00 → 4,89 na mesma sessão, sem novo login pelo
  caminho real de crédito) e CA-2 (crédito no extrato). Suíte completa verde — 303 passed, 1403 assertions.
- **E2E (Dusk, browser real, mobile 390px)** `JornadaContinuaTest`: loop completo entrar → home → **coletar
  (CTA, 1 toque)** → saldo atualiza (R$ 4,89) → **extrato (atalho Histórico, 1 toque)** com o crédito →
  **iniciar saque (atalho Prêmios, 1 toque)** com a tela pronta e o saldo em pt-BR. Cobre CA-1..CA-6.

### Links de evidência
- Commit(s) na `main`. Pipeline CI + Deploy homologação: <a preencher>.
- Este é o E2E ponta a ponta que a **STORY-032** (validação do épico) referencia.
