# Padrões compostos — Design System Quantah

> Combinações recorrentes de componentes. Reduzem a carga cognitiva do usuário não-técnico e
> evitam reinventar a roda. Materializados na stack via `stacks/inertia-react/SKILL.md`.

## `pattern.form`

Campos empilhados verticalmente, label flutuante, erro associado ao campo, `button.primary` no
rodapé.

- Form >5 campos → considerar `pattern.wizard`.
- Validação inline no **blur**, não a cada tecla (a validação canônica é do servidor via
  `useForm`; cliente só faz validação leve de UX).
- Erro nunca é só cor — texto associado, e a mensagem **diz o que fazer**: "Use um e-mail com
  @ e domínio" > "E-mail inválido".
- Campo de formato conhecido usa `input.masked`; data/hora usa `input.datetime` (seletor).

## `pattern.wizard`

Fluxo em estágios. Usar o `stepper`. Mostra progresso ("Passo 2 de 3") e permite voltar sem
perder dado. **Chave para o fluxo de Coleta** (escanear → confirmar → creditar) — cada passo é
uma decisão clara.

## `pattern.listing`

Filtros (bottom-sheet em mobile, gaveta lateral em web), lista virtualizada/paginada com cards,
estado vazio próprio, ordenação. Tabela >5 colunas vira lista de cards no mobile (nunca scroll
horizontal). Ex.: histórico de cupons enviados.

## `pattern.empty`

`empty-state` padronizado com CTA contextual. Cada listagem tem o seu.

## `pattern.error`

Erro recuperável → `snackbar` com ação "Tentar de novo". Erro de tela → página dedicada com
instrução clara e caminho de saída. Em SPA (Inertia), gerenciar foco explicitamente ao trocar
conteúdo.

## `pattern.auth` (telas de acesso)

> Introduzido pelo **DDR-004** (accepted 2026-07-04). Vale para todas as telas de autenticação do
> Coletador (entrar, criar conta, redefinir/nova senha, confirmar e-mail) — EPIC-004.

- **Mobile (≥360px):** `brand.lockup` + tagline sobre o sage (`canvas-soft`) → um `card.content` branco
  com o formulário. CTA primário verde full-width, ≥52px.
- **Desktop (≥1024px):** split 50/50 — painel de marca escuro à esquerda (`card.feature-dark` mood: `ink`
  + headline `primary`) e o **mesmo** card do mobile à direita (largura máx ~380px, sem esticar). O card
  vem **antes** do painel na ordem de leitura assistiva.
- **Ordem do card (quando há login social):** `brand.google-btn` (neutro) → divisor "ou" → campos
  e-mail/senha (`pattern.form`) → **um** `button.primary` verde. Regra de ouro do DS preservada: o social
  nunca é verde; o verde é o CTA de e-mail/senha. Sem social ativo, o botão e o divisor colapsam.
- **Erro de credencial:** callout global acima do form (`role="alert"`), genérico, **sem vazar o campo**.
  Erros de campo (e-mail em uso, senhas diferentes) ficam associados ao campo (`pattern.form`).
- **Loading:** no submit (spinner inline no `button.primary` + `aria-busy`), não skeleton — telas de
  acesso não têm fetch inicial.

## `pattern.lead-confirmacao` (confirmação pós-envio de lead)

> Introduzido pelo **DDR-006** (accepted 2026-07-05). Confirmação de captação de lead B2B (EPIC-005) e
> futuras capturas. **Tela dedicada** (não `snackbar`), servida por **PRG** (POST → redirect → GET).

- **Estrutura:** dentro da casca pública (`pattern.public-shell`, face `b2b`) → `content-band` com
  confirmação positiva **sóbria** (marca/`badge.positive` — usa `positive`, **não** o verde de CTA) +
  headline de recebimento + **próximo passo** ("entraremos em contato") + `CtaLink` de saída (neutro).
- **PRG:** o POST que cria o lead **redireciona** para a rota dedicada de agradecimento; refresh e "voltar"
  não reenviam (sem lead duplicado por F5).
- **Idempotência sem vazamento (LGPD):** e-mail novo e e-mail já cadastrado terminam na **mesma** tela —
  a confirmação nunca revela se o contato já existia.
- **A11y:** ao montar, mover o foco para o `<h1>` da confirmação (`tabindex="-1"` + `focus()`); tom B2B
  sério, sem emoji.
- **Não** reusar `empty-state` (semântica "sem dados + criar", diferente de "recebido com sucesso").

## `pattern.public-shell` (casca das superfícies públicas)

> Introduzido pelo **DDR-005** (accepted 2026-07-05). Vale para as landings públicas do EPIC-005
> (B2C e B2B — Quantah Intelligence) e futuras páginas públicas. Materializado como
> `Layouts/PublicLayout.jsx` (par de `GuestLayout`/`AuthenticatedLayout`); só compõe componentes DS.

- **Estrutura:** `nav.bar` (sticky) + `<main>` (conteúdo da landing) + `footer` (band `ink`). Nenhum
  primitivo novo — compõe `nav.bar`, `nav.link`, `footer`, `brand.lockup`.
- **Header contextual por face** (prop `face`):
  - **B2C:** `brand.lockup` → `nav.link` "Para empresas" (→ B2B) + **um** `button.primary` verde "Entrar"
    (→ login do Coletador). Único CTA verde do contexto.
  - **B2B (Quantah Intelligence):** `brand.lockup` → `nav.link` "Voltar ao app" (→ B2C). **Sem** CTA de
    login (não há conta B2B nesta onda — PDR-003); o verde da face B2B é o CTA do formulário de lead.
- **Footer comum:** wordmark + alternância entre as faces + institucional mínimo (privacidade, contato).
  Sem inflar — só navegação entre faces e links essenciais (simplicidade radical).
- **Regra de ouro preservada:** no máximo **um** `button.primary` verde por contexto; a alternância entre
  faces é `nav.link` (neutro), nunca verde.
- **A11y:** `<nav aria-label>` na barra, `<main>` no conteúdo, `<footer>`; foco visível e alvos ≥48px já
  vêm dos componentes DS. Em ≥360px os links rolam **dentro** da barra (o `nav.bar` nunca estoura a
  largura da página).

## `pattern.surface-rhythm` (assinatura visual)

O ritmo de superfície da marca: **página sage (`canvas-soft`) → cards brancos (`canvas`)**. O
contraste carrega a elevação; evita sombra pesada. Momentos de marca usam `card.feature-dark` /
`hero-band-dark` (ink + verde) com parcimônia — é o "tempero", não o prato.
