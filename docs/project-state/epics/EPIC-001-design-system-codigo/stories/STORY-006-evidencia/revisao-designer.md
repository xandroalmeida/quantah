# Revisão de fidelidade — Designer · STORY-006 (Cards, feedback, nav + vitrine)

**Papel:** Designer · **Data:** 2026-07-02 · **Escopo:** `requires_design: true` — revisar a
**fidelidade** dos componentes de superfície/status/feedback/nav e da **vitrine kitchen-sink** (`/ds`)
à spec de `components.md` e às regras de ouro do DS, em browser real (mobile 390px + desktop 1280px).
Confirmação de fidelidade que a estória pede (PDR-002) — o veredito de implementação do épico é do
Validador (STORY-007).

## Método

- Cross-check contra `components.md` (§Cards, §Navegação, §Feedback & status), `tokens.md`,
  `patterns.md` (surface-rhythm) e as regras de ouro (`README`, `design-system.md`).
- Estilos **computados em browser real** (Dusk/Chrome): contraste, foco, alvo ≥48px, aria.
- Verificação visual em **desktop 1280px** (`ds-desktop-full.png`) e **mobile 390px** (`ds-mobile-top.png`).

## Fidelidade dos componentes — **fiel** (estilos computados)

| Componente | Spec `components.md` | Implementado | OK |
|---|---|---|---|
| `card.content/feature-sage/feature-green/feature-dark` | raio `xl`, padding `xl`; dark = ink+primary (marca) | `Card` 4 variantes por token, raio `xl` p-`xl` | ✓ |
| `badge.positive/negative` (+warning/info) | pill, `body-sm-strong`, ícone+texto | `Badge` pill, `body-sm`/600, ícone+texto | ✓ |
| `snackbar` success/warning/danger/info | canvas, raio `xl`, `md lg`, ícone+texto, `aria-live=polite` | `Snackbar` idem, `role=status` + `aria-live` | ✓ |
| `empty-state` | ícone + título `display-xs` + instrução `body-md` + `button.primary`, instrui o passo | `EmptyState` idem | ✓ |
| `skeleton` | placeholder de loading (nunca spinner em tela vazia) | `Skeleton` line/block/circle, `aria-hidden`, pulse | ✓ |
| `nav.bar` + `nav.link` | canvas/ink, sticky; ativo = indicador `primary` | `NavBar` sticky + `NavLink` (borda inferior `primary` no ativo) | ✓ |
| `nav.bottom` | mobile, item ativo `primary`, alvos ≥48px | `NavBottom` ícone `primary` no ativo, `min-h-3xl` | ✓ |
| `footer` | band ink, texto canvas-soft, `body-sm`, `3xl xl` | `Footer` idem | ✓ |

## A11y — **fiel** (medido em browser real)

- **Feedback nunca só cor:** badges e snackbars têm **ícone + texto** (não só cor). ✓
- **Snackbar anuncia:** `aria-live="polite"` + `role="status"` (mudança dinâmica). ✓
- **Empty-state instrui:** título + instrução + CTA `button.primary` focável. ✓
- **Skeleton decorativo:** `aria-hidden="true"` (fora da árvore de a11y). ✓
- **Contraste AA (rgb real):** card escuro (primary sobre ink) ≈ 13:1; badge.positive ≈ 12:1;
  badge.negative (branco sobre negative-bg) ≈ 18:1 — todos ≥ 4.5:1. ✓
- **Foco visível:** ring `ink` no `NavLink` e nos itens da `NavBottom`. ✓
- **Alvo de toque ≥48px:** itens da `NavBottom` via `min-h-3xl`. ✓

## Vitrine `/ds` — **fiel**

- Renderiza **todos** os componentes do DS (botões STORY-004 + inputs STORY-005 + os desta) com
  estados, organizada por seção com referência à spec. ✓ (CA-2)
- **Navegável:** o próprio `NavBar`/`NavLink` navega por âncoras (#buttons/#inputs/#cards/#feedback/#nav)
  — dogfooding do componente. ✓
- **Ritmo de superfície:** página sage → clusters em card branco (`shadow-elev-2`); cards de destaque
  e o dark (marca) contrastam. ✓

## Regras de ouro — **respeitadas**

1. Verde `primary` só como CTA/indicador (nav ativo, empty-state CTA) — badges de sucesso usam a
   **família semântica** (`positive-deep`), não o verde de marca. ✓
2. Sem segundo accent de marca. ✓
3. Raio `xl` (24px) em cards; badges em `pill`; inputs seguem `md` (STORY-005). ✓
4. Ritmo de superfície sage → branco; elevação por contraste. ✓
5. Feedback nunca só cor (ícone + texto em badge/snackbar). ✓
6. Display 900 no título; 600 no resto. ✓

## Divergência encontrada — corrigida na mesma operação

- **Overflow horizontal em mobile.** A `NavBar` (topo web, 5 links) empurrava a largura da página em
  390px (`scrollWidth` 485 > 375) — viola a regra "body nunca rola na horizontal". **Corrigido:** a
  `NavBar` ganhou `overflow-x-auto` — os links rolam **dentro** da barra, sem empurrar a página. Guarda
  de regressão adicionada no Dusk (`test_no_horizontal_page_overflow_on_mobile`).

## Observações (não-bloqueantes)

- A `nav.bar` é um componente **web**; em mobile o app real usa a `nav.bottom`. A barra de topo rola
  internamente na vitrine (aceitável para uma página de referência de dev).
- Bands/hero de marketing ficaram fora (não são da lista mínima da Onda 1) — entram quando uma tela
  de marketing pedir (via DDR).

## Veredito

> **Fidelidade dos componentes de superfície/feedback/nav e da vitrine `/ds` CONFIRMADA.** Cada
> componente reproduz os tokens e estados da spec em browser real, com a11y AA (contraste, foco, ≥48px,
> aria-live, ícone+texto), e a vitrine prova o DS inteiro em código, navegável e no ritmo de superfície
> da marca. A única divergência (overflow horizontal em mobile) foi **corrigida na mesma operação**.
> Gate de design da STORY-006 satisfeito.
