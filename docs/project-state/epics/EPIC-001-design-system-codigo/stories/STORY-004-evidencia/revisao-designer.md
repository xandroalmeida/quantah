# Revisão de fidelidade — Designer · STORY-004 (Botões do DS)

**Papel:** Designer · **Data:** 2026-07-02 · **Escopo:** `requires_design: true` — revisar a
**fidelidade** do componente `Button` implementado à spec de `design/system/components.md` (§Botões)
e às regras de ouro do DS, em browser real (mobile 375px + desktop 1280px). Não é veredito de
implementação (esse é do Validador no fim do EPIC-001) — é a confirmação de fidelidade que a
estória pede (PDR-002).

## Método

- Cross-check das 5 variantes contra `components.md` §Botões e `tokens.md` (cor, tipografia,
  raio, elevação, toque).
- Estilos **computados em browser real** (Dusk/Chrome) por variante — não leitura de classe.
- Verificação visual nos viewports **mobile 375px** e **desktop 1280px**
  (`botoes-mobile.png`, `botoes-desktop.png` nesta pasta).
- Regras de ouro (`design/system/README.md`, `docs/especificacao/design-system.md`).

## Fidelidade das variantes — **fiel** (estilos computados)

| Variante | Fundo | Texto | Raio | Alvo | Spec `components.md` | OK |
|---|---|---|---|---|---|---|
| `primary` | `rgb(159,232,112)` = `#9fe870` | `rgb(14,15,12)` = on-primary | 24px | 48px | verde primary + on-primary, único CTA | ✓ |
| `secondary` | `rgb(232,235,230)` = `canvas-soft` | `ink` | 24px | 48px | sage, fundo canvas-soft, texto ink | ✓ |
| `tertiary` | `canvas` (branco) + borda 1px `ink` | `ink` | 24px | 50px¹ | outline, borda 1px ink (elev.1) | ✓ |
| `danger` | `rgb(208,50,56)` = `#d03238` = negative | branco (via `canvas`) | 24px | 48px | fundo negative, texto branco | ✓ |
| `icon` | `canvas` + borda 1px `ink` | `ink` | **9999px** (full) | 48×48 | círculo, raio full, padding sm, aria-label | ✓ |

¹ tertiary/icon medem 50px por causa da borda 1px (48 + 2) — acima do piso de 48px. OK.

- **Tipografia:** todos em `16px/600` = `button-md` (Inter 600). ✓
- **Estados:** `disabled` a 38% de opacidade (spec) + clique bloqueado; `loading` com spinner
  inline (label oculto), `aria-busy="true"`, clique bloqueado — **não** dimma (mostra progresso). ✓
- **Contraste AA:** medido no rgb real das 5 variantes ≥ 4.5:1 (danger branco/negative ≈ 5:1). ✓
- **Foco visível:** ring `ink` por teclado (`focus-visible`), verificado em browser. ✓

## Regras de ouro — **respeitadas**

1. Verde `primary` só como CTA; nunca como "sucesso" (a família semântica é outra). ✓
2. Sem segundo accent de marca — o vermelho é a **semântica destrutiva** (`negative`) da variante
   danger, não um accent de marca; nenhum laranja/ciano. ✓
3. Raio `xl` (24px) em todos os botões; `full` no icon-button (correto p/ círculo). ✓
4. **Ritmo de superfície:** página sage (`canvas-soft`) → clusters em card branco (`canvas`). ✓
   (ver divergência corrigida abaixo)
5. A11y AA: contraste, alvo ≥48px, foco visível, feedback nunca só por cor (loading tem spinner +
   aria-busy, não só cor). ✓
6. Paridade mobile/desktop: as variantes reflowam de linha única (desktop) para wrap (mobile 375)
   sem "desktop encolhido"; alvo de toque mantém 48px. ✓

## Divergência encontrada — corrigida na mesma operação

- **`button.secondary` invisível na vitrine (affordance).** Na primeira captura, "Cancelar"
  (fundo `canvas-soft`/sage) sumia porque a vitrine renderizava os botões **direto sobre a página
  sage** (mesmo tom) — o botão parecia texto, sem borda de botão. O componente está **fiel** à
  spec (secondary = fundo canvas-soft); a falha era da **vitrine**, que não seguia o ritmo de
  superfície do DS (página sage → card branco). **Corrigido:** os clusters de botões passaram a
  ficar sobre card `canvas` (branco) com `shadow-elev-2`. Na recaptura, o secondary contrasta e lê
  como botão. Sem mudança no componente `Button` nem nos CAs.

## Observações (não-bloqueantes, para o EPIC-001)

- `pressed` (overlay 12% da spec) foi aproximado por tokens de estado (`active:` → `primary-neutral`
  / `negative-darkest` / `primary-pale`), não por overlay exato de 12%. Fica fiel ao comportamento
  (feedback de pressão perceptível); um overlay exato pode ser um refino de token futuro (DDR) se o
  DS quiser padronizar o press em todas as superfícies.
- A vitrine aqui é só de botões; a kitchen sink completa (todos os componentes) é a STORY-006 —
  herdar este padrão de "cluster sobre card branco".

## Veredito

> **Fidelidade do componente `Button` CONFIRMADA.** As 5 variantes reproduzem os tokens canônicos
> (cor/tipografia/raio/toque) 1:1 em browser real, os estados e a a11y batem com a spec, e as
> regras de ouro são respeitadas em mobile e desktop. A única divergência (secondary invisível por
> falta de ritmo de superfície na vitrine) foi **corrigida na mesma operação**. Gate de design da
> STORY-004 satisfeito.
