# Design System Quantah

Vocabulário visual e de interação compartilhado por todas as telas do Quantah (app do
Colaborador + Quantah Intelligence). Descreve **comportamento e visual em tokens e estados**,
mapeáveis para o tema da stack ativa (Inertia + React / Tailwind).

**Origem:** consolidado de `docs/DESIGN-wise.md` (design system Wise-derived), **adotado pelo
PDR-001**. Duas decisões de fundação estão registradas em DDR: substituição da fonte
proprietária (**DDR-001**) e diferenciação de marca vs. Wise (**DDR-002**).

> **Designer** não escreve código de produção; **Programador** não inventa token.

## Como usar

- Antes de desenhar uma tela, leia este DS — o que você precisa provavelmente já existe.
- Antes de criar componente novo, confirme que o catálogo não cobre. Componente novo entra por
  **DDR** primeiro, não direto na tela.
- Spec de tela referencia componentes por id em `ds_components_used`.

## Navegação

- `tokens.md` — cor, tipografia, espaçamento, raio, elevação, motion, breakpoints.
- `components.md` — biblioteca de componentes (mapeada para React/Inertia).
- `patterns.md` — padrões compostos (form, wizard, listing, empty, error, ritmo de superfície).
- `voice-and-tone.md` — tom, microcopy, vocabulário.

## Regras de ouro (não-negociáveis do DS)

1. Verde `primary` é o **único** accent de CTA — nunca como "sucesso" (use a família semântica).
2. Sem segundo accent de marca; laranja/ciano só em ilustração.
3. Raio `xl` (24px) em botões e cards; nunca CTA com canto reto.
4. Ritmo de superfície: página sage → cards brancos; elevação por contraste, não sombra pesada.
5. Feedback nunca é só cor: sempre ícone + texto.
6. Fonte display peso 900 (hero), 600 no resto (ver DDR-001).

## Status

Versão: 0.1 — consolidação inicial a partir do DESIGN-wise.md (PDR-001).
Última atualização: 2026-07-02.
Referência canônica (contrato durável): `docs/especificacao/design-system.md`.
