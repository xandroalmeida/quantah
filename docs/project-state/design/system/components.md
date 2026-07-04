# Componentes — Design System Quantah

> Biblioteca herdada do `docs/DESIGN-wise.md` (PDR-001). Cada componente vira um **componente
> React reutilizável** em `Components/` (um por conceito, não markup duplicado por página) —
> ver `stacks/inertia-react/SKILL.md`. **id** é o que o spec de tela referencia em
> `ds_components_used`. Componente novo entra por **DDR**, nunca direto na tela.

## Como ler

- **Estados** cobrem `default / hover (web) / focus / pressed / disabled / loading / error`
  quando aplicáveis.
- **Não usar quando** restringe tanto quanto "usar quando".
- Tokens sempre referenciados por nome (ver `tokens.md`).

---

## Botões

Todos: raio `xl` (24px), label `button-md`, padding `md xl`, altura ≥48px em mobile.

### `button.primary`
CTA verde-limão. **No máximo um por contexto.**
- Fundo `primary`, texto `on-primary`.
- hover: `primary-active` · pressed: overlay 12% · disabled: opacidade 38% · loading:
  spinner inline no lugar do label, toque bloqueado.
- Usar quando: ação principal e única (tela, sheet, dialog). Ex.: "Enviar cupom".
- Não usar quando: ação destrutiva (`button.danger`) ou secundária.

### `button.secondary`
Sage. Fundo `canvas-soft`, texto `ink`. Ação secundária ao lado do primário.

### `button.tertiary`
Outline. Fundo `canvas`, texto `ink`, borda 1px `ink` (`elev.1`). Ação de baixo peso.

### `button.danger`
Destrutivo. Fundo `negative`, texto branco. Só para ação irreversível (ex.: "Remover conta").

### `button.icon-circular`
Ícone circular. Fundo `canvas`, ícone `ink`, raio `full`, padding `sm`. **Sempre `aria-label`**
com verbo + objeto.

---

## Inputs & formulários

### `input.text`
- Fundo `canvas`, texto `ink`, borda 1px `ink`, raio `md` (12px), padding `md lg`, `body-md`.
- Label flutuante obrigatório, helper opcional, mensagem de erro associada (`aria-describedby`).
- Estados: default / focus / disabled / error / readonly.

### `input.masked`
Variante de `input.text` para formato conhecido (CPF, telefone, CEP, dinheiro). Máscara é
**ajuda de UX, não validação** — persiste valor canônico sem máscara; validação é do servidor.
Placeholder mostra o formato (`000.000.000-00`).

### `input.datetime`
Data/hora **por seletor** (calendário/relógio), nunca digitação livre. Valor canônico ISO 8601.
Máscara de texto só como fallback.

### `input.select` · `input.checkbox` · `input.radio` · `input.switch`
Do catálogo padrão, herdando tokens de cor/raio/foco. Estado selecionado usa `primary` como
indicador (não como preenchimento de área grande).

---

## Cards & containers

Todos raio `xl` (24px), padding `xl`.

| id | Fundo | Texto | Uso |
|---|---|---|---|
| `card.content` | `canvas` | `ink` | Card branco padrão sobre canvas sage. Sem borda. |
| `card.feature-sage` | `canvas-soft` | `ink` | Card de destaque sage. |
| `card.feature-green` | `primary-pale` | `ink` | Card de destaque verde suave. |
| `card.feature-dark` | `ink` | `primary` | Card escuro com texto verde — **momento de marca**, uso pontual. |

---

## Navegação

### `nav.bar` (topo web)
Fundo `canvas`, texto `ink`, padding `md xl`. Sticky.

### `nav.link`
Texto `ink`, `body-sm-strong`. Ativo: indicador `primary`.

### `nav.bottom` (mobile)
Navegação inferior no app do Colaborador; item ativo com indicador `primary`. Alvos ≥48px.

### `footer`
Band escuro. Fundo `ink`, texto `canvas-soft`, `body-sm`, padding `3xl xl`.

---

## Bands / hero (marketing e telas de destaque)

| id | Fundo | Texto | Tipografia |
|---|---|---|---|
| `hero-band` | `canvas-soft` | `ink` | `display-mega` (Display 900) |
| `hero-band-dark` | `ink` | `primary` | `display-mega` — headline verde sobre ink |
| `content-band` | `canvas` | `ink` | `display-md` |

---

## Feedback & status

### `badge.positive`
Fundo `primary-pale`, texto `positive-deep`, `body-sm-strong`, raio `pill`, padding `xs md`.
Ex.: "Cupom aceito".

### `badge.negative`
Fundo `negative-bg`, texto branco, `body-sm-strong`, raio `pill`. Ex.: "Cupom recusado".

### `snackbar` (toast)
Fundo `canvas`, raio `xl`, padding `md lg`, `body-sm`. Variantes success/warning/danger/info
(ícone + texto; cor nunca sozinha). Mudança dinâmica anunciada via `aria-live="polite"`.

### `empty-state`
Ícone leve + título `display-xs` + instrução `body-md` + `button.primary`. **Sempre instrui o
próximo passo** — "Sem dados" sozinho é proibido. Ex.: "Você ainda não enviou cupons. Enviar o
primeiro."

### `skeleton`
Placeholder de carregamento (nunca spinner em tela vazia) para o primeiro fetch e refresh.

---

## Marca (identidade)

> Introduzidos pelo **DDR-004** (accepted 2026-07-04). Substituem o `ApplicationLogo` do Laravel
> (scaffolding). Wordmark em Inter 900 (DDR-001); acento verde parcimonioso (DDR-002).

### `brand.mark`
Tile quadrado raio `xl` (24px) em `primary` (verde), com glifo de **nota fiscal** (recibo + duas
linhas) traçado em `on-primary` (ink). Comunica o domínio (cupom/nota), não decora. Uso: favicon,
avatar, canto de header, e dentro do `brand.lockup`. Tamanho base 44×44px; escala mantendo o raio
proporcional. **Nunca** verde sobre verde — o glifo é sempre ink.

### `brand.lockup`
`brand.mark` + wordmark "Quantah" (Inter 900, `ink`; vira `canvas` sobre superfície escura), lado a
lado, gap `sm`. Opcional: tagline `Cada nota conta.` (`body-md`, `body`) abaixo. Uso: topo das telas de
acesso (mobile) e do painel de hero (desktop). É o único lugar onde a marca aparece em escala — não
repetir na mesma tela.

### `brand.google-btn`
Variante de `button.tertiary` (outline): fundo `canvas`, borda 1px `ink`, **logo oficial multicolor do
Google** + label "Entrar com Google" / "Criar conta com Google". **Exceção ao DS de acento** (logo de
terceiro): justificada no DDR-004. **Nunca** verde — o verde é do CTA de e-mail/senha.

- **STORY-021:** placeholder desabilitado com selo "Em breve" (`aria-disabled`, `tabindex="-1"`).
- **STORY-022 (ativo):** link real para o fluxo OAuth (`/auth/google/redirect`), navegação de página
  inteira (sai do SPA — não é visita Inertia), sem o selo. É o estado atual em produção.

## Componente de assinatura (a especializar por tela)

O DESIGN-wise.md traz o `currency-converter-card` como widget interativo de assinatura. No
Quantah, o equivalente é o **card de resultado do cupom** ("você pagou X — média R$ Y"): mesmo
chrome (`canvas`, borda 1px `ink`, raio `xl`, padding `xl`). Especificar como componente próprio
quando a estória de Coleta abrir (via DDR).

---

## Lista mínima a cobrir até o 1º épico de telas

`button.primary`, `button.secondary`, `button.tertiary`, `button.danger`, `button.icon-circular`,
`input.text`, `input.masked`, `input.datetime`, `input.select`, `input.checkbox`, `input.radio`,
`input.switch`, `card.content`, `card.feature-sage`, `card.feature-green`, `card.feature-dark`,
`badge.positive`, `badge.negative`, `snackbar`, `empty-state`, `skeleton`, `nav.bar`,
`nav.bottom`, `footer`.
