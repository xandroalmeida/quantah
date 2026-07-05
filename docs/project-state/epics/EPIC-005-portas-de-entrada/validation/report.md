---
epic_id: EPIC-005
type: validation-report
validated_at: 2026-07-05
validated_by: validador (sessão story-028)
verdict: approved
checklist_source: epics/EPIC-005-portas-de-entrada/validation/checklist.md
deployed_sha: da7e2a0
head_sha_local: b098baf
homolog_url: https://quantah-homolog.34.39.229.117.sslip.io
---

# Relatório de Validação — EPIC-005 (Portas de entrada)

## TL;DR

> **Veredito: APPROVED.**
> **Contagem**: 33 `pass`, 4 `pass com ressalva`, 0 `fail` (0 bloqueantes, 0 não-bloqueantes), 1 `n/a` justificado.
> **Bloqueantes**: nenhum. As duas landings (B2C `/`, B2B `/intelligence`) e a lista de leads no Backoffice
> estão vivas em homologação no sha `da7e2a0`; o loop lead→captura→Backoffice foi percorrido de 1ª mão;
> suíte 295/295 verde (cobertura 95,2%, núcleos do lead e da guarda a 100%); CI verde e deploy de homologação
> automatizado. Ressalvas factuais registradas abaixo — nenhuma bloqueia o épico.

---

## Resumo executivo

O EPIC-005 entrega as portas de entrada públicas do Quantah: a **landing B2C** ("Cada nota conta.") na raiz `/`
com CTA para o login (EPIC-004) e CTA para o B2B; a **landing B2B** Quantah Intelligence ("Do cupom ao insight.")
com captação de lead (nome, e-mail, empresa) validada, persistida e deduplicada; e a **lista de leads no
Backoffice**, sob o papel operacional (RBAC ADR-009).

Verifiquei de 1ª mão sobre o sha deployado em homologação (`da7e2a0`, run de deploy 28737220705 = success): as
três superfícies respondem, o fluxo completo funciona (lead capturado na landing B2B aparece na lista do
Backoffice sob o papel operacional; anônimo é barrado), e o tratamento de inválido e duplicado se comporta
conforme os CAs. A suíte automatizada (295 Feature/Unit verdes, 1264 asserções; E2E Dusk das telas do épico
11/11 verdes) cobre cada CA com asserção real; a cobertura geral é 95,2% e os núcleos exigidos (`CapturarLead`,
controllers do lead, guarda do Backoffice, `Models/Lead`) estão a 100% (≥98%). pt-BR, a11y AA medida em browser
real (contraste ≥4.5:1 e foco por teclado), só tokens do DS, e LGPD (PII restrita ao operador, duplicado sem
vazar, sem PII em log, aviso de privacidade sem checkbox) estão cumpridos. Não encontrei nenhum `fail`. As
ressalvas são factuais e não bloqueiam.

---

## Checklist preenchido

### Bloco 1 — Critérios de aceite das estórias

| Item | Status | Evidência |
|---|---|---|
| 1.1 — 025/026/027 `done` no index | ✅ | `index.json` stories: STORY-025/026/027 `status: done` (Apêndice A.1) |
| 1.2 — B2C rota pública sem redirect | ✅ | `Feature/Landing/LandingB2CTest` (served_at_root, is_public, visible_to_authenticated, 404); homolog `/`→200 |
| 1.3 — B2C DS + pt-BR + sem scaffolding | ✅⚠️ | `Browser/LandingB2CTest` (assertSee "Cada nota conta.", assertDontSee Laravel/Hello); `NoRawColorInLandingTest`; ressalva R2 (chaves i18n em inglês no payload) |
| 1.4 — B2C CTA→login | ✅ | `Browser/LandingB2CTest::test_visitante_mobile_...leva_ao_login` (click→waitForLocation /login); `test_cta_destinations_exist` |
| 1.5 — B2C CTA→B2B | ✅ | `Browser/LandingB2CTest::test_cta_para_empresas_leva_ao_b2b` (→ /intelligence, "Do cupom ao insight.") |
| 1.6 — B2C mobile-first + a11y + E2E | ✅ | Dusk viewport 390×844; `Browser/ThemeTest` (contraste AA, foco por Tab) — A.4 |
| 1.7 — B2B rota pública, DS, pt-BR | ✅ | `Feature/Landing/LandingB2BTest::test_landing_b2b_publica_via_inertia`; homolog `/intelligence`→200 |
| 1.8 — B2B lead válido persiste + confirma | ✅ | `LandingB2BTest::test_captura_lead_valido_persiste_e_redireciona` (assertDatabaseHas + redirect /obrigado); homolog A.3 |
| 1.9 — B2B inválido bloqueia sem persistir | ✅ | `::test_captura_invalida_bloqueia_por_campo_sem_persistir` (assertSessionHasErrors + assertDatabaseCount 0); homolog A.3 |
| 1.10 — B2B duplicado idempotente sem vazar | ✅ | `::test_captura_duplicada_idempotente` + `Unit/Lead/CapturarLeadTest` (mesmo id, count 1, não sobrescreve); homolog A.3/A.5 |
| 1.11 — B2B mobile-first + a11y + E2E desvio | ✅ | `Browser/LandingB2BTest` (390×844; feliz→"Recebemos seu contato."; desvio e-mail inválido→erro por campo) |
| 1.12 — Backoffice lista sob papel operacional | ✅ | `Feature/Backoffice/LeadsTest::test_operador_ve_lista_de_leads_com_campos`; homolog A.5 (operador vê 200) |
| 1.13 — sem papel barrado | ✅ | `LeadsTest` (coletador→403; anônimo→redirect /login); `SegmentacaoAreasTest`; homolog `/backoffice/leads` anônimo→302 |
| 1.14 — estado vazio + ordenação determinística | ✅ | `LeadsTest::test_estado_vazio`, `::test_ordenacao_mais_recentes_primeiro` (Novo antes de Antigo) |
| 1.15 — E2E operador vê lead / sem papel barrado | ✅ | `Browser/BackofficeLeadsTest` (2 cenários, verdes) |
| 1.16 — cada CA por teste com asserção real | ✅ | Leitura direta dos arquivos de teste — asserções de estado/DB/rota reais, sem skip (A.2) |

### Bloco 2 — Cobertura de testes

| Item | Status | Evidência |
|---|---|---|
| 2.1 — Geral ≥ 80% | ✅ | **Total 95,2%** — `sail artisan test --coverage` no sha em validação (A.2) |
| 2.2 — Núcleo do lead ≥ 98% | ✅ | `Domain/Lead/CapturarLead` **100%**, `CapturarLeadRequest` **100%**, `Intelligence/LeadController` **100%**, `Models/Lead` **100%** |
| 2.3 — Guarda de acesso ≥ 98% | ✅ | `Backoffice/LeadsController` **100%**; guarda exercida por `LeadsTest`/`SegmentacaoAreasTest` (403/redirect + `test_toda_rota_backoffice_esta_atras_do_guard`) |
| 2.4 — E2E browser real nos 3 fluxos | ✅ | Dusk `LandingB2CTest`+`LandingB2BTest`+`BackofficeLeadsTest`+`ThemeTest` = **11/11 verdes** (A.4) |
| 2.5 — feliz + inválido + duplicado + bordas | ✅ | `CapturarLeadTest` (feliz, normalização, idempotente, caixa/espaço, não-sobrescreve); `LandingB2BTest` (inválido, duplicado) |

### Bloco 3 — Automação

| Item | Status | Evidência |
|---|---|---|
| 3.1 — Setup local um comando | ✅⚠️ | `app/Makefile` alvo `up` (clone limpo → app+Postgres+seed); stack Sail rodando. Ressalva R3 (não re-testado em máquina limpa nesta sessão) |
| 3.2 — CI verde na main | ✅ | `gh run list`: 5 últimos runs `success` em `main`; STORY-027 done run 28737220705 success (A.6) |
| 3.3 — Deploy homolog automatizado | ✅ | `.github/workflows/ci-cd.yml` job `deploy` (needs [tests, dusk], só main) + smoke test em `/up`; homolog vivo |
| 3.4 — Deploy produção automatizado | 🚫 n/a | Não há ambiente de produção provisionado nesta fase MVP; o alvo de entrega do épico é homologação. Fora do escopo do EPIC-005 (não introduz nem regride infra de prod) — A.7 |
| 3.5 — Provisionamento IaC | ✅ | `infra/gcp/provision.sh`, `infra/gcp/startup.sh`, `infra/docker-compose.prod.yml`, `infra/Caddyfile` versionados |

### Bloco 4 — Funcionalidade observável (1ª mão em homologação)

| Item | Status | Evidência |
|---|---|---|
| 4.1 — Superfícies acessíveis no sha deployado | ✅ | `/`→200, `/login`→200, `/intelligence`→200, `/intelligence/obrigado`→200, `/privacidade`→200, `/backoffice/leads`→302 (A.3) |
| 4.2 — Fluxo E2E de 1ª mão | ✅ | Lead capturado na landing B2B (`validacao-story028-…@quantah.test`) aparece na lista do Backoffice sob operador `test@example.com`; duplicado não criou 2º; inválido bloqueado; anônimo barrado (A.3, A.5) |
| 4.3 — Saúde/observabilidade básica | ✅⚠️ | Health endpoint `/up`→200 (usado no smoke do deploy); `Interno/MetricasController` presente. Ressalva R4 (sem dashboard dedicado nesta fase) |

### Bloco 5 — Qualidade transversal

| Item | Status | Evidência |
|---|---|---|
| 5.1 — pt-BR nas superfícies | ✅⚠️ | Copy visível pt-BR ("Cada nota conta.", "Do cupom ao insight.", "Recebemos seu contato.", "Use um e-mail válido", 403 "Acesso restrito"); `assertDontSee('This action is unauthorized')`. Ressalva R2 |
| 5.2 — a11y AA + mobile-first | ✅ | `Browser/ThemeTest`: contraste on-primary/primary **≥4.5:1** (rgb real), foco por Tab com ring/outline; viewports 390×844; erros com `role=alert`, labels associados (`Field`) |
| 5.3 — Só tokens do DS | ✅ | `NoRawColorInLandingTest` (sem hex cru, sem `[#..]`, sem paleta neutra Tailwind) nas 7 fontes públicas do épico; `ThemeTest` confirma tokens (rgb 159,232,112; radius 24px) |
| 5.4 — LGPD / PII do lead | ✅ | PII restrita ao operador (anônimo→302, coletador→403, operador→200); duplicado idempotente sem revelar terceiro; **zero `Log::` no código do épico**; aviso "Política de Privacidade" → `/privacidade`, sem checkbox (DDR-006) |
| 5.5 — Migração reversível e testada | ✅ | `2026_07_05_000001_create_leads_table.php` com `down()` (dropIfExists); aplicada no deploy de homolog + `migrate --seed` no CI |
| 5.6 — Sem segredo versionado | ✅ | `app/.env`, `.env.dusk.local`, `.env.production` não rastreados (git ls-files vazio) e no `.gitignore`; segredos de deploy injetados via GitHub Secrets no workflow |

### Bloco 6 — Documentação e decisões

| Item | Status | Evidência |
|---|---|---|
| 6.1 — Decisões indexadas | ✅ | `index.json` referencia DDR-005 (×5), DDR-006 (×3), ADR-009, ADR-011, PDR-003 (×8), IDR-010; arquivos `decisions/ddr/DDR-005…`, `DDR-006…` existem |
| 6.2 — Notas do agente preenchidas | ✅ | STORY-025/026/027 com "Notas do agente" completas (sync, decisões, descobertas, cobertura, links de evidência) |
| 6.3 — design.screens consistentes | ✅ | `SCREEN-STORY-025-landing-b2c` e `SCREEN-STORY-026-landing-b2b-quantah-intelligence` no `index.json` |

---

## Fails identificados

### Bloqueantes

Nenhum.

### Não-bloqueantes

Nenhum.

> Nenhum fail: todos os itens do checklist são `pass`, `pass com ressalva` ou `n/a` justificado.

---

## Passes com ressalva

- **R2 — Bloco 1.3 / 5.1 (i18n)**: o payload Inertia entregue ao cliente contém chaves do dicionário i18n em
  **inglês** (source-keys: `"Every receipt counts.":"Cada nota conta."`, `"Hello!":"Olá!"`,
  `"This action is unauthorized"`), pois o mecanismo de i18n (ADR-011/IDR-010) usa a string em inglês como
  chave. **A copy renderizada/visível é 100% pt-BR** — as strings em inglês são chaves de tradução no
  dicionário, não texto exibido ao usuário. Registro factual; não é resíduo de scaffolding nem violação de pt-BR.
- **R3 — Bloco 3.1 (setup)**: o setup de um comando (`make up`) está presente e documentado, e o stack Sail
  está no ar; **não** re-executei o `make up` em máquina limpa nesta sessão de validação. Evidência do alvo do
  Makefile + containers rodando.
- **R4 — Bloco 4.3 (observabilidade)**: saúde básica coletada via health endpoint `/up` (usado no smoke do
  deploy) e há `Interno/MetricasController`; **não** há dashboard de observabilidade dedicado nesta fase MVP.
- **R1 — Bloco 2.4 (E2E local — flake de cold-start)**: na 1ª corrida a frio do conjunto Dusk, o primeiro
  teste (`LandingB2CTest::test_visitante_mobile_...`) falhou por timeout de 10s aguardando o seletor do hero;
  re-executado **isolado 4×, passou 4/4** (~0,9s cada); corrida aquecida das 4 telas do épico **11/11 verde**.
  Diagnóstico: cold-start local de asset/JIT no primeiro teste de browser, não regressão — o componente contém
  o `data-testid` e o CI reportou E2E verde. Observação de ambiente da validação; não é flake introduzido pelo
  épico. (A.4)

---

## Limitações da validação

- **Sha deployado sem endpoint de versão**: homologação não expõe o sha em runtime; identifiquei o sha
  deployado (`da7e2a0`) pelo último run de deploy bem-sucedido do CI (28737220705, "STORY-027 done"). O commit
  local `HEAD` é `b098baf` (docs de fechamento do EPIC-004, sem efeito de runtime sobre o EPIC-005).
- **Estado do épico no momento da validação**: o `EPIC-005` estava em `status: ready` (não `in_review`) e a
  própria STORY-028 em `draft` no `index.json` quando a validação foi executada, por instrução direta.
  Todas as estórias de dependência (025/026/027) estavam `done`. Fato de estado do índice; a transição de
  status do épico é decisão do PO.
- **Verificação do operador em homologação**: autentiquei-me como o usuário semeado `test@example.com` (papel
  operacional) para confirmar a lista de leads; a validação do lado do operador depende desse seed.
- **Dado de teste criado em homologação**: a verificação de 1ª mão da captação criou o lead
  `validacao-story028-1783247428@quantah.test` (nome "Validador Story028") em homologação — inerente ao
  exercício do fluxo; registrado aqui para rastreabilidade.

---

## Apêndice A — Evidências detalhadas

### A.1 — Estórias `done` no índice
`docs/project-state/index.json`: STORY-025 `status: done` (owner claude-story025), STORY-026 `done`
(claude-story026), STORY-027 `done` (claude-story027). EPIC-005 `story_ids` = [025, 026, 027, 028];
`validation_report` estava `null` antes desta validação.

### A.2 — Suíte + cobertura (sha em validação)
Comando: `./vendor/bin/sail artisan test --coverage` (Laravel Sail, container `quantah`).
Resultado: `{"tool":"phpunit","result":"passed","tests":295,"passed":295,"assertions":1264}`.
Cobertura **Total: 95,2%**. Linhas de núcleo do épico:
`Domain/Lead/CapturarLead .. 100.0%`, `Http/Requests/CapturarLeadRequest .. 100.0%`,
`Http/Controllers/Intelligence/LeadController .. 100.0%`, `Http/Controllers/Backoffice/LeadsController .. 100.0%`,
`Models/Lead .. 100.0%`. Leitura direta dos arquivos de teste confirma asserções reais (estado de DB,
rota/redirect, contagem, ordenação) — não apenas nomes; nenhum teste em skip.

### A.3 — Homologação: endpoints + captação de lead (1ª mão)
URL base: `https://quantah-homolog.34.39.229.117.sslip.io`.
Endpoints: `/`→200, `/login`→200, `/intelligence`→200, `/intelligence/obrigado`→200, `/privacidade`→200,
`/backoffice/leads`→**302** (anônimo barrado, redirect ao login), `/up`→200.
Captação via POST `/intelligence/leads` (com XSRF-TOKEN do cookie):
- **Válido** (`validacao-story028-…@quantah.test`, "Validador Story028", "Quantah QA") → **302 → /intelligence/obrigado**.
- **Duplicado** (mesmo e-mail, "Outro Nome"/"Outra Empresa") → **302 → /intelligence/obrigado** (mesma confirmação, sem vazar).
- **Inválido** (nome vazio, `nao-e-email`) → **302 → /intelligence** (bloqueado, sem persistir).

### A.4 — E2E em browser real (Dusk)
`./vendor/bin/sail artisan dusk` sobre `LandingB2CTest`, `LandingB2BTest`, `BackofficeLeadsTest`, `ThemeTest`
(corrida aquecida): `{"result":"passed","tests":11,"passed":11,"assertions":32}`.
`ThemeTest` mede em browser real: contraste on-primary/primary **≥4.5:1** (rgb real 159,232,112 sobre 14,15,12),
título Inter 900, radius 24px, e **foco por teclado** (Tab alcança `landing-b2c-cta-entrar` com ring/outline visível).
Flake de cold-start na 1ª corrida documentado em R1 (0/4 na re-execução isolada).

### A.5 — Homologação: lead no Backoffice sob papel operacional (1ª mão)
Login como `test@example.com` (papel operacional) → 302 `/dashboard` (auth OK). GET `/backoffice/leads`
(navegação normal) → **200**, componente `Backoffice/Leads/Index`. O lead capturado em A.3
(`validacao-story028-1783247428@quantah.test`, "Validador Story028") **aparece na lista**. O nome exibido é o
**original** ("Validador Story028"), não "Outro Nome" do reenvio duplicado — confirma idempotência persistida
e não-sobrescrita. (Anônimo em `/backoffice/leads` → 302 `/login`, A.3.)

### A.6 — CI/CD
`gh run list`: 5 runs mais recentes em `main` = `success`. Deploy do épico: run **28737220705**
("chore(STORY-027): done", sha `da7e2a0`) — `conclusion: success`, workflow CI/CD.
`.github/workflows/ci-cd.yml`: jobs `tests`, `dusk`, `deploy` (Deploy homologação, `needs: [tests, dusk]`, só
`main`), com "Smoke test da homologação" em `/up`.

### A.7 — IaC e ambientes
`infra/gcp/provision.sh` + `infra/gcp/startup.sh` (provisionamento), `infra/docker-compose.prod.yml` +
`infra/Caddyfile` (composição/entrada) versionados. Não há job/ambiente de produção separado — homologação é o
alvo desta fase (item 3.4 = n/a).

---

## Apêndice B — Arquivos anexados

Sem anexos binários. Evidência textual reproduzível a partir dos comandos e caminhos citados em A.1–A.7
(suíte/cobertura, `gh run list`, curls de homologação, arquivos de teste e de infra), sobre o sha `da7e2a0`.

---

## Histórico

- 2026-07-05 — relatório inicial submetido por validador (sessão story-028). Veredito: **APPROVED**.
