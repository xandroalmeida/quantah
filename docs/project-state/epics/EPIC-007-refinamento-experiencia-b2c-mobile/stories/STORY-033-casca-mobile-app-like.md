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
status: draft
owner_agent: null
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

> _(a preencher)_

### Decisões tomadas
### Descobertas
### Bloqueios encontrados
### Links de evidência
