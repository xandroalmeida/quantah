---
story_id: STORY-033
slug: casca-mobile-app-like
title: Casca mobile app-like — nav.bottom fixo e visível, header sem corte e modo standalone
epic_id: EPIC-007
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: done
owner_agent: claude-programador
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-033 — Casca mobile app-like

> **Para o agente que vai executar:** leia por inteiro. Reúne 4 ajustes de casca/mobile que compartilham a
> mesma origem (comportamento da viewport no celular). É correção de casca (DDR-007/005), **não** cria tela
> nova. Verifique **em celular real/emulado**, não só no desktop estreito.

## Contexto (por que esta estória existe)

No teste mobile, a casca não se comporta como app. O `AppLayout` usa `h-screen` (100vh), que no Safari iOS
inclui a área atrás da barra do navegador — o `nav.bottom` cai fora do viewport visível: **some em Carteira
e Perfil** e, nas demais telas, **só aparece com scroll**. Na landing pública, o botão **"Entrar"** do
canto superior direito **corta na borda** no celular. E não há **modo standalone**: ao abrir, a barra do
navegador ocupa espaço e quebra a sensação de app.

- Épico: `epics/EPIC-007-refinamento-experiencia-b2c-mobile/epic.md`
- Documentos a ler ANTES: DDR-007 (casca da área logada, `pattern.app-shell`), DDR-005 (casca pública),
  `docs/skills/stacks/inertia-react/SKILL.md`, tokens do DS.
- Arquivos-âncora: `resources/js/Layouts/AppLayout.jsx`, `resources/js/Components/nav/NavBottom.jsx`,
  `resources/js/Layouts/PublicLayout.jsx` (header público), `resources/views/app.blade.php` (metatags).

## O quê (objetivo desta estória)

1. Tornar o `nav.bottom` **fixo e sempre visível** em todas as telas logadas — trocando `h-screen`/100vh por
   altura de viewport dinâmica (`100dvh`) e/ou fixando a barra, respeitando `safe-area-inset-bottom`.
2. Corrigir o **header público** para o "Entrar" não cortar na borda em telas estreitas.
3. Habilitar **modo standalone/PWA**: `manifest.json` (`display: standalone`), ícones, `theme-color` e
   metatags iOS (`apple-mobile-web-app-*`) — abrir sem a barra do navegador quando instalado/adicionado à
   tela inicial.

## Por quê (valor para o usuário)

Navegação previsível (o menu está sempre lá) e uma casca que parece app aumentam a confiança e a fluidez —
condição para o piloto não perder gente por atrito de UI.

## Critérios de aceite

- [ ] **CA-1:** Em celular (homologação), o `nav.bottom` está **fixo e visível sem rolar** em **todas** as
      telas logadas — inclusive **Carteira** e **Perfil**; o conteúdo rola por baixo sem ficar encoberto
      (padding/safe-area aplicado).
- [ ] **CA-2:** Não há **corte de layout** nem overflow horizontal em viewport de 360–390px; o menu respeita
      `safe-area-inset-bottom` (notch/barra de gestos).
- [ ] **CA-3:** Na landing pública mobile, o botão **"Entrar"** aparece **inteiro**, sem cortar na borda
      direita.
- [ ] **CA-4:** Existe `manifest.json` com `display: standalone`, nome, `theme-color` e ícones; as metatags
      iOS estão no documento. Ao **adicionar à tela inicial**, o app abre **sem a barra do navegador**.
- [ ] **CA-5:** Alvos de toque ≥48px e foco/a11y preservados (regressão do EPIC-001/DS); 100% pt-BR.

## Fora de escopo

- Empacotar app nativo para loja (o "app-like" aqui é PWA).
- Service worker/offline e cache de assets (pode virar dívida/estória futura se o piloto pedir).
- Redesenho visual das telas — é só comportamento de casca.

## Padrões de qualidade exigidos

Segue `quality-standards.md`. E2E/verificação em **browser real mobile** (menu fixo visível nas 4 seções;
sem overflow); a11y mínima; sem regressão nas telas existentes.

## Dependências

- **Bloqueada por:** EPIC-006 `done` (casca DDR-007 existente).
- **Bloqueia:** STORY-037 (validação do épico).
- **Pré-requisitos:** DS (EPIC-001), homologação (EPIC-000).

## Decisões já tomadas (não as reabra)

- DDR-007 (fonte única da navegação logada) e DDR-005 (casca pública) seguem valendo — aqui é **corrigir a
  implementação** da casca, não trocar o padrão. Verde como único accent (PDR-001), mobile-first.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam em celular (homologação).
- [ ] E2E/verificação em browser real mobile verde; sem overflow horizontal; a11y verificada.
- [ ] Pipeline verde; IDR se a decisão de `dvh` vs `fixed`/PWA tiver nuance registrável.
- [ ] `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Sem tela nova (`requires_design: false`), mas valide visualmente no celular.
Decisão técnica relevante (ex.: estratégia de PWA) → registre IDR.

## Notas do agente (preenchido durante/após execução)

### Plano (antes de codar)

**Documentos lidos:** epic.md, esta estória, `_project.md`, skill do programador, DDR-005/007 (via
resumo), `AppLayout.jsx`, `PublicLayout.jsx`, `NavBar/NavBottom.jsx`, `app.blade.php`, tokens do DS,
Dusk `NavegacaoB2cTest`, `DuskTestCase`.

**Entendimento:** 4 ajustes de casca com a mesma origem (viewport mobile). (1) `AppLayout` usa
`h-screen`/100vh → no Safari iOS o `nav.bottom` cai atrás da barra do navegador. Correção: `100dvh`
(altura de viewport dinâmica, exclui a chrome do browser) mantendo o flex-column (main rola por
baixo, nav fica colado ao fundo do viewport visível) + `padding-bottom: env(safe-area-inset-bottom)`
no nav. (2) Header público (`NavBar` com `overflow-x-auto` + `px-xl` + CTA no fim) estoura ~50px em
360–390px e o "Entrar" é cortado à direita — reduzir padding/gap no mobile para caber sem clip. (3)
PWA: `public/manifest.json` (`display: standalone`, ícones 192/512, `theme-color`) + metatags iOS
(`apple-mobile-web-app-*`, `apple-touch-icon`) + `viewport-fit=cover` no `app.blade.php`.

**Mapeamento CA → testes (E2E Dusk, browser real, mobile):** arquivo `tests/Browser/CascaMobileTest.php`.
- CA-1: `test_nav_bottom_visivel_sem_rolar_em_todas_as_telas_logadas` — em 360×640, para cada rota
  logada, `nav.bottom` presente com `rect.bottom ≤ innerHeight` e `rect.top ≥ 0` (visível sem scroll).
- CA-2: `test_sem_overflow_horizontal_nas_telas_logadas` — `document.body.scrollWidth ≤ innerWidth`
  em 360px (feliz) e borda 390px; safe-area aplicada (padding-bottom no nav > 0 quando inset > 0 —
  verificado via presença do estilo; inset é 0 no headless, então asserto o mecanismo).
- CA-3: `test_botao_entrar_inteiro_na_landing_mobile` (feliz 360px + borda 390px) — CTA "Entrar"
  visível com `rect.right ≤ innerWidth` (não cortado) e sem overflow do body.
- CA-4: `test_manifest_e_metatags_pwa_presentes` — `<link rel=manifest>` aponta manifest válido
  (`display: standalone`, ícones, theme-color); metatags iOS presentes; `/manifest.json` responde 200.
- CA-5: `test_alvos_de_toque_e_foco_preservados` — itens do `nav.bottom` com altura ≥48px; foco
  visível; labels pt-BR (Início/Cupons/Carteira/Perfil).

**Decisão registrável:** estratégia `100dvh` + flex (em vez de `position: fixed`) e forma de habilitar
PWA → IDR (nuance de por que dvh e não fixed; sem service worker nesta onda).

### Decisões tomadas

- **Altura da casca por `100dvh`** (fallback `h-screen`) mantendo o flex-column + `main` rolável +
  `nav.bottom` `shrink-0` — **não** `position: fixed`. `env(safe-area-inset-bottom)` no `nav.bottom`.
- **PWA instalável sem service worker**: `public/manifest.json` estático (`display: standalone`,
  ícones 192/512 `any maskable`), metatags iOS (`apple-mobile-web-app-*`, `apple-touch-icon`),
  `viewport-fit=cover`. Offline fora de escopo.
- **Header público com densidade responsiva** para o "Entrar" caber a 360–390px sem cortar nem gerar
  scroll horizontal no header: paddings/gaps `md`/`sm` no mobile com `md:` restaurando o desktop, e
  novo prop `compact` no `BrandLockup` (wordmark `display-xs` no mobile). Registrado em **IDR-012**.
- Ícones do PWA gerados do `brand.mark` (tile verde + glifo de cupom) via GD → `public/icons/`.

### Descobertas

- Todas as 5 telas logadas já usam `AppLayout` uniformemente — o "some em Carteira/Perfil" é o efeito
  `100vh` do Safari iOS (não reproduzível no Chrome headless). Os E2E de CA-1/CA-2 são guardas de
  regressão; o comportamento dinâmico validou-se por device/emulador (roteiro manual) + a troca `dvh`.
- O header público estourava ~54px a 360px (CTA cortado) e ainda gerava scroll interno no `overflow-x-auto`
  do `nav.bar` — corrigido pela densidade responsiva, com o teste medindo `scrollWidth - clientWidth` do header.
- `assertPresent` do Dusk é escopado em `body`; tags de `<head>` (manifest/metatags) exigiram checagem
  via `document.querySelector` (script) em vez de `assertPresent`.

### Mapeamento CA → teste (todos verdes)

Arquivo `tests/Browser/CascaMobileTest.php` (E2E Dusk, browser real, 360–390px):
- **CA-1** → `test_nav_bottom_visivel_sem_rolar_em_todas_as_telas_logadas` (nav dentro do viewport nas 5 rotas).
- **CA-2** → `test_sem_overflow_horizontal_nas_telas_logadas` (360 e 390px) + guarda de header sem scroll.
- **CA-3** → `test_botao_entrar_inteiro_na_landing_mobile` (Entrar `right ≤ innerWidth`; header sem scroll interno).
- **CA-4** → `test_manifest_e_metatags_pwa_presentes` (link/metatags via DOM + manifest válido `standalone`/ícones).
- **CA-5** → `test_alvos_de_toque_e_labels_ptbr_no_nav` (alvos ≥48px; labels pt-BR Início/Carteira/Perfil).

### Bloqueios encontrados

Nenhum.

### Links de evidência

- E2E do épico: `tests/Browser/CascaMobileTest.php` — **5/5 verde** (93 asserções).
- Suíte completa sem regressão: **Pest 303/303**; **Dusk 87/87** (após +5 desta estória).
- Verificação visual (device viewport, screenshots): landing 360px com "Entrar" inteiro e header sem
  scroll; Carteira/Perfil 390px com `nav.bottom` fixo e seção ativa em verde. (screenshots efêmeras, não versionadas)
- Decisão registrada: `decisions/idr/IDR-012-casca-mobile-dvh-e-pwa-instalavel.md`.
- Arquivos: `AppLayout.jsx`, `NavBottom.jsx`, `NavBar.jsx`, `PublicLayout.jsx`, `CtaLink.jsx`,
  `BrandLockup.jsx`, `app.blade.php`, `public/manifest.json`, `public/icons/*`.
