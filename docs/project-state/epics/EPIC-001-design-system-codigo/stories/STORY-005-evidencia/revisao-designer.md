# Revisão de fidelidade — Designer · STORY-005 (Inputs do DS)

**Papel:** Designer · **Data:** 2026-07-02 · **Escopo:** `requires_design: true` — revisar a
**fidelidade** dos 7 componentes de input (`input.text/masked/datetime/select/checkbox/radio/switch`)
à spec de `design/system/components.md` (§Inputs & formulários) e às regras de ouro do DS, em browser
real (mobile 375px + desktop 1280px). Não é veredito de implementação (esse é do Validador no fim do
EPIC-001) — é a confirmação de fidelidade que a estória pede (PDR-002).

## Método

- Cross-check dos 7 componentes contra `components.md` §Inputs e `tokens.md` (cor, raio, elevação,
  toque, tipografia).
- Estilos **computados em browser real** (Dusk/Chrome): cor de borda/erro, contraste, foco, alvo ≥48px.
- Verificação visual nos viewports **mobile 375px** e **desktop 1280px** (`inputs-mobile.png`,
  `inputs-desktop.png`, `inputs-choice.png` nesta pasta).
- Regras de ouro (`design/system/README.md`, `docs/especificacao/design-system.md`).

## Fidelidade dos componentes — **fiel** (estilos computados)

| Componente | Chrome | Raio | Estados | Spec `components.md` | OK |
|---|---|---|---|---|---|
| `input.text` (TextField) | fundo `canvas`, borda 1px `ink`, texto `ink`, `body-md` | `md` (12px) | default/focus/disabled/error; **label flutuante** | fundo canvas, borda ink, raio md, label flutuante, helper, erro aria | ✓ |
| `input.masked` (MaskedField) | herda text; placeholder mostra o formato (`0000 …`) | `md` | idem; guarda **valor unmasked** | variante de text, máscara = UX, persiste sem máscara | ✓ |
| `input.datetime` (DateTimeField) | herda text; seletor nativo (calendário) | `md` | idem; guarda **ISO 8601** | por seletor, nunca digitação livre, valor ISO | ✓ |
| `input.select` (SelectField) | herda text; chevron nativo | `md` | idem | herda tokens de cor/raio/foco | ✓ |
| `input.checkbox` (Checkbox) | 24px, quadrado arredondado (`sm`), borda `ink`, marcado `primary` | `sm` | default/checked/disabled/error | selecionado usa `primary` como **indicador** | ✓ |
| `input.radio` (Radio) | 24px, círculo, borda `ink`, marcado `primary` | `full` | idem | idem | ✓ |
| `input.switch` (Switch) | `button role="switch"`, trilho `primary` ON / `canvas-soft` OFF, thumb `ink` | `full` | on/off/focus/disabled | selecionado usa `primary` como indicador | ✓ |

- **Raio dos inputs = `md` (12px)** — corretamente **distinto** dos botões/cards (`xl` 24px). ✓
- **Elevação:** borda 1px `ink` = `elev.1` hairline (spec de inputs). ✓
- **Tipografia:** valor `body-md` (16px); label estático/hint/erro `body-sm`; label ≥600. ✓

## A11y — **fiel** (medido em browser real)

- **Label associado:** `<label htmlFor>` (text/masked/datetime/select/checkbox/radio) e
  `aria-labelledby` (switch, pois button não é rotulável). `el.labels` populado. ✓
- **Erro acessível (feedback nunca só cor):** `aria-invalid="true"` + mensagem `role="alert"` ligada
  por `aria-describedby`; hint também ligado por `aria-describedby`. ✓
- **Contraste AA (rgb real):** borda do input vs fundo ≈ **19:1** (piso UI 3:1); texto de erro
  `negative-darkest` (#a7000d) vs card branco ≈ **7.9:1** (piso AA 4.5:1). ✓
- **Foco visível por teclado:** ring `ink` (`focus:ring-2`) em todos os controles; no erro, ring
  `negative`. ✓
- **Alvo de toque ≥48px:** inputs de caixa via `min-h-3xl`; campos de escolha via linha `min-h-3xl`;
  switch button `min-h-3xl`. ✓

## Regras de ouro — **respeitadas**

1. Verde `primary` só como **indicador** de selecionado (radio/checkbox/switch), nunca como
   preenchimento de área grande nem como "sucesso". ✓
2. Sem segundo accent de marca; o vermelho é a **semântica de erro** (`negative`), não accent. ✓
3. Raio `md` (12px) nos inputs — a assinatura `xl` (24px) fica reservada a botões/cards. ✓
4. **Ritmo de superfície:** página sage (`canvas-soft`) → clusters em card branco (`canvas`) com
   `shadow-elev-2` — herdado da vitrine de botões (STORY-004). ✓
5. A11y AA: contraste, alvo ≥48px, foco visível, feedback textual (não só cor). ✓
6. Paridade mobile/desktop: inputs full-width reflowam de 1280px para 375px sem "desktop encolhido";
   headline empilha; alvo de toque mantido. ✓

## Divergência encontrada — corrigida na mesma operação

- **Checkbox indistinguível do radio.** Na primeira captura, o checkbox (16px + raio `sm` 8px)
  renderizava como **círculo** (8px = metade de 16px), igual ao radio. **Corrigido:** checkbox e radio
  passaram a 24px; o checkbox mantém raio `sm` (quadrado arredondado) e o radio fica circular — agora
  são **par visual distinto**. Sem mudança de token nem de CA. (ver `inputs-choice.png`.)

## Observações (não-bloqueantes, para o EPIC-001)

- **Label flutuante** só em `input.text` (spec). `masked`/`datetime`/`select` usam label estático no
  topo — o masked porque o placeholder mostra o formato (spec), o datetime/select porque não têm
  `:placeholder-shown` útil. Coerente com a spec; se o DS quiser padronizar flutuante em todos,
  vira refino de token/DDR.
- **Seletor de data nativo** (`input.datetime`) — o formato exibido segue a locale do browser
  (ex.: `mm/dd/yyyy`), mas o **valor canônico é sempre ISO 8601**. Um calendário estilizado
  (`react-day-picker`) fica para quando uma tela de Coleta pedir (ADR/IDR), conforme IDR-003.
- A vitrine aqui é só de inputs; a kitchen sink completa é a STORY-006 — herdar este padrão.

## Veredito

> **Fidelidade dos 7 inputs do DS CONFIRMADA.** Cada componente reproduz os tokens canônicos
> (fundo/borda/raio/tipografia) e os estados/erro em browser real, com a11y AA (label, `aria-*`,
> contraste, foco, ≥48px) e as regras de ouro respeitadas em mobile e desktop. A única divergência
> (checkbox = círculo) foi **corrigida na mesma operação**. Gate de design da STORY-005 satisfeito.
