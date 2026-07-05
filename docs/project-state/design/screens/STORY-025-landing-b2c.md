---
id: SCREEN-STORY-025-landing-b2c
story: STORY-025-landing-b2c
epic: EPIC-005-portas-de-entrada
status: shipped
created_at: 2026-07-05
updated_at: 2026-07-05
owner_designer: designer
related_ddrs: [DDR-004, DDR-005]   # DDR-004: herança da identidade de marca do acesso (EPIC-004); DDR-005: casca pública compartilhada (header/rodapé)
ds_components_used: [hero-band, hero-band-dark, brand.lockup, button.primary, button.secondary, nav.bar, nav.link, content-band, footer]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-025-landing-b2c/index.html
prototype_last_validated_at: 2026-07-05   # validado pelo PO (Alexandro) sobre o index.html — "Aprovado, pode implementar"
---

# Spec de tela — Landing B2C ("Cada nota conta.")

> Referência: estória `STORY-025-landing-b2c` (CAs e contexto vêm de lá — **não duplico**).
> Brief do PO: `epics/EPIC-005-portas-de-entrada/design-handoff.md` (decisões do PO já aplicadas).
> **Estágio: spec detalhado (Passo 3 do fluxo paralelo).** Sync de viabilidade registrado nas Notas do
> agente da estória. Casca pública compartilhada formalizada em **DDR-005** (`pattern.public-shell`,
> accepted 2026-07-05). Protótipo HTML fiel em `STORY-025-landing-b2c/index.html`. `draft → ready` só após
> validação humana do protótipo (registra `prototype_last_validated_at`).

## 1. Objetivo da tela

Fazer um visitante que nunca ouviu falar do Quantah **entender em segundos que dá para transformar as notas
fiscais das suas compras em dinheiro de volta** e **clicar para entrar/cadastrar**. Uma tarefa principal:
converter a visita em início de cadastro/login (Coletador).

## 2. Fluxo

### Entrada
- Tráfego direto/divulgação (rota pública, sem sessão). Primeira tela pública do produto.

### Ações possíveis
- **Primária:** "Entrar / Criar conta" → login/cadastro do Coletador (EPIC-004).
- **Secundária:** "Para empresas" / "Quantah Intelligence" → landing B2B (STORY-026).
- Terciária (rodapé): links institucionais mínimos (política de privacidade, contato) — se existirem.

### Saída
- Após CTA primário → tela de login/cadastro (EPIC-004), com continuidade visual (decisão do PO: herança forte).
- Após CTA B2B → landing B2B (STORY-026). Enquanto 026 não subir, o link aponta para a rota planejada
  (placeholder alinhado com o Programador no sync).

## 3. Layout

### Mobile (≥360px)

```
+----------------------------------+
| [nav.bar]  Quantah      [Entrar] |  <- brand.lockup à esq., link compartilhado
+----------------------------------+
|                                  |
|   hero-band (canvas-soft, sage)  |
|   "Cada nota conta."             |  <- display-xl/xxl, ink
|   subtítulo curto body-lg        |
|                                  |
|   [ Entrar / Criar conta ]       |  <- button.primary (verde), único CTA verde
|   Para empresas →                |  <- button.secondary / nav.link
+----------------------------------+
|  Como funciona (content-band)    |
|   1 ── Escaneie a nota           |  <- 3 passos, ícone leve + título + 1 linha
|   2 ── Acumule cashback          |
|   3 ── Saque o saldo             |
+----------------------------------+
|  [footer] privacidade · contato  |  <- footer compartilhado (mesmo do B2B)
+----------------------------------+
```

- Ação primária acima da dobra. Verde só no CTA primário (regra de ouro dos tokens: `primary` uma vez).
- "Como funciona" em 3 blocos empilhados no mobile.

### Desktop (≥1024px)

```
+----------------------------------------------------------+
| [nav.bar] Quantah          Para empresas   [Entrar]      |
+----------------------------------------------------------+
|  hero (2 colunas)                                        |
|   ESQ: "Cada nota conta."    | DIR: arte/mock leve       |
|   subtítulo + [Entrar] [Para empresas]                   |
+----------------------------------------------------------+
|  Como funciona — 3 passos lado a lado (content-band)     |
+----------------------------------------------------------+
|  [footer]                                                |
+----------------------------------------------------------+
```

- Não é "mobile esticado": hero ganha 2 colunas, os 3 passos ficam lado a lado. Considerar `hero-band-dark`
  para um bloco de reforço de marca (headline verde sobre `ink`), a validar no protótipo.

## 4. Estados (detalhados)

Landing estática (sem fetch de dados) — os estados são de composição e interação, todos no protótipo:

- **Padrão — mobile (≥360px):** casca + hero empilhado (headline → subtítulo → CTAs full-width) → "Como
  funciona" (3 passos empilhados) → band de fecho → footer. `?vp=mobile`.
- **Padrão — desktop (≥1024px):** hero em 2 colunas (copy+CTAs à esquerda; painel de marca
  `card.feature-dark` à direita, "momento de marca" com o glifo de nota + "+ R$ 0,03 creditados"); 3
  passos lado a lado; footer em linha. `?vp=desktop`. Não é "mobile esticado".
- **Interação dos CTAs:** `button.primary` — hover `primary-active`, active `primary-neutral`, foco
  anel `ink` (offset sobre sage). `?state=focus` demonstra o foco no CTA "Entrar / Criar conta".
  `nav.link`/links de rodapé com foco visível próprio.
- **Tablet (768–1023px):** hero 1 coluna (painel de marca oculto); 3 passos empilham (comportamento do
  mobile) — sem estado próprio relevante (degrada do desktop).
- **CTA-B2B:** destino **vivo** — `/intelligence` já responde 200 (`Intelligence/Reservado.jsx`, a 026 o
  substitui). Não há placeholder desabilitado; o link navega normalmente.
- Sem loading/erro/vazio — não há dado carregado nesta tela.

## 5. Microcopy (completo — pt-BR, voz B2C: próximo/encorajador)

| Local | Texto |
|---|---|
| Header — link B2B | **Para empresas** |
| Header — CTA entrar | **Entrar** |
| Hero — headline | **Cada nota conta.** *(tagline fixa; via i18n `t('Every receipt counts.')`)* |
| Hero — subtítulo | **Transforme as notas fiscais das suas compras em dinheiro de volta. Escaneie o cupom, junte cashback e saque quando quiser.** |
| Hero — CTA primário | **Entrar / Criar conta** |
| Hero — CTA secundário | **Para empresas →** |
| Painel de marca (desktop) | **Cada nota vira saldo.** · mock: **+ R$ 0,03** / **creditados nesta nota** |
| Seção | **Como funciona** |
| Passo 1 | **Escaneie a nota** — Aponte a câmera para o QR Code do cupom fiscal da sua compra. |
| Passo 2 | **Acumule cashback** — Cada nota validada vira saldo em reais na sua carteira. |
| Passo 3 | **Saque o saldo** — Quando quiser, saque por PIX direto para a sua conta. |
| Band de fecho | **Cada nota conta.** / Comece agora a transformar seus cupons em cashback. / CTA **Entrar / Criar conta** |
| Footer | wordmark **Quantah** · **Para empresas** · **Privacidade** · **Contato** · **© 2026 Quantah · Cada nota conta.** |

> Sem emoji, sem entusiasmo performático (`voice-and-tone.md`). Termos do glossário: "cupom/nota",
> "cashback", "saldo", "saque/PIX". "Quantah" é nome próprio (não traduzido).

## 6. Acessibilidade (AA)

- **Contraste:** `ink #0e0f0c` sobre `canvas-soft #e8ebe6` e `canvas`; `on-primary #0e0f0c` sobre
  `primary #9fe870` (CTA — nunca texto branco sobre verde); `primary` sobre `ink` no band de fecho (headline
  grande, ≥3:1); `canvas-soft` sobre `ink` no footer. Verde só como accent de CTA, nunca como texto de corpo.
- **Alvos de toque:** `button.primary` ≥48px; `nav.link`/links de rodapé ≥44px de altura de alvo.
- **Foco:** anel visível em todos os CTAs e links (teclado). Ordem de tab natural (header → hero →
  passos → fecho → footer).
- **Semântica:** `<nav aria-label>` (barra e rodapé), `<main>` no conteúdo, `<section aria-labelledby>`
  por bloco, um único `<h1>` ("Cada nota conta."), `<h2>` "Como funciona". Números dos passos e glifos são
  `aria-hidden` (decorativos); o nome acessível vem do texto ao lado.
- **Sem texto só-cor:** os passos têm rótulo textual; o número é reforço visual.

## 7. Identificadores estáveis (âncoras de E2E — CA-6)

| Elemento | `data-testid` |
|---|---|
| Casca — nav | `public-nav` |
| Casca — footer | `public-footer` |
| Header — CTA entrar | `landing-b2c-nav-entrar` |
| Header — link B2B | `landing-b2c-nav-b2b` |
| Hero (bloco) | `landing-b2c-hero` |
| **CTA primário de entrada (canônico)** | `landing-b2c-cta-entrar` |
| CTA para B2B (canônico) | `landing-b2c-cta-b2b` |
| CTA entrar no band de fecho | `landing-b2c-cta-entrar-fim` |
| Seção "Como funciona" | `landing-b2c-como-funciona` |
| Rodapé — link B2B | `landing-b2c-foot-b2b` |

> O E2E do CA-6 ancora em `landing-b2c-cta-entrar` (hero) para ir da landing ao login; a proposta em
> pt-BR é verificada por `landing-b2c-hero` ("Cada nota conta.").

## 8. Exceções ao Design System

Nenhuma. A casca pública é composição de componentes do DS formalizada em **DDR-005**
(`pattern.public-shell`) — não é exceção nem componente novo. Os ids de band (`hero-band`,
`hero-band-dark`, `content-band`) já são do DS (`components.md`); a materialização em React é implementação.

## 9. Protótipo HTML fiel

`STORY-025-landing-b2c/index.html` — HTML/CSS/JS vanilla, zero build, abre clicando. Tokens reais do DS.
Cobre mobile (`?vp=mobile`) e desktop (`?vp=desktop`) e o estado de foco do CTA (`?state=focus`). Sem
rede/backend; CTAs não navegam (protótipo). Apresentado ao PO para validação antes de `ready`.

## 10. Dependências e premissas

- Rota de login do EPIC-004 (destino do CTA primário) — existe (`route('login')`, `done`).
- Landing B2B (STORY-026) — destino do CTA secundário; `/intelligence` já é rota pública viva
  (`Intelligence/Reservado.jsx`); a 026 substitui a página nesse namespace.
- Casca compartilhada com a STORY-026 via `PublicLayout` (DDR-005) — fonte única, sem duplicar.

## 11. Histórico de mudanças

| Data | Mudança | Quem | Motivo |
|---|---|---|---|
| 2026-07-05 | criação (rabisco) | designer | rabisco inicial pós-decisões do PO; aguarda sync com Programador |
| 2026-07-05 | spec detalhado + protótipo (Passo 3) | designer | pós-sync; DDR-005 accepted; microcopy/estados/a11y/ids completos e protótipo HTML fiel |
