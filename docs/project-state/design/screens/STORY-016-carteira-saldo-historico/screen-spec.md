---
id: SCREEN-STORY-016-carteira-saldo-historico
story: STORY-016-tela-carteira-saldo-historico
epic: EPIC-003-carteira-e-cashback
status: ready
created_at: 2026-07-03
updated_at: 2026-07-03
owner_designer: claude-story016
related_ddrs: []
ds_components_used: [card.feature-dark, card.content, badge.positive, empty-state, skeleton, snackbar, button.primary, nav.bottom, nav.bar]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-016-carteira-saldo-historico/index.html
prototype_last_validated_at: 2026-07-03
---

# Spec de tela — Carteira do Colaborador

> Referência: estória `STORY-016-tela-carteira-saldo-historico` (CAs e contexto vêm de lá — **não duplico**).
> Backend (fonte de dados) já entregue na STORY-015: `carteiras.saldo_centavos` (cache reconciliável, ≥0)
> e `carteira_transacoes` (ledger; crédito `credito_cashback` referencia `cupom_id`). Nada é hardcoded.

## 1. Objetivo da tela

O Colaborador **vê seu saldo em reais crescer** e confere o **histórico** de cupons válidos e os créditos
de cashback correspondentes. É a tela que fecha o loop percebido do incentivo. Uma tarefa: *acompanhar o
que ganhei*. (Saque é a STORY-017 — fora daqui.)

## 2. Fluxo

### Entrada
- Pela **navegação inferior** do app (item "Carteira", `nav.bottom`) — a carteira é uma das seções raiz do
  Colaborador. Rota `/carteira`, atrás de `auth` (mesmo padrão de `/coletar`). Guest → redirect para `/login`.
- Dados chegam como **props do controller** (Inertia server-render): `saldo` e `extrato` já vêm prontos.

### Ações possíveis na tela
- **Ação primária:** nenhuma ação de escrita — é uma tela de leitura. A "ação" é *entender o saldo*.
- **Ação no estado vazio:** CTA **"Capturar cupom"** → `/coletar` (destrava o próximo passo de quem ainda
  não tem crédito).
- **Ação no estado de erro:** **"Tentar de novo"** (recarrega a tela).
- **Navegação:** trocar de seção pela `nav.bottom` (Início / Cupons / Carteira / Perfil).

### Saída
- **Sucesso (preenchido):** permanece na tela; o Colaborador lê saldo + histórico.
- **Vazio:** CTA leva a `/coletar`.
- **Erro recuperável:** "Tentar de novo" refaz a visita.

## 3. Layout

> **Superfície (surface-rhythm do DS):** página **sage** (`bg-canvas-soft`) → cards. O **saldo** usa o card
> de marca `card.feature-dark` (fundo `ink`, número em `primary`) — momento de marca **pontual** (é o único
> na tela). Os itens do histórico usam `card.content` (branco). O verde `primary` é **só** do saldo/CTA;
> o crédito recebido usa a família **`positive`** (`badge.positive`), nunca o verde de CTA.

### Mobile (≥360px) — viewport primário

```
+------------------------------------------+  ← bg-canvas-soft (sage), scroll vertical
|  Carteira                        [wallet] |  h1 display-sm, WalletIcon (mute)
|                                          |
|  +------------------------------------+  |  ← card.feature-dark (ink), raio xl, p-xl
|  |  Seu saldo                         |  |     label body-sm, primary-neutral
|  |                                    |  |
|  |  R$ 12,47                          |  |  ← display-xl/900, cor primary  (screen-carteira-saldo)
|  |                                    |  |
|  |  Cada nota conta.                  |  |     hint body-sm, canvas-soft
|  +------------------------------------+  |
|                                          |
|  Histórico                               |  h2 body-md-strong, ink   (gap-xl acima)
|                                          |
|  +------------------------------------+  |  ← card.content (branco), lista de cards
|  | [receipt] Cupom de R$ 87,90        |  |     título body-md-strong
|  |           15 jan 2026        +R$ 0,09|  |     data body-sm mute · crédito badge.positive
|  +------------------------------------+  |
|  +------------------------------------+  |
|  | [receipt] Cupom de R$ 235,43       |  |
|  |           14 jan 2026        +R$ 0,24|  |
|  +------------------------------------+  |
|              ... (lista rola) ...        |
+------------------------------------------+
|  [Início] [Cupons] (Carteira) [Perfil]   |  ← nav.bottom, item "Carteira" ativo, ≥48px
+------------------------------------------+
```

- Componentes do DS: `card.feature-dark` (saldo), `card.content` (itens), `badge.positive` (crédito),
  `nav.bottom`, `empty-state`/`skeleton`/`snackbar` (estados). Ícones `WalletIcon`, `ReceiptIcon`.
- Conteúdo em coluna única, largura plena com respiro (`px-lg`, `gap-xl` entre blocos; interior de card `p-xl`).
- **Histórico é lista de cards** (`pattern.listing`) — nunca tabela nem scroll horizontal no mobile.
- Alvo de toque ≥48px (itens da nav; CTA do estado vazio).

### Desktop (≥1024px)

```
+------------------------------------------------------------+
| Quantah        Início   Cupons   Carteira   Perfil         |  ← nav.bar (topo), sem bottom-nav no web
+------------------------------------------------------------+
|                                                            |  bg-canvas-soft
|        +----------------------------------------+          |
|        |  Seu saldo                             |          |  ← saldo card, coluna central max-w-2xl
|        |  R$ 12,47                              |          |     (não estica — espaço lateral é respiro)
|        |  Cada nota conta.                      |          |
|        +----------------------------------------+          |
|        Histórico                                           |
|        +----------------------------------------+          |
|        | Cupom de R$ 87,90   15 jan 2026  +R$0,09|          |
|        +----------------------------------------+          |
|        +----------------------------------------+          |
|        | Cupom de R$ 235,43  14 jan 2026  +R$0,24|          |
|        +----------------------------------------+          |
+------------------------------------------------------------+
```

- Mudança relevante vs. mobile: navegação vai para **`nav.bar` no topo** (o DS desaconselha bottom-nav
  persistente em web). Conteúdo **centralizado** em `max-w-2xl` — o saldo e a lista **não esticam**; o espaço
  lateral é respiro, não área a preencher. Mesma hierarquia e microcopy do mobile.

### Tablet (768px)
Sem comportamento novo: herda o mobile (coluna única, bottom-nav) com mais respiro lateral (`max-w-2xl`
centralizado). Não requer layout próprio.

## 4. Estados

> **Observação de domínio importante:** o crédito de cashback só é lançado quando **> 0** (STORY-015 não
> gera transação de crédito zero). Logo **saldo zero ⟺ histórico vazio** — os dois se confundem num único
> estado "vazio/primeira vez". Não existe "saldo positivo com histórico vazio" nem vice-versa.

### 4.1. Caminho feliz (preenchido)
Saldo > 0 no card de marca + lista de cupons/créditos (mais recente no topo). Cada item: valor do cupom,
data e o crédito `+R$ Y,YY` em `badge.positive`. Microcopy na §5.

### 4.2. Loading (primeiro fetch / refresh)
**Skeleton**, nunca spinner em tela vazia: um bloco `skeleton` no lugar do saldo + 3 linhas `skeleton`
no lugar dos itens. Estrutura da tela já visível (título "Carteira").

```
| Carteira                                 |
| +------------------------------------+   |
| | ░░░░░░░░  ░░░░░░░░░░░░░░░░░░        |   |  ← skeleton block (saldo)
| +------------------------------------+   |
| Histórico                                |
| ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   |  ← skeleton line ×3
| ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   |
```

### 4.3. Vazio (sem créditos ainda — primeira vez)
Saldo **R$ 0,00** no card de marca (mostra o zero — o incentivo é ver crescer) **+ `empty-state`** no lugar
do histórico, com CTA que destrava a coleta. Nunca "Sem dados" sozinho.

```
| Carteira                                 |
| +------------------------------------+   |
| |  Seu saldo                         |   |
| |  R$ 0,00                           |   |
| |  Cada nota conta.                  |   |
| +------------------------------------+   |
| +------------------------------------+   |  ← empty-state (card branco)
| |            [receipt]               |   |
| |   Seu saldo vai aparecer aqui      |   |  título display-xs
| |   Envie cupons válidos e ganhe     |   |  body-md
| |   0,1% de cada um em cashback.     |   |
| |        [ Capturar cupom ]          |   |  button.primary → /coletar
| +------------------------------------+   |
```

### 4.4. Erro
Falha ao carregar a carteira (ex.: rede em navegação client-side). Como os dados vêm como **props do
servidor**, o caminho de erro primário é a visita Inertia falhar; a tela mostra um **`snackbar` de erro
recuperável** com ação:
- **Erro de rede / inesperado:** `snackbar` variante `danger`: "Não foi possível carregar sua carteira."
  + ação **"Tentar de novo"** (refaz a visita). Sem stack trace.

### 4.5. Sem permissão
Não autenticado nunca chega a renderizar a tela — a rota `auth` **redireciona para `/login`** antes
(mesmo contrato do `/coletar`). Não há estado visual de "sem permissão" dentro da tela.

### 4.6. Parcial / degradado
Não se aplica: saldo e extrato vêm juntos na mesma prop server-render. Ou a tela carrega, ou cai no 4.4.

### 4.7. Primeira vez vs recorrente
A "primeira vez" é exatamente o estado **4.3 (vazio)**. Recorrente = 4.1. Sem onboarding adicional.

## 5. Microcopy completo

| Lugar | Texto |
|---|---|
| Título da tela | `Carteira` |
| Rótulo do saldo | `Seu saldo` |
| Valor do saldo | `R$ 12,47` (formatado de `saldo_centavos`; sempre `R$ 0,00` quando zero) |
| Hint do saldo | `Cada nota conta.` |
| Título da seção histórico | `Histórico` |
| Item — título | `Cupom de R$ 87,90` (valor do cupom formatado) |
| Item — data | `15 jan 2026` (data de emissão do cupom, curta em pt-BR) |
| Item — crédito | `+R$ 0,09` (crédito do cupom, em `badge.positive`) |
| Estado vazio — título | `Seu saldo vai aparecer aqui` |
| Estado vazio — instrução | `Envie cupons válidos e ganhe 0,1% de cada um em cashback.` |
| Estado vazio — CTA | `Capturar cupom` |
| Erro — mensagem | `Não foi possível carregar sua carteira.` |
| Erro — ação | `Tentar de novo` |
| Nav inferior | `Início` · `Cupons` · `Carteira` · `Perfil` |

Vocabulário e tom: `docs/project-state/design/system/voice-and-tone.md` (App do Colaborador — direto,
simples, recompensador, **sem emoji**; celebra com discrição). Dinheiro sempre `R$ 0,00` (2 casas,
vírgula decimal). Crédito prefixado com `+`.

## 6. Acessibilidade (notas específicas desta tela)

- **Foco inicial** ao abrir: o `<h1>` "Carteira" (ou o container principal) recebe foco lógico; ordem de
  leitura: título → saldo → histórico → nav.
- **Saldo** é texto real (não imagem): leitor de tela lê "Seu saldo, R$ 12,47". O card de marca mantém
  contraste AA (número `primary` `#9fe870` sobre `ink` `#0e0f0c` — alto contraste).
- **Cada item** do histórico é um item de lista semântico (`<ul>/<li>`); o crédito tem rótulo
  compreensível ("mais R$ 0,09 de cashback") — não depender só da cor verde (ícone/《+》+ texto).
- **Estado vazio**: título é heading; CTA é `<button>`/`<a>` real com foco visível, alvo ≥48px.
- **Erro** (`snackbar`): `role="status"`/`aria-live="polite"`; ação "Tentar de novo" é botão focável.
- **Nav inferior**: `<nav aria-label="Seções">`, item ativo com `aria-current="page"`, alvos ≥48px,
  ícone + rótulo textual (não ícone sozinho).
- **Contraste** de todos os tokens usados: herda o piso AA já verificado no EPIC-001/DS.

## 7. Identificadores estáveis sugeridos para teste

| Elemento | Identificador lógico |
|---|---|
| Container da tela | `screen-carteira` |
| Título | `screen-carteira-title` |
| Card de saldo | `screen-carteira-saldo-card` |
| Valor do saldo | `screen-carteira-saldo` |
| Seção histórico | `screen-carteira-historico` |
| Item do histórico | `screen-carteira-item` |
| Crédito do item | `screen-carteira-item-credito` |
| Estado vazio | `screen-carteira-vazio` |
| CTA do estado vazio | `screen-carteira-vazio-cta` |
| Skeleton de carregamento | `screen-carteira-skeleton` |
| Snackbar de erro | `screen-carteira-erro` |
| Ação "Tentar de novo" | `screen-carteira-erro-retry` |
| Navegação inferior | `screen-carteira-nav` |

> Nomes lógicos (contrato do spec); o Programador materializa como `data-testid` na página React.

## 8. Exceções ao Design System

| O que diverge | Por quê | Vai virar DDR? |
|---|---|---|
| Nenhuma | Tela composta 100% de componentes/tokens/padrões existentes do DS | não |

> Uso de `card.feature-dark` para o saldo é uso **pontual** sancionado pelo DS (momento de marca), não
> exceção. A navegação inferior + topo já são padrões do DS (`nav.bottom`/`nav.bar`).

## 9. Protótipo HTML fiel (validação humana)

- **Localização:** `STORY-016-carteira-saldo-historico/index.html` (nesta mesma pasta).
- **Cobertura:** estados `preenchido`, `vazio`, `loading`, `erro` alcançáveis por seletor de chips
  (`?state=`); viewports **mobile** e **desktop** alternáveis por toggle (o layout de navegação muda).
- **Fidelidade:** tokens reais do DS inline (`:root`); microcopy = §5 palavra por palavra; `data-testid`
  da §7 aplicados.
- **Restrições:** HTML/CSS/JS vanilla, sem rede; abre clicando no arquivo. Comentário de topo declara
  "protótipo de validação, não código de produção".
- **Validação humana:** apresentado ao Alexandro; registrar aprovação/ajustes + data em
  `prototype_last_validated_at` e na §11.

### Checklist antes de marcar `ready`
- [ ] `index.html` abre sem erro.
- [ ] Estados preenchido/vazio/loading/erro alcançáveis.
- [ ] Mobile e desktop navegáveis (toggle).
- [ ] Microcopy do protótipo = §5.
- [ ] `data-testid` da §7 presentes.
- [ ] Tokens reais do DS aplicados.
- [ ] Protótipo apresentado ao humano e sinal capturado.

## 10. Dependências e premissas

- **Fonte de dados (STORY-015):** `Carteira` (`saldo_centavos`) e `CarteiraTransacao`
  (`credito_cashback`, `valor_centavos`, `cupom_id`). O histórico junta cada crédito ao seu `Cupom`
  (`valor_total`, `data_emissao`) via `cupom_id` — respeitando a segregação (referência lógica, sem FK dura).
- **Contrato de props (a implementar pelo Programador):** `saldo` (centavos + formatado) e `extrato`
  (lista de `{ cupom_valor, data, credito }`). Formatação de reais no servidor ou na borda de apresentação.
- **Rota** `/carteira` sob `auth`. **Saque fora de escopo** (STORY-017).
- Sem DDR pendente.

## 11. Histórico de mudanças

| Data | Mudança | Quem | Motivo |
|---|---|---|---|
| 2026-07-03 | criação (spec + protótipo v1) | claude-story016 | rabisco→spec detalhado, todos os estados |
| 2026-07-03 | fix loading | claude-story016 | skeleton somia no fundo sage; placeholders com contraste (bloco cinza + cards brancos) |
| 2026-07-03 | validação humana — **aprovado** | Alexandro | protótipo revisado no navegador (mobile/estados); nav incluída nesta estória; loading corrigido → `ready` |
