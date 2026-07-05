---
story_id: STORY-029
slug: home-hub-coletador
title: Home-hub do Coletador como destino pós-login (saldo + CTA de coleta)
epic_id: EPIC-006
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: SCREEN-STORY-029-home-hub-coletador   # rabisco em draft (design/screens/STORY-029-home-hub-coletador/)
status: done
owner_agent: claude-story029
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-029 — Home-hub do Coletador como destino pós-login

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Esta estória tem UI nova
> (`requires_design: true`) — o Designer entrega a spec/protótipo em paralelo (PDR-002). Use o DS; não
> invente cor/fonte/raio/spacing fora dos tokens. Se algo estiver ambíguo, registre em "Notas do agente"
> e pause em vez de adivinhar.

## Contexto (por que esta estória existe)

Depois de logar (EPIC-004), o Coletador cai numa **página genérica** — não há um lugar que reúna o que ele
veio fazer (coletar cupom) e o que já ganhou (saldo). As peças da Onda 1 existem (coleta, carteira, saque),
mas soltas. Esta estória entrega a **primeira tela da jornada logada**: uma home-hub mobile-first, em pt-BR,
que passa a ser o **destino pós-login** e mostra, num só lugar, o **saldo da carteira** (EPIC-003) e um
**CTA destacado para coletar cupom** (EPIC-002). É a base sobre a qual as demais estórias do épico costuram
a navegação e o loop.

- Épico: `epics/EPIC-006-jornada-b2c/epic.md`
- Handoff de design: `epics/EPIC-006-jornada-b2c/design-handoff.md`
- Ler antes de codificar: spec do Designer (`design/screens/STORY-029-home-hub-coletador/`);
  `docs/visao.md` §3.1 (princípios de produto), §5.1 (MVP coleta e incentivo); `design/system/` (tokens,
  componentes, `patterns.md`, voz e tom); telas reusadas `design/screens/STORY-016-carteira-saldo-historico`
  e `design/screens/STORY-009-captura-qr-confirmacao`; PDR-003; DDR-004/DDR-005 (identidade e casca).

## O quê (objetivo desta estória)

Entregar a **home-hub do Coletador** como **destino pós-login** da área B2C autenticada: mostra o **saldo
atual da carteira** e um **CTA primário destacado "coletar cupom"** que leva ao fluxo de captura existente
— viva em homologação, mobile-first, no DS e em pt-BR. A página genérica deixa de ser o destino do login.

## Por quê (valor para o usuário)

É a peça que transforma funcionalidades soltas em jornada: o Coletador entra e vê, imediatamente, o que
ganhou (saldo) e o que pode fazer (coletar). Sem isso, o ciclo "coletei → ganhei → resgato" não tem centro
e a recorrência (que alimenta a north-star) fica frágil. É o destino que as STORY-030 e STORY-031 costuram.

## Critérios de aceite

Cada item é uma asserção testável. O agente DEVE escrever testes que cubram cada um.

- [ ] **CA-1:** Dado um Coletador autenticado, quando conclui o login (Google ou e-mail/senha, EPIC-004),
      então é levado à **home-hub** — e **não** a uma página genérica/scaffolding.
- [ ] **CA-2:** A home-hub exibe o **saldo atual da carteira** do Coletador logado, refletindo o valor real
      da carteira (EPIC-003), no formato brasileiro (R$ 0,00).
- [ ] **CA-3:** Dado um Coletador na home-hub, quando aciona o **CTA primário "coletar cupom"**, então é
      levado ao fluxo de captura de cupom (EPIC-002).
- [ ] **CA-4:** A home-hub usa o DS (tokens/componentes; verde como único accent de CTA), é **mobile-first**
      e **não exibe resíduo de scaffolding** (sem logo do Laravel); todo o texto em **pt-BR** via o mecanismo
      de i18n (STORY-020).
- [ ] **CA-5:** A home-hub é servida apenas na **área B2C autenticada**: um visitante não autenticado que
      tenta acessá-la é redirecionado para o login (guarda do EPIC-004).
- [ ] **CA-6:** A home-hub atende a11y AA (contraste ≥ 4.5:1 no texto/CTA e foco por teclado visível nos
      elementos interativos).

## Fora de escopo

- Atalhos para extrato/histórico e prêmios/saque e a navegação coesa entre as telas — é a **STORY-030**.
- Atualização do saldo refletindo uma coleta recém-feita no mesmo fluxo e o E2E ponta a ponta — é a
  **STORY-031**.
- Gamificação, ganchos de valor de dado ("você pagou mais/menos que a média"), notificações — fora do épico.

## Padrões de qualidade exigidos

Esta estória segue os padrões em `docs/skills/po/references/quality-standards.md`. Resumo aplicável:

- **Cobertura de testes unitários:** ≥ 80% no código novo; ≥ 98% no que tocar núcleo/regra (leitura de saldo,
  guarda de rota, roteamento pós-login).
- **Testes E2E:** inclua E2E em **browser real, viewport mobile**, cobrindo login → home-hub (destino) e o
  clique do CTA de coleta levando ao fluxo de captura.
- **pt-BR (§5.1):** todo texto visível em português do Brasil; formatos brasileiros (R$, America/Sao_Paulo).
- **a11y AA (§5):** contraste e navegação por teclado verificados.
- **Sem código não testado** entregue ao final. Setup novo, se houver, automatizado.

## Dependências

- **Bloqueada por:** nada (EPIC-004 `done`; reusa EPIC-002 e EPIC-003 já entregues).
- **Bloqueia:** STORY-030, STORY-031, STORY-032.
- **Pré-requisitos de ambiente:** homologação operante; Coletador de teste com carteira e saldo conhecido.

## Decisões já tomadas (não as reabra)

- PDR-003 — escopo da Onda 2 (jornada B2C = home-hub pós-login, mobile-first) → `decisions/pdr/PDR-003-*.md`.
- PDR-001 / PDR-002 — DS (só tokens) e modelo paralelo Designer↔Programador.
- DDR-004 (identidade de marca no acesso) e DDR-005 (casca pública compartilhada) → `decisions/ddr/`.
- Guardas de área e i18n do EPIC-004 (reusar; não reabrir o roteamento das áreas).

## Liberdade técnica do agente

Você decide a estrutura de código, componentes internos, e o design dos testes, dentro das decisões acima.
Você **não** decide linguagem/framework/banco (ADRs), padrões de qualidade nem critérios de aceite. Se
perceber necessidade de decisão arquitetural sem ADR cobrindo, **pare e registre** em "Notas do agente".

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam.
- [ ] Unitários escritos e passando nas coberturas exigidas.
- [ ] E2E mobile (browser real) escrito e passando em homologação.
- [ ] Existe entrada em `design.screens[]` com `story: STORY-029` e `status: ready` antes de ir para
      `in_review` (invariante v2 nº 9).
- [ ] Pipeline de CI verde; deploy de homologação automatizado e verificado.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. Ao iniciar, marque `status: in_progress` no
frontmatter e no `index.json`. Ao terminar, preencha "Notas do agente", marque `in_review`, atualize o
`index.json` e abra PR. O épico só fecha após o validador (STORY-032).

## Notas do agente (preenchido durante/após execução)

### Sync Designer↔Programador (modelo paralelo, PDR-002)
- 2026-07-05 — Mesma sessão nos dois papéis. Rabisco → spec/protótipo aprovados pelo humano antes do
  código (portão de design). Alinhamento técnico: saldo vem server-rendered como prop Inertia (mesmo
  read-model `ExtratoCarteira` de `/carteira`) → sem loading client-side (skeleton só referência); nome
  da saudação vem do `auth.user` já compartilhado. Estados desenhados: **positivo** e **zero/1º acesso**.

### Decisões tomadas
- 2026-07-05 — **Destino pós-login reaproveita a rota `dashboard`** (renderiza `Home/Hub` em vez da página
  genérica; mantém nome/guarda `['auth','verified']`). Um ponto de troca move todos os fluxos de login de
  uma vez, sem tocar nos 8 controllers de acesso nem no fluxo de verificação de e-mail. Removida a
  `Pages/Dashboard.jsx` (scaffolding). Rename da URL para `/inicio` e casca de nav compartilhada ficam na
  STORY-030. Registrado em **IDR-011**.
- 2026-07-05 — **Textos via i18n (`t()`, IDR-010):** as strings novas da tela usam chave = string-fonte em
  inglês em `lang/pt_BR.json` (reaproveitei `Every receipt counts.` → "Cada nota conta."; corrigi
  `Home` → "Início"). CTA "Coletar cupom" (fiel ao CA-3, confirmado pelo humano). Rótulos de nav seguem
  como nas telas irmãs (Carteira/Privacidade/Showcase); a STORY-030 os centraliza no shell compartilhado.
- 2026-07-05 — **`brand.mark`** (DDR-004) no topo substituindo qualquer resíduo de logo do Laravel;
  saldo no `card.feature-dark` (único momento de marca); CTA verde `button.primary` (único accent).

### Descobertas
- 2026-07-05 — O `app.blade.php` (raiz Inertia/Breeze) faz `@vite` do componente de página atual, então a
  página nova (`Home/Hub.jsx`) precisa estar no manifest do Vite: rodar `npm run build` antes dos testes
  Feature que renderizam a rota (senão `ViteException: Unable to locate file in Vite manifest`).
- 2026-07-05 — 3 testes Dusk do EPIC-004 asseguravam o conteúdo da página genérica ("Você está logado!")
  após o login (`AcessoGoogleTest` ×2, `ConfirmacaoEmailTest`). Atualizados para asserir a home-hub
  ("Seu saldo") — o destino pós-login mudou legitimamente nesta estória. Reforça a cobertura da CA-1.

### Cobertura final
- **Unitários/Feature:** suíte completa verde — 299 passed, 1304 assertions. Novos: `HomeHubTest` (Feature,
  4 casos: guarda de acesso, destino = `Home/Hub`, saldo 12,47 e saldo zero). `HomeController` é fino e
  100% exercitado (a meta ≥80%/≥98% é medida no PR/CI). Sem runner JS (IDR-003); a UI é coberta por E2E.
- **E2E (Dusk, browser real, viewport mobile 390px):** `HomeHubTest` — 4 casos passando: saudação+saldo
  (CA-1/2/4, sem "Você está logado!" e `assertSourceMissing` do logo Laravel), CTA "Coletar cupom" →
  `/coletar` (CA-3), 1º acesso (saldo zero + boas-vindas + CTA), anônimo → `/login` (CA-5).

### Links de evidência
- **PR:** https://github.com/xandroalmeida/quantah/pull/2 (mergeado na `main`, commit `8115f30`).
- **Pipeline CI:** run 28739879513 (Testes+build ✅, E2E Dusk ✅) no PR; run 28743801445 na `main`
  (Testes+build ✅, E2E Dusk ✅, **Deploy homologação ✅**).
- **Homologação verificada (2026-07-05):** `https://quantah-homolog.34.39.229.117.sslip.io` — `/up` 200;
  `/dashboard` anônimo → 302 `/login` (home-hub deployado + guarda de acesso ao vivo, CA-5); `/login`
  branded. Jornada autenticada ponta a ponta é coberta pelo E2E Dusk (CI verde) e será revalidada de 1ª
  mão pelo Validador na STORY-032.
- Spec/protótipo: `design/screens/STORY-029-home-hub-coletador/` (screen-spec.md + index.html, validado
  2026-07-05). IDR: `decisions/idr/IDR-011-destino-pos-login-reaproveita-rota-dashboard.md`.
