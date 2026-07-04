---
story_id: STORY-020
slug: fundacao-i18n-e-varredura-ptbr
title: Fundação de i18n (pt-BR) e varredura das superfícies existentes
epic_id: EPIC-004
sprint_id: null
type: enablement
target_role: programador
requires_design: false
design_screen_id: null
status: in_review
owner_agent: programador
created_at: 2026-07-04
updated_at: 2026-07-04
estimated_session_size: M
---

# STORY-020 — Fundação de i18n e varredura pt-BR

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Esta é uma estória
> **enablement** (horizontal, justificada): estabelece o mecanismo de localização e paga a dívida de
> idioma das telas atuais, destravando as telas novas do épico.

## Contexto (por que esta estória existe)

O produto tem strings de scaffolding em inglês (login/registro do Breeze, "Whoops!", "Log in",
"Remember me") e o mecanismo de localização ainda não está estabelecido. O requisito transversal pt-BR
(quality-standards §5.1) exige **todo texto visível em português do Brasil**, sem hardcode fora do
mecanismo de i18n. Estabelecer isso agora faz as telas novas (login de marca, áreas) já nascerem em
pt-BR.

**Por que é enablement (horizontal):** o mecanismo de i18n é infraestrutura transversal; sozinho não é
um fluxo de usuário. Justificativa: destrava STORY-021 (login de marca em pt-BR) e todas as telas
seguintes, e entrega valor observável imediato — as telas atuais em pt-BR.

- Épico: `epics/EPIC-004-acesso-e-areas/epic.md`
- Ler antes: ADR de i18n produzido na STORY-019; `docs/project-state/design/system/voice-and-tone.md`
  (voz/tom); quality-standards §5.1.

## O quê (objetivo desta estória)

Estabelecer o mecanismo de i18n (pt-BR) conforme o ADR e **varrer todas as superfícies já existentes**
para pt-BR, sem resíduo de inglês nem strings hardcoded fora do mecanismo.

## Por quê (valor para o usuário)

O Coletador (e o operador) veem um produto coeso em português — condição para parecer produto, não
scaffolding — e sustenta a métrica de qualidade da onda (100% pt-BR).

## Critérios de aceite

- [ ] **CA-1:** Mecanismo de i18n do ADR configurado; existe um único lugar canônico para as strings de
      interface (nada de texto de UI hardcoded fora dele nas telas tocadas).
- [ ] **CA-2:** Superfícies existentes **varridas para pt-BR**, sem resíduo de inglês: telas de auth do
      Breeze (login, registro, recuperação/reset de senha, verificação de e-mail), a página `/dashboard`
      atual, vitrine `/ds`, carteira, e o backoffice de saques.
- [ ] **CA-3:** Mensagens de validação e erro exibidas ao usuário estão em pt-BR (incl. as do
      back-end/validações do Breeze).
- [ ] **CA-4:** Formatos brasileiros aplicados onde há moeda/data: `R$ 1.234,56`, `dd/mm/aaaa`, fuso
      `America/Sao_Paulo`.
- [ ] **CA-5:** E2E cobre ao menos uma asserção de texto em pt-BR numa superfície pública (ex.: `/login`
      exibe rótulos em português) e a ausência de uma string de scaffolding conhecida (ex.: não há
      "Log in"/"Remember me"/"Whoops!").

## Fora de escopo

- Rebrand visual da tela de login (substituir o logo do Laravel, DS) — isso é a STORY-021.
- Tradução de conteúdo das landing pages (não existem ainda — EPIC-005).
- i18n multi-idioma (só pt-BR nesta fase).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`, em particular **§5.1 (pt-BR)**. Cobertura ≥ 80%
no código novo; E2E cobrindo a asserção de idioma (CA-5). Sem código não testado.

## Dependências

- **Bloqueada por:** STORY-019 (ADR de i18n).
- **Bloqueia:** STORY-021 (login de marca deve usar o mecanismo).
- **Pré-requisitos de ambiente:** homologação operante (herdada da Onda 1).

## Decisões já tomadas (não as reabra)

- ADR de i18n (STORY-019) — mecanismo de localização e formatos. Quality-standards §5.1 — o resultado exigido.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam; unitários + E2E (CA-5) verdes; cobertura exigida atingida.
- [ ] Pipeline verde; deploy de homologação verificado (telas atuais em pt-BR ao vivo).
- [ ] IDR registrado se houve decisão técnica relevante (ex.: organização das chaves de tradução).
- [ ] `index.json` = `done`; "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. Ao iniciar, marque `in_progress` e assuma a
estória no `index.json`. Decisões de baixo nível relevantes viram IDR.

## Notas do agente (preenchido durante/após execução)

### Plano (registrado antes de codar — 2026-07-04, programador)

**Documentos lidos:** STORY-020 inteira; ADR-011 (mecanismo i18n, `accepted`); ADR-010; quality-standards §5.1;
skill programador (TDD/gates); código atual (Breeze auth pages, AuthenticatedLayout, HandleInertiaRequests,
ExtratoCarteira/SaquesController — formatação).

**Entendimento consolidado:** o inglês está concentrado no **scaffold do Breeze** (11 arquivos: 6 telas Auth,
3 partials de Profile, Profile/Edit, Dashboard) + strings de nav no AuthenticatedLayout + `Head title`. As
superfícies de feature (Carteira, Coleta, Backoffice, Métricas) já nasceram em pt-BR. Moeda (`number_format(…,
',','.')`) e data (`d/m/Y`) já são BR; o **único gap de CA-4 é o fuso** — datetimes renderizam em UTC, não
`America/Sao_Paulo`.

**Mecanismo (ADR-011):** localização nativa do Laravel como **fonte única**. `laravel-lang/common` (dev) gerou
`lang/pt_BR/*.php` (validation/auth/passwords) + `lang/pt_BR.json` (262 chaves EN→pt-BR, ~13KB). Chaves = **string
fonte em inglês** (padrão JSON translations): `__('Log in')` no BE, `t('Log in')` no FE. `HandleInertiaRequests`
compartilha o JSON do locale ativo como prop `translations`; helper fino `t()` no React lê a prop (sem lib de i18n
de runtime — decisão de FE do ADR-011). `app.timezone` permanece **UTC** (persistência); SP é fuso de exibição.

**Plano (3–5 bullets):**
1. Config: `APP_LOCALE/APP_FAKER_LOCALE=pt_BR` (.env + phpunit.xml); `Carbon::setLocale('pt_BR')` no AppServiceProvider.
2. `App\Support\Formato` (moeda/moedaComSimbolo/data/dataHora com fuso SP) — extrai os 3 `number_format` duplicados (regra de 3) e resolve o fuso; unit-testado (CA-4).
3. `HandleInertiaRequests::share('translations')` + `resources/js/i18n.js` (`t()`), fiado no `app.jsx`.
4. Varredura pt-BR dos 11 arquivos do Breeze + nav do AuthenticatedLayout + `Head title` via `t()`/`__()`; corrigir `appName` fallback 'Laravel'→'Quantah'.
5. Testes: Unit `FormatoTest` (CA-4); Feature `LocalizacaoTest` (CA-1/CA-3: locale, resolução, validação pt-BR, prop compartilhada); E2E Dusk `I18nPtBrTest` (CA-2/CA-5: /login, /register, erro de credencial — pt-BR presente e sem "Log in"/"Remember me"/"Whoops").

**Mapa CA → testes:** CA-1 → LocalizacaoTest (locale/resolução/prop) · CA-2 → I18nPtBrTest (Dusk, telas varridas) ·
CA-3 → LocalizacaoTest (validação/credencial pt-BR) · CA-4 → FormatoTest (moeda/data/fuso) · CA-5 → I18nPtBrTest
(asserção pt-BR + ausência de scaffolding).

**Fora de escopo confirmado:** trocar o logo do Laravel (ApplicationLogo) e rebrand visual do login = STORY-021.

### Decisões tomadas
- **Mecanismo (ADR-011):** `laravel-lang/common` (dev) gera `lang/pt_BR/*.php` + `lang/pt_BR.json`;
  `app.locale=pt_BR` (config default + .env + phpunit). Chaves = **string-fonte em inglês** (`__()`/`t()`).
- **FE:** `HandleInertiaRequests` compartilha o dicionário do locale (prop `translations`); `resources/js/i18n.js`
  expõe `t()` lendo o mapa registrado no boot (`app.jsx`). Sem lib de i18n de runtime. `appName` fallback → Quantah.
- **Formatos (CA-4):** `App\Support\Formato` (moeda/data/dataHora) — unifica os `number_format` duplicados e
  aplica o fuso de **exibição** `America/Sao_Paulo` nos datetimes do backoffice. `app.timezone` segue **UTC**.
- **IDR-010** registra o padrão (chave=string-fonte, `t()` via prop, `laravel-lang`, `Formato`).

### Mapa CA → teste (final)
- **CA-1** (mecanismo/fonte única) → `tests/Feature/I18n/LocalizacaoTest.php`: `test_locale_ativo_e_ptbr`,
  `test_traducao_de_string_do_breeze_resolve_para_ptbr`, `test_chave_sem_traducao_retorna_a_propria_chave`,
  `test_login_compartilha_prop_translations`, `test_pagina_autenticada_tambem_compartilha_translations`.
- **CA-2** (varredura) → `tests/Browser/I18nPtBrTest.php`: `test_login_em_ptbr_sem_residuo_de_ingles`,
  `test_registro_em_ptbr` (+ inspeção: nenhum literal de UI fora de `t()` nas 11 telas do Breeze).
- **CA-3** (validação/credencial pt-BR) → `LocalizacaoTest`: `test_validacao_de_campo_obrigatorio_em_ptbr`,
  `test_credenciais_invalidas_em_ptbr`.
- **CA-4** (formatos BR) → `tests/Unit/Support/FormatoTest.php` (moeda: feliz/bordas/negativo; dataHora:
  conversão SP, virada de dia, null).
- **CA-5** (pt-BR + ausência de scaffolding) → `I18nPtBrTest`: `test_login_...` (assertSee pt-BR,
  assertDontSee 'Log in'/'Remember me'/'Password'/'Whoops') e `test_erro_de_credencial_em_ptbr`.

### Descobertas
- O inglês estava **restrito ao scaffold do Breeze** (11 arquivos + nav do AuthenticatedLayout + Head titles);
  as telas de feature já eram pt-BR. Moeda/data já eram BR — o único gap real de CA-4 era o **fuso**.
- Botões do DS usam `text-transform: uppercase` → o Selenium lê o texto em CAIXA ALTA. O E2E asserta rótulos
  (não transformados) para pt-BR e submete o form por seletor CSS, não por texto de botão. (registrado no IDR-010)
- `.env.dusk.local` pré-existente fixava `APP_LOCALE=en`; corrigido para `pt_BR` (senão o E2E roda em inglês).

### IDRs criados
- **IDR-010** — i18n: chaves por string-fonte, `t()` via prop do Inertia e `App\Support\Formato` —
  `decisions/idr/IDR-010-i18n-chaves-por-string-fonte-e-t-no-inertia.md` (`accepted`).

### Cobertura final
- Código novo: **`App\Support\Formato` 100% (5/5)**; **`HandleInertiaRequests` 95% (20/21)** — a única linha
  descoberta é o fallback defensivo para `lang/<locale>.json` ausente (não ocorre em prod). `AppServiceProvider`
  100%. `t()` (JS) coberto por **E2E Dusk** (sem runner JS — Vitest adiado, [[IDR-003]]).
- Suíte completa: **251 unit+feature verdes** (999 asserções); **55 E2E Dusk verdes** (197 asserções).

### Links de evidência
- Suíte unit+feature: `sail artisan test` → 251 passed. E2E: `sail artisan dusk` → 55 passed.
- E2E da estória: `sail artisan dusk --filter I18nPtBrTest` → 3 passed (16 asserções), red→green provado
  (red contra o build antigo em inglês; green após `npm run build`).
- Deploy homolog: CI/CD na main (`.github/workflows/ci-cd.yml`) + smoke `/up`; verificação de `/login` pt-BR ao vivo.
