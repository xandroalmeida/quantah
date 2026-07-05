---
id: DDR-007
title: Casca de navegação da área logada — nav persistente (bottom/top) + atalhos da home, substituindo o scaffolding Breeze
status: accepted   # proposed | accepted | superseded | rejected | deferred
created_at: 2026-07-05
decided_at: 2026-07-05
approved_by: Alexandro
supersedes: ~
superseded_by: ~
related_ddrs: [DDR-005, DDR-004, DDR-002, DDR-001]
related_adrs: [ADR-010]
related_pdrs: [PDR-001, PDR-003]
scope: navegação (área B2C autenticada — transversal ao EPIC-006)
affects_screens: [SCREEN-STORY-030-navegacao-b2c, SCREEN-STORY-029-home-hub-coletador, SCREEN-STORY-016-carteira-saldo-historico, SCREEN-STORY-017-solicitar-saque, SCREEN-STORY-009-captura-qr-confirmacao]
---

# DDR-007 — Casca de navegação da área logada (Coletador)

## Contexto

Com a home-hub (STORY-029) existindo, o EPIC-006 precisa da **costura de navegação** que liga o Coletador
às telas da Onda 1 — **extrato/histórico** e **saque** (EPIC-003) e **coleta** (EPIC-002) — com **retorno
consistente** à home e **sem página genérica** (STORY-030, CA-1..CA-4).

Hoje a navegação da área logada é **inconsistente e parcialmente scaffolding**:
- O par `nav.bottom`/`nav.bar` com as seções `Início · Cupons · Carteira · Perfil` está **duplicado
  inline** em 4 arquivos (`Pages/Home/Hub.jsx`, `Pages/Carteira/Index.jsx`, `Pages/Privacidade.jsx`,
  `Pages/DesignSystem/Showcase.jsx`) — fonte de divergência.
- `Pages/Coleta/Captura.jsx` **não tem navegação** (tela centrada) — beco sem saída: só o "voltar" do
  navegador.
- `Pages/Saque/Solicitar.jsx` tem apenas "Cancelar" → `/carteira` (não à home).
- `Pages/Profile/Edit.jsx` ainda usa o **`AuthenticatedLayout` do Breeze**, que renderiza o
  **`ApplicationLogo` (logo do Laravel)** e um menu "Dashboard/Profile/Log Out" cinza — **scaffolding numa
  rota logada** (viola CA-4: "sem logo do Laravel").

Antes de o Programador costurar a navegação, uma decisão é **durável** (vale para toda a área logada e para
telas futuras) e cara de reverter: **existe uma casca de navegação única da área logada? Qual a forma
(barra inferior, gaveta, hub com "voltar")? Como o Coletador retorna à home e alcança extrato/saque?**

Lido: STORY-030 e STORY-029 (CAs, fora de escopo), `design-handoff.md` (SCREEN-STORY-030), DS
(`components.md` → `nav.bottom`, `nav.bar`, `nav.link`, `card.content`; `patterns.md` → `surface-rhythm`,
`public-shell`; `voice-and-tone.md`), **DDR-005** (casca **pública** — o análogo para as superfícies
públicas; esta DDR é o par **logado**), **DDR-004** (marca no acesso), **ADR-010** (segmentação das 3
áreas; B2C = `auth`). Telas reusadas: `SCREEN-STORY-016` (carteira/extrato), `SCREEN-STORY-017` (saque),
`SCREEN-STORY-009` (coleta).

## Forças (drivers)

- **Retorno consistente / sem beco sem saída** (alto, CA-3): de qualquer tela da jornada o Coletador tem
  de voltar à home de forma previsível — hoje coleta e saque não oferecem isso.
- **≤ 2 toques para coleta e saldo/histórico** (alto, métrica do épico / CA-1/CA-2): os destinos precisam
  estar a um ou dois toques da home.
- **Zero scaffolding na área logada** (alto, CA-4): nenhuma rota logada pode mostrar o logo do Laravel ou
  o menu genérico do Breeze.
- **Princípio #4 — padronização > criatividade** (alto): uma casca única em vez de `nav` duplicada por
  página; reusar `nav.bottom`/`nav.bar`/`nav.link` do DS.
- **DRY / manutenção** (alto): o `SECOES` duplicado em 4 arquivos diverge; uma casca é fonte única.
- **Princípio #2 — mobile-first com paridade** (alto): padrão mobile consagrado = **barra inferior**;
  desktop = **barra superior** (o DS já desaconselha bottom-nav em web).
- **Consistência com a casca pública** (médio, DDR-005): a área logada deve ser o **par** da casca
  pública — mesma marca, mesma lógica de "uma casca compõe só o DS".
- **Simplicidade radical / não inflar** (médio): a barra tem no máximo ~4 seções raiz; atalhos extras são
  poucos e contextuais.

## Opções consideradas

### Opção A — `AppLayout`: casca única com nav persistente (bottom mobile / top desktop) + atalhos da home *(proposta)*

Um layout `Layouts/AppLayout.jsx` (par logado de `PublicLayout`/`GuestLayout`) que compõe
**`nav.bottom` (mobile) + `<main>` + `nav.bar` (desktop)** com as **4 seções raiz** `Início · Cupons ·
Carteira · Perfil`, recebendo `active` (a seção atual). Zero primitivo novo: usa `NavBottom`, `NavBar`,
`NavLink` do DS. Aplicada a **todas** as telas logadas (home, coleta, carteira, saque, perfil),
substituindo o `SECOES` inline e o `AuthenticatedLayout` do Breeze.

- **Retorno à home (CA-3):** "Início" na barra volta à home-hub de qualquer tela — a barra é persistente,
  logo não há beco sem saída (inclui coleta e saque, hoje sem nav).
- **Atalhos da home (CA-1/CA-2):** na home-hub, abaixo do saldo/CTA, dois atalhos rápidos em `card.content`
  — **Histórico** (→ `/carteira`, extrato) e **Prêmios** (→ `/carteira/saque`) — **1 toque** cada. (Extrato
  também é 1 toque pela aba "Carteira"; saque, 1 toque pelo atalho ou 2 por Carteira → botão "Sacar".)
- **Sem scaffolding (CA-4):** o `AuthenticatedLayout`/`ApplicationLogo` do Breeze saem das rotas logadas;
  o Perfil passa a usar o `AppLayout` (marca Quantah, sem logo Laravel).

```
mobile (≥360px) — home-hub                desktop (≥1024px) — carteira
+------------------------------+          +-----------------------------------------------+
| Olá, Ana            [marca]  |          | Quantah   Início  Cupons  (Carteira)  Perfil  |
| +--------------------------+ |          +-----------------------------------------------+
| | Seu saldo  R$ 12,47      | |          |  Carteira                                     |
| +--------------------------+ |          |  Seu saldo  R$ 12,47   ...                     |
| [  +  Coletar cupom       ] |          +-----------------------------------------------+
| +----------+ +-----------+  |            (sem barra inferior no desktop — nav no topo)
| |Histórico | | Prêmios   |  |  ← atalhos (card.content), 1 toque cada
| +----------+ +-----------+  |
+------------------------------+
| (Início) Cupons Carteira Perfil |  ← nav.bottom persistente, "Início" ativo, ≥48px
+------------------------------+
```

- **Prós:** entrega retorno consistente e ≤2 toques num só lugar; **fonte única** (DRY) para a nav logada;
  só composição do DS (sem componente novo); remove o scaffolding do Breeze; padrão mobile consagrado
  (bottom-nav) com paridade desktop (top-nav); **par simétrico** da casca pública (DDR-005).
- **Contras:** tocar em 5 telas para adotar a casca (mecânico); a home ganha dois atalhos que **coexistem**
  com a aba "Carteira" (leve redundância — assumida: atalho é conveniência de 1 toque).

### Opção B — Cada tela monta sua navegação inline (status quo estendido)

Manter o `SECOES` duplicado por página e adicionar nav às telas que faltam (coleta/saque), sem casca.

- **Prós:** nenhuma abstração nova.
- **Contras:** **duplicação** em 5+ arquivos que **diverge** (viola DRY e o princípio #4); mantém o
  `AuthenticatedLayout` Breeze (logo Laravel) no Perfil → **não** resolve CA-4; retorno consistente vira
  copy-paste frágil. Rejeitada.

### Opção C — Hub-and-spoke: home é o único hub, cada tela tem só "voltar à home"

Sem barra persistente; a home concentra os atalhos e cada tela tem um botão "voltar à home".

- **Prós:** home enxuta como centro; menos cromo por tela.
- **Contras:** navegação **lateral** entre seções (ex.: extrato → coleta) exige passar pela home = **mais
  toques**; perde o padrão mobile consagrado (bottom-nav) que o usuário não-técnico já conhece; "voltar"
  por tela é fácil de esquecer nalguma tela (risco de beco). Pior para a métrica ≤2 toques e para a
  paridade com a casca pública. Rejeitada.

### Status quo — `SECOES` duplicado + `AuthenticatedLayout` Breeze no Perfil, coleta/saque sem nav

- **Contras:** é exatamente o estado que o épico precisa superar — inconsistente, com scaffolding
  (logo Laravel) e becos sem saída (coleta). Rejeitada.

## Avaliação contra os princípios

| Princípio | A (proposta) | B | C | Status quo |
|---|---|---|---|---|
| 1. Simplicidade radical | ✅ casca única, ≤4 seções | ⚠️ duplicado | ✅ home enxuta | ⚠️ |
| 2. Mobile-first com paridade | ✅ bottom mobile / top desktop | ⚠️ inconsistente | ⚠️ sem padrão consagrado | ⚠️ |
| 3. Tom profissional do domínio | ✅ marca Quantah, sem Laravel | ❌ logo Laravel no Perfil | ✅ | ❌ |
| 4. Padronização > criatividade | ✅ reusa DS, fonte única | ❌ duplicação | ⚠️ padrão próprio | ❌ |
| 5. Acessibilidade | ✅ `<nav aria-label>`, `aria-current`, ≥48px | ⚠️ inconsistente | ⚠️ "voltar" por tela | ⚠️ |
| 6. Performance percebida | ✅ sem fetch, nav client-side | ✅ | ✅ | ✅ |
| 7. Estados além do feliz | ✅ item ativo por tela; retorno sempre | ⚠️ por página | ⚠️ risco de beco | ❌ |

## Decisão

> **Adotada (proposta):** **Opção A** — `AppLayout`, casca única de navegação da área logada: `nav.bottom`
> (mobile) / `nav.bar` (desktop) persistente com 4 seções raiz + atalhos rápidos na home; substitui o
> `SECOES` inline e o `AuthenticatedLayout` do Breeze em todas as rotas logadas.

É a única que entrega juntas as forças de maior peso — **retorno consistente**, **≤2 toques** e **zero
scaffolding** — sem duplicação e sem componente novo (só compõe `nav.bottom`/`nav.bar`/`nav.link` +
`card.content`). B mantém o logo do Laravel (não resolve CA-4) e duplica; C custa toques na navegação
lateral e larga o padrão mobile consagrado. A casca logada fica **simétrica** à pública (DDR-005): um
`Layout` que só compõe o DS.

## Consequências

### Positivas
- Navegação logada **fonte única**; as 5 telas deixam de divergir.
- Retorno à home previsível de qualquer tela (inclui coleta e saque, hoje becos); extrato/saque a ≤2
  toques da home.
- Remove o scaffolding do Breeze (logo Laravel + menu genérico) da área logada — fecha a CA-4.
- Novas telas logadas nascem já com a casca.
- Zero componente novo no DS — só um **Layout** (par de `PublicLayout`/`GuestLayout`).

### Negativas / trade-offs assumidos
- Adotar a casca toca 5 telas (mecânico) e aposenta `AuthenticatedLayout`/`ApplicationLogo` (Breeze) das
  rotas logadas — assumido; é dívida de scaffolding sendo paga.
- Os atalhos da home coexistem com a aba "Carteira" (leve redundância) — assumido: atalho é conveniência
  de 1 toque, alinhado a "atalhos a partir da home" do handoff.

### Impacto no Design System
- **Novo padrão:** `pattern.app-shell` (casca da área logada: `nav.bottom` mobile + `nav.bar` desktop,
  4 seções raiz `Início · Cupons · Carteira · Perfil`, item ativo com `aria-current`, "Início" = retorno à
  home; convenção de **atalhos rápidos na home** em `card.content`). Documentar em
  `design/system/patterns.md` **nesta operação** se o DDR for aceito. É o **par logado** do
  `pattern.public-shell` (DDR-005).
- **Nenhum** primitivo novo — reuso de `nav.bottom`, `nav.bar`, `nav.link`, `card.content`.

### Impacto em telas existentes
- `SCREEN-STORY-029` (home): ganha os atalhos rápidos (Histórico/Prêmios) e passa a usar a casca.
- `SCREEN-STORY-016` (carteira), `SCREEN-STORY-017` (saque), `SCREEN-STORY-009` (coleta): passam a usar a
  casca (coleta e saque ganham a barra + retorno). `Pages/Profile/Edit.jsx`: sai do `AuthenticatedLayout`
  Breeze para a casca (perde o logo Laravel). `Privacidade.jsx`/`Showcase.jsx` (públicas/vitrine) migram o
  `SECOES` inline para a casca conforme aplicável (Privacidade é pública — pode seguir na casca pública).

## Implementação sugerida (notas para o Programador)

- Criar `resources/js/Layouts/AppLayout.jsx` compondo `Components/nav/{NavBar,NavLink,NavBottom}`; prop
  `active` (`'inicio' | 'cupons' | 'carteira' | 'perfil'`) marca a seção; um mapa declarativo das 4 seções
  (label, href via Ziggy `route()`, icon). Envolver Home/Carteira/Coleta/Saque/Perfil.
- Atalhos da home: lista de `card.content` (ícone + rótulo), `Histórico` → `route('carteira.index')`,
  `Prêmios` → `route('saque.create')`. Alvos ≥48px.
- Aposentar `AuthenticatedLayout.jsx` e `ApplicationLogo.jsx` (Breeze) das rotas logadas; remover imports.
  Manter o teste que garante ausência do logo Laravel (viewBox `0 0 316 316`) passando em todas as telas
  logadas.
- Rótulos de nav via `t()` (IDR-010): `Home`→"Início" (já existe), `Profile`→"Perfil" (já existe);
  adicionar `Coupons`→"Cupons", `Wallet`→"Carteira". Atalhos: `History`→"Histórico", `Rewards`→"Prêmios".
- Identificadores lógicos que os E2E ancoram: `app-nav` (barra), `app-nav-inicio|cupons|carteira|perfil`,
  `screen-home-atalho-historico`, `screen-home-atalho-sacar`.
- **Naming do destino:** o home-hub vive hoje em `/dashboard` (IDR-011). A STORY-030 **pode** renomear
  para `/inicio` ao criar a casca (atualizando os 8 redirects de acesso + `waitForLocation('/dashboard')`
  dos Dusk num só passe) — opcional, decisão de implementação; se renomear, atualizar IDR-011.

## Critérios para revisitar

- Se a área logada ganhar uma **5ª seção raiz** — reavaliar (bottom-nav satura em ~5 itens; considerar
  agrupar).
- Se surgir navegação hierárquica profunda (sub-telas com breadcrumb) — reavaliar se a casca simples
  ainda serve.
- Se a home acumular mais que 2–3 atalhos — reavaliar para não inflar (simplicidade radical).

## Aprovação humana

| Campo | Valor |
|---|---|
| Apresentado em | 2026-07-05 |
| Aprovado por | Alexandro |
| Data da aprovação | 2026-07-05 |
| Observações do aprovador | Aprovado ("Aprovo design e DDR") sobre o resumo em chat — Opção A confirmada. Atalho de saque na home rotulado **"Prêmios"** (não "Sacar"). URL da home renomeada para **/inicio** junto da casca. |

> Aprovado. Casca de navegação da área logada (`AppLayout` / `pattern.app-shell`) vigente para o EPIC-006
> (STORY-030), par logado do `pattern.public-shell` (DDR-005).
