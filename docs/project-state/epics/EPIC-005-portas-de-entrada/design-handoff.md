# Handoff de Design — EPIC-005 (Portas de entrada)

> **Brief PO → Designer.** Ponto de partida para as duas telas `requires_design: true` do épico:
> **STORY-025 (landing B2C)** e **STORY-026 (landing B2B — Quantah Intelligence)**. Segue o modelo
> paralelo (PDR-002 / `designer/references/collaboration-with-developer.md`): Designer e Programador
> pegam a mesma estória juntos; o Designer produz o rabisco (≤30 min) → sync → spec detalhado + protótipo
> HTML fiel → apresenta ao humano antes de `ready`. Este documento dá o **o quê / por quê / referências**;
> o **como** (layout, hierarquia, microinterações) é do Designer. Onde o brief tocar UI, é intenção do PO,
> não prescrição — proponha e valide.

Criado: 2026-07-05 · Owner: PO (Alexandro) · Épico: `epics/EPIC-005-portas-de-entrada/epic.md`

## Por que este épico existe (resumo)

A plataforma não tem porta de entrada pública. Quem chega cai direto no login (EPIC-004), sem entender o
que é o Quantah; e um interessado B2B não tem para onde olhar. Estas duas landings são a face pública:
uma converte visitante em Coletador (topo do funil B2C), a outra apresenta o Quantah Intelligence e captura
lead B2B. Ambas mobile-first, em pt-BR, sobre o DS.

## Leitura obrigatória antes do rabisco

- Estórias: `stories/STORY-025-landing-b2c.md` e `stories/STORY-026-landing-b2b-captacao-de-lead.md`
  (frontmatter, CAs, "fora de escopo" — os CAs são o contrato funcional; **não os duplique no spec**).
- `docs/visao.md` §2 (problema/oportunidade), §10 (monetização), **§11 (marca, taglines, arquitetura
  B2C/B2B)**.
- Design System vivo: `design/system/` — em especial `components.md` (bands/hero, marca, footer,
  empty-state, snackbar, form/inputs), `patterns.md` (`pattern.form`, `pattern.auth`, `pattern.empty`,
  `pattern.error`, `pattern.surface-rhythm`), `voice-and-tone.md` (as duas faces), `tokens.md`.
- DDR-004 (identidade de marca no acesso — herança visual do EPIC-004) e o SCREEN da STORY-021 (login),
  para onde o CTA de entrada do B2C aponta e para manter continuidade visual.
- PDR-003 (escopo da onda: B2B = só captação de lead; sem login/conta B2B) e PDR-001 (DS).

## Marca e voz (contexto de produto — vem de `visao.md` §11 e do DS)

- **Duas faces, uma marca.** Quantah (app B2C, Coletador) × Quantah Intelligence (B2B). O DS já registra a
  distinção em `voice-and-tone.md`:
  - **B2C (Coletador):** próximo, encorajador, simples — colega prestativo. Tagline: **"Cada nota conta."**
  - **B2B (Quantah Intelligence):** sério, credível, analítico, preciso, sem informalidade.
    Tagline: **"Do cupom ao insight."**
- **Regra de ouro do DS preservada:** verde (`primary`) como **único accent de CTA**; sem gradiente
  festivo, sem emoji no produto, sem mascote. Ilustração só quando comunica.
- O DS já tem `hero-band` / `hero-band-dark` (bands de marketing) e a família `brand.*` — reuse antes de
  propor componente novo. Componente novo só via **DDR**.

## STORY-025 — Landing B2C (pública)

**Intenção:** primeira tela que o visitante vê; explica a proposta do Quantah para o Coletador e dá um
caminho claro de entrada. Tom **B2C** (próximo/encorajador).

**Deve permitir (dos CAs — funcional, não layout):**

- Um **CTA primário de entrada** que leva ao login/cadastro do Coletador (EPIC-004) — é a conversão que a
  métrica primária do épico mede.
- Um **CTA secundário para a página B2B** (destino = landing da STORY-026).
- Proposta de valor da marca ("Cada nota conta.") e o "porquê coletar cupons" em linguagem simples.

**O PO precisa que o design cubra (não esqueça a parte feia):** a landing é majoritariamente estática, mas
especifique ao menos: estado **padrão** (mobile ≥360px e desktop ≥1024px), **foco/hover** dos CTAs, e o
comportamento do CTA-B2B **enquanto a STORY-026 não existir** (o link aponta para a rota planejada; alinhe
com o Programador o placeholder até 026 subir).

**Ativos do DS a reusar (sugestão — Designer decide a composição):** `hero-band`/`hero-band-dark`,
`brand.lockup`, `button.primary` (entrar), `button.secondary` ou `nav.link` (ir para B2B), `content-band`,
`footer`.

**Decisões do PO aplicadas:** header/rodapé **compartilhado** (candidato a DDR); conteúdo = hero + CTAs +
seção **"como funciona" (3 passos)**, sem moldura de piloto; **herança visual forte** do login (DDR-004:
`brand.lockup`, tipografia, verde).

## STORY-026 — Landing B2B (Quantah Intelligence, pública)

**Intenção:** vitrine do produto de dados + **captação de lead**. Tom **B2B** (sério/analítico). Nesta onda
é só vitrine + lista de interessados — **sem login/conta B2B** (PDR-003).

**Deve permitir (dos CAs — funcional, não layout):**

- Proposta de valor ("Do cupom ao insight.") com o tom analítico da face B2B.
- **Formulário de captação de lead**: nome, e-mail, empresa → valida, persiste e confirma.
- Tratamento explícito de **desvios**: campo ausente / e-mail inválido (erro por campo, sem persistir) e
  **e-mail duplicado** (idempotente, sem vazar dado de terceiros).

**O PO precisa que o design cubra todos os estados do formulário:** **padrão/vazio**, **preenchendo**,
**enviando** (loading — sem spinner em tela branca; use `skeleton`/estado do botão), **sucesso**
(confirmação em pt-BR via `snackbar`/tela de agradecimento), **erro de validação por campo**
(`pattern.form` + `pattern.error`) e **duplicado** (mensagem neutra). Rótulos associados aos campos,
mensagem de erro textual (não só borda).

**Ativos do DS a reusar (sugestão):** `hero-band-dark` (headline verde sobre `ink` combina com o tom
B2B), `pattern.form` + `input.text`, `button.primary` (enviar), `snackbar`/`empty-state`, `footer`.

**Decisões do PO aplicadas:** header/rodapé **compartilhado** (mesmo da B2C); confirmação de sucesso =
**tela dedicada de agradecimento** (não só `snackbar`) — especifique-a como tela/estado do fluxo, com
espaço para o próximo passo ("entraremos em contato"); **LGPD** = aviso curto + link para a política, **sem
checkbox** (Designer propõe o texto, PO valida antes de produção).

## Requisitos transversais (valem para as duas telas)

- **Mobile-first com paridade** (≥360px e ≥1024px; tablet se o comportamento mudar) — `mobile-desktop-parity.md`.
- **pt-BR 100%** via i18n (STORY-020); sem resíduo de scaffolding em inglês, sem logo do Laravel.
- **a11y AA**: contraste dos tokens, alvo de toque ≥48px, foco visível, semântica, rótulos de formulário —
  `accessibility-basics.md`. É o piso que o Programador vai cumprir e o Validador vai conferir no fim do épico.
- **Só tokens do DS** (PDR-001); verde como único accent de CTA.
- **Identificadores lógicos** sugeridos no spec para ancorar os E2E (CA-6 de cada estória).

## LGPD — atenção na STORY-026 (dado pessoal do lead)

O formulário coleta PII (nome, e-mail, empresa). **A microcopy de consentimento/privacidade é de decisão
do PO** (SKILL do Designer, fronteira fuzzy de microcopy): você **propõe** o texto e o local (ex.: aviso
curto abaixo do botão de envio, link para política), o **PO valida** antes de ir para produção. Traga essa
proposta no spec; não finalize o texto legal sozinho.

## Decisões do PO (respondidas 2026-07-05)

As cinco perguntas em aberto foram decididas pelo PO (Alexandro). Partem resolvidas — o Designer desenha
sobre elas; se alguma se mostrar inviável ou pior no protótipo, registre e traga de volta ao PO.

1. **Header/footer público compartilhado (B2C↔B2B).** As duas landings usam uma **barra e um rodapé
   comuns**, com link de alternância entre as faces. Consistência de marca e navegação cruzada dos CTAs.
   → Sendo um elemento durável em mais de uma tela, o **header/rodapé público candidata-se a DDR** — o
   Designer avalia e formaliza se for padrão (não invente componente novo fora do DS sem DDR).
2. **Landing B2C = hero + CTAs + "como funciona" (3 passos).** Incluir uma seção curta de 3 passos que
   explica a proposta, sem inflar a página (simplicidade radical). Sem moldura de piloto nesta onda.
3. **Confirmação do lead B2B = tela dedicada de agradecimento.** Após envio bem-sucedido, o visitante vai
   para uma **tela de agradecimento** (não apenas `snackbar`), com espaço para o próximo passo (ex.:
   "entraremos em contato"). Isso adiciona uma tela/estado ao fluxo da STORY-026 — especifique-a.
4. **LGPD = aviso curto + link para a política, sem checkbox.** Consentimento no ato do envio (menor
   fricção, adequado a lead B2B). O Designer **propõe o texto**; o **PO valida** antes de produção. Sem
   checkbox de opt-in nesta onda.
5. **Continuidade visual forte com o login (DDR-004).** A landing B2C herda a identidade do EPIC-004
   (`brand.lockup`, tipografia, verde como accent) para reconhecimento imediato ao passar de "entrar" para
   o cadastro/login.

## Fora de escopo do design (não desenhar nesta onda)

- Área B2B autenticada / login B2B (PDR-003). SEO avançado, blog, páginas institucionais. Automação de
  e-mail/nurturing do lead. A **lista de leads no Backoffice** é a STORY-027 (`requires_design: false`,
  reusa o padrão do Backoffice do EPIC-004) — não precisa de spec de tela dedicado.

## Próximos passos (fluxo paralelo)

1. Designer produz **rabisco** (≤30 min) de cada landing em `design/screens/STORY-025-landing-b2c.md` e
   `design/screens/STORY-026-landing-b2b-quantah-intelligence.md` (`status: draft`) e adiciona as entradas
   em `design.screens[]` do `index.json`.
2. **Sync curto** com o Programador (limitações da stack Inertia + React).
3. Spec detalhado + **protótipo HTML fiel** (todos os estados) → `present_files` ao PO para validação →
   `status: ready`. Ao virar `ready`, preencher `design_screen_id` nas estórias (invariante v2 #10).
