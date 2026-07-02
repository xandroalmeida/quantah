---
idr_id: IDR-001
slug: tokens-no-tema-tailwind-sem-css-vars
title: Tokens do DS vivem no theme.extend do Tailwind; CSS variables adiadas
status: accepted
decided_at: 2026-07-02
decided_by: programador
owner_agent: claude-programador-story002
related_story: STORY-002
related_adrs: [ADR-000]
related_idrs: []
supersedes: null
superseded_by: null
created_at: 2026-07-02
updated_at: 2026-07-02
---

# IDR-001 — Tokens do DS vivem no `theme.extend` do Tailwind; CSS variables adiadas

## Contexto

A STORY-002 mapeou os tokens do Design System (`docs/project-state/design/system/tokens.md`)
para o tema da stack. A sub-skill `stacks/inertia-react` admite duas materializações: config do
Tailwind (`theme.extend`) e/ou CSS custom properties. A estória deixou a escolha ao Programador
(§ "Liberdade técnica") e o DoD pede registrar a "estratégia de CSS vars". Como o EPIC-001
(biblioteca de componentes) e todas as telas vão consumir esse tema, a decisão precisa ser
explícita para os próximos agentes — não pode ficar só implícita no `tailwind.config.js`.

## Decisão

> **Decidi mapear os tokens diretamente no `theme.extend` do `tailwind.config.js` como a única
> fonte de tokens no código, e adiar a camada de CSS variables até existir necessidade real de
> tema em runtime (ex.: dark mode ou multi-marca).**

Cada token semântico do DS vira um utilitário Tailwind (`bg-primary`, `text-on-primary`,
`rounded-xl`, `text-display-xl`, `p-xl`, `shadow-elev-2`, `md:`/`lg:`). O valor cru (hex, px)
aparece **exclusivamente** no `tailwind.config.js` — que é o de-para token→stack. Fonte Inter
(400/600/900, DDR-001) carregada via `fonts.bunny.net` no `app.blade.php`, como o starter já
fazia para a Figtree.

## Por quê

- **Simplicidade radical / KISS.** Uma só fonte de tokens no código. Uma segunda camada de CSS
  vars seria indireção sem consumidor: hoje não há dark mode nem multi-marca no escopo (a visão
  não pede runtime theming). Abstração antes da necessidade é débito (coding-principles §2).
- **Segue o framework opinativo.** `theme.extend` é o caminho idiomático do Tailwind para
  tokens; os utilitários gerados são exatamente o que os componentes React vão usar.
- **Custo de troca baixo.** Se o dark mode/multi-marca chegar, migra-se para CSS vars
  referenciadas no theme (`primary: 'rgb(var(--primary) / <alpha-value>)'`) sem reescrever o JSX,
  que já usa só utilitários semânticos.

## Alternativas consideradas

- **CSS variables desde já (theme referenciando `var(--token)`)**: descartada — adiciona uma
  camada e um ponto de verdade extra sem consumidor real hoje (nenhum runtime theming no escopo).
- **Self-host da fonte Inter (arquivos no bundle)**: descartada por ora — o starter já usa o CDN
  bunny.net e a estória é fundação mínima; self-host vira otimização de performance quando o
  budget de rede do app do Colaborador for medido (datastore/perf-first, com número).

## Consequências

### Para outros agentes
- **Tokens novos entram no `theme.extend`**, com o valor canônico de `tokens.md` — nunca hex/px
  cru no JSX. A biblioteca de componentes do EPIC-001 consome esses utilitários.
- **Não introduzir uma camada paralela de CSS vars** para os mesmos tokens sem reabrir esta IDR.
- Se surgir **dark mode / multi-marca**, esta IDR é o ponto de partida para migrar para CSS vars.

### Para o projeto
- Zero dependência nova. Fonte via CDN (mesma abordagem do starter).
- `tailwind.config.js` passa a ser o artefato de de-para tokens→stack (documentado no topo).

### Trade-offs aceitos
- Sem theming em runtime até a migração para CSS vars (aceitável: fora do escopo atual).
- Fonte via CDN cria dependência de rede de terceiro no primeiro paint (revisável por perf).

## Como verificar

- Guarda automatizada: `Tests\Feature\DesignSystem\TailwindThemeTokensTest` (tokens presentes no
  tema) e `NoRawColorInHelloTest` (sem cor crua no JSX). Se alguém introduzir CSS vars paralelas
  ou cor crua, o time percebe aqui.
- Se a visão passar a exigir dark mode ou multi-marca → reabrir esta IDR.

## Tipo

- [x] **Padrão transversal**: forma canônica de materializar tokens do DS na stack.
- [ ] **Workaround**
- [ ] **Convenção interna**
- [ ] **Otimização**
- [ ] **Refatoração estrutural**

---

## Histórico

- 2026-07-02 — criada como `accepted` por programador (sessão claude-programador-story002)
  durante STORY-002.
