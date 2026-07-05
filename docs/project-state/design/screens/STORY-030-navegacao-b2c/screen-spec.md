---
id: SCREEN-STORY-030-navegacao-b2c
story: STORY-030
epic: EPIC-006-jornada-b2c
status: shipped              # implementada e mergeada na main (STORY-030); protótipo validado 2026-07-05
created_at: 2026-07-05
updated_at: 2026-07-05
owner_designer: claude-story030
related_ddrs: [DDR-007]      # padrão da casca de navegação da área logada (proposto nesta estória)
ds_components_used: [nav.bottom, nav.bar, nav.link, card.content, button.primary, brand.mark]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-030-navegacao-b2c/index.html
prototype_last_validated_at: 2026-07-05
---

# Spec de tela — Navegação coesa da área B2C

> Referência: estória `STORY-030-navegacao-coesa-b2c` (CAs e contexto vêm de lá — **não duplico**).
> Esta spec descreve um **padrão de navegação** (a casca da área logada), não uma tela isolada — o padrão
> se aplica a **todas** as telas logadas (home, coleta, carteira/extrato, saque, perfil). O padrão é
> formalizado no **DDR-007** (`pattern.app-shell`). As telas de destino já existem (STORY-009/016/017); aqui
> desenho **como se navega entre elas** e **como se volta à home**.

## 1. Objetivo da tela

Fazer a jornada logada "andar": ligar a home-hub às telas da Onda 1 (extrato/histórico, saque, coleta) com
**retorno consistente** à home e **sem página genérica** — de modo que coleta e saldo/histórico fiquem a
**≤ 2 toques** da home. Uma tarefa: *mover-se entre as seções sem se perder*.

## 2. Fluxo

### Entrada
- O Coletador autenticado está em qualquer tela logada (home, coleta, carteira, saque, perfil). A **casca
  de navegação** (`AppLayout`, DDR-007) está presente em todas elas.

### Ações possíveis
- **Navegar entre seções raiz** pela barra: **Início** (home-hub) · **Cupons** (coleta, `/coletar`) ·
  **Carteira** (extrato/saldo, `/carteira`) · **Perfil** (`/profile`). Item da seção atual = **ativo**.
- **Atalhos rápidos na home** (abaixo do saldo/CTA): **Histórico** → extrato (`/carteira`) e **Prêmios** →
  solicitar saque (`/carteira/saque`) — 1 toque cada.
- **Voltar à home** de qualquer tela: item **Início** da barra (sempre presente).

### Saída
- Cada ação de navegação leva à seção/tela correspondente, mantendo a barra (sem beco sem saída).
- "Início" retorna à home-hub de qualquer ponto.

## 3. Layout

> **Casca (DDR-007):** `nav.bottom` no mobile (barra inferior persistente) e `nav.bar` no desktop (barra
> superior). 4 seções raiz; item ativo com indicador `primary` + `aria-current`. Superfície segue o
> `pattern.surface-rhythm` (sage → cards). Marca Quantah na barra (desktop) / topo (mobile) — **sem** logo
> do Laravel em nenhuma rota logada.

### Mobile (≥360px) — viewport primário

Home-hub com os atalhos rápidos + barra inferior:

```
+------------------------------------------+  ← bg-canvas-soft
|  Olá, Ana                        [marca] |
|  +------------------------------------+  |  card.feature-dark (saldo)
|  |  Seu saldo   R$ 12,47              |  |
|  +------------------------------------+  |
|  [        +  Coletar cupom          ]   |  button.primary (STORY-029)
|                                          |
|  +----------------+ +----------------+   |  ← atalhos rápidos (card.content), ≥48px
|  | [receipt]      | | [wallet]       |   |
|  | Histórico      | | Prêmios        |   |
|  +----------------+ +----------------+   |
|         (screen-home-atalho-*)           |
+------------------------------------------+
|  (Início)  Cupons  Carteira  Perfil      |  ← nav.bottom persistente, "Início" ativo, ≥48px
+------------------------------------------+
```

Barra inferior nas outras seções (item ativo muda) — ex.: Carteira ativa:

```
|  Início  Cupons  (Carteira)  Perfil      |  ← extrato aberto; "Início" volta à home
```

- Componentes: `nav.bottom` (4 itens: `HomeIcon`/`ReceiptIcon`/`WalletIcon`/`UserIcon` + rótulo),
  `card.content` (atalhos com ícone + rótulo), `brand.mark` (topo da home). Alvos ≥48px.
- **Retorno:** a barra é persistente em todas as telas logadas — coleta e saque, hoje sem nav, passam a
  tê-la; "Início" é o retorno consistente (CA-3).

### Desktop (≥1024px)

```
+-------------------------------------------------------------+
| Quantah     Início   Cupons   Carteira   Perfil             |  ← nav.bar (topo), sem barra inferior
+-------------------------------------------------------------+
|                                                             |  bg-canvas-soft
|     (conteúdo da seção atual, centralizado max-w-2xl)       |
+-------------------------------------------------------------+
```

- Mudança relevante vs. mobile: a navegação vai para a **`nav.bar` no topo** (o DS desaconselha
  bottom-nav em web); item ativo com indicador `primary`. Os atalhos da home aparecem como cards na
  coluna central (mesma hierarquia do mobile).

### Tablet (768px)
Herda o mobile (barra inferior) com mais respiro lateral. Sem layout próprio.

## 4. Estados

> O "estado" relevante aqui é **qual seção está ativa** e a **presença consistente da casca** — não há
> loading/erro próprios da navegação (as telas de destino têm os seus).

### 4.1. Seção ativa (por tela)
Cada tela logada marca seu item na barra como ativo (`aria-current="page"`, indicador `primary`): home →
Início; coleta → Cupons; carteira/extrato → Carteira; saque → Carteira (sub-tela de Carteira, item
"Carteira" ativo); perfil → Perfil.

### 4.2. Atalhos da home
Sempre visíveis na home (navegação, não ação): **Histórico** e **Prêmios**. Independem do saldo (a tela de
saque aplica a regra de valor mínimo). 1 toque cada.

### 4.3. Retorno consistente (sem beco)
Toda tela logada tem a barra → "Início" volta à home. Não há tela logada sem saída.

### 4.4. Sem scaffolding (CA-4)
Nenhuma rota logada renderiza o `AuthenticatedLayout`/`ApplicationLogo` do Breeze (logo do Laravel) nem o
menu genérico. O Perfil passa a usar a casca (marca Quantah).

### 4.5. Sem permissão
Visitante anônimo em rota logada → redirect `/login` (guarda do EPIC-004). A casca só existe autenticado.

## 5. Microcopy completo

| Lugar | Texto |
|---|---|
| Nav — seção 1 | `Início` |
| Nav — seção 2 | `Cupons` |
| Nav — seção 3 | `Carteira` |
| Nav — seção 4 | `Perfil` |
| Atalho da home — histórico/extrato | `Histórico` |
| Atalho da home — prêmios/saque | `Prêmios` |

Vocabulário e tom: `voice-and-tone.md` (App do Colaborador — direto, simples, sem emoji). Rótulos via i18n
(`t()`, IDR-010): `Home`→"Início", `Coupons`→"Cupons", `Wallet`→"Carteira", `Profile`→"Perfil",
`History`→"Histórico", `Rewards`→"Prêmios".

> **Decisão de microcopy — atalho de saque:** o PO escolheu **"Prêmios"** (enquadra como área de
> recompensas; a estória fala em "prêmios/saque"). Destino real: `/carteira/saque` (tela de solicitar saque).

## 6. Acessibilidade (notas específicas)

- **Barra:** `<nav aria-label="Seções">`; item ativo com `aria-current="page"`; ícone **+ rótulo textual**
  (nunca ícone sozinho); alvos ≥48px; foco por teclado visível (vem dos componentes DS).
- **Atalhos da home:** `<a>` reais (não `div` clicável) com rótulo textual; ícone decorativo
  (`aria-hidden`); alvo ≥48px.
- **Ordem de foco:** conteúdo da tela → barra (a barra vem por último na ordem de leitura no mobile).
- **Contraste:** item ativo (`ink` sobre `canvas`, ícone `primary`) e inativo (`body`) ≥ 4.5:1 (AA).
- **Sem depender de cor:** o item ativo tem `aria-current` além do indicador verde.

## 7. Identificadores estáveis sugeridos para teste

| Elemento | Identificador lógico |
|---|---|
| Barra de navegação | `app-nav` |
| Item Início | `app-nav-inicio` |
| Item Cupons | `app-nav-cupons` |
| Item Carteira | `app-nav-carteira` |
| Item Perfil | `app-nav-perfil` |
| Atalho Histórico (home) | `screen-home-atalho-historico` |
| Atalho Prêmios (home) | `screen-home-atalho-premios` |

> Nomes lógicos (contrato do spec); o Programador materializa como `data-testid`. Os testids das telas de
> destino (STORY-009/016/017) já existem e não mudam.

## 8. Exceções ao Design System

| O que diverge | Por quê | Vai virar DDR? |
|---|---|---|
| Padrão de navegação da área logada inexistente em `patterns.md` | É um padrão transversal durável | **sim — DDR-007** |
| Rótulo do atalho de saque = "Prêmios" (decidido pelo PO) | Enquadra como recompensas; destino /carteira/saque | não (microcopy; decidido) |

> Nenhum componente/primitivo novo — a casca compõe `nav.bottom`/`nav.bar`/`nav.link`/`card.content` do DS.

## 9. Protótipo HTML fiel (validação humana)

- **Localização:** `STORY-030-navegacao-b2c/index.html`.
- **Cobertura:** navegar entre as 4 seções (Início/Cupons/Carteira/Perfil) vendo o item ativo mudar; os
  atalhos da home (Histórico/Prêmios) levando a Carteira/Saque; o retorno à home por "Início" de qualquer
  seção; viewports **mobile** (barra inferior) e **desktop** (barra superior) alternáveis.
- **Fidelidade:** tokens reais do DS inline; microcopy = §5; `data-testid` da §7 aplicados.
- **Restrições:** HTML/CSS/JS vanilla, sem rede; abre clicando. Comentário de topo declara "protótipo de
  validação, não código de produção".

### Checklist antes de marcar `ready`
- [ ] `index.html` abre sem erro.
- [ ] Navegação entre as 4 seções com item ativo correto.
- [ ] Atalhos da home levam a extrato/saque; "Início" volta à home de qualquer seção.
- [ ] Mobile (barra inferior) e desktop (barra superior) navegáveis.
- [ ] Microcopy = §5; `data-testid` da §7 presentes.
- [ ] Tokens reais do DS; sem logo do Laravel.
- [ ] Apresentado ao humano e sinal capturado.

## 10. Dependências e premissas

- **Depende de:** STORY-029 (home-hub) `done`; **DDR-007** aprovado (padrão da casca) antes de codar UI.
- **Telas de destino** já entregues: `/coletar` (STORY-009), `/carteira` (STORY-016), `/carteira/saque`
  (STORY-017). Nenhuma regra nova de coleta/carteira/saque.
- **Rotas** sob `auth` (EPIC-004). O home-hub vive em `/dashboard` (IDR-011); a STORY-030 pode renomear
  para `/inicio` ao criar a casca (decisão de implementação — atualizar IDR-011 se o fizer).

## 11. Histórico de mudanças

| Data | Mudança | Quem | Motivo |
|---|---|---|---|
| 2026-07-05 | criação (spec + protótipo v1) | claude-story030 | rabisco → spec do padrão de navegação; DDR-007 proposto |
| 2026-07-05 | validação humana — **aprovado** | Alexandro | DDR-007 aceito; atalho de saque = "Prêmios"; URL renomeada p/ /inicio → `ready` |
| 2026-07-05 | implementada (casca AppLayout) + deploy homolog | claude-story030 | 5 telas na casca; scaffolding Breeze removido → `shipped` |
