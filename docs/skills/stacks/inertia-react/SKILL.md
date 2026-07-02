---
name: stack-inertia-react
description: Sub-skill de stack — Inertia + React (UI web sobre Laravel). Conhecimento idiomático e opinativo de Inertia + React que os papéis (Designer, Programador, Validador) consultam quando Inertia + React é a camada de UI web da stack ativa (ver _project.md › Stack ativa). Complementa o método dos papéis com o como-fazer específico de Inertia + React; não o substitui.
---

> **Sub-skill de stack.** Ativada quando Inertia + React é a UI web do projeto (`_project.md` › Stack ativa). Os papéis trazem o método; esta sub-skill traz o idiomático de Inertia + React. Backend: ver `stacks/laravel/SKILL.md`.

# Inertia + React — sub-skill de stack (UI web)

Inertia + React é a **UI web rica deste template** — uma SPA-like construída em React **sem API REST/GraphQL separada para manter**. A ponte Inertia liga o Laravel ao React: o controller devolve uma *página* Inertia com *props* (em vez de uma view Blade ou JSON), o Inertia troca o conteúdo no cliente sem full reload, e o roteamento continua morando no Laravel. Você ganha o frontend componível e interativo do React mantendo controllers, validação, autorização e dados no Laravel — sem o custo de versionar e documentar uma API pública.

Esta sub-skill diz **como materializar na tecnologia** o que os papéis decidiram. Ela **não decide UX** — isso é do Designer (`designer/SKILL.md`), que entrega a spec de tela e o Design System. Ela **não reescreve o backend** — models, validação de domínio, autorização, queries, jobs e a montagem das props seguem `stacks/laravel/SKILL.md`. Inertia + React é a camada que materializa a spec do Designer com a riqueza de interação que o React permite.

## Quando esta stack se aplica (e quando NÃO)

Esta é a stack para a **UI rica** — quando a interação de cliente justifica o custo de um frontend React. Não é o default; o default é Livewire (`stacks/livewire/SKILL.md`). Adote Inertia + React quando:

| Use Inertia + React quando | Prefira Livewire quando |
|---|---|
| Interações ricas no cliente: drag-and-drop, edição inline complexa, canvas | CRUD, formulários, telas padrão de back-office |
| Estado de cliente complexo e interdependente (filtros + seleção + edição simultâneos) | Estado simples, server-driven resolve bem |
| Dashboards muito interativos, com cálculo/visualização client-side | Tela com pouca interatividade, muito dado de servidor |
| Time com competência real de React e tooling de frontend | Time pequeno sem especialista de frontend |
| Componível: reaproveitar uma biblioteca rica de componentes React | Você quer uma só linguagem (PHP) cobrindo tudo |

**Não use Inertia + React** para fugir de escrever PHP, nem "porque React é mais moderno". O custo é real: bundle JS, build, estado duplicado cliente/servidor, SEO/SSR a decidir, dois ecossistemas de teste. A **escolha Inertia/React vs Livewire é do Arquiteto, via ADR** — não do Programador no calor da estória, nem do Designer (que desenha agnóstico). Se o Designer achar que a tela pede interação rica demais para Livewire, ele **levanta a viabilidade** no rabisco e escala; quem decide a stack é o Arquiteto.

## O que Inertia + React já entrega (não reinvente)

Antes de montar um cliente de API ou um router de cliente, lembre que a ponte já dá:

- **Roteamento no Laravel** — as rotas e os controllers continuam no backend; o controller devolve `Inertia::render('Pagina', [...props])`. Você **não** mantém um router de cliente paralelo nem versiona uma API.
- **Páginas e props** — cada página é um componente React em `resources/js/Pages/...`; recebe props vindas do controller. É o contrato controller↔página.
- **Visitas Inertia** — `<Link>` e `router.visit/get/post/put/delete` trocam de página sem full reload, com histórico do browser preservado.
- **Partial reloads** — `router.reload({ only: [...] })` / props evaluadas sob demanda recarregam **só** as props que mudaram, sem rebuscar a página inteira. Casa com o Princípio de performance percebida do Designer.
- **Form helper** — `useForm` cobre estado do form, submit, `processing`, e **erros de validação vindos do Laravel** mapeados por campo. Não reescreva validação no cliente: a canônica é a do Laravel.
- **Shared data** — `usePage().props` expõe dados compartilhados (usuário autenticado, flash messages) sem prop-drilling.
- **Estados de progresso** — barra de progresso de navegação e `processing`/`recentlySuccessful` do `useForm` dão feedback sem você cablar.
- **Deferred props / prefetch / polling** — recursos prontos para adiar dado pesado, pré-buscar e atualizar periodicamente.
- **SSR opcional** — Inertia tem modo SSR; é **decisão explícita** (ver abaixo), não default automático.

Reinventar qualquer um — sobretudo "montar uma API REST + cliente fetch + router de cliente" — joga fora a razão de existir do Inertia e gera o débito que ele evita. Use a ponte; desça ao custom só onde ela comprovadamente não cobre.

## Padrões idiomáticos

**Página = tela; componente = peça reutilizável.** Páginas (`Pages/`) recebem props do controller e são o ponto de entrada de uma tela; componentes (`Components/`) são as peças do Design System e blocos reutilizáveis, sem conhecer o controller. Não enfie lógica de página em componente reutilizável nem vice-versa.

**Props do servidor são a fonte de verdade dos dados; estado de cliente é só o que é de cliente.** O dado canônico vem nas props (servidor). Crie estado de cliente (`useState`) apenas para o que é genuinamente de UI: aberto/fechado, hover, seleção transitória, rascunho antes de submeter. **Não** copie props para o estado e mantenha as duas em paralelo — é a maior fonte de bug desta stack (ver gotchas).

**`useForm` para formulários, sempre.** Ele já liga estado, submit, `processing` e erros do Laravel por campo. Submeter com `fetch` cru e remapear erro na mão é reinventar o que a ponte dá. A validação que vale é a do controller (`stacks/laravel/SKILL.md`); o cliente pode ter validação leve de UX, mas **nunca** é a fonte de verdade.

**Máscaras de entrada em campo formatado.** Todo campo com formato conhecido (CPF, CNPJ, PIS/PASEP, telefone, CEP, dinheiro, cartão, placa) recebe **máscara de entrada** — formatação de UX no cliente. Default do template: **`react-imask`** (IMask) — `maska` é alternativa legítima via ADR. Regra de ouro: guarde no `useForm` o valor **unmasked** (canônico, só dígitos) — use o callback de valor não-mascarado do componente (`onAccept`/`unmask`), **não** o texto formatado. A máscara **não** valida: a validação canônica é a rule do Laravel (`stacks/laravel/SKILL.md`), que já volta por campo no `useForm`; o valor persistido é o sem máscara. Placeholder mostra o formato; `inputMode="numeric"` em campo de documento. **Data/hora não usam máscara** — usam seletor (ver abaixo).

**Seletores de data/hora.** Campo de data, hora ou data+hora (`input.datetime` do DS) entra por **seletor**, não digitação livre. Default do template: **`react-day-picker`** (calendário) + input de hora — outra lib (`react-datepicker`, MUI) via ADR; nativo `<input type="date|time|datetime-local">` é fallback simples. Regra de ouro: guarde no `useForm` o **valor canônico ISO 8601** (string `AAAA-MM-DD` / `HH:MM` / datetime), **nunca** a copy localizada nem um `Date` serializado de forma ambígua. O seletor **não** valida: a validação canônica é a rule do Laravel (`stacks/laravel/SKILL.md`), que volta por campo no `useForm`; persistência com fuso em UTC (`stacks/database/database-method.md`). Seletor navegável por teclado e com digitação como alternativa (acessibilidade).

**Partial reload em vez de rebuscar tudo.** Mudou um filtro que afeta só a lista? `router.reload({ only: ['lista'] })` — não recarregue a página inteira nem monte um endpoint separado.

**Autorização não é da UI.** Esconder um botão no React é UX, não segurança. A checagem real (Policies/Gates) é no controller/Laravel. Botão escondido + rota desprotegida = falha de segurança.

**Composição e hooks, sem over-engineering de estado.** Comece com props + `useState`/`useReducer` local. Biblioteca de estado global (Zustand/Redux) só quando o estado interdependente justificar — e isso é decisão a registrar (ADR se vira padrão do projeto), não default. Estado de servidor já vem do Inertia; não recrie um cache de servidor no cliente sem necessidade real.

**`key` estável em listas.** `key` único e estável em listas React (não índice de array em lista mutável) para o reconciliador não embaralhar. Use o identificador lógico da spec do Designer como base quando fizer sentido.

## Implementar uma spec de tela do Designer nesta stack

O Designer entrega: spec de tela (`design/screens/STORY-XXX-*.md`) + protótipo HTML fiel + referência aos componentes do Design System. Sua tarefa é **materializar essa spec em Inertia + React fielmente**, sem reinterpretar UX. De-para típico:

| Na spec do Designer (agnóstica) | Em Inertia + React |
|---|---|
| Tela / fluxo | Página Inertia (`Pages/...`) + rota/controller no Laravel que faz `Inertia::render` |
| Layout mobile + desktop (paridade) | JSX com utilitários Tailwind responsivos (`sm:`, `lg:`) — ver seção de estilo |
| Componente do DS (botão, input, card, empty-state) | Componente React reutilizável (`Components/...`) encarnando o componente do DS |
| Estados (vazio, loading, erro, sucesso, sem permissão) | Render condicional por estado + `processing` do `useForm` + erros por campo + bloco empty-state + checagem de permissão (prop) |
| Microcopy (labels, placeholders, mensagens) | Texto **exato** da spec no JSX — não improvise; divergência de microcopy é bug |
| Identificador estável sugerido (`item-card-acao-btn`) | `data-testid="item-card-acao-btn"` (RTL/E2E) e `key` estável |
| Microinteração / feedback imediato | Estado de cliente + transição CSS/lib de animação — feedback em ≤100ms é meta do DS |

**Estados não são opcionais.** A spec do Designer lista vazio/loading/erro/sem-permissão como entregáveis (Princípio 7 do Designer). Em React isso vira render condicional: empty-state quando a coleção é vazia, loading/skeleton durante navegação/partial reload (e `processing` no submit), erro por campo (`useForm`) e bloco de erro de tela para falhas não-de-campo, sem-permissão a partir de prop de autorização. Implementar só o caminho feliz é entregar meio spec.

**Não cruze a fronteira.** Se um estado falta na spec, ou uma interação não foi desenhada, **não invente UX** — devolva ao Designer. Você decide *como construir em React*; o Designer decide *como o usuário experimenta*.

## Estilo / Design System na prática

**Tailwind CSS é o default do ecossistema** (o starter Laravel + Inertia + React já vem com Tailwind + Vite). O Design System do Designer é descrito em **tokens e comportamento, de forma agnóstica** (`designer/references/design-system-craft.md`). Sua tarefa é **mapear esses tokens para o Tailwind do projeto** — não reinventar a paleta.

- **Tokens → `tailwind.config` + CSS variables.** Cor, tipografia, espaçamento, raio, sombra, motion e breakpoints do DS viram a config do Tailwind (theme.extend) e/ou custom properties CSS. Token semântico (`color.brand.primary`, `space.4`) vira utilitário/classe; **nunca espalhe valor cru** (`#1E40AF`, `16px`) no JSX — a regra de ouro dos tokens do Designer vale aqui.
- **Componentes do DS → componentes React.** Cada componente do DS (botão com variantes/estados, input, card, empty-state) vira um **componente React** tipado (`<Button variant="primary">`) com seus estados (default/hover/focus/active/disabled/loading/error) em classes Tailwind. Reusar antes de criar é regra do Designer **e** sua: um componente React por conceito do DS, não markup duplicado por página. Se o projeto adotar uma biblioteca de componentes headless, isso é decisão de ADR — ela serve ao DS, não o substitui.
- **Breakpoints alinhados.** Breakpoints do DS mapeados aos prefixos do Tailwind (`sm:`/`md:`/`lg:`/`xl:`). Paridade mobile/desktop (`designer/references/mobile-desktop-parity.md`) se implementa com utilitários responsivos, não com componentes separados por viewport.
- **Divergência do DS** que pareça necessária → não decida sozinho: é exceção de spec (Designer) ou mudança de token (DDR do Designer). Você implementa o token; não o redefine.

## Testes nesta stack (incl. E2E em browser real)

A disciplina é a de `programador/references/testing-discipline.md` — caminho feliz **nunca** basta; cada CA tem feliz, inválido, exceção e borda; FE web exige **E2E em browser real** para cada caminho mapeado. Aqui há **duas camadas distintas**, e a primeira **não** substitui a segunda:

- **Componentes — Vitest + React Testing Library (rápidos, jsdom).** Vitest roda os testes de componente; React Testing Library testa do ponto de vista do usuário (busca por papel/label/texto, não por classe). Cobrem a **lógica de componente**: render condicional por estado, callback disparado, formatação, validação de UX leve, empty-state aparece. **Limites conhecidos (de `testing-discipline.md`):** jsdom não roda CSS de verdade, não dispara eventos como browser real, não valida acessibilidade de teclado nem layout. Por isso **não conta como E2E** — é o unit/integração desta camada.
- **E2E em browser real — Dusk ou Playwright.** Um deles dirige um Chrome real e valida o que o jsdom não pega: CSS real, layout, foco por teclado, eventos reais, a ponte Inertia (navegação sem reload, props chegando, partial reload) funcionando ponta a ponta. Ancore seletores nos **identificadores estáveis da spec** via `data-testid`. Cubra **cada caminho mapeado** — feliz, alternativos e cada fluxo de erro alcançável pela UI — não só o feliz. Um único E2E de caminho feliz **não fecha o gate**.

> A ferramenta de E2E é decisão de ADR. **Dusk** é o default do ecossistema Laravel (integração nativa, banco de teste); **Playwright** é alternativa forte e comum em times de frontend React. Escolha uma e registre via ADR — não rode as duas por inércia. Vitest + RTL para componente é o idiomático de React e **complementa**, não substitui, o E2E.

O **Validador** (`validador/`) emite o veredito independente no fim do épico em browser real; o **Programador** anexa evidência (vídeo/print/link) de E2E por desfecho mapeado no PR. Esta sub-skill diz **com o quê** (Vitest+RTL + Dusk/Playwright); o método dos gates é dos papéis.

## Acessibilidade na prática

O piso é o de `designer/references/accessibility-basics.md` (WCAG 2.1 AA — contraste, foco visível, teclado, semântica, erro textual, label em ícone, alvo de toque ≥44–48px). Em React, acessibilidade exige disciplina porque é fácil produzir markup não-semântico:

- **Elementos semânticos reais.** `<button>` para ação, `<a>`/`<Link>` para navegação, `<nav>`, `<main>`, headings em ordem. Nunca `<div onClick>` para o que é botão — perde foco, teclado e leitor de tela.
- **Label associado.** `<label htmlFor>` + `id`, ou input dentro de label. Não confie em placeholder como label.
- **Erro textual, não só cor.** Mensagem de erro do `useForm` vinculada ao campo (`aria-describedby`), não borda vermelha sozinha.
- **Foco em SPA.** Inertia troca conteúdo sem reload — o foco **não** volta sozinho ao topo como num full load. Em navegação e em abertura de modal/drawer, gerencie o foco explicitamente (mover foco ao container novo, focar primeiro elemento, retornar foco ao gatilho ao fechar). Modal precisa de foco-trap e `Esc`.
- **Mudança dinâmica anunciada.** Conteúdo que muda via partial reload/estado (resultado de busca, toast) em `aria-live="polite"`.
- **Ícone sozinho tem label.** Botão só com ícone recebe `aria-label` com verbo + objeto.

Verificação: RTL favorece queries acessíveis (por papel/label), o que já pressiona markup correto; complemente com axe/Lighthouse no DOM e teclado sozinho no E2E em browser real. Falha de piso é **bloqueio de PR**, não trade-off (regra do Designer).

## Defaults deste template (e como divergir via ADR)

| Decisão | Default do template | Como divergir |
|---|---|---|
| UI web rica | **Inertia + React** (quando ADR a escolhe; default geral é Livewire) | ADR registra a escolha por escopo |
| Estilo | **Tailwind CSS** | Outra abordagem → ADR + remapear DS |
| Máscara de entrada (campos formatados) | **`react-imask`** (IMask); valor unmasked no `useForm` | `maska` → ADR |
| Seletor de data/hora | **`react-day-picker`** (calendário) + input de hora; valor ISO 8601 no `useForm` | Nativo p/ caso simples; outra lib (`react-datepicker`/MUI) → ADR |
| Build / dev server | **Vite** | — (é o idiomático do starter) |
| Teste de componente | **Vitest + React Testing Library** | — (idiomático de React) |
| E2E browser real | **Dusk ou Playwright** (escolha uma) | A escolha é ADR; não rode as duas |
| Validação | **Rules do Laravel** (erros via `useForm`) | — (não duplicar como fonte de verdade) |
| Roteamento | **No Laravel** (Inertia render) | — (não criar router de cliente paralelo) |
| Estado global | **Props + estado local**; lib global só com justificativa | Lib global vira padrão → ADR |
| SSR | **Decisão explícita** (off por default) | Ligar SSR → ADR com motivo (SEO/TTFB) |

Backend (controllers, montagem de props, models, migrations, policies, queries, jobs, filas) **não é desta sub-skill** — segue `stacks/laravel/SKILL.md`. Não duplique aqui regra de domínio, autorização ou acesso a dados.

## Armadilhas conhecidas (gotchas)

- **Estado duplicado cliente/servidor.** Copiar props para `useState` e manter as duas cópias é a fonte número um de bug aqui: a tela mostra valor velho após um reload de props, ou diverge do servidor. Trate props como fonte de verdade; só crie estado de cliente para o que é genuinamente de UI (rascunho, aberto/fechado, seleção transitória). Use `key` na página/componente para forçar remount quando precisar resetar estado a partir de novas props.
- **Bundle inchado.** React + libs sobem o JS; bundle grande atrasa o carregamento e fere a performance percebida do Designer. Code-splitting por página, imports dinâmicos para o pesado, e vigie o tamanho do bundle no build. Não importe biblioteca enorme para resolver coisa pequena.
- **SEO/SSR esquecido.** Por default a página renderiza no cliente — conteúdo público pode não indexar bem e o primeiro paint demora. Se SEO ou TTFB importam (páginas públicas), **ligar SSR é decisão explícita via ADR**, com o custo operacional que traz (servidor Node de SSR). Não descubra isso em produção.
- **Reinventar a API.** Montar endpoints REST + cliente fetch + router de cliente joga fora a razão do Inertia e duplica autorização/serialização. Use páginas + props + partial reloads.
- **Validação só no cliente.** Validação React é UX, não segurança. A canônica é a do Laravel; `useForm` já traz os erros do servidor por campo. Cliente nunca é a fonte de verdade.
- **Guardar o valor mascarado no `useForm`.** Salvar o texto formatado (`123.456.789-09`) em vez do valor unmasked (`12345678909`) manda máscara pro servidor e quebra a validação/persistência. Ligue o `useForm` ao valor não-mascarado (`onAccept`/`unmask`). Máscara não valida nem pode bloquear colar.
- **Data localizada / `Date` cru no `useForm` e timezone shift.** Guardar `01/07/2026` ou um `Date` serializado ambíguo em vez de ISO 8601 (`2026-07-01`) quebra validação/persistência; converter `Date`↔string sem cuidar do fuso desloca o dia. Ligue o `useForm` ao valor ISO (do `react-day-picker` ou do input nativo) e deixe a conversão de fuso para o servidor.
- **Foco perdido em navegação SPA.** Sem full reload, leitor de tela e teclado podem ficar "presos" no conteúdo antigo. Gerencie foco em cada navegação e em modais (ver acessibilidade).
- **Confiar no Vitest/jsdom como se fosse E2E.** jsdom não roda CSS, foco real nem a ponte Inertia no browser. Gate de E2E em browser real (Dusk/Playwright) continua de pé.
- **`useEffect` para buscar dados que deviam vir em props.** Se o dado pode vir do controller como prop, ele deve — não monte um fetch no cliente para o que a página já poderia ter recebido. `useEffect` de fetch costuma ser sintoma de fugir do modelo Inertia.
- **Lógica de negócio vazando para o React.** O componente orquestra a tela; regra de domínio mora no Laravel (`stacks/laravel/SKILL.md`). Componente gordo de regra é débito e duplica o que já existe (ou deveria existir) no servidor.

> **Inertia + React é a UI rica: SPA-like sem API separada, React costurado ao Laravel pela ponte Inertia. O Designer decide a experiência e entrega a spec/DS; esta sub-skill diz como materializar fielmente em páginas/componentes React + Tailwind, com estados completos, props como fonte de verdade, E2E em browser real e acessibilidade de piso. Backend e props em `stacks/laravel/SKILL.md`. Escolher esta stack (e ligar SSR) é ADR, não gosto.**
