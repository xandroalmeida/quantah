# Design System — Quantah (esqueleto inicial)

> Este é o template para inicializar o Design System **vivo** em `docs/project-state/design/system/`. Os tokens reais saem do **protótipo** em `docs/prototipo/` (manifest, CSS, comportamento das telas) e da paleta derivada dele. O DS é descrito em **tokens e comportamento** — não em código de produção — mas com a forma que mapeia direto para o sistema de tema da stack do projeto. Toda inclusão/alteração relevante de componente, token ou padrão passa por um **DDR** (Design Decision Record); mudança de fundação visual (paleta, tipografia, regra de acento) exige DDR **e** atualização coordenada com o PO.

> Este template é **agnóstico de frontend**: a estrutura de tokens, componentes e padrões — e o método de evolução via DDR — vale para qualquer stack. Os tokens mapeiam para o **sistema de tema da stack de FE ativa** e cada componente do DS aponta para o **componente equivalente do catálogo da stack** — o de-para concreto (nomes do tema, widgets/componentes, mecanismo de responsividade, APIs de acessibilidade) vive na sub-skill de FE correspondente (ver `_project.md` › Stack ativa; ex.: `stacks/flutter/SKILL.md`, `stacks/livewire/SKILL.md`, `stacks/inertia-react/SKILL.md`).

A estrutura recomendada do DS são 4 arquivos sob `docs/project-state/design/system/`:

```
docs/project-state/design/system/
├── README.md              ← entrada (visão + como navegar)
├── tokens.md              ← fundações: cor, tipografia, espaçamento, raio, sombra, motion, breakpoints
├── components.md          ← biblioteca de componentes (mapeada para o componente equivalente da stack ativa)
├── patterns.md            ← padrões compostos (form, listagem, wizard, vazio, erro)
└── voice-and-tone.md      ← tom, microcopy, vocabulário
```

A seguir, esqueleto sugerido para cada arquivo.

---

## `README.md`

```markdown
# Design System Quantah

Vocabulário visual e de interação compartilhado pelas telas do Quantah (todas as plataformas que a stack de FE ativa atende). Descreve **comportamento e visual em termos de tokens e estados**, com a forma que **mapeia para o sistema de tema da stack ativa** (o de-para concreto vive na sub-skill de FE). Designer **não escreve código de produção**; Programador **não inventa token**.

## Como usar

- Antes de desenhar uma tela nova, leia este DS — provavelmente o que você precisa já existe.
- Antes de criar componente novo, confirme que **o componente que a stack de FE ativa já entrega não cobre** (ver sub-skill). Reaproveite o catálogo da stack primeiro.
- Componente novo entra por **DDR** primeiro (ver `docs/skills/designer/templates/ddr.md`), não direto.
- Spec de tela referencia componentes pelo id (`ds_components_used` no frontmatter do spec).

## Navegação

- `tokens.md` — fundações (cor, tipografia, espaçamento, raio, sombra, motion, breakpoints).
- `components.md` — biblioteca de componentes (com componente equivalente da stack ativa quando aplicável).
- `patterns.md` — padrões compostos recorrentes.
- `voice-and-tone.md` — tom de voz e vocabulário.

## Status

Versão: 0.1 — esqueleto inicial.
Última atualização: YYYY-MM-DD.
```

---

## `tokens.md`

```markdown
# Tokens

> Tokens são as fundações. Toda decisão visual sai daqui. **Não use valor cru** em spec — use token. O Programador mapeia cada token para o sistema de tema da stack ativa (o de-para concreto está na sub-skill de FE).

## Cor (ponto de partida — do `manifest.json` do protótipo)

> Substitua os valores de partida abaixo pela cor de marca e fundo declarados no `manifest.json` do seu projeto. Cada token de cor mapeia para um papel no esquema de cor da stack ativa (cor de marca/semente, fundo, superfície, primária, sobre-primária, contorno, erro) — o nome concreto desse papel no tema da stack vive na sub-skill de FE.

| Token | Valor de partida | Papel no esquema de cor | Uso |
|---|---|---|---|
| `brand.seed` | `<cor de marca do theme_color>` | semente que gera a paleta | Geração da paleta |
| `surface.page` | `<cor de fundo do background_color>` | fundo | Fundo da página |
| `surface.elevated` | `#FFFFFF` | superfície | Cards, sheets, dialogs |
| `primary` | derivado do seed | primária | CTA primário, indicador ativo |
| `on-primary` | derivado do seed | sobre-primária | Texto/ícone sobre `primary` |
| `secondary` | derivado do seed | secundária | Acento secundário (raríssimo) |
| `outline` | derivado do seed | contorno | Bordas de input, divisores |
| `error` | default do tema | erro | Erro, validação negativa, ação irreversível |
| `on-error` | default do tema | sobre-erro | Texto sobre `error` |

**Regras de uso (regras de ouro do DS):**

- O acento `brand.seed` é o **único condutor de interação**. Reserve para **um** CTA primário por tela e indicadores de estado positivo (ex.: ação confirmada, sucesso). Não use em decoração, ícones genéricos, nem em duas ações por tela.
- A paleta inteira deriva do seed — não introduza cor adicional sem DDR.
- **Flat por design.** Sem gradientes. Sem segundo acento concorrente. Elevação em níveis baixos (0–2 padrão; 3 em casos especiais).
- Cor de feedback **nunca é o único canal** — sempre acompanha ícone + texto (acessibilidade e usuário não-técnico).

> Qualquer adição/alteração à paleta exige DDR.

## Tipografia

- **Família:** definir no DDR-001 do DS (sugestões: Inter / Roboto / Plus Jakarta Sans / fonte de sistema). **Uma família única** para texto; mono opcional só para códigos/PIN se realmente precisar.
- **Regra de peso:** preferir 400 (regular) e 500 (medium). Evitar 700+ em texto comum — contraste sai de tamanho e cor, não de bold. Headings podem usar 600.
- **Escala tipográfica** (cada papel mapeia para o nível equivalente na escala de tipografia da stack ativa — ver sub-skill de FE):

| Token (DS) | Papel | Tamanho sugerido | Weight |
|---|---|---:|---:|
| `display` | display | 36sp | 400 |
| `headline` | headline | 28sp | 500 |
| `title` | title | 22sp | 500 |
| `subtitle` | subtitle | 16sp | 500 |
| `body` | body | 16sp | 400 |
| `body-sm` | body pequeno | 14sp | 400 |
| `label` | label | 14sp | 500 |
| `caption` | caption | 12sp | 400 |

> O usuário não-técnico precisa **ler sem esforço**. Não desça body abaixo de 14sp em produção. Web pode subir para 17–18sp em corpo de texto.

## Espaçamento (em dp / logical px)

| Token | Valor |
|---|---:|
| `xs` | 4 |
| `sm` | 8 |
| `md` | 16 |
| `lg` | 24 |
| `xl` | 32 |
| `2xl` | 48 |
| `3xl` | 64 |

Use múltiplos de 4. Um padding de 16 ≡ `space.md`.

## Raio (border-radius)

| Token | Valor | Uso |
|---|---:|---|
| `radius.sm` | 8 | Chips, badges, inputs densos |
| `radius.md` | 12 | Botões, inputs, topo de bottom-sheet |
| `radius.lg` | 16 | Cards, dialogs, sheets |
| `radius.xl` | 24 | Bottom-sheets modais grandes, hero containers |
| `radius.full` | 9999 | Avatares, dots de status, botão flutuante |

## Elevação

Use níveis baixos. O tema da stack normalmente já desenha sombra suave nesses níveis.

| Token | Nível | Uso |
|---|---:|---|
| `elev.0` | 0 | Background da página, área "plana" |
| `elev.1` | 1 | Card padrão, barra de topo |
| `elev.2` | 2 | Sheet modal, toast |
| `elev.3` | 3 | Dialog, gaveta aberta |

Acima de `elev.3` → erro de design (sinal de hierarquia confusa).

## Motion (durações + curvas)

| Token | Duração | Curva (easing) | Uso |
|---|---:|---|---|
| `motion.fast` | 100ms | ease-out | Feedback imediato (press, hover web) |
| `motion.base` | 200ms | ease-in-out | Mudanças de estado, abrir/fechar inline |
| `motion.slow` | 300ms | ease-in-out (cúbica) | Transição entre telas, gavetas |

> Transições têm propósito (orientar atenção), nunca decoração. Acima de 300ms → erro de design (exceto onboarding deliberado). A curva concreta da stack ativa está na sub-skill de FE.

## Breakpoints

| Token | Min-width (dp) | Apelido | Uso típico |
|---|---|---|---|
| `bp.compact` | 0 | mobile (base — mobile-first) | Navegação inferior, layout em coluna única |
| `bp.medium` | 600 | tablet vertical | Navegação lateral, master-detail leve |
| `bp.expanded` | 840 | tablet horizontal / web pequena | Navegação lateral estendida, 2 colunas |
| `bp.large` | 1200 | web/desktop | Navegação lateral/gaveta, 2–3 colunas |
| `bp.extraLarge` | 1600 | desktop largo | Limitar largura útil — não estique conteúdo |

O mecanismo de responsividade que alterna entre breakpoints é da stack ativa (ver sub-skill de FE).

## Toque e acessibilidade (pisos)

- **Alvo mínimo de toque: 48×48 dp.** Itens densos em web podem ser menores, mas em mobile **nunca**.
- Contraste WCAG AA: 4.5:1 para texto normal, 3:1 para texto grande/ícone.
- Foco visível **sempre** (a stack ativa normalmente entrega o indicador por padrão — ver sub-skill).
```

---

## `components.md`

```markdown
# Componentes

> Cada componente do DS mapeia, sempre que possível, para um componente existente do catálogo da stack de FE ativa. O **componente concreto** que materializa cada entrada vive na sub-skill de FE (ver `_project.md` › Stack ativa; ex.: `stacks/flutter/SKILL.md`) — preencha o campo "Componente da stack equivalente" com base nela. Componente custom só existe quando o catálogo da stack realmente não cobre — e entra por DDR. O vocabulário de domínio nos exemplos (ex.: `card.<entidade>`) é genérico — substitua pelos termos do seu glossário.

## Como ler

- **id** é o que o spec de tela referencia em `ds_components_used`.
- **Componente da stack equivalente** é a referência de implementação (qual componente da stack de FE ativa cobre — ver sub-skill; Programador segue, exceto se ADR disser outra coisa).
- **Estados** cobrem `default`, `hover` (web), `focus`, `pressed`, `disabled`, `loading`, `error` quando aplicáveis.
- **Não usar quando** é tão importante quanto **usar quando** — restringe.

---

### `button.primary`

**Descrição:** ação principal de uma tela ou bloco. Existe **no máximo uma por contexto**.

**Componente da stack equivalente:** o botão de ação preenchido/primário do catálogo da stack ativa (ver sub-skill de FE). Para CTA muito alto-impacto em mobile (ex.: "Confirmar ação"), variante com ícone à esquerda.

**Anatomia:** label (obrigatório, verbo no infinitivo curto), ícone opcional à esquerda, padding interno horizontal `space.lg`, altura ≥48 em mobile, raio `radius.md`, cor `primary`, texto `label` em `on-primary`.

**Estados:**

| Estado | Comportamento |
|---|---|
| default | `primary` + `on-primary` |
| hover (web) | overlay 8% sobre `primary` |
| focus | indicador de foco padrão da stack |
| pressed | overlay 12% sobre `primary` |
| disabled | opacidade 38%, cursor padrão |
| loading | indicador de progresso inline no lugar do label; toque bloqueado |

**Usar quando:** ação principal e única do contexto (tela, sheet, dialog).

**Não usar quando:** ação destrutiva (`button.danger`); ação secundária (`button.secondary`).

```
+-------------------------------+
|   Confirmar ação              |
+-------------------------------+
```

---

### `button.secondary`

**Componente da stack equivalente:** botão de contorno ou de texto do catálogo da stack ativa (ver sub-skill). (Descrever brevemente.)

---

### `input.text`

**Componente da stack equivalente:** o campo de texto de formulário do catálogo da stack ativa (com label, helper e erro integrados — ver sub-skill).

**Anatomia:** label flutuante (obrigatório), input, texto de ajuda opcional abaixo, mensagem de erro quando aplicável.

**Estados:** default / focus / disabled / error / readonly.

**Acessibilidade:** label sempre associado ao input, erro associado e anunciado por leitor de tela, foco visível obrigatório.

**Máscara/formato:** campo com formato conhecido — CPF, CNPJ, PIS/PASEP, telefone, CEP, dinheiro, cartão, placa — **sempre** apresenta **máscara de entrada progressiva** (variante `input.masked`). O placeholder mostra o formato esperado (ex.: `000.000.000-00`, `(00) 00000-0000`). A máscara é **ajuda de UX, não validação**: a validação canônica é do servidor e o valor persistido é o **canônico, sem máscara** (ver `pattern.form`). A API concreta da máscara vive na sub-skill de FE ativa. **Data, hora e data+hora são exceção:** entram por **seletor** (variante `input.datetime`), não por máscara de texto — esta fica só como **fallback** de digitação.

**Microcopy para não-técnico:** label diz **o que é**, placeholder dá **exemplo concreto** ("Ex.: João da Silva") — em campo mascarado, o exemplo é o próprio formato —, helper explica **por que pedimos** se necessário (ex.: "Para o Analista B2B saber quem realizou a ação").

---

### `input.datetime`

**Componente da stack equivalente:** o seletor de data/hora do catálogo da stack ativa (calendário/relógio — ver sub-skill).

**Anatomia:** campo com label flutuante que **abre um seletor** (calendário para data, relógio/roda para hora) ao focar/tocar; texto de ajuda opcional; mensagem de erro quando aplicável.

**Variantes (granularidade):** só-data, só-hora, data+hora. Declare também os **limites** quando houver (mínimo/máximo, faixas bloqueadas — ex.: "não permite data futura").

**Estados:** default / focus / disabled / error / readonly.

**Regra:** data/hora **sempre** entram por **seletor**, nunca por digitação livre de formato. A máscara de texto (`input.masked`, ex.: `dd/mm/aaaa`) é só **fallback** de digitação, não o default.

**Acessibilidade:** seletor navegável por teclado, label associado e anunciado por leitor de tela, **entrada digitada como alternativa** ao gesto de calendário/relógio (ver `accessibility-basics.md`).

**Contrato de valor:** o valor é canônico em **ISO 8601** (data = `AAAA-MM-DD`, hora = `HH:MM`, data+hora em UTC com fuso); o seletor é ajuda de UX, a validação canônica e a persistência com fuso são do servidor (ver `pattern.form` e `stacks/database/database-method.md`). O widget concreto do seletor vive na sub-skill de FE ativa.

---

### `card.<entidade>` (ex. de domínio)

**Componente da stack equivalente:** card/container com tile ou conteúdo custom interno do catálogo da stack ativa (ver sub-skill).

(mesmo formato — descrever; substitua `<entidade>` pelo termo do seu glossário)

---

### `empty-state`

**Anatomia:** ícone leve (não ilustração elaborada), título curto em `title`, instrução em `body` em linguagem simples, CTA primário (`button.primary`).

**Regra:** estado vazio **sempre** instrui o próximo passo, em linguagem que o não-técnico entende. "Sem dados" sozinho é proibido. **Bom (ex. de domínio):** "Nenhum registro por aqui ainda. Publique o primeiro."

---

### `snackbar` (toast)

**Componente da stack equivalente:** o mecanismo de toast/notificação transitória da stack ativa (ver sub-skill).

(descrever variantes success/warning/danger/info)

---

> **Lista inicial mínima a cobrir até EPIC-001** (`card.<entidade>` é exemplo de domínio — substitua pelos termos do glossário; o componente concreto de cada id vive na sub-skill de FE): `button.primary`, `button.secondary`, `button.danger`, `button.text`, `input.text`, `input.masked` (campos com formato: documento/telefone/CEP/dinheiro; data/hora → fallback de `input.datetime`), `input.datetime` (data/hora/data+hora — por seletor), `input.select`, `input.checkbox`, `input.radio`, `input.switch`, `chip`, `segmented`, `card.<entidade-a>`, `card.<entidade-b>`, `list.tile`, `empty-state`, `snackbar`, `bottom-sheet`, `dialog`, `nav.bar` (navegação inferior mobile) + `nav.rail` (navegação lateral tablet/web), `app.bar` (barra de topo), `stepper` (fluxo em estágios), `skeleton`, `badge`.
```

---

## `patterns.md`

```markdown
# Padrões compostos

> Combinações recorrentes de componentes para resolver problemas frequentes. Cada padrão evita reinventar a roda e baixa a carga cognitiva do usuário não-técnico. Os componentes concretos que materializam cada padrão vivem na sub-skill de FE ativa.

## `pattern.form`

Composição: campos verticalmente empilhados num formulário, label flutuante, mensagem de erro associada, CTA primário no rodapé.

**Regras:**

- Form longo (>5 campos) é candidato a `pattern.wizard`.
- Validação inline disparada no blur — não a cada keystroke (a API de validação é da stack ativa — ver sub-skill).
- Mensagem de erro nunca é só cor — sempre texto associado e legível.
- Linguagem do erro **explica o que fazer**: "Use um e-mail com `@` e domínio." > "E-mail inválido".
- Campo com formato conhecido (CPF, CNPJ, PIS/PASEP, telefone, CEP, dinheiro) usa **máscara de entrada** (`input.masked`) — é ajuda de UX, **não** substitui a validação canônica do servidor. Persistir o valor **sem máscara** (canônico, só os dígitos). O mecanismo concreto da máscara vive na sub-skill de FE.
- Campo de **data, hora ou data+hora** usa **seletor** (`input.datetime`), nunca digitação livre — declare granularidade e limites (min/max) no spec. O seletor é ajuda de UX; o valor é **ISO 8601** canônico e a validação/persistência com fuso é do servidor (ver `stacks/database/database-method.md`). Máscara de texto (`dd/mm/aaaa`) só como fallback de digitação. O widget concreto do seletor vive na sub-skill de FE.

(sketch inline)

## `pattern.wizard`

Composição: fluxo em estágios (horizontal em web, vertical em mobile), navegação anterior/próximo, possibilidade de salvar rascunho.

**Regras:**

- Use para fluxos com >5 campos ou decisão em estágios — quebra reduz carga cognitiva.
- Mostre progresso ("Passo 2 de 4") e o que **ainda falta**.
- Permita voltar sem perder dado preenchido.

(sketch inline)

## `pattern.listing`

Composição: filtros (bottom-sheet em mobile, gaveta lateral em tablet/web), lista virtualizada com paginação infinita ou cards, estado vazio, ordenação.

**Regras:**

- Tabela com >5 colunas vira lista de cards no mobile — não scroll horizontal.
- Sempre tem estado vazio próprio.
- Filtros mantêm-se entre navegações (URL/route params em web, restore em mobile).

(sketch inline)

## `pattern.empty`

Composição: `empty-state` padronizado com CTA contextual.

## `pattern.error`

Composição: erro recuperável (toast com ação "Tentar de novo") vs erro de tela (página dedicada com instrução clara + caminho de saída).
```

---

## `voice-and-tone.md`

```markdown
# Voice & Tone

> Como o Quantah fala com o usuário. Detalhe pleno em `docs/skills/designer/references/tone-and-voice.md` — este arquivo é o **resumo aplicável** no dia-a-dia. Os exemplos de microcopy usam vocabulário de domínio genérico marcado como exemplo — substitua pelos termos do seu glossário.

## Tom

- **Direto, simples, respeitoso.** O usuário típico é Colaborador ou Analista B2B — **não-técnico**. Fale como um colega prestativo, não como sistema.
- **Sem entusiasmo performático.** "Tudo certo." > "Uhuu! Foi! 🎉"
- **Sem culpar o usuário.** "Não encontramos esse documento." > "Documento inválido — verifique."
- **Sem jargão técnico em microcopy.** "Não conseguimos salvar agora. Tente novamente em alguns minutos." > "Erro 500 — falha no servidor."
- **Frase curta vence frase elegante.** Usuário lendo no celular em pé não terá paciência para parágrafo.

## Vocabulário

Use o `glossary.md` do PO. Termos do domínio (exemplos genéricos — substitua pelos do seu projeto): `Registro`, `Atividade`, Colaborador, Analista B2B, `Entidade`. **Não rebatize.**

## Padrões de microcopy

| Situação | Padrão | Exemplo (de domínio) |
|---|---|---|
| CTA primário | verbo no infinitivo curto | "Confirmar ação" |
| CTA secundário | verbo no infinitivo, neutro | "Cancelar" |
| Confirmação destrutiva | nomeia o objeto | "Recusar este item?" |
| Sucesso | curto, sem emoji | "Ação confirmada." |
| Erro recuperável | o que aconteceu + o que fazer | "Não foi possível confirmar. Tentar de novo." |
| Vazio | o que falta + como conseguir | "Você ainda não publicou registros. Publicar o primeiro." |
| Loading | preferir skeleton — sem texto | — |
| Placeholder | exemplo, não instrução | `Ex.: 11912345678` (telefone) |
```
