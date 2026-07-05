---
id: DDR-005
title: Casca pública compartilhada — header/rodapé comuns às landings B2C e B2B, com alternância entre as faces
status: accepted   # proposed | accepted | superseded | rejected | deferred
created_at: 2026-07-05
decided_at: 2026-07-05
approved_by: Alexandro
supersedes: ~
superseded_by: ~
related_ddrs: [DDR-004, DDR-002, DDR-001]
related_adrs: [ADR-010]
related_pdrs: [PDR-001, PDR-003]
scope: navegação (superfícies públicas — transversal ao EPIC-005)
affects_screens: [SCREEN-STORY-025-landing-b2c, SCREEN-STORY-026-landing-b2b-quantah-intelligence]
---

# DDR-005 — Casca pública compartilhada (header/rodapé das landings)

## Contexto

O EPIC-005 entrega **duas faces públicas** da mesma marca: a landing **B2C** ("Cada nota conta.",
STORY-025) e a landing **B2B / Quantah Intelligence** ("Do cupom ao insight.", STORY-026). O PO decidiu
(handoff, decisão #1) que as duas usam **uma barra e um rodapé comuns, com link de alternância entre as
faces**, e sinalizou que, sendo elemento durável em mais de uma tela, o **header/rodapé público se
candidata a DDR** — a formalizar antes de codar UI, sem inventar componente fora do DS.

Hoje **não existe casca pública**: as páginas públicas montam `<main>` direto (`Pages/Hello.jsx`,
`Pages/Intelligence/Reservado.jsx`). Há um `GuestLayout` (telas de acesso, split-hero — DDR-004) e um
`AuthenticatedLayout` (área logada), mas nada para a vitrine pública. Sem uma casca comum, a B2C e a B2B
duplicariam header/rodapé e divergiriam com o tempo.

Antes de o Programador cristalizar as duas landings, uma decisão é **durável** (vale para ambas as
telas e para futuras páginas públicas) e cara de reverter: **existe uma casca pública compartilhada?
Como ela navega entre as duas faces?**

Lido: STORY-025 e STORY-026 (CAs, fora de escopo), `design-handoff.md` (decisões #1 e #5 do PO),
**DDR-004** (herança de marca no acesso — o lockup, a hierarquia; o CTA "Entrar" do B2C aponta para a
tela desenhada nele), **DDR-002/001** (marca é base de sistema; Inter 900), DS (`components.md` →
`nav.bar`, `nav.link`, `footer`, `brand.lockup`; `patterns.md`; `voice-and-tone.md` — as duas faces),
**ADR-010** (segmentação das 3 áreas; B2B pública/reservada em `/intelligence`), PDR-003 (B2B só
captação de lead nesta onda — **sem login B2B**).

## Forças (drivers)

- **Consistência de marca entre as faces** (alto): B2C e B2B são "duas faces, uma marca"
  (`voice-and-tone.md`); a casca é o que torna isso legível — mesma barra, mesmo rodapé, tom ajustado.
- **Navegação cruzada é requisito de produto** (alto): o CTA-B2B da B2C (CA-4/STORY-025) e o retorno ao
  app precisam de um lugar previsível; a alternância entre faces vive na casca.
- **Princípio #4 — padronização > criatividade** (alto): reusar `nav.bar`/`nav.link`/`footer`/
  `brand.lockup` em vez de markup por página; **um** CTA verde por contexto (regra de ouro).
- **DRY / manutenção** (alto): header/rodapé duplicados por página divergem; uma casca é fonte única.
- **Princípio #2 — mobile-first com paridade** (alto): a barra precisa funcionar em ≥360px (links
  rolam dentro da barra, sem estourar a largura) e ganhar respiro no desktop.
- **Restrição de escopo — sem login B2B** (médio, PDR-003): a casca **não** pode oferecer "entrar" na
  face B2B (não há conta B2B); o CTA de entrada é exclusivo da face B2C.
- **Extensibilidade** (baixo): futuras páginas públicas (institucional, política) herdam a casca.

## Opções consideradas

### Opção A — `PublicLayout`: casca única compartilhada, header contextual por face *(proposta)*

Um layout React `Layouts/PublicLayout.jsx` (par dos existentes `GuestLayout`/`AuthenticatedLayout`) que
compõe **`nav.bar` + conteúdo + `footer`**, recebendo a `face` (`'b2c' | 'b2b'`) como prop. Zero
primitivo novo: usa `NavBar`, `NavLink`, `Footer`, `BrandLockup` do DS.

- **Header (`nav.bar`, sticky):** `brand.lockup` à esquerda (leva à home da face). À direita, itens
  **contextuais**:
  - face **B2C**: link `nav.link` "Para empresas" (→ `/intelligence`) + `button.primary` **"Entrar"**
    (→ `route('login')`). Único CTA verde do contexto.
  - face **B2B**: link `nav.link` "Para você" / "Voltar ao app" (→ `/`). **Sem** CTA de login (não há
    conta B2B — PDR-003). O CTA verde do contexto B2B é o "Quero saber mais" do formulário de lead.
- **Footer (`footer`, band `ink`):** wordmark + alternância entre faces + links institucionais mínimos
  (privacidade, contato). Comum às duas faces.

```
mobile (≥360px) — face B2C           desktop (≥1024px) — face B2C
+------------------------------+     +----------------------------------------------+
| [◧]Quantah  Empresas [Entrar]|     | [◧] Quantah      Para empresas   [ Entrar ]  |
+------------------------------+     +----------------------------------------------+
| … conteúdo da landing …      |     | … conteúdo da landing (hero, passos) …       |
+------------------------------+     +----------------------------------------------+
| [footer ink] Quantah         |     | [footer ink]  Quantah · Para empresas ·      |
|  Para empresas · privacidade |     |               privacidade · contato          |
+------------------------------+     +----------------------------------------------+
```

- **Prós:** consistência de marca e navegação cruzada num só lugar; **fonte única** (DRY); só composição
  do DS (sem débito de componente novo); respeita "um CTA verde por contexto" e a ausência de login B2B;
  paridade mobile/desktop trivial (a `nav.bar` já rola links dentro da barra); futuras páginas públicas
  herdam. Alinha com a herança visual do login (DDR-004): mesma marca da porta de entrada.
- **Contras:** a prop `face` introduz uma pequena ramificação de conteúdo na casca — mitigável mantendo
  a lógica declarativa (mapa por face), sem condicionais espalhadas.

### Opção B — Cada landing monta seu próprio header/rodapé inline

Sem layout compartilhado; B2C e B2B repetem a barra e o rodapé no próprio JSX.

- **Prós:** nada de abstração nova; cada página é autocontida.
- **Contras:** **duplicação** de header/rodapé que **diverge** com o tempo (viola DRY e o princípio #4);
  a alternância entre faces vira copy-paste frágil; contraria a decisão explícita do PO de casca comum.
  Rejeitada.

### Opção C — `PublicLayout` sem alternância entre faces (header só marca + CTA)

Casca compartilhada, mas a navegação cruzada B2C↔B2B fica **só** nos CTAs do corpo, não no header/rodapé.

- **Prós:** casca ainda mais simples.
- **Contras:** perde a navegação cruzada previsível que o PO pediu (decisão #1); o visitante que rola até
  o rodapé não acha a outra face; assimétrico (a B2B não teria como voltar ao app pela casca). Rejeitada.

### Status quo — sem casca pública (páginas montam `<main>` direto)

- **Contras:** é o ponto de partida que este épico precisa superar; sem header/rodapé não há navegação
  entre as faces nem consistência de marca. Rejeitada.

## Avaliação contra os princípios

| Princípio | A (proposta) | B | C | Status quo |
|---|---|---|---|---|
| 1. Simplicidade radical | ✅ casca única, conteúdo declarativo | ✅ | ✅ | ✅ |
| 2. Mobile-first com paridade | ✅ nav.bar já responsiva | ⚠️ duplicado | ✅ | ⚠️ |
| 3. Tom profissional do domínio | ✅ marca consistente entre faces | ⚠️ deriva | ✅ | ⚠️ |
| 4. Padronização > criatividade | ✅ reusa DS, um CTA verde | ❌ duplicação | ✅ | ❌ |
| 5. Acessibilidade | ✅ `<nav>`/`<main>`/`<footer>`, foco visível | ⚠️ inconsistente | ✅ | ⚠️ |
| 6. Performance percebida | ✅ sem fetch | ✅ | ✅ | ✅ |
| 7. Estados além do feliz | ✅ CTA-B2B com destino vivo; foco/hover | ⚠️ por página | ✅ | ❌ |

## Decisão

> **Adotada (proposta):** **Opção A** — `PublicLayout` compartilhado, header/rodapé contextuais por face.

É a única que entrega ao mesmo tempo as duas forças de maior peso — **consistência de marca entre as
faces** e **navegação cruzada previsível** — sem duplicação e sem débito de componente novo (só compõe
`nav.bar`/`nav.link`/`footer`/`brand.lockup` do DS). B duplica e diverge; C perde a navegação cruzada que
o PO pediu. A casca respeita a regra de ouro (um CTA verde por contexto) e a ausência de login B2B
(PDR-003), e dá herança visual da porta de entrada (DDR-004).

## Consequências

### Positivas
- Header/rodapé **fonte única** para toda superfície pública; B2C e B2B não divergem.
- Alternância entre as faces num lugar previsível (barra e rodapé), em mobile e desktop.
- Novas páginas públicas (institucional, política de privacidade) já nascem com a casca.
- Zero componente novo no DS — só um **Layout** (par de `GuestLayout`/`AuthenticatedLayout`).

### Negativas / trade-offs assumidos
- A prop `face` acopla um mapa de conteúdo (links/CTA por face) à casca — assumido; mantido declarativo
  para não espalhar condicionais.
- A casca precisa de disciplina para **não** virar um menu institucional inflado — nesta onda: só
  alternância entre faces + privacidade/contato (simplicidade radical).

### Impacto no Design System
- **Novo padrão:** `pattern.public-shell` (casca pública: `nav.bar` contextual + `footer` comum,
  alternância entre faces, um CTA verde só na face B2C). Documentar em `design/system/patterns.md`
  **nesta operação** se o DDR for aceito.
- **Nenhum** componente/primitivo novo — reuso de `nav.bar`, `nav.link`, `footer`, `brand.lockup`.
- Materializar em React os ids de band do DS já documentados (`hero-band`, `hero-band-dark`,
  `content-band`) é implementação (não é componente novo — já são DS via PDR-001), fora do escopo deste
  DDR.

### Impacto em telas existentes
- `SCREEN-STORY-025-landing-b2c` e `SCREEN-STORY-026-landing-b2b-quantah-intelligence` (specs deste
  épico) — desenhados sobre a casca.
- `Pages/Intelligence/Reservado.jsx` — a STORY-026 o substitui pela landing B2B dentro da casca; nenhuma
  outra tela logada/acesso é afetada.

## Implementação sugerida (notas para o Programador)

- Criar `resources/js/Layouts/PublicLayout.jsx` compondo `Components/nav/{NavBar,NavLink,Footer}` e
  `Components/brand/BrandLockup`; prop `face` (`'b2c' | 'b2b'`) escolhe links/CTA por um mapa declarativo.
- Semântica a11y: `<nav aria-label>` na barra, `<main>` no conteúdo, `<footer>`; foco visível já vem dos
  componentes DS. Alvos ≥48px (o `button.primary`/`nav.link` já cumprem).
- Identificadores lógicos que os E2E ancoram: `public-nav`, `public-footer`, `landing-b2c-cta-entrar`,
  `landing-b2c-cta-b2b` (materializados em `data-testid`).
- CTA "Entrar" → `route('login')` (Ziggy); "Para empresas" → `/intelligence`. Microcopy pt-BR reusando
  `t()` onde a string já existe (`Sign in` → "Entrar").

## Critérios para revisitar

- Se surgir uma **terceira** superfície pública com navegação diferente (ex.: blog, docs) — reavaliar se
  a casca generaliza ou precisa variar.
- Se a área B2B ganhar login numa onda futura — o header da face B2B passará a ter CTA de entrada
  (revisar a regra "sem login B2B").
- Se o menu público crescer além de alternância + institucional mínimo — reavaliar padrão de navegação
  (evitar inflar a casca).

## Aprovação humana

| Campo | Valor |
|---|---|
| Apresentado em | 2026-07-05 |
| Aprovado por | Alexandro |
| Data da aprovação | 2026-07-05 |
| Observações do aprovador | Aprovado ("Aprovado, seguir") sobre o resumo em chat — Opção A confirmada. |

> Aprovado. Casca pública (`PublicLayout` / `pattern.public-shell`) vigente para o EPIC-005 (STORY-025/026).
