---
name: stack-flutter
description: Sub-skill de stack — Flutter (frontend mobile, e web/desktop quando aplicável). Conhecimento idiomático e opinativo de Flutter que os papéis (Designer, Programador, Validador) consultam quando Flutter é o frontend da stack ativa (ver _project.md › Stack ativa). Complementa o método dos papéis com o como-fazer específico de Flutter; não o substitui. Backend: ver stacks/laravel.
---

> **Sub-skill de stack.** Ativada quando Flutter é o frontend do projeto (`_project.md` › Stack ativa). Os papéis trazem o método; esta sub-skill traz o idiomático de Flutter. Backend típico: ver `stacks/laravel/SKILL.md` (auth via Sanctum).

# Flutter — sub-skill de stack (frontend)

Flutter é um framework de UI declarativa com **uma única base de código Dart** compilada para Android, iOS, Web e desktop. Esta sub-skill reúne o como-fazer idiomático que cada papel consulta: o **Designer** decide UX/UI e entrega spec + tokens; aqui está **como materializar isso em Flutter** (quais widgets, como mapear tokens para `ThemeData`, que `Key` usar para teste). O **Programador** implementa e testa; aqui estão as camadas de teste e integração com backend que tornam a entrega "done". O **Validador** verifica evidência; aqui está o equivalente Flutter do "E2E em browser real".

Não decida experiência aqui — isso é do Designer. Não decida stack/state management/roteador aqui sem ADR — isso é do Arquiteto. Esta sub-skill diz **como** dentro do que já foi decidido.

## Quando esta stack se aplica (e quando NÃO)

**Aplica-se** quando `_project.md` › Stack ativa declara Flutter como frontend — tipicamente apps mobile (Android/iOS) com a mesma base de código atendendo também Web/desktop quando o projeto pede paridade total.

**NÃO se aplica** — e você deve usar a sub-skill de UI web correspondente (`stacks/livewire`, `stacks/inertia-react`) — quando o frontend é uma aplicação web server-rendered ou SPA web que não compartilha código com o app. Se o projeto é só web, Flutter Web raramente é a melhor escolha (SEO, peso do bundle, indexação) — confira a ADR de stack antes de assumir.

Backend é assunto de outra stack: a API consumida pelo Flutter é tipicamente Laravel (ver `stacks/laravel/SKILL.md`), com autenticação por token Sanctum.

## O que o Flutter já entrega (não reinvente)

Antes de propor qualquer widget custom, use o catálogo pronto. O Designer desenha pensando nele; o Programador implementa com ele. **Reusar o widget da framework é regra, não exceção** — widget custom é débito de manutenção.

**Material 3 vs Cupertino.** Material 3 é o default do produto (multi-plataforma, consistente, tema robusto via `ColorScheme`). Cupertino (`CupertinoButton`, `CupertinoPageScaffold`, `CupertinoSwitch`) entra **pontualmente** onde "sentir nativo" no iOS agrega valor — sem fragmentar o Design System. Não mantenha duas UIs paralelas; use widgets `.adaptive` (`Switch.adaptive`, `Slider.adaptive`, `showAdaptiveDialog`) quando a diferença plataforma-a-plataforma importa.

| Necessidade | Widget Material 3 |
|---|---|
| Estrutura de tela | `Scaffold` (appBar, body, bottomNavigationBar, drawer, FAB) |
| Navegação primária | `NavigationBar` (mobile) / `NavigationRail` (tablet/web) / `NavigationDrawer` (web largo ou secundária) |
| Barra de topo | `AppBar` / `SliverAppBar` (colapsável, scroll-aware) |
| Bloco de conteúdo | `Card`, `ListTile`, `ExpansionTile` |
| Sobreposições | `showModalBottomSheet` (`BottomSheet`), `AlertDialog` / `Dialog`, `showAdaptiveDialog` |
| Fluxo em estágios | `Stepper` (horizontal em web, vertical em mobile) |
| Formulário | `Form` + `TextFormField` (label/erro/validação integrados) |
| Seleção | `DropdownMenu`, `SegmentedButton`, `FilterChip`/`ChoiceChip`, `Checkbox`/`Radio`/`Switch` |
| Busca | `SearchBar` / `SearchAnchor` |
| Tabela tabular | `DataTable` / `PaginatedDataTable` (em mobile vira lista de cards — ver responsividade) |
| Listas longas | `ListView.builder` / `SliverList` (virtualizado — nunca `Column` com 500 filhos) |

**Heurística:** se você descreveu um padrão e existe um widget Material com esse nome, use-o. Custom só quando o catálogo realmente não cobre — e, do lado do Designer, isso passa por DDR antes de aparecer numa tela.

## Responsividade e paridade (mobile / tablet / web-desktop)

Mobile-first com propósito. A mesma widget tree atende os três tamanhos; o que muda é **como ela colapsa**. Alinhe sempre com os breakpoints do Material 3 — assim o spec do Designer vira código direto, sem ginástica.

| Classe M3 | Min-width (dp) | Layout típico |
|---|---|---|
| `compact` | 0–599 | mobile — `NavigationBar` inferior, coluna única |
| `medium` | 600–839 | tablet vertical — `NavigationRail`, master-detail leve |
| `expanded` | 840–1199 | tablet horizontal / web pequena — rail + 2 colunas |
| `large` | 1200–1599 | web/desktop — drawer/rail extendida, 2–3 colunas |
| `extra-large` | ≥1600 | limitar largura útil; não esticar conteúdo |

**Ferramentas idiomáticas:**

- `LayoutBuilder` + `MediaQuery.sizeOf(context)` para decidir layout por largura disponível. Prefira `sizeOf`/`.maybeOf` às chamadas antigas `MediaQuery.of(context).size` (rebuilds mais finos).
- `AdaptiveScaffold` (`flutter_adaptive_scaffold`) ou `NavigationSuiteScaffold` (`material`) para transicionar navegação entre `NavigationBar` ↔ `NavigationRail` ↔ `NavigationDrawer` automaticamente por breakpoint.
- `Wrap` para chips/tags que quebram linha; `Flexible`/`Expanded` para repartir espaço em `Row`/`Column`; `FractionallySizedBox` para proporções.
- `SafeArea` sempre que houver notch/barra de sistema.

**Regras de paridade (do método do Designer, materializadas aqui):** tabela com >5–7 colunas vira lista de cards em `compact` — nunca scroll horizontal infinito. Navegação inferior só em mobile; em web vira rail/drawer lateral. Sem funcionalidade hover-only (não há hover real em mobile). Alvo de toque ≥48dp (`MaterialTapTargetSize.padded`, default).

## Implementar uma spec de tela do Designer nesta stack

O Designer entrega um spec (`design/screens/STORY-XXX-<slug>.md`) com fluxo, layouts por viewport, todos os estados, microcopy, identificadores lógicos de teste e notas de acessibilidade — ver `designer/references/screen-spec-craft.md`. Materializar em Flutter:

- **Fluxo → rotas.** Cada tela é uma rota (`go_router` é o default idiomático; navegação declarativa, deep links, URL em web). Não empilhe `Navigator.push` imperativo para fluxos complexos.
- **Layouts por viewport → `LayoutBuilder`/`AdaptiveScaffold`** com os breakpoints da tabela acima.
- **Estados → estados explícitos do widget.** Vazio, loading, erro, parcial, sucesso, sem permissão, offline: cada um é um ramo renderizável. Loading usa skeleton (ex.: `shimmer` ou placeholder Material), **não** spinner em tela vazia. Erro recuperável vira `SnackBar` com action "Tentar de novo"; erro de tela vira página dedicada com caminho de saída.
- **Microcopy → strings exatas** da tabela do spec. Sem "lorem", sem reescrever o tom do Designer.
- **Campos formatados → máscara.** Todo campo com formato conhecido (CPF, CNPJ, PIS/PASEP, telefone, CEP, dinheiro, cartão, placa) usa `inputFormatters` no `TextFormField`. Default do template: **`mask_text_input_formatter`** para máscaras posicionais; `FilteringTextInputFormatter.digitsOnly` (nativo) quando só dígitos bastam. Guarde/envie o valor **sem máscara** (só dígitos): a máscara é UX, **não** validação — a canônica (dígitos verificadores, formato) é a rule do backend Laravel (`stacks/laravel/SKILL.md`). Use `keyboardType: TextInputType.number` em campo de documento; máscara não pode impedir colar. **Data/hora não se digita mascarado** — abre seletor (ver abaixo).
- **Seletores de data/hora → picker builtin.** Campo de data, hora ou data+hora (`input.datetime` do DS) abre um **seletor**, nunca `TextFormField` cru. Default do template: os builtin do Material — **`showDatePicker`** (data), **`showTimePicker`** (hora), os dois combinados para data+hora; `CupertinoDatePicker` no iOS quando o design pede. Sem dependência. Guarde/envie o valor **canônico**: `DateTime` em **UTC** serializado como ISO 8601 (`toUtc().toIso8601String()`); formate ao fuso do usuário só na apresentação (`stacks/database/database-method.md`). O seletor é UX: a validação canônica (formato, limites) é a rule do Laravel (`stacks/laravel/SKILL.md`). Package de picker custom (range/calendário) → ADR.
- **Identificadores lógicos → `Key`.** O Designer sugere o nome lógico (ex.: `item-card-acao-btn`); você aplica como `ValueKey('item-card-acao-btn')` no widget. É o que ancora widget tests e integration tests sem acoplar à árvore. Convenção: `screen-<slug>-<elemento>`. Não invente outro esquema sem motivo — o nome do spec é o contrato.

**Gestão de estado — simples é o belo (alinhado com o Arquiteto).** Prefira o mais simples que resolve: `setState` para estado local de widget; `Provider` ou `Riverpod` para estado compartilhado/injeção de dependência. Só escale para soluções mais pesadas (BLoC, redux-likes) com evidência concreta de necessidade — e isso é decisão de ADR do Arquiteto, não escolha de implementação solta. Não misture três soluções de estado no mesmo app.

## Tema / Design System na prática

O Design System é **stack-agnóstico** (tokens + comportamento), mas na stack padrão mapeia direto para o sistema de tema do Flutter — o Programador **não traduz, só aplica**. Detalhe do ofício em `designer/references/design-system-craft.md`; o de-para vive em `designer/templates/design-system.md`.

| Token do DS | Mapeamento Flutter |
|---|---|
| `brand.seed` | `seedColor` em `ColorScheme.fromSeed(seedColor: ...)` |
| `surface.page` / `surface.elevated` | `ColorScheme.surface` / `surfaceContainer*` |
| `primary` / `on-primary` / `error` / `outline` | campos homônimos do `ColorScheme` |
| escala tipográfica (`title`, `body`, `label`...) | `TextTheme` (`titleLarge`, `bodyLarge`, `labelLarge`...) |
| raio (`radius.md`, `radius.lg`) | `ShapeBorder` / `RoundedRectangleBorder(borderRadius: ...)` em component themes |
| elevação (`elev.1`–`elev.3`) | `elevation` por componente no `ThemeData` |
| motion (`motion.fast`–`motion.slow`) | duração + `Curves` (`easeOut`, `easeInOut`, `easeInOutCubic`) |

**Idiomático:**

- **Um `ThemeData` central** construído de `ColorScheme.fromSeed` — paleta M3 inteira derivada do seed. Não espalhe `Color(0xFF...)` cru pelos widgets; consuma sempre via `Theme.of(context).colorScheme` / `.textTheme`. Valor cru no widget é o mesmo pecado que valor cru no spec.
- **Component themes** (`filledButtonTheme`, `cardTheme`, `inputDecorationTheme`...) centralizam raio, padding, cor — evita repetir estilo widget a widget.
- **Light e dark** via dois `ColorScheme.fromSeed(brightness: ...)`; respeite `MediaQuery.platformBrightnessOf` se o produto suporta os dois.
- Mudança de token (paleta, tipografia) é mudança no `ThemeData` central e propaga sozinha — desde que ninguém tenha cravado valor cru. Do lado do design, é DDR (`designer/references/design-system-craft.md`).

## Testes nesta stack

> Esta é a camada que o Designer não cobre e a sub-skill **autora**: ela conecta o catálogo Flutter à exigência de testes do Programador (`programador/references/testing-discipline.md`) e ao veredito do Validador.

O Programador exige TDD e **E2E em browser real para cada fluxo de FE** (`programador/references/testing-discipline.md`). Em mobile não há "browser real" — **o equivalente é `integration_test` rodando em device/emulador real**. É esse o gate que substitui o E2E web; um único teste de caminho feliz **não fecha o gate**.

| Camada | Ferramenta | Cobre | Roda em |
|---|---|---|---|
| Unidade | `flutter_test` / `test` | lógica pura, modelos, view-models, mappers de API | VM Dart (rápido) |
| Widget | `flutter_test` (`WidgetTester`, `pumpWidget`) | um widget/tela isolada: renderiza, interage (`tap`/`enterText`), verifica via `find.byKey(ValueKey('...'))` | VM Dart (sem device) |
| Golden | `matchesGoldenFile` (`flutter test --update-goldens`) | regressão visual pixel-a-pixel de componentes/estados-chave do DS | VM Dart |
| Integração / E2E | `integration_test` + `flutter drive` (ou `patrol`) | fluxo ponta-a-ponta na app real, contra backend mockado em container | **device/emulador real** |

**Como o spec do Designer torna isso barato:** os identificadores lógicos viram `find.byKey` — testes ancoram no `ValueKey('screen-x-btn')`, não em texto frágil nem na posição na árvore. Por isso o Designer sugere o nome no spec e o Programador o aplica fielmente.

**Regras (alinhadas com o Programador):**

- TDD: teste vermelho antes do widget. Widget test cobre os **estados** do spec (vazio, erro, loading, sucesso) — não só o feliz.
- Para cada fluxo de usuário mapeado na estória (feliz, cada alternativo, cada erro alcançável), há **um cenário `integration_test` por desfecho** em device/emulador real. Evidência (vídeo/print do run no device) anexada ao PR — é o equivalente do vídeo de E2E web que o Validador espera.
- Golden tests com parcimônia: nos componentes do DS e nos estados visuais que regridem com facilidade. Não golden em tela inteira instável (data dinâmica quebra o pixel).
- Micro-validações de input (formato, encoding, limites) ficam em unit/widget — não inflam a suíte de `integration_test`.
- A suíte de `integration_test` completa deve rodar em tempo razoável no CI (device farm ou emulador headless); mantenha-a focada nos desfechos de fluxo.

## Acessibilidade na prática

O piso é WCAG 2.1 AA (intransponível — `designer/references/accessibility-basics.md`). Em Flutter:

- **Semântica vem dos widgets Material.** `FilledButton`/`TextFormField`/`ListView`/`DataTable` já expõem a árvore de acessibilidade correta (ARIA em Web, TalkBack/VoiceOver em mobile). Nunca use `GestureDetector` cru como botão — perde semântica.
- **`Semantics`** para o que o widget não infere: `Semantics(header: true)` em títulos de seção, `Semantics(label: 'verbo + objeto', button: true)` em gesto custom, `Semantics(liveRegion: true)` em `SnackBar`/banner dinâmico (vira `aria-live` na web).
- **`MergeSemantics`** agrupa nós que devem ser lidos como um só (label + valor); **`ExcludeSemantics`** / `excludeFromSemantics: true` esconde imagem decorativa.
- **Ícone-ação sozinho:** `IconButton(tooltip: 'Recusar item')` — o `tooltip` já vira label de acessibilidade. Imagem com conteúdo usa `Image(semanticLabel: ...)`.
- **Contraste** verificado nas combinações do `ColorScheme` (Material Theme Builder + WebAIM) antes de virar tema.
- **Alvo de toque ≥48dp** garantido por `MaterialTapTargetSize.padded` (default); não baixe `iconSize` sem padding.
- **`textScaleFactor`** respeitado por default — teste a tela com fonte aumentada no SO.
- **Verificação:** Flutter DevTools › aba Accessibility mostra a árvore de semântica exposta; teclado sozinho na Web é o teste mais revelador; widget tests podem assertar `Semantics` e alvos de toque.

## Flutter Web specifics

Quando a mesma base atende Web, decisões que em mobile são automáticas precisam de cuidado explícito (sinalize no spec quando a tela existir em web):

- **Scroll inercial e física:** o scroll desktop difere do mobile; valide com mouse/trackpad reais, não só no emulador touch.
- **Hover states:** `MouseRegion` / `InkWell.onHover` para feedback de hover — mas hover é informação **extra**, nunca o único trigger de funcionalidade.
- **Atalhos de teclado:** `Shortcuts` + `Actions` (ou `CallbackShortcuts`) para Cmd/Ctrl+K (busca), Esc (fechar), etc. Foco navegável por Tab vem dos widgets Material; widget custom precisa de `Focus`/`FocusableActionDetector`.
- **Rotas / URL:** com `go_router`, use URLs limpas e habilite o `PathUrlStrategy` (sem `#`). Deep links e estado refletido na URL são esperados em web.
- **SEO e peso:** Flutter Web renderiza em canvas — indexação por buscadores é fraca e o bundle inicial é pesado. Para telas públicas que dependem de SEO, isso é um trade-off real — escale ao Arquiteto antes de assumir Flutter Web como solução de site público.

## Auth / integração com backend

> Camada **autorada** pela sub-skill: como o app fala com a API (tipicamente Laravel + Sanctum — `stacks/laravel/SKILL.md`).

- **HTTP client:** `dio` é o default idiomático (interceptors, timeout, cancelamento, refresh) — `package:http` serve para casos simples. Centralize a base URL e os headers num único client; não espalhe chamadas soltas pelos widgets.
- **Auth por token (Sanctum):** para clientes mobile, use **API tokens** do Sanctum (não cookies/CSRF, que são para SPA same-site). Fluxo: o app faz `POST /login` com credenciais, recebe um token, e o anexa em todas as chamadas via header `Authorization: Bearer <token>` (interceptor do `dio`). Logout revoga o token no backend.
- **Armazenamento do token:** `flutter_secure_storage` (Keychain no iOS, Keystore/EncryptedSharedPreferences no Android). **Nunca** `SharedPreferences` cru nem em memória volátil para credenciais — token é segredo.
- **Camada de dados:** isole a API numa camada de `repository`/`service` que devolve modelos do domínio (Dart) — a UI não conhece JSON. Mappers (`fromJson`) são testáveis em unit. Erros HTTP (401, 422, 5xx) viram tipos de erro do domínio que a UI traduz em estado de tela (sem permissão, validação, erro de rede) — sem vazar `statusCode` cru pro usuário (o microcopy é do Designer).
- **Offline/rede:** trate `SocketException`/timeout como estado de tela ("offline") desenhado no spec, não como crash.

## Defaults deste template (e como divergir via ADR)

Estes são pontos de partida opinativos. Divergir é legítimo — mas via **ADR do Arquiteto**, não por preferência solta de implementação.

| Tema | Default do template | Diverge via |
|---|---|---|
| Design language | Material 3 base; Cupertino pontual no iOS | ADR |
| Navegação / rotas | `go_router` (declarativa, deep link, URL web) | ADR |
| Gestão de estado | `setState` local; `Provider`/`Riverpod` compartilhado | ADR |
| Máscara de entrada (campos formatados) | `mask_text_input_formatter` (+ `FilteringTextInputFormatter` nativo) | ADR |
| Seletor de data/hora | `showDatePicker`/`showTimePicker` builtin (Cupertino no iOS); `DateTime` UTC ISO 8601 | Package de picker custom → ADR |
| HTTP client | `dio` | ADR |
| Armazenamento seguro | `flutter_secure_storage` | ADR |
| Adaptação de navegação | `AdaptiveScaffold` / `NavigationSuiteScaffold` | spec/ADR |
| E2E mobile | `integration_test` em device/emulador real | ADR (ferramenta: `patrol` etc.) |

## Armadilhas conhecidas (gotchas)

- **`Column`/`Row` com lista longa** → reconstrói tudo e estoura layout. Use `ListView.builder`/`SliverList` (virtualizado).
- **Valor cru espalhado** (`Color(0xFF...)`, `EdgeInsets.all(13)`, `Duration(milliseconds: 450)`) → quebra o DS e dificulta mudar a paleta. Consuma `Theme.of(context)` e a escala de tokens.
- **`GestureDetector` como botão** → sem semântica, sem foco, sem ripple. Use `FilledButton`/`IconButton`/`InkWell` com `Semantics`.
- **Spinner em tela vazia** → o Designer pede skeleton. Loading sem skeleton é falha de performance percebida.
- **Listas sem `Key` estável** → reordenação/animação embaralha estado de widget; testes ficam frágeis. Use o `ValueKey` do identificador do spec.
- **`MediaQuery.of(context).size` em excesso** → rebuilds largos. Prefira `MediaQuery.sizeOf(context)` (escuta só o que importa).
- **`async` após `await` usando `context`** → "use of BuildContext across async gaps". Guarde referências antes do `await` ou cheque `if (!mounted) return;`.
- **Flutter Web tratado como site comum** → SEO fraco, bundle pesado. Não é substituto de web server-rendered para conteúdo público — escale ao Arquiteto.
- **Token em `SharedPreferences` cru** → vazamento de credencial. Use `flutter_secure_storage`.
- **Máscara confundida com validação** → `inputFormatters` formata na tela, não valida CPF/CNPJ nem impede lixo. Enviar/persistir o valor mascarado quebra o backend: guarde o valor sem máscara (só dígitos) e valide no servidor. Máscara também não pode bloquear colar.
- **Data em `TextFormField` mascarado ou em fuso local ambíguo** → data/hora não se digita: use o picker builtin. `DateTime.now()` local persistido sem fuso vira horário ambíguo — guarde `toUtc().toIso8601String()` e converta ao fuso do usuário só na tela.
- **Duas UIs paralelas (Material + Cupertino)** mantidas à mão → débito dobrado. Use widgets `.adaptive` e um DS único.
- **Golden test em tela com dado dinâmico** → quebra a cada run. Golden só em componentes/estados estáveis.

> **Flutter é o COMO; o método é dos papéis. O Designer decide a experiência e os tokens; esta sub-skill os materializa em widgets, `ThemeData` e `Key`. O Programador testa em camadas — widget, golden, e `integration_test` em device real como o E2E do mobile. A API entra por uma camada isolada, com token Sanctum em armazenamento seguro. Simples antes de pesado; widget pronto antes de custom; token antes de valor cru.**
