---
story_id: STORY-026
slug: landing-b2b-captacao-de-lead
title: Landing B2B Quantah Intelligence ("Do cupom ao insight.") com captação de lead
epic_id: EPIC-005
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: SCREEN-STORY-026-landing-b2b-quantah-intelligence   # rabisco em draft (design/screens/STORY-026-landing-b2b-quantah-intelligence.md)
status: in_progress
owner_agent: claude-story026
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-026 — Landing B2B (Quantah Intelligence) com captação de lead

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Esta estória tem UI nova
> (`requires_design: true`) — o Designer entrega a spec/protótipo em paralelo (PDR-002). Use o DS; não
> invente cor/fonte/raio/spacing fora dos tokens. Há dado pessoal envolvido (lead): atenção à LGPD (§4).

## Contexto (por que esta estória existe)

O lado B2B (Quantah Intelligence) é onde está a receita principal, mas hoje um interessado não tem para
onde olhar nem como manifestar interesse. Esta estória entrega a **vitrine pública do produto de dados** e
uma **captação de lead** — nesta onda o B2B é só vitrine + lista de interessados (PDR-003), sem login/conta
B2B. O lead capturado é persistido para virar demanda rastreável; a visibilidade no Backoffice é a
STORY-027.

- Épico: `epics/EPIC-005-portas-de-entrada/epic.md`
- Ler antes: spec do Designer (`design/screens/STORY-026-landing-b2b-quantah-intelligence/`);
  `docs/visao.md` §10 (monetização), §11 (marca — "Do cupom ao insight.", tom sério/analítico do B2B);
  `design/system/`; PDR-003 (B2B = só captação de lead nesta onda); ADR-009 (RBAC — o lead vive atrás do
  papel operacional); PDR-001 (DS).

## O quê (objetivo desta estória)

Entregar a **landing B2B pública** (Quantah Intelligence), no DS e em pt-BR, com a proposta de valor
("Do cupom ao insight.") e um **formulário de captação de lead** (nome, e-mail, empresa) que **valida e
persiste** o lead — vivo em homologação, mobile-first.

## Por quê (valor para o usuário)

Dá ao interessado B2B um lugar para entender a oferta e deixar contato, e começa a formar a **lista de
demanda B2B** (métrica primária do épico: um lead registrado e verificável). É o primeiro passo do funil de
receita principal.

## Critérios de aceite

Cada item é uma asserção testável. O agente DEVE escrever testes que cubram cada um.

- [ ] **CA-1:** Existe uma rota **pública** que serve a landing B2B (Quantah Intelligence); um visitante
      não logado a abre. A página usa o DS e está 100% em **pt-BR** via i18n, com a proposta da marca
      ("Do cupom ao insight.") e tom sério/analítico (voz e tom do DS).
- [ ] **CA-2:** Dado um visitante na landing B2B, quando preenche o formulário com **nome, e-mail e
      empresa válidos** e envia, então o lead é **persistido** e ele recebe uma **confirmação em pt-BR**.
- [ ] **CA-3:** Dado um envio com **campo obrigatório ausente ou e-mail inválido**, quando envia, então o
      sistema **bloqueia** e mostra mensagem de erro em pt-BR citando o campo problemático, **sem persistir**.
- [ ] **CA-4:** Dado um e-mail **já cadastrado como lead**, quando reenvia, então o sistema trata o
      **duplicado** de forma idempotente (não cria lead duplicado) e responde sem vazar dado de terceiros.
- [ ] **CA-5:** A página é **mobile-first** e atende a11y AA (contraste, alvo ~48px, foco visível, rótulos
      de formulário associados, mensagens de erro acessíveis).
- [ ] **CA-6:** E2E em browser real (mobile) percorre o caminho feliz de captação (preencher → enviar →
      confirmação) e ao menos um desvio (inválido **ou** duplicado), com asserções de texto em pt-BR.

## Fora de escopo

- **Lista/visualização dos leads no Backoffice** — é a STORY-027 (aqui só captura + persistência).
- Login/conta B2B, área B2B autenticada — fora da onda (PDR-003).
- Automação de e-mail marketing / nurturing do lead — fora do épico (só captura + persistência).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`: cobertura ≥ 80% no código novo e **≥ 98% na regra
de captação/persistência do lead** (núcleo de negócio: validação, deduplicação); **E2E em browser real**
(CA-6); **a11y AA**; **pt-BR** (§5.1); **LGPD** (§4 — dado pessoal do lead: minimização, sem vazar
existência de terceiros no tratamento de duplicado, sem PII em log). Sem código não testado.

## Dependências

- **Bloqueada por:** — (independente; reusa persistência/RBAC existentes).
- **Bloqueia:** STORY-027 (a lista de leads no Backoffice consome o que esta estória persiste) e STORY-028
  (validação).
- **Pré-requisitos de ambiente:** homologação operante; spec/protótipo do Designer para a tela.

## Decisões já tomadas (não as reabra)

- PDR-003 (B2B nesta onda = captação de lead, sem login/conta B2B).
- ADR-009 (RBAC — o lead vive atrás do papel operacional; consumido no Backoffice pela STORY-027).
- PDR-001 (DS), ADR-011/IDR-010 (i18n). Marca/tom conforme `docs/visao.md` §11.

## Liberdade técnica do agente

Você decide o modelo de dados do lead (colunas, índices), a estrutura de código/testes e refatorações
locais, dentro das decisões já tomadas. Você **não** decide stack/framework, critérios de aceite, nem a
spec visual (é do Designer). Se perceber necessidade de decisão arquitetural sem ADR cobrindo, **pare e
registre** em "Notas do agente".

## Definição de Pronto (DoD)

- [ ] Todos os critérios de aceite passam.
- [ ] Testes unitários escritos e passando, atingindo as coberturas exigidas (incl. ≥ 98% na regra do lead).
- [ ] Teste E2E (CA-6) escrito e passando em homologação.
- [ ] Entrada em `design.screens[]` (`SCREEN-STORY-026-...`) existe antes de `in_review` (invariante v2 #9).
- [ ] Pipeline de CI verde; deploy de homologação realizado e verificado.
- [ ] IDR registrado se houve descoberta técnica relevante (ex.: modelo do lead).
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. Coordene com o Designer (PDR-002) para a spec antes
de codificar UI (`design.screens[*].status: ready` é pré-condição — invariante v2 #12).

## Briefing de Design (PO → Designer)

Brief consolidado das duas landings do épico em `epics/EPIC-005-portas-de-entrada/design-handoff.md`
(seção **STORY-026 — Landing B2B**). Ponto de partida para o rabisco: intenção da tela, **todos os estados
do formulário de lead** (padrão/preenchendo/enviando/sucesso/erro por campo/duplicado), ativos do DS a
reusar (`hero-band-dark`, `pattern.form` + `input.text`, `button.primary`, `snackbar`), marca/voz B2B
("Do cupom ao insight."), a11y AA + pt-BR, e **LGPD** (microcopy de consentimento proposta pelo Designer,
validada pelo PO). Perguntas em aberto no handoff. Segue o fluxo paralelo (PDR-002): rabisco → sync →
spec + protótipo HTML → validação humana antes de `ready`.

## Notas do agente (preenchido durante/após execução)

### Sync de viabilidade (Designer × Programador · 2026-07-05)

Fluxo paralelo (PDR-002). Casca pública já existe (DDR-005 / `PublicLayout` face `b2b`). Limitações da
stack e ajustes acordados:

- **Rota `/intelligence` passa a servir a landing B2B** (`LandingB2B`), substituindo
  `Pages/Intelligence/Reservado.jsx` (área "reservada" da STORY-023). Impacto de teste **consciente**:
  `SegmentacaoAreasTest` (Feature e Browser) asserta hoje o "Em breve" (`b2b-em-breve`) da página
  reservada — será **repontado** para a landing real (pública, sem login, "Do cupom ao insight.",
  formulário de lead). A barreira B2B "sem login" continua válida (não há CTA de entrada na face B2B).
- **Formulário:** `pattern.form` com `useForm` + `TextField` (o `Field` já entrega a11y: `aria-invalid`,
  `aria-describedby`, `role="alert"` no erro, label associado). `Button loading={processing}` (spinner no
  botão, sem tela branca). Padrão do `Saque/Solicitar.jsx`.
- **Backend (núcleo ≥98%):** controller fino + `FormRequest` (validação pt-BR) + ação de domínio
  `CapturarLead` (normaliza e-mail, deduplica) — isolar a regra num objeto testável garante os 98%.
  Modelo `Lead` + migração `leads` (nome, e-mail **único**, empresa, timestamps).
- **Duplicado idempotente (CA-4 / LGPD):** `firstOrCreate` por e-mail normalizado (minúsculas/trim) →
  **mesma** tela de agradecimento em ambos os casos, sem revelar existência de terceiro. Sem PII em log.
- **Sucesso = tela dedicada de agradecimento (decisão #3 do PO):** via **PRG** (POST redireciona para
  GET dedicado, refresh-safe) — formaliza em **DDR-006** (gate G3) antes de codar UI. Evita `snackbar`-only.
- **LGPD (decisão #4 do PO):** aviso curto + link para a política, **sem checkbox**. Proponho o texto
  (G3), o PO valida. **Gap:** não existe página/rota de política de privacidade hoje → o destino do link
  precisa de decisão do PO (criar página mínima `/privacidade` nesta estória × outro destino).
- **Ordem:** DDR-006 + texto LGPD (G3) → migração/modelo/ação/testes vermelhos → controller/rotas →
  UI (landing + form + agradecimento) → Dusk (feliz + desvio) → CI/homolog.

### Decisões tomadas
- <data> — <decisão local, ex.: modelo do lead>

### Descobertas
- <data> — <gotcha / item para o PO/Designer saberem>

### Bloqueios encontrados
- <data> — <bloqueio> — <resolução ou aberto>

### IDRs criados
- <nenhum até aqui>

### Cobertura final
- Unitários: <%>
- E2E: <cenários, evidência>

### Links de evidência
- PR / Pipeline / Deploy de homologação: <urls>
