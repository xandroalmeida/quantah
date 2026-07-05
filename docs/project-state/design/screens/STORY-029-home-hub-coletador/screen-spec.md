---
id: SCREEN-STORY-029-home-hub-coletador
story: STORY-029
epic: EPIC-006-jornada-b2c
status: shipped              # implementada e mergeada na main (STORY-029, PR #2); protótipo validado 2026-07-05
created_at: 2026-07-05
updated_at: 2026-07-05
owner_designer: claude-story029
related_ddrs: [DDR-004]      # brand.mark/brand.lockup (identidade que substitui o scaffolding)
ds_components_used: [card.feature-dark, button.primary, card.content, empty-state, nav.bottom, nav.bar, brand.mark]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-029-home-hub-coletador/index.html
prototype_last_validated_at: 2026-07-05
---

# Spec de tela — Home-hub do Coletador

> Referência: estória `STORY-029-home-hub-coletador` (CAs e contexto vêm de lá — **não duplico**).
> Fonte de dados já entregue: `carteiras.saldo_centavos` (EPIC-003, cache reconciliável ≥0), lido via
> `App\Domain\Cashback\ExtratoCarteira::para($user)` — o mesmo read-model de `/carteira`. Nada hardcoded.
> A **navegação coesa** (atalhos extrato/saque, ≤2 toques, retorno consistente) é a **STORY-030** — aqui
> o home-hub é o **destino pós-login** com **saldo + CTA de coleta**, servido dentro do shell já existente.

## 1. Objetivo da tela

Ser o **destino pós-login** da área B2C: numa olhada, o Coletador vê **o que ganhou** (saldo da carteira) e
**o que faz agora** (CTA primário "Coletar cupom"). Uma tarefa: *entrar e começar/continuar a coletar*.
Substitui a página genérica (dashboard scaffolding do EPIC-004) como centro da jornada.

## 2. Fluxo

### Entrada
- **Pós-login (Google ou e-mail/senha, EPIC-004):** ao concluir o login o Coletador é levado direto ao
  home-hub (CA-1) — não à página genérica. Rota logada (ex.: `home` / `/inicio`), atrás de `auth`.
- **Pela navegação:** item "Início" da `nav.bottom` (mobile) / `nav.bar` (desktop) — o home-hub é a seção
  raiz do Coletador.
- **Dados:** `saldo` chega como **prop do controller** (Inertia server-render), já formatado; `nome` vem de
  `auth.user` (prop compartilhada). Guest → **redirect para `/login`** antes de renderizar (CA-5).

### Ações possíveis na tela
- **Ação primária:** **Coletar cupom** → fluxo de captura existente `/coletar` (EPIC-002) — CA-3.
- **Navegação:** trocar de seção pela nav (Início / Cupons / Carteira / Perfil). Atalhos dedicados a
  extrato/saque são da STORY-030.

### Saída
- **Coletar cupom:** navega para `/coletar`.
- **Sem ação:** permanece no home-hub (é o centro / ponto de retorno da jornada).

## 3. Layout

> **Superfície (`pattern.surface-rhythm`):** página **sage** (`canvas-soft`) → cards. O **saldo** usa o card
> de marca `card.feature-dark` (fundo `ink`, número em `primary`) — **único** momento de marca da tela. O
> verde `primary` é **só** do CTA "Coletar cupom" (único accent de CTA, PDR-001) e do número do saldo sobre
> o card ink; nunca verde sobre verde. Identidade no topo via `brand.mark` (nota-fiscal), não logo Laravel.

### Mobile (≥360px) — viewport primário

Estado recorrente (saldo positivo):

```
+------------------------------------------+  ← bg-canvas-soft (sage)
|  Olá, Ana                        [mark]  |  h1 display-sm · brand.mark 32px à direita
|                                          |
|  +------------------------------------+  |  ← card.feature-dark (ink), raio xl, p-xl
|  |  Seu saldo                         |  |     label body-sm, primary-neutral
|  |  R$ 12,47                          |  |  ← display-xl/900, primary   (screen-home-saldo)
|  |  Cada nota conta.                  |  |     hint body-sm, canvas-soft
|  +------------------------------------+  |
|                                          |
|  +------------------------------------+  |  ← button.primary full-width, ≥52px
|  |        +  Coletar cupom            |  |     (screen-home-cta) → /coletar
|  +------------------------------------+  |
|  Registre mais uma nota e aumente seu    |  body-sm, body, centralizado
|  cashback.                               |
|                                          |
+------------------------------------------+
|  (Início) [Cupons] [Carteira] [Perfil]   |  ← nav.bottom, "Início" ativo, ≥48px
+------------------------------------------+
```

Estado primeiro acesso (saldo zero) — o CTA vem embrulhado num bloco acolhedor:

```
|  Olá, Ana                        [mark]  |
|  +------------------------------------+  |  ← card.feature-dark
|  |  Seu saldo                         |  |
|  |  R$ 0,00                           |  |     mostra o zero (incentivo: ver crescer)
|  |  Cada nota conta.                  |  |
|  +------------------------------------+  |
|  +------------------------------------+  |  ← card.content (bloco boas-vindas)
|  |            [receipt]               |  |
|  |    Comece a ganhar cashback        |  |  display-xs
|  |    Registre uma nota fiscal e      |  |  body-md
|  |    ganhe 0,1% de cada compra de    |  |
|  |    volta.                          |  |
|  |     [   +  Coletar cupom   ]       |  |  button.primary → /coletar
|  +------------------------------------+  |
|  (Início) [Cupons] [Carteira] [Perfil]   |
```

- Componentes do DS: `card.feature-dark` (saldo), `button.primary` (CTA), `card.content`+`empty-state`
  (bloco de boas-vindas do estado zero), `nav.bottom`, `brand.mark`. Ícone `ReceiptIcon`, `PlusIcon`.
- Coluna única, largura plena com respiro (`px-lg`, `gap-xl` entre blocos; interior de card `p-xl`).
- Alvo de toque ≥48px (CTA ≥52px; itens da nav ≥48px).

### Desktop (≥1024px)

```
+------------------------------------------------------------+
| Quantah        Início   Cupons   Carteira   Perfil         |  ← nav.bar (topo), sem bottom-nav
+------------------------------------------------------------+
|                                                            |  bg-canvas-soft
|        Olá, Ana                                            |  h1, coluna central max-w-2xl
|        +----------------------------------------+          |
|        |  Seu saldo                             |          |  ← saldo card (não estica)
|        |  R$ 12,47                              |          |
|        |  Cada nota conta.                      |          |
|        +----------------------------------------+          |
|        [        +  Coletar cupom          ]               |  button.primary
|        Registre mais uma nota e aumente seu cashback.      |
+------------------------------------------------------------+
```

- Mudança relevante vs. mobile: navegação vai para **`nav.bar` no topo** (o DS desaconselha bottom-nav em
  web). Conteúdo **centralizado** em `max-w-2xl` — saldo e CTA **não esticam**; o espaço lateral é respiro.
  Mesma hierarquia e microcopy do mobile.

### Tablet (768px)
Sem comportamento novo: herda o mobile (coluna única, bottom-nav) com mais respiro lateral. Não requer
layout próprio.

## 4. Estados

> O saldo é **sempre** mostrado (mesmo zero). A variação visível principal é **zero (primeiro acesso)** ×
> **positivo (recorrente)**. Ambos são caminho feliz.

### 4.1. Caminho feliz — saldo positivo (recorrente)
Saldo > 0 no card de marca + CTA "Coletar cupom" em destaque + linha de apoio curta. Microcopy na §5.

### 4.2. Loading (primeiro fetch / refresh)
**Skeleton** de referência (bloco no lugar do saldo + botão), nunca spinner em tela vazia.

> **Nota de implementação (Programador, 2026-07-05):** a página é **server-rendered** (Inertia entrega
> `saldo`/`nome` junto com o HTML), então **não há loading client-side a materializar** — o "loading" real é
> a barra de progresso de navegação do Inertia e o "erro" é a página de erro do framework. O skeleton deste
> protótipo fica como referência/fallback (idem STORY-016). Desvio consciente a registrar nas Notas do agente.

```
| Olá, ...                                 |
| +------------------------------------+   |
| | ░░░░░░░░  ░░░░░░░░░░░░░░░░          |   |  ← skeleton (saldo)
| +------------------------------------+   |
| ░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░░   |  ← skeleton (CTA)
```

### 4.3. Vazio / primeira vez — saldo zero
Saldo **R$ 0,00** no card de marca **+ bloco acolhedor** (`card.content` no estilo `empty-state`) que
instrui e leva à coleta. Nunca "Sem dados" sozinho — sempre instrui o próximo passo. Serve tanto para o
primeiro acesso quanto para quem zerou o saldo após saque (copy não afirma "primeira nota da vida").

### 4.4. Erro
Como `saldo`/`nome` vêm como **props do servidor**, o caminho de erro primário é a **visita Inertia falhar**
→ página de erro do framework. Referência de design: `snackbar` `danger` "Não foi possível carregar sua
página." + ação "Tentar de novo" (refaz a visita). Sem stack trace. (A produção server-render não cabla o
snackbar — teatro para dado que sempre chega com a página; mantido como referência/fallback.)

### 4.5. Sem permissão
Não autenticado nunca renderiza a tela — a rota `auth` **redireciona para `/login`** antes (CA-5, guarda do
EPIC-004). Não há estado visual de "sem permissão" dentro da tela.

### 4.6. Parcial / degradado
Não se aplica: `saldo` e `nome` vêm juntos na mesma resposta server-render. Ou a tela carrega, ou cai no 4.4.

### 4.7. Primeira vez vs recorrente
A "primeira vez" é o estado **4.3 (saldo zero)**, com o bloco acolhedor. Recorrente = **4.1**. Sem
onboarding adicional (simplicidade radical).

## 5. Microcopy completo

| Lugar | Texto |
|---|---|
| Saudação (h1) | `Olá, {primeiro nome}` (ex.: `Olá, Ana`) · sem nome disponível: `Olá` |
| Rótulo do saldo | `Seu saldo` |
| Valor do saldo | `R$ 12,47` (de `saldo_centavos`; sempre `R$ 0,00` quando zero) |
| Hint do saldo | `Cada nota conta.` |
| CTA primário | `Coletar cupom` |
| Linha de apoio (estado positivo) | `Registre mais uma nota e aumente seu cashback.` |
| Boas-vindas — título (estado zero) | `Comece a ganhar cashback` |
| Boas-vindas — instrução (estado zero) | `Registre uma nota fiscal e ganhe 0,1% de cada compra de volta.` |
| Boas-vindas — CTA (estado zero) | `Coletar cupom` |
| Erro — mensagem (referência) | `Não foi possível carregar sua página.` |
| Erro — ação (referência) | `Tentar de novo` |
| Nav | `Início` · `Cupons` · `Carteira` · `Perfil` |

Vocabulário e tom: `docs/project-state/design/system/voice-and-tone.md` (App do Colaborador — direto,
simples, recompensador, **sem emoji**; celebra com discrição). Dinheiro sempre `R$ 0,00` (2 casas, vírgula
decimal), fuso `America/Sao_Paulo`.

> **Decisão de microcopy — CTA "Coletar cupom":** o CA-3 da estória e os entregáveis do épico nomeiam
> literalmente "coletar cupom"; a rota é `/coletar` e o domínio é "coleta". A tela de carteira (STORY-016)
> usa "Capturar cupom" no seu empty-state. Adotei **"Coletar cupom"** aqui por fidelidade ao CA e à
> linguagem do épico. É uma pequena divergência de rótulo entre superfícies (não bloqueante); se o PO
> preferir unificar o verbo em todo o app, ajusto — ver §8.

## 6. Acessibilidade (notas específicas desta tela)

- **Foco inicial** ao abrir: o `<h1>` (saudação) recebe foco lógico; ordem de leitura: saudação → saldo →
  CTA → (bloco de boas-vindas, no estado zero) → nav.
- **Saldo** é texto real (não imagem): leitor lê "Seu saldo, R$ 12,47". Número `primary` `#9fe870` sobre
  `ink` `#0e0f0c` → contraste alto (AA ✅).
- **CTA "Coletar cupom"**: `<a>`/`<button>` real, texto `on-primary` `#0e0f0c` sobre `primary` `#9fe870`
  (nunca branco sobre verde), foco visível, alvo ≥52px. Ícone `+` é decorativo (`aria-hidden`), o rótulo
  textual carrega o significado.
- **brand.mark**: decorativo/identidade → `aria-hidden` (ou `alt=""`); não é ação.
- **Bloco de boas-vindas (estado zero):** título é heading; CTA é botão/link real com foco visível.
- **Nav:** `<nav aria-label="Seções">`, item ativo com `aria-current="page"`, alvos ≥48px, ícone + rótulo
  textual (não ícone sozinho).
- **Contraste** de todos os tokens usados: herda o piso AA já verificado no DS.

## 7. Identificadores estáveis sugeridos para teste

| Elemento | Identificador lógico |
|---|---|
| Container da tela | `screen-home` |
| Saudação (h1) | `screen-home-greeting` |
| Marca (brand.mark) | `screen-home-brand` |
| Card de saldo | `screen-home-saldo-card` |
| Valor do saldo | `screen-home-saldo` |
| CTA primário "Coletar cupom" | `screen-home-cta` |
| Bloco de boas-vindas (estado zero) | `screen-home-welcome` |
| Linha de apoio (estado positivo) | `screen-home-support` |
| Navegação | `screen-home-nav` |

> Nomes lógicos (contrato do spec); o Programador materializa como `data-testid` na página React.

## 8. Exceções ao Design System

| O que diverge | Por quê | Vai virar DDR? |
|---|---|---|
| Nenhuma exceção estrutural | Tela composta 100% de componentes/tokens/padrões existentes do DS | não |
| Rótulo do CTA "Coletar cupom" ≠ "Capturar cupom" da carteira | Fidelidade ao CA-3/épico; rota `/coletar` | não (microcopy; decisão do PO se unificar) |

> Uso de `card.feature-dark` para o saldo é uso **pontual** sancionado pelo DS (momento de marca), não
> exceção. `brand.mark` no topo é a identidade do DDR-004 substituindo o scaffolding.

## 9. Protótipo HTML fiel (validação humana)

- **Localização:** `STORY-029-home-hub-coletador/index.html` (nesta mesma pasta).
- **Cobertura:** estados `positivo`, `zero`, `loading`, `erro` alcançáveis por chips (`?state=`); viewports
  **mobile** e **desktop** alternáveis por toggle (o layout de navegação muda).
- **Fidelidade:** tokens reais do DS inline (`:root`); microcopy = §5 palavra por palavra; `data-testid` da
  §7 aplicados.
- **Restrições:** HTML/CSS/JS vanilla, sem rede; abre clicando no arquivo. Comentário de topo declara
  "protótipo de validação, não código de produção".
- **Validação humana:** apresentar ao Alexandro; registrar aprovação/ajustes + data em
  `prototype_last_validated_at` e na §11.

### Checklist antes de marcar `ready`
- [ ] `index.html` abre sem erro.
- [ ] Estados positivo/zero/loading/erro alcançáveis.
- [ ] Mobile e desktop navegáveis (toggle).
- [ ] Microcopy do protótipo = §5.
- [ ] `data-testid` da §7 presentes.
- [ ] Tokens reais do DS aplicados.
- [ ] Protótipo apresentado ao humano e sinal capturado.

## 10. Dependências e premissas

- **Fonte de dados (EPIC-003):** `Carteira.saldo_centavos` via `ExtratoCarteira::para($user)` (mesmo
  contrato de `/carteira`). Formatação de reais na borda de apresentação (`App\Support\Formato::moeda`).
- **Contrato de props (a implementar pelo Programador):** `saldo` (centavos + formatado) e `nome` (de
  `auth.user`). Sem novo endpoint.
- **Rota** logada sob `auth` (destino pós-login). **Atalhos/navegação coesa** = STORY-030. **Loop de saldo
  refletindo a coleta e E2E ponta a ponta** = STORY-031.
- Sem DDR pendente (usa DDR-004 já `accepted`).

## 11. Histórico de mudanças

| Data | Mudança | Quem | Motivo |
|---|---|---|---|
| 2026-07-05 | criação (spec + protótipo v1) | claude-story029 | rabisco → spec detalhado; estados zero/positivo/loading/erro; mobile+desktop |
| 2026-07-05 | validação humana — **aprovado** | Alexandro | protótipo revisado no navegador; CTA "Coletar cupom" confirmado; sem ajustes → `ready` |
| 2026-07-05 | implementada e mergeada (PR #2) | claude-story029 | UI de produção fiel ao spec; deploy homolog verificado → `shipped` |
