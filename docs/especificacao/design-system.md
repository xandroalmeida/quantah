# Design System Quantah — Referência canônica

> **Contrato durável de UX/UI do Quantah.** Este é o ponteiro canônico do Design System referido
> pelo `index.json` (`design.system.canonical_reference`). O DS **vivo** (tokens, componentes,
> padrões, voz) mora em `docs/project-state/design/system/` e é editado pelo Designer via DDR.
> Este documento fixa a origem, as regras invariantes e o mapa dos artefatos.

## Origem e governança

- **Fonte:** `docs/DESIGN-wise.md` (design system Wise-derived).
- **Adoção:** decidida no **PDR-001** (produto) — status `accepted`.
- **Stack alvo:** Inertia + React (ADR-000, `accepted`); tokens → `tailwind.config.js` +
  CSS variables (`stacks/inertia-react/SKILL.md`).
- **Decisões de fundação registradas:**
  - **DDR-001** — substituição da fonte proprietária Wise Sans por Inter 900 / Manrope.
  - **DDR-002** — diferenciação de marca: usar o DS como *base de sistema*, não clonar o Wise.
- **Evolução:** qualquer mudança de fundação (paleta, tipografia, regra de acento) ou novo
  componente entra por **DDR** com aprovação humana. O schema do `index.json` é do PO.

## Artefatos vivos (fonte de detalhe)

| Arquivo | Conteúdo |
|---|---|
| `docs/project-state/design/system/README.md` | Entrada e regras de ouro. |
| `docs/project-state/design/system/tokens.md` | Cor, tipografia, spacing, raio, elevação, motion, breakpoints. |
| `docs/project-state/design/system/components.md` | Biblioteca de componentes (id + estados + React). |
| `docs/project-state/design/system/patterns.md` | Padrões compostos. |
| `docs/project-state/design/system/voice-and-tone.md` | Tom e microcopy. |

## Invariantes (não mudam sem DDR + aval humano)

1. **Verde `#9fe870` (`primary`) é o único accent de CTA** — nunca indicador de sucesso.
2. **Sem segundo accent de marca** (laranja/ciano só em ilustração/gráfico).
3. **Raio 24px (`xl`) em botões e cards**; nunca CTA com canto reto.
4. **Ritmo de superfície:** página sage (`#e8ebe6`) → cards brancos (`#ffffff`); elevação por
   contraste de superfície.
5. **Tipografia:** display peso 900 no hero, 600 no restante (fonte conforme DDR-001).
6. **Acessibilidade AA:** contraste 4.5:1 (texto), alvo de toque ≥48px em mobile, foco visível,
   feedback nunca só por cor.

## Padrões transversais herdados do PDR-001 (qualidade de produto, do PO)

Toda estória com UI: referencia o DS canônico; usa só tokens do sistema; é
`requires_design: true`; mobile-first com paridade responsiva.
