---
story_id: STORY-025
slug: landing-b2c
title: Landing B2C pública ("Cada nota conta.") com CTA de entrada e CTA para o B2B
epic_id: EPIC-005
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: SCREEN-STORY-025-landing-b2c   # rabisco em draft (design/screens/STORY-025-landing-b2c.md)
status: in_progress
owner_agent: claude-story025
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-025 — Landing B2C pública

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Esta estória tem UI nova
> (`requires_design: true`) — o Designer entrega a spec/protótipo em paralelo (PDR-002). Use o DS; não
> invente cor/fonte/raio/spacing fora dos tokens. Se algo estiver ambíguo, registre em "Notas do agente"
> e pause em vez de adivinhar.

## Contexto (por que esta estória existe)

Hoje a plataforma não tem porta de entrada pública: quem chega cai direto no login, sem entender o que é o
Quantah nem por que vale a pena coletar cupons. Esta estória entrega a **primeira página que um visitante
vê** — a landing do app de consumidor (Coletador), mobile-first e em pt-BR, com a proposta de valor da marca
("Cada nota conta.") e um caminho claro de entrada para o login que já existe (EPIC-004).

- Épico: `epics/EPIC-005-portas-de-entrada/epic.md`
- Ler antes: spec do Designer (`design/screens/STORY-025-landing-b2c/`); `docs/visao.md` §2 (problema),
  §11 (marca/taglines — "Cada nota conta.", §11.3 arquitetura B2C/B2B); `design/system/` (tokens,
  componentes, voz e tom); PDR-003; PDR-001 (DS); ADR/estória de acesso do EPIC-004 (rota de login).

## O quê (objetivo desta estória)

Entregar a **landing B2C pública** (rota pública, sem login), no DS e em pt-BR, com a proposta de valor do
Quantah para o Coletador, um **CTA primário que leva ao login/cadastro** (EPIC-004) e um **CTA secundário
que leva à página B2B** (Quantah Intelligence) — viva em homologação, mobile-first.

## Por quê (valor para o usuário)

Sem porta de entrada não há como divulgar nem atrair Coletadores para o piloto. Esta página é o topo do
funil B2C (métrica primária do épico: o clique no CTA vira início de cadastro/login) e a primeira
impressão de produto do Quantah.

## Critérios de aceite

Cada item é uma asserção testável. O agente DEVE escrever testes que cubram cada um.

- [ ] **CA-1:** Existe uma rota **pública** (acessível sem autenticação) que serve a landing B2C; um
      visitante não logado a abre sem ser redirecionado para o login.
- [ ] **CA-2:** A página usa o DS (tokens/componentes; verde como único accent de CTA) e **não exibe
      resíduo de scaffolding** (sem logo do Laravel); todo o texto em **pt-BR** via o mecanismo de i18n
      (STORY-020), incluindo a proposta de valor da marca ("Cada nota conta.").
- [ ] **CA-3:** Dado um visitante na landing, quando aciona o **CTA primário de entrada**, então é levado
      à tela de login/cadastro do Coletador (EPIC-004).
- [ ] **CA-4:** Dado um visitante na landing, quando aciona o **CTA para o B2B**, então é levado à rota da
      landing Quantah Intelligence (STORY-026).
- [ ] **CA-5:** A página é **mobile-first** e atende a11y AA nas superfícies desta estória (contraste dos
      tokens, alvo de toque ~48px, foco visível, marcação semântica e textos alternativos onde aplicável).
- [ ] **CA-6:** E2E em browser real (mobile) abre a landing como visitante anônimo, verifica a proposta em
      pt-BR e percorre o CTA de entrada até chegar ao login, com asserções de texto em pt-BR.

## Fora de escopo

- A **página B2B em si** e a captação de lead — são da STORY-026 (aqui só o CTA que aponta para ela).
- Login/cadastro e seus fluxos — já entregues no EPIC-004 (aqui só o link/redirecionamento).
- SEO avançado, blog, páginas institucionais, animações elaboradas — fora do épico.

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`: cobertura ≥ 80% no código novo; **E2E em browser
real** do caminho landing → login (CA-6); **a11y AA**; **pt-BR** (§5.1); só tokens do DS (PDR-001). Sem
código não testado.

## Dependências

- **Bloqueada por:** — (EPIC-004 já entregou o login para onde o CTA aponta; i18n da STORY-020 disponível).
- **Bloqueia:** STORY-028 (validação do épico). O CTA para o B2B tem o destino preenchido pela STORY-026;
  até lá, o link aponta para a rota planejada do B2B.
- **Pré-requisitos de ambiente:** homologação operante; spec/protótipo do Designer para a tela.

## Decisões já tomadas (não as reabra)

- PDR-001 (DS — só tokens, verde como accent de CTA, mobile-first, a11y AA).
- PDR-003 (escopo da onda; login B2C Google + e-mail/senha já existe).
- ADR-011 / IDR-010 (i18n pt-BR nativo). Marca/taglines conforme `docs/visao.md` §11.

## Liberdade técnica do agente

Você decide a estrutura de código/testes, componentes internos e refatorações locais. Você **não** decide
stack/framework, critérios de aceite, nem a spec visual (é do Designer). Se faltar decisão arquitetural sem
ADR cobrindo, **pare e registre** em "Notas do agente".

## Definição de Pronto (DoD)

- [ ] Todos os critérios de aceite passam.
- [ ] Testes unitários escritos e passando, atingindo a cobertura exigida.
- [ ] Teste E2E (CA-6) escrito e passando em homologação.
- [ ] Entrada em `design.screens[]` (`SCREEN-STORY-025-landing-b2c`) existe antes de `in_review`
      (invariante v2 #9).
- [ ] Pipeline de CI verde; deploy de homologação realizado e verificado.
- [ ] IDR registrado se houve descoberta técnica relevante.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. Coordene com o Designer (PDR-002) para a spec da
tela antes de codificar UI (`design.screens[*].status: ready` é pré-condição — invariante v2 #12).

## Briefing de Design (PO → Designer)

Brief consolidado das duas landings do épico em `epics/EPIC-005-portas-de-entrada/design-handoff.md`
(seção **STORY-025 — Landing B2C**). Ponto de partida para o rabisco: intenção da tela, estados a cobrir,
ativos do DS a reusar (`hero-band`, `brand.lockup`, `button.primary`, `footer`), marca/voz B2C
("Cada nota conta."), a11y AA + pt-BR, e perguntas em aberto para o PO decidir. Segue o fluxo paralelo
(PDR-002): rabisco → sync com Programador → spec + protótipo HTML → validação humana antes de `ready`.

## Notas do agente (preenchido durante/após execução)

### Sync de viabilidade (Designer × Programador · 2026-07-05)

Fluxo paralelo (PDR-002 / `collaboration-with-developer.md`). Um agente veste os dois papéis,
declarando a troca. Limitações da stack (Inertia + React) levantadas e ajustes acordados:

- **Bands do DS ainda não são componentes React.** `hero-band`, `hero-band-dark` e `content-band`
  existem em `design/system/components.md` (ids do DS, tokens definidos) mas **não** têm componente
  em `resources/js/Components/`. Não são "componentes novos" (já são DS via PDR-001) → materializá-los
  em React **não exige DDR novo**. Serão criados em `Components/bands/` (um por conceito), reusados
  pela B2C e pela B2B (026).
- **Header/rodapé público compartilhado = padrão durável cross-screen (B2C↔B2B).** Compõe blocos DS
  existentes (`nav.bar`, `nav.link`, `footer`, `brand.lockup`) — sem primitivo novo — mas o *padrão de
  composição* (casca pública `PublicLayout` com alternância entre as faces) é durável → **formalizar
  como DDR-005** antes de codar UI (gate humano G1). Sem componente fora do DS sem DDR.
- **`/` hoje serve `Pages/Hello.jsx` (scaffolding EPIC-000).** A landing B2C **substitui** a home. O
  Hello é resíduo de scaffolding ("Olá do Quantah 👋" — inclusive com emoji, que o DS proíbe) → será
  **aposentado**. Impacto de teste consciente (não é regressão): `tests/Feature/HelloWorldTest.php`,
  `tests/Browser/HelloWorldTest.php` e `tests/Feature/DesignSystem/NoRawColorInHelloTest.php`
  serão **substituídos** por testes equivalentes da landing (rota pública + contrato de cor crua).
- **i18n:** a tagline "Cada nota conta." já existe no dicionário via `t('Every receipt counts.')`
  (renderizada pelo `BrandLockup`, DDR-004). "Entrar" já existe (`Sign in`). Microcopy própria da
  landing segue o padrão vigente do projeto (pt-BR inline, como `Intelligence/Reservado.jsx` e
  `Saque/Solicitar.jsx`), reusando `t()` onde a string traduzida já existe. Satisfaz CA-2 (100% pt-BR,
  sem resíduo em inglês).
- **Destinos dos CTAs (já existem):** CTA primário → `route('login')` (`/login`, EPIC-004);
  CTA-B2B → `/intelligence` (rota pública viva hoje com `Intelligence/Reservado.jsx`; a 026 a
  substitui). **Não há placeholder morto** — o destino B2B já responde 200.
- **Ordem natural de implementação:** DDR-005 (G1) → `PublicLayout` + bands → página `LandingB2C` →
  repontar `/` no `web.php` → testes Feature (público/pt-BR/CTAs) → Dusk mobile landing→login (CA-6).

### Decisões tomadas
- 2026-07-05 — Aposentar `Pages/Hello.jsx` (scaffolding) e seus 3 testes; a home `/` passa a servir a
  landing B2C. Substituir os testes por equivalentes da landing (decisão local, dentro da liberdade
  técnica; não altera CA).

### Descobertas
- 2026-07-05 — `/intelligence` já é rota pública viva (`Intelligence/Reservado.jsx`), então o CTA-B2B da
  B2C tem destino real desde já; a STORY-026 substitui a página nesse mesmo namespace.

### Bloqueios encontrados
- <nenhum até aqui>

### IDRs criados
- <nenhum até aqui>

### Cobertura final
- Unitários: <%>
- E2E: <cenários, evidência>

### Links de evidência
- PR / Pipeline / Deploy de homologação: <urls>
