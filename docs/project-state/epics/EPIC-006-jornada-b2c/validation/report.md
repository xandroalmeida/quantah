---
epic_id: EPIC-006
type: validation-report
validated_at: 2026-07-05
validated_by: validador (sessão claude-story032)
verdict: approved
checklist_source: epics/EPIC-006-jornada-b2c/validation/checklist.md
deployed_sha: 905197d
head_sha_local: 905197d
homolog_url: https://quantah-homolog.34.39.229.117.sslip.io
---

# Relatório de Validação — EPIC-006 (Jornada do Coletador)

## TL;DR

> **Veredito: APPROVED.**
> **Contagem**: 32 `pass`, 6 `pass com ressalva`, 0 `fail` (0 bloqueantes, 0 não-bloqueantes), 2 `n/a` justificado.
> **Bloqueantes**: nenhum. A home-hub (`/inicio`) é o destino pós-login em homologação (sha `905197d`); a
> navegação coesa liga home ↔ coleta ↔ carteira/extrato ↔ saque com retorno consistente e **sem página
> genérica** (a antiga `/dashboard` responde **404**, sem logo do Laravel em nenhuma rota logada); o loop
> ponta a ponta (entrar → home → coletar → saldo atualiza → extrato → iniciar saque) passa em **E2E de
> browser real (mobile 390px)** sobre o sha deployado. Suíte 303/303 verde (cobertura geral 95,2%;
> `HomeController` e os núcleos `ExtratoCarteira`/`CreditarCashbackService` a 100%); CI verde e deploy de
> homologação automatizado. Ressalvas factuais abaixo — nenhuma bloqueia.

---

## Resumo executivo

O EPIC-006 costura a jornada B2C pós-login. Verifiquei sobre o sha deployado em homologação (`905197d`, run de
deploy 28745142487 = success):

- **STORY-029** — a home-hub é o **destino pós-login** (substitui a página genérica do scaffolding): mostra o
  **saldo** da carteira (card de marca, R$ formato BR) e o **CTA verde "Coletar cupom"**; anônimo é barrado.
- **STORY-030** — casca de navegação única (`AppLayout` / `pattern.app-shell`, **DDR-007**) com barra
  persistente nas 5 telas logadas → **retorno consistente** à home (coleta e saque deixaram de ser becos);
  **atalhos** Histórico (extrato) e Prêmios (saque) a **1 toque** da home; o scaffolding do Breeze
  (`AuthenticatedLayout` + logo do Laravel) foi removido da área logada; URL renomeada `/dashboard` → `/inicio`.
- **STORY-031** — o **loop de valor**: após uma coleta válida que gera crédito, o saldo da home reflete o
  novo crédito **sem novo login**, o crédito aparece no extrato e o saque é alcançável — coberto por E2E de
  browser real (mobile) ponta a ponta.

A suíte automatizada (303 Feature/Unit verdes, 1403 asserções; E2E Dusk das telas do épico verdes) cobre cada
CA com asserção real. Cobertura geral 95,2%; o código novo do épico (`HomeController`) está a 100% e os
núcleos tocados (`ExtratoCarteira` — leitura de saldo; `CreditarCashbackService` — crédito) a 100% (≥98%).
pt-BR, DS (verde como único accent; `card.feature-dark` pontual), mobile-first e a11y (contraste dos tokens,
foco por teclado, alvos ≥48px, `aria-current`/`aria-hidden`) verificados. Não encontrei nenhum `fail`.

---

## Checklist preenchido

### Bloco 1 — Critérios de aceite das estórias

| Item | Status | Evidência |
|---|---|---|
| 1.1 — 029/030/031 `done` no index | ✅ | `index.json` stories: STORY-029/030/031 `status: done` |
| 1.2 — 029/CA-1 destino pós-login = home-hub | ✅ | `Feature/Home/HomeHubTest::test_destino_pos_login_renderiza_home_hub` (component `Home/Hub`); `Browser/AcessoGoogleTest`/`ConfirmacaoEmailTest` (após login/verificação → "Seu saldo"); `Feature/Auth/*` redirect → `route('inicio')` |
| 1.3 — 029/CA-2 saldo real BR | ✅ | `Feature/Home/HomeHubTest` (`saldo.reais` 12,47 e 0,00 via `ExtratoCarteira`); `Browser/HomeHubTest` (R$ 12,47 / R$ 0,00) |
| 1.4 — 029/CA-3 CTA → /coletar | ✅ | `Browser/HomeHubTest::test_cta_coletar_leva_ao_fluxo_de_captura` (click → waitForLocation `/coletar`) |
| 1.5 — 029/CA-4 DS + pt-BR + sem logo Laravel | ✅⚠️ | `Browser/HomeHubTest` (assertDontSee "Você está logado!", `assertSourceMissing` viewBox `0 0 316 316`); i18n via `t()` (IDR-010); ressalva R6 (chaves i18n em inglês) |
| 1.6 — 029/CA-5 guarda de acesso | ✅ | `Feature/Home/HomeHubTest::test_home_hub_exige_autenticacao` (→ /login); homolog `/inicio` anônimo → 302 /login |
| 1.7 — 029/CA-6 a11y AA | ✅⚠️ | Componentes DS (foco `focus-visible`, `on-primary` sobre verde, alvos ≥48px); `brand.mark`/ícones `aria-hidden` (spec §6); ressalva R1 (sem auditoria axe automatizada) |
| 1.8 — 030/CA-1 extrato ≤2 toques | ✅ | `Browser/NavegacaoB2cTest::test_atalho_historico_abre_extrato_em_um_toque` (1 toque → `/carteira`) |
| 1.9 — 030/CA-2 saque ≤2 toques | ✅ | `Browser/NavegacaoB2cTest::test_atalho_premios_abre_saque_em_um_toque` (1 toque → `/carteira/saque`) |
| 1.10 — 030/CA-3 retorno consistente | ✅ | `Browser/NavegacaoB2cTest::test_retorno_a_home_de_coleta_e_de_saque` (de /coletar e /carteira/saque, "Início" → /inicio) |
| 1.11 — 030/CA-4 nenhuma rota logada genérica | ✅ | `Feature/Navegacao/CascaNavegacaoTest::test_rotas_logadas_renderizam_a_propria_tela` (cada rota → sua tela); `Browser/NavegacaoB2cTest::test_nenhuma_rota_logada_mostra_logo_do_laravel`; homolog `/dashboard` → 404 |
| 1.12 — 030/CA-5+CA-6 DS/mobile/pt-BR/a11y | ✅⚠️ | Casca `nav.bottom`/`nav.bar` do DS, `aria-current`, alvos ≥48px (`NavBottom`); Dusk 390px; ressalva R1 (a11y via DS+E2E, não axe) |
| 1.13 — 031/CA-1 saldo reflete coleta sem novo login | ✅ | `Feature/Jornada/JornadaContinuaTest::test_saldo_da_home_reflete_a_coleta_sem_novo_login` (0,00 → 4,89 na mesma sessão, caminho real `CreditarCashbackService`) |
| 1.14 — 031/CA-2 extrato mostra o crédito | ✅ | `Feature/Jornada/JornadaContinuaTest::test_extrato_mostra_o_credito_da_coleta`; `Browser/JornadaContinuaTest` (item "Cupom de R$ 4.890,00" / +R$ 4,89) |
| 1.15 — 031/CA-3 iniciar saque alcançável | ✅ | `Browser/JornadaContinuaTest` (atalho Prêmios → `/carteira/saque`, `screen-saque-submit` presente, saldo R$ 4,89) |
| 1.16 — 031/CA-4+CA-5 ≤2 toques + E2E ponta a ponta | ✅⚠️ | `Browser/JornadaContinuaTest::test_jornada_continua_do_coletador` (entrar→home→coletar→saldo→extrato→saque, 390px); ressalva R3 (coleta representada por cupom validado + crédito real; validação SEFAZ assíncrona fora do browser) |
| 1.17 — 031/CA-6 pt-BR + DS, sem genérica | ✅ | `Browser/JornadaContinuaTest` (saldo/extrato/saque em pt-BR); nenhuma etapa cai em página genérica (1.11) |
| 1.18 — cada CA por teste com asserção real | ✅ | Leitura direta dos arquivos: asserções de estado/DB/rota/DOM reais, sem `skip`, sem nome-só |

### Bloco 2 — Cobertura de testes

| Item | Status | Evidência |
|---|---|---|
| 2.1 — código novo ≥80% | ✅ | `--coverage`: **`HomeController` 100%** (PHP novo do épico); total 95,2%. Nav/casca é JS (sem runner JS, IDR-003) coberto por E2E |
| 2.2 — núcleo/regra ≥98% | ✅ | `ExtratoCarteira` **100%**, `CreditarCashbackService` **100%**, `CalculadoraCashback` 100%, `Support/Formato` 100% |
| 2.3 — E2E browser real (mobile) | ✅ | Dusk 390px: `HomeHubTest`, `NavegacaoB2cTest`, `JornadaContinuaTest` — job "E2E (Dusk): success" nos runs de deploy |
| 2.4 — além do caminho feliz | ✅ | Guarda de acesso, estado zero (home), retorno de coleta/saque, ausência de scaffolding, saldo 0 → crédito |

### Bloco 3 — Automação

| Item | Status | Evidência |
|---|---|---|
| 3.1 — setup local um comando | ✅ | `app/Makefile` (`make up`); herdado/validado no EPIC-000 |
| 3.2 — CI verde na main | ✅ | Runs 28745039020 (92e30cf) e 28745142487 (905197d): "Testes + build: success", "E2E (Dusk): success" |
| 3.3 — deploy homolog automatizado | ✅ | `.github/workflows/ci-cd.yml` job `deploy` (só na main, tudo verde); runs acima "Deploy homologação: success" |
| 3.4 — deploy produção automatizado | 🚫 n/a | n/a — nesta fase **MVP** não há ambiente de produção ativo; o pipeline entrega **homologação** automatizada. Deploy de produção será endereçado quando o ambiente existir (fora do escopo deste épico) |
| 3.5 — provisionamento IaC | ✅ | `infra/docker-compose.prod.yml` + `infra/Caddyfile` versionados; deploy por `scp`/`ssh` no pipeline, sem cliques |

### Bloco 4 — Funcionalidade observável (1ª mão em homologação)

| Item | Status | Evidência |
|---|---|---|
| 4.1 — telas da jornada acessíveis no sha deployado | ✅ | homolog `905197d`: `/up` 200, `/` 200, `/login` branded (Quantah, sem logo Laravel); rotas logadas respondem (302 → /login para anônimo) |
| 4.2 — jornada ponta a ponta em browser real | ✅⚠️ | `Browser/JornadaContinuaTest` (real browser, mobile) verde sobre o sha deployado; ressalva R2 (o passo-a-passo autenticado foi o E2E automatizado, não um login manual meu — sem credencial de Coletador semeada em homolog) |
| 4.3 — rota genérica antiga removida; logado sem auth barrado | ✅ | homolog `/dashboard` → 404; `/inicio`,`/coletar`,`/carteira`,`/carteira/saque`,`/profile` anônimos → 302 /login |
| 4.4 — saúde básica coletada | ✅ | `/up` → 200 (health endpoint do framework) em homologação |

### Bloco 5 — Qualidade transversal

| Item | Status | Evidência |
|---|---|---|
| 5.1 — pt-BR §5.1 | ✅ | i18n `t()`/`lang/pt_BR.json` (IDR-010); Dusk asserta "Seu saldo"/"Olá, Ana"/"Cada nota conta."/"Cupom de R$…"; sem inglês de scaffolding (assertDontSee "Você está logado!") |
| 5.2 — a11y AA + mobile-first | ✅⚠️ | Tokens de contraste do DS (verde `#9fe870` sobre ink `#0e0f0c`), foco `focus-visible`, alvos ≥48px, `aria-current`/`aria-hidden`; Dusk 390px; ressalva R1 (sem axe/Lighthouse automatizado) |
| 5.3 — só tokens do DS | ✅⚠️ | Home/casca compõem tokens/componentes do DS (verde único accent); ressalva R5 (partials do Perfil mantêm estilo Breeze cinza dentro da casca — logo Laravel removido; restyle é dívida fora do escopo de nav) |
| 5.4 — LGPD §4 | ✅ | Épico **não coleta dado pessoal novo** (composição de telas existentes: saldo/extrato/saque do EPIC-003); nenhum novo tratamento de PII |
| 5.5 — migração reversível | 🚫 n/a | n/a — EPIC-006 **não introduz migração** de banco (composição de telas + renomeação de rota; reusa o esquema de EPIC-002/003). Nada a reverter |
| 5.6 — sem segredo versionado | ✅ | Diff do épico revisado: só código/testes/docs/tokens; nenhum segredo (chaves/env) adicionado |

### Bloco 6 — Documentação e decisões

| Item | Status | Evidência |
|---|---|---|
| 6.1 — decisões indexadas | ✅ | `index.json` `decisions`: **DDR-007** `accepted` (approved_by Alexandro), **IDR-011** `accepted` (atualizado com o rename); DDR-004/005, IDR-010, ADR-010/011, PDR-003 aplicáveis presentes |
| 6.2 — notas do agente preenchidas | ✅ | STORY-029/030/031 com "Notas do agente" (decisões, descobertas, cobertura, evidência) preenchidas |
| 6.3 — design.screens[] consistentes | ✅ | `SCREEN-STORY-029-home-hub-coletador` e `SCREEN-STORY-030-navegacao-b2c` presentes, `status: shipped`, com specs + protótipos validados 2026-07-05 |

---

## Ressalvas / limitações (factuais — nenhuma bloqueia)

- **R1 (a11y — cobertura da verificação):** a a11y AA foi verificada pelos **tokens de contraste do DS**
  (garantia de ≥4.5:1 nos pares usados), pela estrutura semântica (`aria-current`, `aria-hidden`, rótulos +
  ícone) e pelos **alvos ≥48px** e foco visível dos componentes DS, além do E2E mobile. **Não** rodei uma
  auditoria automatizada (axe/Lighthouse) nesta validação. Suficiente para `pass`; registrado para o PO.
- **R2 (jornada 1ª mão):** o passo-a-passo **autenticado** (login → home → coletar → saldo → extrato → saque)
  foi verificado pelo **E2E Dusk em browser real** (mobile 390px) rodando **verde sobre o sha deployado**, e
  não por um login manual meu em homologação — não havia credencial de Coletador semeada disponível ao
  validador. Verifiquei manualmente as superfícies públicas e as guardas de todas as rotas logadas.
- **R3 (representação da coleta no E2E):** o "coletar → saldo atualiza" no E2E representa a **validação
  assíncrona** do cupom (SEFAZ, EPIC-002) por um cupom já `validado` + o **serviço real de crédito** do
  EPIC-003; a coleta pela UI para em `pendente` (validação assíncrona fora do browser — mesma limitação do
  `ColetaCapturaTest` do EPIC-002). O "coletar a partir da home" (CTA → `/coletar`, 1 toque) é exercido de
  verdade. Documentado nas Notas da STORY-031.
- **R4 (cobertura de controllers Breeze pré-existentes):** `EmailVerificationNotificationController` 0% e
  `EmailVerificationPromptController` 66,7% — controllers **do EPIC-004** (Breeze); o EPIC-006 apenas trocou
  a string `route('dashboard')` → `route('inicio')` neles, **sem adicionar lógica**. Não são lacunas novas do
  épico (o código novo, `HomeController`, está a 100%).
- **R5 (Perfil):** os formulários do Perfil (partials do Breeze) seguem com estilo cinza dentro da casca DS; o
  **logo do Laravel foi removido** (CA-4 cumprido) e a navegação/retorno funcionam. O restyle completo dos
  partials ao DS é dívida **fora do escopo** da STORY-030 (navegação).
- **R6 (chaves i18n em inglês):** as strings novas usam **chave = string-fonte em inglês** em
  `lang/pt_BR.json` (convenção do projeto, IDR-010). O texto renderizado é 100% pt-BR.

---

## Apêndice — comandos e evidências

- **Suíte:** `cd app && make test` → 303 passed, 1403 assertions (Unit+Feature).
- **Cobertura:** `sail artisan test --coverage` → Total 95,2%; `HomeController` 100%; `ExtratoCarteira` 100%;
  `CreditarCashbackService` 100%; `Support/Formato` 100%.
- **E2E:** `cd app && make e2e` (Laravel Dusk, Chrome via Selenium, viewport 390px); job "E2E (Dusk)" verde
  nos runs de deploy 28745039020 e 28745142487.
- **Homologação (`905197d`):** `/up` 200 · `/` 200 · `/login` branded (sem viewBox `0 0 316 316`) ·
  `/inicio` anônimo → 302 `/login` · `/dashboard` → **404** · `/coletar`,`/carteira`,`/carteira/saque`,
  `/profile` anônimos → 302 `/login`.
- **CI/CD:** `.github/workflows/ci-cd.yml` — Testes+build, E2E Dusk, Deploy homologação (main, gated por
  tudo verde). Runs 28745039020 e 28745142487 = success.

> **Veredito final: APPROVED.** Zero `fail`. As 6 ressalvas são factuais e não bloqueiam. A transição de
> status do épico (`in_review → done`) é decisão do PO — não alterada por este relatório.
