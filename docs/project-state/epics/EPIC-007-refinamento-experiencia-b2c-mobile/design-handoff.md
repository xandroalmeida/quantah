# Handoff de Design — EPIC-007 (Refinamento da experiência B2C mobile)

> **Brief PO → Designer.** Ponto de partida para a única tela `requires_design: true` do épico:
> **STORY-034 — tela de detalhe do cupom**. Segue o modelo paralelo (PDR-002 /
> `designer/references/collaboration-with-developer.md`): Designer e Programador pegam a estória juntos; o
> Designer produz o rabisco (≤30 min) → sync → spec detalhado + protótipo HTML fiel → apresenta ao humano
> antes de `ready`. Este documento dá o **o quê / por quê / referências**; o **como** (layout, hierarquia,
> microinterações) é do Designer. Onde o brief tocar UI, é intenção do PO, não prescrição — proponha e valide.

Criado: 2026-07-05 · Owner: PO (Alexandro) · Épico:
`epics/EPIC-007-refinamento-experiencia-b2c-mobile/epic.md`

## Por que este épico existe (resumo)

A jornada B2C está de pé (EPIC-006), mas o teste no celular expôs atritos. A maioria dos ajustes é de casca
(STORY-033), regra (STORY-035) ou coerência (STORY-036) — **sem tela nova**. A exceção é a **tela de
detalhe do cupom** (STORY-034): hoje o cupom aparece como um número solto no histórico, sem onde/quando a
compra foi feita e sem como conferir os itens. Esta tela dá **contexto e prova** ("a nota contou").

## Leitura obrigatória antes do rabisco

- Estória: `stories/STORY-034-cupom-listagem-e-detalhe.md` (frontmatter, CAs, "fora de escopo" — os CAs são
  o contrato funcional; **não os duplique no spec**).
- Design System vivo: `design/system/` — `components.md` (cards/superfícies, badges de status, empty-state),
  `patterns.md` (`pattern.surface-rhythm`, listas, navegação e retorno), `voice-and-tone.md` (face B2C),
  `tokens.md`.
- Telas base reusadas: `design/screens/STORY-016-carteira-saldo-historico` (a **listagem/histórico** de
  onde se abre o detalhe) e `STORY-030-navegacao-b2c` (retorno consistente à casca).
- Modelo de dados: ADR-001 (cupom canônico) — o cupom tem estabelecimento (novo `nome_emitente`),
  `data_emissao`, `valor_total`, `status` e **itens** (`descricao`, `quantidade`/`unidade`,
  `valor_unitario`, `valor_total`). ADR-006: sem PII do consumidor na tela.

## Telas a especificar

### SCREEN-STORY-034 — Detalhe do cupom (cabeçalho + itens)

- **Intenção (PO):** ao tocar um cupom na listagem/histórico, abrir uma tela que responda "**que compra foi
  essa e o que tinha nela**". Mobile-first, sobre o DS, com retorno consistente à listagem (casca DDR-007).
- **Conteúdo mínimo:**
  - **Cabeçalho:** nome do estabelecimento (fallback quando ausente — ex.: CNPJ/"Estabelecimento não
    identificado"), data de emissão (pt-BR), valor total (R$), e o **status** do cupom (badge — pendente/
    validado/etc., reusando os estados do DS).
  - **Lista de itens:** por item, descrição, quantidade + unidade, valor unitário e valor total. Legível em
    telas estreitas (sem overflow horizontal; descrições longas quebram/truncam com elegância).
- **Estados a cobrir:** cupom **validado com itens** (caso cheio); cupom **pendente/sem itens ainda**
  (empty/loading acolhedor — a extração é assíncrona); estabelecimento **sem nome** (fallback).
- **Fronteira:** hierarquia, composição da lista de itens e microinterações são do Designer. O PO fixa:
  cabeçalho (estabelecimento, data, total, status) + lista de itens como o conteúdo, sobre o DS, a11y AA,
  100% pt-BR, com retorno claro à listagem.

### Ajuste na listagem (sem tela nova, mas toca a UI existente)

- Na **listagem/histórico da Carteira** (SCREEN-STORY-016), cada item passa a mostrar **estabelecimento +
  data de emissão** (além de valor/crédito) e fica **clicável** (afeta ao detalhe). Não é tela nova — é
  evolução do item de lista existente; alinhar com o Designer para manter o ritmo/tokens do DS.

> **STORY-033, STORY-035 e STORY-036** não têm tela nova. STORY-033 é comportamento de casca (viewport/
> `nav.bottom` fixo/PWA) sobre DDR-007/005; STORY-035 é regra de ingestão; STORY-036 é remoção/renome
> (i18n). Se o rótulo do menu de escanear precisar de validação de voz, alinhe rapidamente com o Designer.
