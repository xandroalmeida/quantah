# Tokens — Design System Quantah

> Fundações do DS. **Fonte:** `docs/DESIGN-wise.md` (design system Wise-derived), adotado pelo
> **PDR-001**. Estes tokens são a verdade; **nunca use valor cru** em spec ou código — use o
> token. O Programador mapeia cada token para o `tailwind.config.js` + CSS variables da stack
> Inertia+React (ver `docs/skills/stacks/inertia-react/SKILL.md` › "Estilo / Design System na
> prática"). Alteração de fundação (paleta, tipografia, regra de acento) exige **DDR**.

## Cor

Paleta completa herdada do DESIGN-wise.md. **Regra de ouro:** o verde-limão `primary` é o
**único accent de CTA** — nunca é indicador de "sucesso" (isso é a família semântica positiva).

### Marca & accent

| Token | Hex | Papel / uso |
|---|---|---|
| `primary` | `#9fe870` | Verde-limão. Único CTA primário e accent de marca. |
| `on-primary` | `#0e0f0c` | Texto/ícone sobre `primary`. |
| `primary-active` | `#cdffad` | Estado ativo/hover mais claro do verde. |
| `primary-neutral` | `#c5edab` | Verde neutro de preenchimento ativo. |
| `primary-pale` | `#e2f6d5` | Verde palíssimo — fundo de badge/tint suave. |

### Superfície

| Token | Hex | Uso |
|---|---|---|
| `canvas` | `#ffffff` | Branco — interior de cards. |
| `canvas-soft` | `#e8ebe6` | Sage — fundo de página / hero band. **Mood da marca.** |

### Texto (tinta)

| Token | Hex | Uso |
|---|---|---|
| `ink` | `#0e0f0c` | Quase-preto com calor olívico. Texto e títulos padrão. |
| `ink-deep` | `#163300` | Verde-floresta profundo — tinta sobre superfícies positivas. |
| `body` | `#454745` | Texto de corpo secundário. |
| `mute` | `#868685` | Menor prioridade — caption, placeholder, fine print. |

### Semânticas

| Token | Hex | Uso |
|---|---|---|
| `positive` | `#2ead4b` | Sucesso. |
| `positive-deep` | `#054d28` | Positivo pressionado / texto sobre superfície positiva. |
| `warning` | `#ffd11a` | Atenção. |
| `warning-deep` | `#b86700` | Warning pressionado. |
| `warning-content` | `#4a3b1c` | Texto sobre superfície de warning. |
| `negative` | `#d03238` | Erro / ação destrutiva. |
| `negative-deep` | `#a72027` | Destrutivo pressionado. |
| `negative-darkest` | `#a7000d` | Texto destrutivo de máxima ênfase. |
| `negative-bg` | `#320707` | Fundo escuro de callout destrutivo. |

### Accents terciários (só ilustração/gráfico)

| Token | Hex | Uso |
|---|---|---|
| `accent-orange` | `#ffc091` | Pêssego — dentro de conteúdo ilustrativo. |
| `accent-cyan` | `#38c8ff` | Azul-céu — accent terciário de ilustração. |

**Regras de uso:**

- `primary` (verde) só em **um** CTA por contexto. Nunca como cor de sucesso — use `positive`.
- **Sem segundo accent de marca.** Laranja/ciano só em ilustração/gráfico, jamais como CTA.
- **Nunca verde sobre verde** — o CTA verde senta sobre superfície neutra (sage/branco/ink).
- Feedback nunca é só cor: sempre acompanha ícone + texto (a11y + usuário não-técnico).
- Elevação vem do **contraste de superfície** (`canvas-soft` de fundo × `canvas` nos cards),
  não de sombra pesada.

## Tipografia

Duas famílias (ver **DDR-001** para a substituição da fonte proprietária Wise Sans):

- **Display:** `Inter` peso **900** (ou `Manrope` 800/900) — momentos de marca (hero, headlines).
- **Texto/UI:** `Inter` (400/600) — sub-displays, corpo, labels, botões.

> Regra da marca: **peso 900 no hero, 600 no resto.** Contraste vem de tamanho + peso, não de
> bold espalhado.

| Token | Família/peso | Tamanho | Line-height | Uso |
|---|---|---:|---:|---|
| `display-mega` | Display 900 | 126px | 107px | Hero em escala máxima. |
| `display-xxl` | Display 900 | 96px | 82px | Sub-hero. |
| `display-xl` | Display 900 | 64px | 54px | Hero padrão. |
| `display-lg` | Inter 400 | 47px | 70px | Sub-display leve. |
| `display-md` | Display 900 | 40px | 34px | Headline de seção/card. |
| `display-sm` | Inter 600 | 32px | 38px | Título de seção (Inter). |
| `display-xs` | Inter 600 | 24px | 31px | Sub-seção. |
| `body-lg` | Inter 400 | 20px | 30px | Parágrafo de destaque. |
| `body-md` | Inter 400 | 16px | 24px | Corpo padrão. |
| `body-md-strong` | Inter 600 | 16px | 24px | Corpo em negrito inline. |
| `body-sm` | Inter 400 | 14px | 20px | Corpo secundário. |
| `body-sm-strong` | Inter 600 | 14px | 20px | Caption forte / nav-link. |
| `caption` | Inter 400 | 12px | 16px | Fine print. |
| `button-md` | Inter 600 | 16px | 24px | Label de botão. |

> Não descer corpo abaixo de 14px em produto. No app mobile do Colaborador, corpo padrão 16px.

## Espaçamento (base 4px)

| Token | Valor |
|---|---:|
| `xxs` | 2px |
| `xs` | 4px |
| `sm` | 8px |
| `md` | 12px |
| `lg` | 16px |
| `xl` | 24px |
| `2xl` | 32px |
| `3xl` | 48px |

Bands usam `3xl` (48px) vertical no desktop; interior de card `xl` (24px).

## Raio (border-radius)

| Token | Valor | Uso |
|---|---:|---|
| `none` | 0 | Bands full-bleed. |
| `sm` | 8px | Pills inline, badges pequenos. |
| `md` | 12px | **Inputs**, chrome menor. |
| `lg` | 16px | Cards médios. |
| `xl` | 24px | **Raio canônico** de botões e cards. Assinatura da marca. |
| `pill` | 9999px | Status pills. |
| `full` | 9999px | Containers de ícone circulares, avatares. |

> **Regra não-negociável:** botões e cards em `xl` (24px). Nunca CTA com canto reto.

## Elevação

O DS usa **contraste de superfície** como pista principal de elevação, não sombra.

| Token | Nível | Uso |
|---|---:|---|
| `elev.0` | Flat | Padrão — sem sombra nem borda. |
| `elev.1` | Hairline | Borda 1px `ink` — botão terciário (outline), inputs. |
| `elev.2` | Soft card | Card branco (`canvas`) sobre sage (`canvas-soft`) — o contraste É a elevação. |

## Motion

| Token | Duração | Curva | Uso |
|---|---:|---|---|
| `motion.fast` | 100ms | ease-out | Feedback imediato (press, hover). |
| `motion.base` | 200ms | ease-in-out | Mudança de estado, abrir/fechar inline. |
| `motion.slow` | 300ms | ease-in-out | Transição entre telas, gavetas. |

> Acima de 300ms = erro de design (salvo onboarding deliberado).

## Breakpoints

Alinhados ao DESIGN-wise.md e aos prefixos do Tailwind.

| Token | Min-width | Tailwind | Mudanças-chave |
|---|---|---|---|
| `mobile` | 0 | (base) | Hero empilha; grids 1-up; nav inferior. |
| `tablet` | 768px | `md:` | Grids 2-up. |
| `desktop` | 1024px | `lg:` | Hero split; grids cheios; nav lateral. |

## Toque e acessibilidade (pisos)

- Alvo mínimo de toque **48×48px** em mobile (botão ~48px = padding 12 + line 24).
- Contraste WCAG AA: 4.5:1 texto normal, 3:1 texto grande/ícone. **Atenção:** verde `primary`
  é claro — texto sobre ele usa `on-primary` (`#0e0f0c`); nunca texto branco sobre `primary`.
- Foco sempre visível.
