---
epic_id: EPIC-004
type: validation-report
validated_at: 2026-07-05
validated_by: validador (sessão 2026-07-05)
verdict: approved_with_pending  # approved | rejected | approved_with_pending
checklist_source: epics/EPIC-004-acesso-e-areas/validation/checklist.md
sha_validado: e1d97f00231932af5ce49963f39d4a327f5aa84b
---

# Relatório de Validação — EPIC-004 (Acesso e áreas)

## TL;DR

> **Veredito**: **APPROVED com pendências**.
> **Contagem**: 18 pass, 6 pass com ressalva, 1 fail (0 bloqueante, 1 não-bloqueante), 2 n/a justificados.
> **Não-bloqueante (fato)**: o pipeline de CI não possui etapa de análise de dependências vulneráveis nem
> de detecção de segredos (quality-standards §2.2/§4); nenhum segredo versionado nem vulnerabilidade crítica
> foi observado por verificação manual.

---

## Resumo executivo

O EPIC-004 dá ao Coletador uma porta de entrada de marca (Google ou e-mail/senha), segmenta os três públicos
(B2C / B2B reservado / Backoffice) e estabelece o mecanismo de i18n com o produto em pt-BR. A validação foi
feita de 1ª mão sobre o sha `e1d97f0` (HEAD da `main`, mesmo sha deployado em homologação): suíte completa
executada localmente (**277 unit+feature, 1100 asserções, 0 falhas; cobertura global 95,0%**), E2E em browser
real via Dusk (**69/69 na reexecução**), CI verde na `main` (run `28727597678`), e smoke HTTP direto contra
`https://quantah-homolog.34.39.229.117.sslip.io` (login/áreas acessíveis, barreira do Backoffice ativa). Todos
os CAs das cinco estórias de implementação têm teste com asserção real e sem `skip`; o núcleo de contas
(resolução Google) e de autorização (Gate `operar-saques`) está a 100%.

Foram observadas: uma pendência não-bloqueante (ausência de scanner de dependências/segredos no CI), um flake
de ordenação intermitente na suíte Dusk local (o cenário funciona — passou 5/5 isolado e 69/69 na reexecução),
e ressalvas sobre cobertura de ramos de scaffolding do Breeze fora do núcleo, observabilidade e ausência de spec
LGPD consolidada. Nenhum item bloqueante. Detalhes e evidência por bloco abaixo.

---

## Checklist preenchido

### Bloco 1 — Critérios de aceite das estórias

| Item | Status | Evidência |
|---|---|---|
| 1.1 — STORY-019..023 `done` no `index.json` | ✅ | `index.json` stories[]: 019,020,021,022,023 = `done`. STORY-024 = `ready` (esta validação). |
| 1.2 — STORY-020 CA-1..5 com teste | ✅ | `Feature/I18n/LocalizacaoTest`, `Unit/Support/FormatoTest` (Formato 100%), E2E `Browser/I18nPtBrTest` (3). Ver A.1. |
| 1.3 — STORY-021 CA-1..6 com teste | ✅ | `Browser/AcessoColetadorTest` (6), `Feature/Auth/RegistrationTest`, `PasswordResetTest`. Ver A.1. |
| 1.4 — STORY-022 CA-1..5 com teste | ✅ | `Feature/Auth/GoogleAccountResolutionTest` (6 ramos), `GoogleLoginControllerTest`, E2E `Browser/AcessoGoogleTest` (3). Ver A.1. |
| 1.5 — STORY-023 CA-1..5 com teste | ✅ | `Feature/Acesso/SegmentacaoAreasTest` (8), E2E `Browser/SegmentacaoAreasTest` (3). Ver A.2. |
| 1.6 — Testes cobrem o CA de fato, sem skip | ✅ | Amostragem de asserções reais (A.2); `grep markTestSkipped/Incomplete tests/` → zero. |

### Bloco 2 — Cobertura de testes

| Item | Status | Evidência |
|---|---|---|
| 2.1 — Cobertura geral ≥ 80% | ✅ | **95,0%** total (`php artisan test --coverage --min=80` passou). Ver A.3. |
| 2.2 — Núcleo de contas/autorização ≥ 98% | ⚠️ | Núcleo designado 98–100% (`UpsertGoogleUser` 100%, `AppServiceProvider`/Gate 100%, `RegisteredUserController` 100%, `PasswordResetLinkController` 100%, `AuthenticatedSessionController` 100%). Ressalva: ramos de scaffolding Breeze abaixo de 98% — ver "Passes com ressalva" e A.3. |
| 2.3 — E2E cobre cada fluxo tocado | ✅ | Dusk: login/cadastro (`AcessoColetadorTest`), Google (`AcessoGoogleTest`), i18n (`I18nPtBrTest`), barreira de áreas (`SegmentacaoAreasTest`), confirmação e-mail (`ConfirmacaoEmailTest`). |
| 2.4 — FE web: E2E em browser real | ✅ | Laravel Dusk (Chrome via container `quantah-selenium`), executado de 1ª mão. Ver A.4. |
| 2.5 — Feliz + inválidos + exceções + bordas | ✅ | Testes rotulados por categoria (ex.: `GoogleAccountResolutionTest` 6 ramos incl. e-mail não verificado/fail-secure; `SegmentacaoAreasTest` feliz/inválido/borda/exceção). |

### Bloco 3 — Automação

| Item | Status | Evidência |
|---|---|---|
| 3.1 — Setup local automatizado (um comando) | ⚠️ | Sub-skill `docs/skills/setup-ambiente/` + Laravel Sail (`vendor/bin/sail`). Stack local funcional (usei-a para rodar a suíte). Ressalva: não reexecutei o bootstrap em máquina limpa — ver Limitações. |
| 3.2 — CI verde na `main` | ✅ | `gh run list --branch main`: run **`28727597678`** (HEAD `e1d97f0`) **success**, 2m31s. Falha isolada anterior (`28720257832`, meio da STORY-022) corrigida; runs seguintes verdes. |
| 3.3 — Deploy automático para homologação | ✅ | `.github/workflows/ci-cd.yml` job `deploy` (`if: ref==main && push`); `infra/README.md` confirma; homolog serve o sha (A.5). |
| 3.4 — Deploy para produção automatizado | 🚫 | n/a — não há ambiente de produção nesta fase (MVP); o DoD do épico entrega **homologação**. Observação sobre a promoção tag-based (§2.2) em "Fails/observações". |
| 3.5 — Provisionamento via IaC | ✅ | `infra/gcp/provision.sh` + `startup.sh` + `docker-compose.prod.yml` + `Caddyfile` (ADR-007). Sem cliques manuais. |

### Bloco 4 — Funcionalidade observável (homologação)

| Item | Status | Evidência |
|---|---|---|
| 4.1 — Entregável acessível em homolog | ✅ | curl 1ª mão: `/up`=200, `/login`=200, `/intelligence`=200, `/backoffice/saques` (sem login)→302 `/login`. Ver A.5. |
| 4.2 — Fluxo end-to-end percorrível | ✅ | E2E browser real (Dusk 69/69) + smoke HTTP 1ª mão. Google login e reset por Gmail SMTP verificados ao vivo por Alexandro (notas STORY-022). |
| 4.3 — Logs e métricas básicas | ⚠️ | Health check `/up` definido (`bootstrap/app.php`, usado pelo smoke do CI). Ressalva: logs estruturados = default do Laravel; métricas RED/alerta não evidenciados. |

### Bloco 5 — Qualidade transversal

| Item | Status | Evidência |
|---|---|---|
| 5.1 — Scanner de segurança/dependências no CI | ❌ | `ci-cd.yml` não tem etapa de `composer audit`/`npm audit`/gitleaks/CodeQL; sem `.github/dependabot.yml`. Não-bloqueante (F-NB-1). |
| 5.2 — Migrações reversíveis e testadas | ⚠️ | `add_google_columns_to_users_table` tem `down()` reversível (dropa `google_id`/`avatar`, reverte `password`). `up` roda a cada deploy (`migrate --force`); `down` não exercido em homolog — ressalva. |
| 5.3 — LGPD: dado pessoal novo alinhado | ⚠️ | Perfil Google (`google_id`, `avatar`) introduzido sob ADR-010/PDR-003 aceitos por Alexandro (PO). Ressalva: `docs/especificacao/` só contém `design-system.md` — sem spec LGPD/aviso de privacidade consolidado para citar como cobertura. |
| 5.4 — Segredos: nenhum no código | ✅ | `git ls-files` sem `.env` versionado; grep por material de segredo (GOCSPX-/AIza/PRIVATE KEY) em arquivos versionados → zero; `.gitignore` cobre `app/.env`; OAuth/SMTP via secrets do GitHub injetados no deploy. Ressalva: sem scanner automatizado (ver 5.1). |
| 5.5 — Logs sem PII/segredos | ⚠️ | Sem segredos em código/logs de app observados; não foi possível auditar integralmente os logs de runtime em homolog — ver Limitações. |

### Bloco 6 — Documentação

| Item | Status | Evidência |
|---|---|---|
| 6.1 — README/documentação atualizada | ✅ | `infra/README.md` descreve deploy/IaC/secrets atuais; estórias documentam decisões locais. |
| 6.2 — ADRs/IDRs do épico indexados | ✅ | `index.json`: ADR-010 (L917), ADR-011 (L932), IDR-010 (L1070, `supersedes`/relação ADR-011), DDR-004 (L1134). |
| 6.3 — "Notas do agente" preenchidas | ✅ | STORY-019..023 com Notas do agente completas (decisões, descobertas, mapa CA→teste, evidência). |
| 6.4 — Diagramas atualizados | 🚫 | n/a — o épico não altera topologia/persistência que exija atualização de diagrama (reusa `users`/RBAC do ADR-009; só colunas em `users`). |

---

## Fails identificados

### Bloqueantes

> Nenhum.

### Não-bloqueantes

#### F-NB-1 — CI sem análise de dependências vulneráveis e sem detecção de segredos
- **Bloco**: 5.1 (e relacionado a 5.4).
- **Critério esperado**: quality-standards §2.2 ("todo push para branch de feature dispara CI leve: … análise de dependências vulneráveis … detecção de segredos commitados") e §4 ("análise de dependências vulneráveis no pipeline").
- **O que verifiquei**: `grep -niE 'gitleaks|trivy|codeql|snyk|composer audit|npm audit|secret' .github/workflows/ci-cd.yml` retorna apenas usos de `secrets.*` (injeção de deploy), nenhuma etapa de varredura; `.github/dependabot.yml` ausente. O CI executa Pint + testes + build + deploy, sem gate de segurança de dependências/segredos.
- **Classificação**: não-bloqueante — capacidade transversal de CI ausente, pré-existente e não específica do épico; nenhum segredo versionado nem vulnerabilidade crítica foi observado por verificação manual (5.4 pass). Não se enquadra nas condições bloqueantes de `verdict-criteria.md` (nenhum segredo no código nem vuln crítica descobertos).
- **Evidência**: ver Apêndice A.6.

---

## Passes com ressalva

- **2.2 — Cobertura de núcleo**: o núcleo designado das estórias (resolução de conta Google, Gate de
  autorização, registro, link de reset anti-enumeração, sessão) está 98–100%. Abaixo da barra de 98% ficam
  ramos de **scaffolding do Breeze fora do núcleo do épico**: `Http/Requests/Auth/LoginRequest` 65,2%
  (linhas 67–76 = caminho de *lockout* por rate-limit, >5 tentativas), `EmailVerificationNotificationController`
  0% (reenvio de verificação), `EmailVerificationPromptController` 66,7%, `VerifyEmailController` 80%. O
  *enforcement* da verificação de e-mail em si é coberto (`EmailVerificationEnforcementTest`). Cobertura
  global 95,0%. (A.3)
- **2.3/2.4 — E2E Dusk (flake de ordenação)**: na 1ª execução da suíte completa local, 68/69, com falha de
  `AcessoColetadorTest::test_login_no_padrao_visual_em_ptbr_sem_logo_laravel` ("Did not see [Quantah]"). Isolado:
  **5/5 pass**; reexecução da suíte completa: **69/69 pass**. Flake de ordenação/timing por reuso do browser
  entre testes (documentado na STORY-022 — testes autenticados chamam `logout()` no fim). O cenário funciona
  (marca renderiza; homolog `/login` traz "Quantah"; CI verde). (A.4)
- **3.1 — Setup local**: automatizado via `setup-ambiente` + Sail e funcional (rodei a suíte por ele), mas não
  reexecutei o bootstrap "clone → um comando" em máquina limpa nesta sessão.
- **4.3 — Observabilidade**: health check `/up` presente; sem evidência de métricas RED/alerta ou logs
  estruturados dedicados.
- **5.2 — Migração**: `down()` reversível presente; reversão não exercida em homologação (só `up` via
  `migrate --force` no deploy).
- **5.3 — LGPD**: dado pessoal novo (perfil Google) sob ADR-010/PDR-003 aceitos pelo PO; sem spec LGPD/aviso
  de privacidade consolidado em `docs/especificacao/` para citar como cobertura documental.
- **5.4 — Segredos**: nenhum versionado (verificado manualmente); sem scanner automatizado que reforce isso
  no CI (ligado a F-NB-1).

> **Observação factual adicional (Bloco 3.4)**: a promoção atual é *push na `main` → deploy de homologação
> automático*, não a promoção tag-based (RC em homolog, tag + gate humano em produção) descrita em
> quality-standards §2.2. É estado de infra transversal, pré-existente ao épico; registrado como fato.

---

## Limitações da validação

- **Status do épico**: o `index.json` traz EPIC-004 em `in_progress` (não `in_review`). A pré-condição
  formal do workflow do validador pede `in_review`; prossegui porque as cinco estórias de dependência estão
  `done` e o PO (Alexandro) pediu explicitamente a execução da STORY-024. A transição de status do épico é do PO.
- **Verificação ao vivo do login Google/e-mail em homolog**: exercida de 1ª mão no nível HTTP (curl) e por
  E2E em browser real local; o fluxo OAuth Google completo *em homologação* e o envio real de e-mail (reset/
  verificação por Gmail SMTP) foram verificados ao vivo por Alexandro (notas STORY-022), não reexecutados por
  mim contra o Google real nesta sessão (credenciais reais só em homolog; testes usam driver fake).
- **Logs de runtime em homolog**: não tive acesso a um dashboard de logs/métricas de homologação para
  auditar PII/segredos em log de forma exaustiva (5.5).
- **Cobertura por-estória**: a cobertura foi medida na suíte completa (global 95,0% + por-módulo), não como
  delta isolado por estória; os módulos novos do épico foram inspecionados individualmente (A.3).

---

## Apêndice A — Evidências detalhadas

### A.1 — Mapa CA → teste (STORY-020/021/022)
- **STORY-020**: CA-1/CA-3 → `Feature/I18n/LocalizacaoTest` (locale ativo pt_BR, resolução de string, prop
  `translations`, validação/credencial pt-BR); CA-4 → `Unit/Support/FormatoTest` (moeda/data/fuso SP, `Formato`
  100%); CA-2/CA-5 → `Browser/I18nPtBrTest` (pt-BR presente + ausência de "Log in"/"Remember me"/"Whoops").
- **STORY-021**: CA-1..CA-6 → `Browser/AcessoColetadorTest` (6 métodos: padrão visual sem logo Laravel,
  registro, erro de credencial global, reset sem enumeração, placeholder Google, jornada cadastro→logout→login)
  + `Feature/Auth/RegistrationTest`, `PasswordResetTest`.
- **STORY-022**: CA-1..CA-5 → `Feature/Auth/GoogleAccountResolutionTest` (6 ramos: cria/vincula/já-tem-google/
  não-verificada/recusa-sem-verified/sem-email), `GoogleLoginControllerTest` (callback cria/vincula/cancelado/
  falha/não-verificado), `Browser/AcessoGoogleTest` (3: cria+autentica, vincula, cancelamento pt-BR). Núcleo
  `UpsertGoogleUser` 100%.

### A.2 — STORY-023 (asserções reais amostradas)
`tests/Feature/Acesso/SegmentacaoAreasTest.php`:
- `test_toda_rota_backoffice_esta_atras_do_guard` → assertContains 'auth' e 'can:operar-saques' em toda rota `/backoffice`.
- `test_coletador_autenticado_recebe_403_no_backoffice` → `assertForbidden()`; contraste `test_operador_acessa_o_backoffice` → `assertOk()`.
- `test_guest_no_backoffice_redireciona_para_login` → `assertRedirect('/login')`.
- `test_area_b2b_intelligence_e_publica_sem_login` → `assertOk()` + `assertInertia(component 'Intelligence/Reservado')`; `test_area_b2b_nao_tem_rota_autenticada_nem_feature` → assertNotContains 'auth' + `assertSame(['GET','HEAD'])`.
- `test_pagina_403_esta_em_ptbr` → `assertSee('Acesso restrito')` + `assertDontSee('This action is unauthorized')` + `assertDontSee('Forbidden')`.

### A.3 — Cobertura (1ª mão)
Comando: `./vendor/bin/sail artisan test --coverage --min=80` sobre sha `e1d97f0`.
Resultado: `{"phpunit","passed","tests":277,"passed":277,"assertions":1100}`; **Total: 95,0 %** (gate 80% passou).
Módulos novos/núcleo do épico:
```
Actions/Auth/UpsertGoogleUser ................ 100.0%
Http/Controllers/Auth/GoogleAuthController .... 100.0%
Http/Controllers/Auth/RegisteredUserController  100.0%
Http/Controllers/Auth/AuthenticatedSessionController 100.0%
Http/Controllers/Auth/PasswordResetLinkController 100.0%
Http/Controllers/Auth/PasswordController ...... 100.0%
Exceptions/Auth/UnverifiedGoogleEmailException  100.0%
Providers/AppServiceProvider (Gate operar-saques) 100.0%
Support/Formato .............................. 100.0%
Support/Auth/FakeGoogleProvider .............. 100.0%
Http/Middleware/HandleInertiaRequests ......... 95.2%
Http/Controllers/Auth/NewPasswordController ... 87.5%
Http/Controllers/Auth/VerifyEmailController ... 80.0%
Http/Controllers/Auth/EmailVerificationPromptController 66.7%
Http/Requests/Auth/LoginRequest .............. 65.2% (67–76: lockout por rate-limit)
Http/Controllers/Auth/EmailVerificationNotificationController 0.0% (reenvio)
```

### A.4 — E2E Dusk (1ª mão)
- Suíte completa run 1: `{"tests":69,"passed":68,"failed":1}` — `AcessoColetadorTest::test_login_no_padrao_visual_em_ptbr_sem_logo_laravel` ("Did not see [Quantah]").
- Teste isolado ×5: `passed, passed, passed, passed, passed` (5/5).
- Suíte completa run 2: `{"tests":69,"passed":69,"assertions":253}` (69/69).
- Conclusão: flake de ordenação/timing por reuso de browser; funcionalidade íntegra.

### A.5 — Smoke homologação (1ª mão)
`https://quantah-homolog.34.39.229.117.sslip.io` — `/up`=200, `/login`=200 (payload traz "Quantah" e o
dicionário i18n `"Log in":"Iniciar sessão"`, `"Remember me":"Lembrar-me"` — as strings inglesas são **chaves**
do mecanismo, não UI renderizada; sem logo/`ApplicationLogo` do Laravel no payload), `/intelligence`=200
("Em breve"/"Reservado"), `/backoffice/saques` sem login → 302 `/login` (barreira ativa).

### A.6 — Segurança/segredos (1ª mão)
- `git ls-files | grep '\.env'` → só `.env.example`. Nenhum `.env` versionado.
- `git ls-files -z | xargs -0 grep -lE 'GOCSPX-|AIza…|PRIVATE KEY'` → zero.
- `.gitignore` cobre `app/.env`. OAuth/SMTP injetados no deploy via secrets do GitHub (`ci-cd.yml` › "Injetar Google + Mail").
- `ci-cd.yml`: sem etapa de scanner de dependências/segredos; `.github/dependabot.yml` ausente (F-NB-1).

---

## Apêndice B — Arquivos anexados

> Evidência capturada como output de comando reproduzível (Apêndice A) e links a artefatos versionados; não
> houve necessidade de anexar binários. Screenshots reais do Dusk já vivem em `app/tests/Browser/screenshots/`
> (produzidos pelas estórias).

- CI run verde: `gh run view 28727597678` (branch `main`, sha `e1d97f0`).
- Cobertura/E2E: reproduzíveis via `sail artisan test --coverage` e `sail artisan dusk` sobre `e1d97f0`.

---

## Histórico

- 2026-07-05 — relatório inicial submetido por validador (sessão 2026-07-05). Veredito: **APPROVED com pendências**.
