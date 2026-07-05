# Handoff de Design — EPIC-006 (Jornada do Coletador)

> **Brief PO → Designer.** Ponto de partida para as telas `requires_design: true` do épico:
> **STORY-029 (home-hub do Coletador)** e **STORY-030 (navegação coesa da área B2C)**. Segue o modelo
> paralelo (PDR-002 / `designer/references/collaboration-with-developer.md`): Designer e Programador pegam
> a mesma estória juntos; o Designer produz o rabisco (≤30 min) → sync → spec detalhado + protótipo HTML
> fiel → apresenta ao humano antes de `ready`. Este documento dá o **o quê / por quê / referências**; o
> **como** (layout, hierarquia, microinterações) é do Designer. Onde o brief tocar UI, é intenção do PO,
> não prescrição — proponha e valide.

Criado: 2026-07-05 · Owner: PO (Alexandro) · Épico: `epics/EPIC-006-jornada-b2c/epic.md`

## Por que este épico existe (resumo)

Depois de logar (EPIC-004), o Coletador cai numa página genérica — as peças da Onda 1 (coleta, carteira,
saque) existem, mas soltas. Este épico entrega a **costura**: uma **home-hub mobile** que passa a ser o
destino pós-login e liga coletar → saldo → extrato → saque numa jornada contínua, sem página genérica. É a
entrega final de valor B2C da onda e a condição para o piloto sustentar a coleta (north-star).

## Leitura obrigatória antes do rabisco

- Estórias: `stories/STORY-029-home-hub-coletador.md` e `stories/STORY-030-navegacao-coesa-b2c.md`
  (frontmatter, CAs, "fora de escopo" — os CAs são o contrato funcional; **não os duplique no spec**).
- `docs/visao.md` §3.1 (princípios de produto), §5.1 (MVP coleta e incentivo), §4.1 (personas de coleta).
- Design System vivo: `design/system/` — `components.md` (cards/superfícies, marca, empty-state, snackbar),
  `patterns.md` (`pattern.surface-rhythm`, navegação e retorno, `pattern.empty`), `voice-and-tone.md`
  (face B2C), `tokens.md`.
- Telas da Onda 1 que a jornada reusa e para onde a home aponta:
  `design/screens/STORY-016-carteira-saldo-historico` (saldo/extrato), `STORY-009-captura-qr-confirmacao`
  (coleta), `STORY-017-solicitar-saque` (saque).
- DDR-004 (identidade de marca no acesso) e **DDR-005 (casca pública compartilhada)** — para continuidade
  visual entre a casca e a área logada.

## Telas a especificar

### SCREEN-STORY-029 — Home-hub do Coletador (destino pós-login)

- **Intenção (PO):** primeira tela da jornada logada. Precisa comunicar, de imediato, **o que ganhei**
  (saldo da carteira, com destaque) e **o que faço agora** (CTA primário "coletar cupom"). Mobile-first.
- **Conteúdo mínimo:** saldo atual (R$ 0,00, formato BR); CTA primário destacado de coleta (verde, único
  accent de CTA); ponto de partida para os atalhos que a STORY-030 detalha.
- **Estados a cobrir:** saldo zero (primeiro acesso — empty-state acolhedor que convida a coletar) e saldo
  positivo. Nome/saudação do Coletador se fizer sentido na voz da marca.
- **Fronteira:** hierarquia visual, composição e microinterações são do Designer. O PO só fixa: saldo +
  CTA de coleta como os dois elementos primários, sobre o DS, a11y AA.

### SCREEN-STORY-030 — Navegação coesa da área B2C

- **Intenção (PO):** o padrão de navegação que liga a home às telas da Onda 1 (extrato/histórico,
  prêmios/saque, coleta) e o **retorno consistente** à home — sem beco sem saída e sem página genérica.
- **Meta observável:** coletar e consultar saldo/histórico em **≤ 2 toques** a partir da home.
- **Decisão de padrão:** a forma da navegação (barra inferior, atalhos em cards na home, etc.) é do
  Designer — proponha o que melhor serve o mobile e a a11y. Se virar padrão transversal da área logada,
  registre um **DDR**.
- **Fronteira:** o PO fixa os destinos (extrato, saque, coleta), a meta de ≤ 2 toques e "nenhuma página
  genérica"; o **como** é do Designer.

> **STORY-031** (jornada contínua + E2E) **não** tem tela nova — reusa a home (029), a navegação (030) e as
> telas da Onda 1. Não requer artefato de design próprio.

## Requisitos transversais (valem para as duas telas)

- **pt-BR** em todo texto visível (i18n, STORY-020); formatos brasileiros (R$, dd/mm/aaaa, America/Sao_Paulo).
- **DS estrito:** só tokens do sistema (sem cor/fonte/raio/spacing fora dos tokens); **verde como único
  accent de CTA** (PDR-001).
- **Mobile-first** e **a11y AA:** contraste ≥ 4.5:1, foco por teclado visível, alvos toucáveis adequados.
- **Sem resíduo de scaffolding** (sem logo do Laravel, sem dashboard genérico do EPIC-004).

## O que está fora (não desenhar)

- Gamificação (pontos/ranking/níveis), ganchos de valor de dado ("você pagou mais/menos que a média"),
  notificações push/e-mail — Ondas futuras (visão §8.2).
- Novas telas de coleta/carteira/saque — reusa as da Onda 1 como estão.

## Fluxo de entrega (lembrete do modelo paralelo)

Rabisco (≤30 min) → sync com o Programador → spec detalhado + protótipo HTML fiel em
`design/screens/STORY-029-home-hub-coletador/` e `design/screens/STORY-030-navegacao-b2c/` → apresentar ao
humano → entrada em `design.screens[]` com `status: ready` (pré-condição para a UI ir a `in_review`,
invariante v2 nº 9). DDRs novos (ex.: padrão de navegação da área logada) vão em `decisions/ddr/`.
