---
idr_id: IDR-012
slug: casca-mobile-dvh-e-pwa-instalavel
title: Casca mobile — altura por 100dvh (não position:fixed), PWA instalável sem service worker, header responsivo
status: accepted  # proposed | accepted | superseded
decided_at: 2026-07-05
decided_by: programador
owner_agent: claude-programador
related_story: STORY-033
related_adrs: []
related_idrs: []
related_ddrs: [DDR-005, DDR-007]
supersedes: null
superseded_by: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# IDR-012 — Casca mobile: `100dvh`, PWA instalável sem service worker, header responsivo

> Implementation Decision Record. Registra **como** a casca mobile app-like (STORY-033) foi
> corrigida, para que a validação (STORY-037) e futuras estórias saibam por que a altura usa `dvh`,
> por que ainda **não** há service worker, e por que componentes do DS (`nav.bar`, `CtaLink`,
> `brand.lockup`) ganharam densidade responsiva.

## Contexto

O `AppLayout` (casca logada, DDR-007) usava `h-screen` (100vh). No Safari iOS, `100vh` conta a área
atrás da barra do navegador, então o `nav.bottom` (último filho do flex-column) caía fora do viewport
visível — sumia em Carteira/Perfil e só aparecia com scroll nas demais. O header público (DDR-005)
usava paddings/gaps de desktop e `overflow-x-auto`, e o CTA "Entrar" era cortado à direita em
360–390px. Não havia manifest nem metatags iOS: adicionar à tela inicial abria com a barra do
navegador. Escopo da onda exclui offline/app nativo.

## Decisão

> **Decidi: (1) medir a casca com `h-[100dvh]` (fallback `h-screen`) mantendo o flex-column +
> `main` rolável + `nav.bottom` `shrink-0`, em vez de `position: fixed`; (2) habilitar PWA
> instalável com um `public/manifest.json` estático (`display: standalone`) + metatags iOS +
> `viewport-fit=cover`, SEM service worker/offline; (3) dar densidade responsiva ao header público
> no mobile (paddings/gaps menores + prop `compact` no `BrandLockup`) para o "Entrar" caber sem
> cortar e sem scroll horizontal.**

## Por quê

- **`100dvh` + flex, não `fixed`:** o viewport dinâmico já exclui a chrome do browser, então o nav
  fica colado ao fundo visível sem precisar de `position: fixed` + `padding-bottom` compensatório no
  conteúdo (que duplicaria a altura do nav em dois lugares e arriscaria conteúdo encoberto). Mantém a
  estrutura da casca (DDR-007) — é correção de implementação, não troca de padrão. `env(safe-area-inset-bottom)`
  no `nav.bottom` cobre notch/barra de gestos.
- **PWA sem service worker:** o "app-like" desta onda é standalone (abrir sem a barra), que depende só
  de manifest + metatags iOS — não de SW. Offline/cache está explicitamente fora de escopo; um SW
  vazio ou mal versionado causaria mais bug (cache preso) que valor agora.
- **Header responsivo:** manter marca + cross-link + CTA no header a 360px exigia caber ~54px a mais.
  Densidade responsiva (paddings `md`/gaps `sm` no mobile, `md:` restaura o desktop) + wordmark
  `display-xs` no mobile via `compact` resolve sem remover afordâncias nem tocar a escala do hero.

## Alternativas consideradas

- **`position: fixed` no nav + padding-bottom no conteúdo:** descartada — mais superfície para erro
  (altura do nav repetida, conteúdo encoberto se o padding sair de sincronia); `dvh` resolve na raiz.
- **Service worker mínimo agora:** descartado — offline está fora de escopo e SW mal gerido prende
  assets em cache; entra quando o piloto pedir (vira estória própria).
- **Esconder o cross-link "Para empresas" no mobile:** descartado — remover afordância é decisão de
  IA/design (DDR), não de implementação; a densidade responsiva coube tudo sem isso.

## Consequências

### Para outros agentes
- Casca de altura cheia usa `h-[100dvh]` (com fallback `h-screen`) — não reintroduzir `h-screen` sozinho.
- Não existe service worker: qualquer necessidade offline/precache é estória nova, não "já tem PWA".
- `BrandLockup` agora aceita `compact` (wordmark `display-xs` → `display-sm` em `md`); o `NavBar`,
  `CtaLink` e o grupo do header têm paddings/gaps menores no mobile (`md:` restaura desktop). Ao mexer
  nesses componentes do DS, preserve a densidade responsiva.
- Ícones do PWA em `public/icons/` (192/512 `any maskable` + `apple-touch-icon` 180), gerados do
  `brand.mark`; `manifest.json` estático servido pela raiz pública.

### Para o projeto
- +`public/manifest.json`, +`public/icons/*`, +metatags no `app.blade.php`. Sem dependência nova.

### Trade-offs aceitos
- O comportamento de raiz do `dvh` (Safari iOS) não é reproduzível no Chrome headless — os E2E de
  CA-1/CA-2 são guardas de regressão (nav no viewport, sem overflow); o dinâmico valida-se no device.
- Sem offline: PWA instala e abre standalone, mas exige rede (aceitável nesta onda).

## Como verificar

- `tests/Browser/CascaMobileTest.php` cobre: nav no viewport em todas as telas logadas, sem overflow
  horizontal a 360–390px, "Entrar" inteiro + header sem scroll, manifest/metatags presentes, alvos ≥48px.
- Se surgir requisito offline, reabrir para adicionar service worker (nova estória) — não editar este IDR.

## Tipo

- [x] **Convenção interna**: casca usa `dvh`; densidade responsiva nos componentes do header do DS.
- [x] **Workaround**: `dvh` contorna o `100vh` do Safari iOS.

---

## Histórico

- 2026-07-05 — criada como `accepted` por programador (sessão claude-programador) durante STORY-033.
