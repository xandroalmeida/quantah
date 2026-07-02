---
name: stack-livewire
description: Sub-skill de stack — Livewire (UI web sobre Laravel). Conhecimento idiomático e opinativo de Livewire que os papéis (Designer, Programador, Validador) consultam quando Livewire é a camada de UI web da stack ativa (ver _project.md › Stack ativa). Complementa o método dos papéis com o como-fazer específico de Livewire; não o substitui.
---

> **Sub-skill de stack.** Ativada quando Livewire é a UI web do projeto (`_project.md` › Stack ativa). Os papéis trazem o método; esta sub-skill traz o idiomático de Livewire. Backend: ver `stacks/laravel/SKILL.md`.

# Livewire — sub-skill de stack (UI web)

Livewire é a **UI web padrão deste template** — server-driven, montada em componentes PHP + templates Blade, com interatividade de cliente coberta por Alpine.js. O modelo mental: o **estado vive no servidor**, o componente re-renderiza no backend e o Livewire envia ao browser apenas o diff do DOM. Você escreve quase tudo em PHP e Blade; cai para JavaScript só onde a interação é genuinamente de cliente.

Esta sub-skill diz **como materializar na tecnologia** o que os papéis decidiram. Ela **não decide UX** — isso é do Designer (`designer/SKILL.md`), que entrega a spec de tela e o Design System. Ela **não reescreve o backend** — models, validação de domínio, autorização, queries e jobs seguem `stacks/laravel/SKILL.md`. Livewire é a camada que costura a spec do Designer à lógica de aplicação do Laravel.

## Quando esta stack se aplica (e quando NÃO)

Livewire é o default porque cobre **a maioria das telas de produto de negócio** com o menor custo de JS e a menor superfície de bugs de sincronização. Use-o quando:

| Use Livewire quando | Reavalie (talvez `inertia-react`) quando |
|---|---|
| CRUD, formulários, wizards, tabelas com filtro/paginação | Interação cliente rica: drag-and-drop complexo, canvas, edição colaborativa em tempo real |
| Telas padrão de painel administrativo / back-office | Estado de cliente grande e interdependente que sofre com round-trip a cada mudança |
| Time pequeno, sem especialista em frontend dedicado | Dashboards muito interativos com cálculo client-side intenso |
| Interatividade leve a média (toggles, modais, validação ao vivo, busca incremental) | Offline-first ou app que precisa funcionar com latência alta sem feedback travado |
| Você quer **uma** linguagem (PHP) cobrindo lógica e tela | SEO de páginas públicas pesadas que exige SSR fino e controle de bundle |

**Regra prática:** comece em Livewire. A migração para `inertia-react` em uma tela específica é uma decisão arquitetural — registre via **ADR** (não é escolha de gosto do Programador). Misturar Livewire e Inertia no mesmo projeto é possível, mas é débito: justifique por tela, não adote como hábito.

A **escolha de Livewire vs Inertia/React é do Arquiteto, via ADR** — não do Designer nem do Programador no calor da estória. O Designer desenha a experiência de forma agnóstica; se uma tela parecer pedir interação rica demais para Livewire, o Designer **levanta a dúvida de viabilidade** no rabisco inicial e escala, não decide a stack.

## O que Livewire já entrega (não reinvente)

Antes de escrever JS ou um componente custom, lembre que o framework já dá:

- **Ciclo de vida do componente** — `mount()` (inicialização), `render()` (devolve a view Blade), hooks `updating`/`updated`, `hydrate`/`dehydrate`, `boot`. Propriedades públicas viram estado reativo automaticamente.
- **Binding bidirecional** — `wire:model` (e `wire:model.live`, `.blur`, `.debounce.300ms`) liga input a propriedade do componente sem você escrever handler.
- **Ações** — `wire:click`, `wire:submit`, `wire:keydown.enter` chamam métodos públicos do componente PHP diretamente.
- **Validação integrada ao Laravel** — `$this->validate()` com as mesmas rules do backend; mensagens de erro disponíveis em Blade via `@error`. Não duplique regra de validação de domínio no cliente.
- **Estados de carregamento** — `wire:loading`, `wire:loading.attr="disabled"`, `wire:target` dão skeleton/spinner/disabled sem JS manual. Casa direto com o Princípio de performance percebida do Designer.
- **Navegação SPA-like** — `wire:navigate` faz transições entre páginas sem full reload, preservando sensação de app.
- **Paginação** — trait `WithPagination` integra a paginação do Laravel.
- **Upload de arquivo** — `WithFileUploads` com preview e progresso.
- **Eventos** — `$this->dispatch()` para comunicação entre componentes e para Alpine/JS no browser.
- **Lazy loading** — `#[Lazy]` adia render de componente pesado com placeholder.
- **Alpine.js embutido** — Livewire já carrega Alpine; use-o para interatividade puramente de cliente (toggle de menu, dropdown, máscara) sem round-trip ao servidor.

Reinventar qualquer um desses em JS custom é gerar débito e abrir flanco de bug. Use o que o framework dá; desça ao custom só quando ele comprovadamente não cobre.

## Padrões idiomáticos

**Componente = unidade de tela ou de seção coesa.** Um componente Livewire (`app/Livewire/...` + view `resources/views/livewire/...`) corresponde a uma tela ou a uma seção autocontida (uma tabela com filtros, um formulário, um modal). Não faça um componente-deus que gerencia a página inteira; também não estilhace em dezenas de micro-componentes que disparam muitos round-trips.

**Propriedades públicas são o estado — mantenha-as enxutas.** Tudo que é propriedade pública é serializado a cada request (vai e volta no payload). Guarde só o necessário; derive o resto em métodos/computed properties (`#[Computed]`). Objetos grandes (coleções de models inteiros) na propriedade pública incham o payload e o snapshot — prefira IDs e recarregue, ou use computed.

**Alpine para o que é de cliente; Livewire para o que toca dados.** Toggle de visibilidade, foco, animação de UI, máscara de input → Alpine (`x-data`, `x-show`, `x-on`), sem ir ao servidor. Persistir, validar contra regra de negócio, buscar dados → método Livewire. Saber traçar essa linha é a competência central nesta stack: cada `wire:` desnecessário é um round-trip; cada lógica de dados no cliente é uma regra de negócio fora do lugar.

**Máscaras de entrada em campo formatado.** Todo campo com formato conhecido (CPF, CNPJ, PIS/PASEP, telefone, CEP, dinheiro, cartão, placa) recebe **máscara de entrada** — é ajuda de UX puramente de cliente, então mora no **Alpine**, sem round-trip. Default do template: **`@alpinejs/mask`** (diretiva `x-mask`), plugin oficial já do ecossistema Alpine. Regra de ouro: a máscara **não** é validação nem fonte de verdade — o `wire:model` recebe o **valor canônico sem máscara** (só dígitos), a validação canônica é a rule do Laravel (`stacks/laravel/SKILL.md`) e o valor persistido é o sem máscara. Placeholder mostra o formato (ex.: `000.000.000-00`); teclado numérico (`inputmode`) em campo de documento. Não escreva máscara em JS custom — o plugin cobre; caso raríssimo não coberto vira decisão localizada. **Data/hora não usam máscara** — usam seletor (ver abaixo); `x-mask` de `dd/mm/aaaa` só como fallback de digitação.

**Seletores de data/hora.** Campo de data, hora ou data+hora (`input.datetime` do DS) entra por **seletor**, não digitação livre. Default do template: **Flatpickr** montado via Alpine (`x-init`) numa **ilha `wire:ignore`** (o diff do Livewire não pode recriar o DOM controlado pela lib). O `wire:model` recebe o **valor canônico ISO 8601** (`Y-m-d`, `H:i` ou datetime ISO), nunca a copy localizada. O nativo `<input type="date|time|datetime-local">` é fallback simples e legítimo. O seletor é UX: a validação canônica (formato, limites `after`/`before`) é a rule do Laravel (`stacks/laravel/SKILL.md`) e a persistência é com fuso em UTC (`stacks/database/database-method.md`). Seletor navegável por teclado e com digitação como alternativa (acessibilidade). Outra lib de picker → ADR.

**Validação ao vivo com parcimônia.** `wire:model.live` em todo campo gera um request por tecla. Use `.blur` ou `.debounce` por padrão; reserve `.live` para casos onde o feedback imediato é parte da experiência (busca incremental, validação de unicidade). A regra de validação canônica é a do Laravel (`stacks/laravel/SKILL.md`) — Livewire a executa, não a substitui.

**Autorização não é da UI.** Esconder um botão no Blade é UX, não segurança. A checagem real (`authorize`, Policies, Gates) acontece no método do componente / na camada Laravel. Botão escondido + ação desprotegida = falha de segurança.

**Eventos para desacoplar.** Componentes irmãos conversam por `dispatch`/`#[On]`, não por referência direta. Modal de criação dispara `item-criado`; a tabela ouve e recarrega. Evita acoplamento e mantém componentes testáveis isoladamente.

**`wire:key` em listas.** Em `@foreach` que renderiza componentes/blocos dinâmicos, `wire:key` único é obrigatório para o diff de DOM não embaralhar elementos. Use o nome lógico de identificador sugerido pelo Designer no spec como base do key quando fizer sentido.

## Implementar uma spec de tela do Designer nesta stack

O Designer entrega: spec de tela (`design/screens/STORY-XXX-*.md`) + protótipo HTML fiel + referência aos componentes do Design System. Sua tarefa é **materializar essa spec em Livewire fielmente**, sem reinterpretar UX. De-para típico:

| Na spec do Designer (agnóstica) | Em Livewire |
|---|---|
| Tela / fluxo | Componente Livewire (classe + view Blade), rota apontando para ele |
| Layout mobile + desktop (paridade) | Markup Blade com utilitários Tailwind responsivos (`sm:`, `lg:`) — ver seção de estilo |
| Componente do DS (botão, input, card, empty-state) | Componente Blade reutilizável (`resources/views/components/...`) que encarna o componente do DS |
| Estados (vazio, loading, erro, sucesso, sem permissão) | Branches no `render`/Blade + `wire:loading` + `@error` + bloco de empty-state + checagem de policy |
| Microcopy (labels, placeholders, mensagens) | Texto **exato** da spec no Blade — não improvise; divergência de microcopy é bug |
| Identificador estável sugerido (`item-card-acao-btn`) | `dusk="item-card-acao-btn"` (atributo de teste do Dusk) e/ou `wire:key` no elemento |
| Microinteração / feedback imediato | Alpine (`x-transition`, estados visuais) ou `wire:loading` — feedback em ≤100ms é meta do DS |

**Estados não são opcionais.** A spec do Designer lista vazio/loading/erro/sem-permissão como entregáveis (Princípio 7 do Designer). Em Livewire isso vira: empty-state com `@if($items->isEmpty())`, loading com `wire:loading`/skeleton, erro com `@error` e bloco de erro de tela para falhas não-de-campo, sem-permissão com checagem de policy renderizando o estado correspondente. Implementar só o caminho feliz é entregar meio spec.

**Não cruze a fronteira.** Se durante a implementação você perceber que um estado falta na spec, ou que uma interação não foi desenhada, **não invente UX** — devolva ao Designer. Você decide *como construir em Livewire*; o Designer decide *como o usuário experimenta*.

## Estilo / Design System na prática

**Tailwind CSS é o default do ecossistema** (Livewire + Tailwind é o par idiomático; o starter padrão já vem assim). O Design System do Designer é descrito em **tokens e comportamento, de forma agnóstica** (`designer/references/design-system-craft.md`). Sua tarefa é **mapear esses tokens para o Tailwind do projeto** — não reinventar a paleta.

- **Tokens → `tailwind.config` + CSS variables.** Cor, tipografia, espaçamento, raio, sombra, motion e breakpoints do DS viram a configuração do Tailwind (theme.extend) e/ou custom properties CSS. Token semântico do DS (`color.brand.primary`, `space.4`) vira utilitário/classe; **nunca espalhe valor cru** (`#1E40AF`, `16px`) no Blade — a regra de ouro dos tokens do Designer vale aqui.
- **Componentes do DS → componentes Blade.** Cada componente do DS (botão com variantes/estados, input, card, empty-state) vira um **componente Blade** (`<x-button variant="primary">`) com seus estados (default/hover/focus/active/disabled/loading/error) materializados em classes Tailwind. Reusar antes de criar é regra do Designer **e** sua: um componente Blade por conceito do DS, não markup duplicado por tela.
- **Breakpoints alinhados.** Use os breakpoints do DS mapeados aos prefixos do Tailwind (`sm:`/`md:`/`lg:`/`xl:`). Paridade mobile/desktop (`designer/references/mobile-desktop-parity.md`) se implementa com utilitários responsivos, não com telas separadas.
- **Divergência do DS** que você perceba necessária → não decida sozinho: é exceção de spec (Designer) ou mudança de token (DDR do Designer). Você implementa o token; não o redefine.

## Testes nesta stack (incl. E2E em browser real)

A disciplina é a de `programador/references/testing-discipline.md` — caminho feliz **nunca** basta; cada CA tem feliz, inválido, exceção e borda; FE web exige **E2E em browser real** para cada caminho mapeado. Em Livewire isso se concretiza em duas camadas:

- **Testes de componente Livewire (rápidos, sem browser).** Os helpers do Livewire (`Livewire::test(Componente::class)`) permitem montar o componente, `->set()` propriedades, `->call()` métodos, e asserir estado/eventos/validação/render: `assertSee`, `assertSet`, `assertHasErrors`, `assertDispatched`, `assertRedirect`. Rápidos como teste de feature do Laravel. Cobrem a **lógica** do componente: validação dispara, ação muda estado, evento é emitido, estado vazio/erro renderiza. São o equivalente a unit/integração desta camada — não são E2E.
- **E2E em browser real — Laravel Dusk.** Dusk dirige um Chrome real (ChromeDriver) e é o E2E idiomático do ecossistema Laravel/Livewire. Ele valida o que o teste de componente **não** valida: CSS de verdade, foco por teclado, eventos reais de browser, a ponte Livewire↔Alpine funcionando no DOM. Ancore os seletores nos **identificadores estáveis da spec do Designer** via `dusk="..."` (não em estrutura frágil de DOM). Cubra **cada caminho mapeado** do fluxo — feliz, alternativos e cada fluxo de erro alcançável pela UI — não só o feliz. Um único E2E de caminho feliz **não fecha o gate**.

> O par helper-de-componente (rápido, lógica) + Dusk (browser real, fluxo) cobre as duas necessidades. A ferramenta de E2E é decisão de ADR; **Dusk é o default deste template** por integração nativa com Laravel/Livewire. Playwright é alternativa legítima se o projeto já o usa — registre via ADR.

O **Validador** (`validador/`) emite o veredito independente no fim do épico rodando os fluxos em browser real; o **Programador** anexa evidência (vídeo/print/link) de E2E por desfecho mapeado no PR. Esta sub-skill diz **com o quê** (Dusk + helpers Livewire); o método dos gates é dos papéis.

## Acessibilidade na prática

O piso é o de `designer/references/accessibility-basics.md` (WCAG 2.1 AA — contraste, foco visível, teclado, semântica, erro textual, label em ícone, alvo de toque ≥44–48px). Em Livewire, acessibilidade é **HTML semântico no Blade** — você tem controle direto do markup, então não há desculpa:

- **Elementos semânticos reais.** `<button>` para ação, `<a>` para navegação, `<nav>`, `<main>`, headings em ordem. Nunca `<div wire:click>` para o que é botão — perde foco, teclado e leitor de tela.
- **Label associado.** `<label for>` + `id` no input (ou label envolvente). `wire:model` não dispensa label.
- **Erro textual, não só cor.** A mensagem de `@error` vinculada ao campo (`aria-describedby`), não borda vermelha sozinha — daltonismo é comum.
- **Foco e mudança dinâmica.** Conteúdo que muda via Livewire (resultado de busca, banner) deve ser anunciado: `aria-live="polite"` em região que atualiza. Após `wire:navigate`, garanta que o foco vá a lugar sensato.
- **Ícone sozinho tem label.** Botão só com ícone recebe `aria-label` com verbo + objeto.
- **Modal/sheet em Alpine** precisa de foco-trap, `Esc` para fechar e retorno de foco ao gatilho — Alpine não dá isso de graça; implemente ou use plugin de foco.

Verificação: teclado sozinho na página (Dusk pode dirigir Tab/Enter), axe/Lighthouse no DOM renderizado para o piso. Falha de piso é **bloqueio de PR**, não trade-off (regra do Designer).

## Defaults deste template (e como divergir via ADR)

| Decisão | Default do template | Como divergir |
|---|---|---|
| UI web | **Livewire** (server-driven) | ADR para adotar `inertia-react` num escopo |
| Interatividade de cliente | **Alpine.js** (já embutido) | JS custom localizado, justificado |
| Máscara de entrada (campos formatados) | **`@alpinejs/mask`** (`x-mask`) | Outra lib de máscara → ADR |
| Seletor de data/hora | **Flatpickr** (via Alpine, ilha `wire:ignore`); valor ISO 8601 no `wire:model` | Nativo `<input type=date>` p/ caso simples; outra lib → ADR |
| Estilo | **Tailwind CSS** | Outra abordagem → ADR + remapear DS |
| E2E browser real | **Laravel Dusk** | Playwright/Cypress → ADR |
| Teste de lógica de componente | **Helpers `Livewire::test()`** | — (é o idiomático) |
| Validação | **Rules do Laravel** via `$this->validate()` | — (não duplicar no cliente) |
| Navegação | **`wire:navigate`** (SPA-like) | — |

Backend (models, migrations, policies, queries, jobs, filas) **não é desta sub-skill** — segue `stacks/laravel/SKILL.md`. Não duplique aqui regra de domínio, autorização ou acesso a dados.

## Armadilhas conhecidas (gotchas)

- **Re-render a cada interação.** Toda ação Livewire re-renderiza o componente no servidor e diffa o DOM. Lógica cara dentro de `render()` roda em todo request — mova para `mount()`, computed properties (`#[Computed]`, cacheadas no request) ou eventos. Não faça query pesada no caminho de render.
- **Payload inchado por estado gordo.** Propriedade pública com coleção grande de models vai e volta inteira a cada request, serializada no snapshot. Guarde IDs/escalares; recarregue ou use computed. Sintoma: requests lentos e payloads enormes no network tab.
- **`wire:model.live` em excesso.** Um request por tecla trava a digitação sob latência. Default `.blur`/`.debounce`; `.live` só onde a experiência exige.
- **Estado mora no servidor — não confie no cliente.** O cliente pode estar dessincronizado entre requests; nunca trate valor vindo do browser como confiável para regra de negócio. Revalide no servidor (a validação do Laravel já faz isso). Esconder botão ≠ proteger ação.
- **Falta de `wire:key` em listas dinâmicas.** Sem key, o diff embaralha/reusa elementos errados ao reordenar/filtrar. Sempre `wire:key` único em `@foreach`.
- **Conflito Livewire ↔ Alpine no mesmo elemento.** Os dois manipulam o DOM; use `wire:ignore` em ilhas controladas só por Alpine/lib externa para o diff do Livewire não as sobrescrever.
- **`wire:click` em `<div>` em vez de `<button>`.** Quebra acessibilidade (foco/teclado/leitor de tela). Use o elemento semântico certo.
- **Confundir máscara com validação.** `x-mask` formata na tela; não valida CPF/CNPJ nem impede lixo. Vincular `wire:model` ao valor mascarado e persistir com máscara é bug: envie/persista o valor canônico (só dígitos) e valide no servidor (rule do Laravel). Máscara também não pode bloquear colar.
- **Flatpickr sem `wire:ignore`.** O picker vive em DOM controlado pela lib; sem `wire:ignore`, o diff do Livewire recria o elemento e quebra o seletor (é o conflito Livewire↔Alpine na prática). Isole a ilha. E ligar o `wire:model` ao texto localizado (`01/07/2026`) em vez de ISO (`2026-07-01`) quebra validação/persistência: guarde ISO 8601.
- **Achar que o helper de componente substitui E2E.** Ele testa lógica PHP, não o browser. CSS, foco real, ponte Alpine e fluxo ponta-a-ponta só caem no Dusk. Gate de E2E em browser real continua de pé.
- **Lógica de negócio vazando para o componente.** O componente Livewire orquestra a tela; regra de domínio mora na camada Laravel (services/actions/models — `stacks/laravel/SKILL.md`). Componente gordo de regra é débito.

> **Livewire é o default: server-driven, pouco JS, uma linguagem. O Designer decide a experiência e entrega a spec/DS; esta sub-skill diz como materializar fielmente em componentes Livewire + Blade + Alpine + Tailwind, com estados completos, E2E em Dusk e acessibilidade de piso. Backend em `stacks/laravel/SKILL.md`. Trocar de stack numa tela é ADR, não gosto.**
