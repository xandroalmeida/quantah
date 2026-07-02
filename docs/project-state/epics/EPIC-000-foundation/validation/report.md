---
epic_id: EPIC-000
type: validation-report
validated_at: 2026-07-02
validated_by: validador (sessão 826f6e93)
verdict: approved
checklist_source: epics/EPIC-000-foundation/validation/checklist.md
---

# Relatório de Validação — EPIC-000 (Foundation)

## TL;DR

> **Veredito: APPROVED.**
> **Contagem**: 15 itens do checklist — 12 `pass`, 3 `pass com ressalva`, 0 `fail`, 0 `n/a`.
> **Fails**: nenhum (zero bloqueantes, zero não-bloqueantes).
> **Ressalvas factuais**: (1) `make up` verificado por inspeção + evidência da estória, não re-executado de clone limpo nesta sessão; (2) o E2E em browser real (Dusk) roda contra a app servida no runner do CI, não contra a URL de homologação — homologação é coberta por smoke HTTP 200 automatizado + verificação independente desta validação; (3) `index.json` e `epic.md` mantêm EPIC-000 em `status: ready` enquanto STORY-000/001/002 estão `done`.

---

## Resumo executivo

O EPIC-000 entrega a fundação do Quantah: um "hello world" servido via Inertia/React, com o tema do Design System aplicado (paleta, Inter 400/600/900, raio de 24px), acessível em homologação por HTTPS, publicado por um pipeline CI/CD automatizado a cada push na `main`. Verifiquei diretamente: a URL de homologação responde **HTTP 200** (e `/up` 200) servindo a página com o tema; o pipeline `CI/CD` roda testes (unit+feature com gate de cobertura `--min=80`), E2E em browser real (Dusk) e build a cada push/PR, e faz deploy automático de homologação no push da `main` sem passo manual; a cobertura observada é **87.3%** (acima do mínimo de 80%); não há segredos versionados (deploy usa GitHub Secrets; `app/.env` é ignorado e não rastreado); o contraste do CTA é **13.05:1** (acima do AA de 4.5:1) e o foco por teclado é visível.

Todos os 15 itens do checklist do épico foram atendidos com evidência. Três itens recebem `pass com ressalva` por nuance factual registrada abaixo (nenhuma configura fail segundo `verdict-criteria.md`). Não há item bloqueante nem não-bloqueante. Veredito: **APPROVED**.

**Commit em validação**: `0412edf` (branch `main`).

---

## Checklist preenchido

### Bloco 1 — Ambiente e deploy

| Item | Status | Evidência |
|---|---|---|
| 1.1 — Hello-world acessível na URL de homologação (HTTP 200) | ✅ PASS | `curl` em `https://quantah-homolog.34.39.229.117.sslip.io/` → **200** (0.33s, 29972 bytes); `/up` → **200**. Corpo serve "Quantah" + Inter. Ver A.1. |
| 1.2 — Deploy para homologação automatizado (dispara no merge, sem passo manual) | ✅ PASS | Job `deploy` em `.github/workflows/ci-cd.yml` (`if: github.ref == 'refs/heads/main' && github.event_name == 'push'`, `needs: [tests, dusk]`); CI run `28612051361` → job "Deploy homologação" **success**, com "Smoke test da homologação" no próprio job. Nenhum passo manual. Ver A.2. |
| 1.3 — Ambiente de dev sobe com **um** comando a partir de clone limpo, com seed | ⚠️ PASS com ressalva | `app/Makefile` alvo `up`: bootstrap do vendor via `composer:2` em Docker → `sail up -d` → `composer install` → `key:generate` → `npm install/build` → `artisan migrate --seed --force` → `http://localhost:8000`. Documentado em `app/README.md` §"Ambiente de desenvolvimento — um comando". Evidência de execução em `STORY-000-evidencia/CA-1-stack-local.md`. **Ressalva**: mecanismo verificado por inspeção + evidência da estória; não re-executei `make up` de clone limpo nesta sessão (ver Limitações). |

### Bloco 2 — Pipeline

| Item | Status | Evidência |
|---|---|---|
| 2.1 — CI roda suíte de testes + build a cada PR/push | ✅ PASS | `ci-cd.yml` `on: push [main]` + `pull_request [main]`; jobs `tests` (composer, npm build, `php artisan test --coverage --min=80`) e `dusk` (E2E). PR #1 rodou em `pull_request` (runs `28611061914`, `28610935539` success). Ver A.2/A.3. |
| 2.2 — Pipeline **barra o merge** quando teste/build falha | ✅ PASS | Histórico do CI mostra 2 runs **failure** na `main` antes do verde (`28608653025` — 50s; `28608816315` — 1m6s), e o job `deploy` depende de `needs: [tests, dusk]` (não roda se qualquer um falha). Falha do gate ⇒ sem deploy. Ver A.4. |
| 2.3 — Pipeline verde no merge que gerou o deploy validado | ✅ PASS | Run `28612051361` (push `main`, commit `0412edf`): `Testes + build` **success**, `E2E (Dusk)` **success**, `Deploy homologação` **success**. Ver A.3. |

### Bloco 3 — Design System aplicado

| Item | Status | Evidência |
|---|---|---|
| 3.1 — Paleta do DS aplicada (verde `primary` só em CTA; superfícies sage/branco) | ✅ PASS | `Hello.jsx`: único elemento `bg-primary` é o CTA "Enviar cupom"; página `bg-canvas-soft` (sage) → card `bg-canvas` (branco). `tailwind.config.js` mapeia os tokens de cor canônicos. Confirmação de fidelidade do Designer (`STORY-002-evidencia/revisao-designer.md`: 22/22 cores, regras de ouro respeitadas). Ver A.5. |
| 3.2 — Tipografia Inter carregada (400/600/900); display em peso 900 (DDR-001) | ✅ PASS | Página de homolog referencia `fonts.bunny.net/css?family=inter:400,600,900`. `Hello.jsx` título `font-display font-black`. Designer confirmou peso 900 por estilo computado em browser real. Ver A.5. |
| 3.3 — Raio 24px em botão; nenhum valor cru de cor/spacing no JSX | ✅ PASS | CSS servido em homolog contém `border-radius:24px`; `borderRadius.xl=24px` no tema; CTA e card usam `rounded-xl`. `Hello.jsx` usa apenas utilitários de token (sem hex/valor arbitrário). Guardas `NoRawColorInHelloTest` + `TailwindThemeTokensTest` no repo. Ver A.5. |

### Bloco 4 — Qualidade

| Item | Status | Evidência |
|---|---|---|
| 4.1 — Cobertura ≥80% no código novo das estórias de implementação | ✅ PASS | CI run `28612051361`, job `Testes + build`: **Total 87.3%**, gate `php artisan test --coverage --min=80` verde; 38 testes, 138 assertions. Ver A.6. |
| 4.2 — Ao menos um E2E em browser real cobrindo a hello-world em homologação | ⚠️ PASS com ressalva | Job `dusk` (Laravel Dusk / Chrome real) **success**; 7 cenários (`ThemeTest` ×5, `HelloWorldTest` ×2) exercendo a hello-world com o tema. **Ressalva**: o Dusk roda contra a app servida no runner (`php artisan serve`, ambiente equivalente — permitido pela CA-5 da STORY-001), não contra a URL de homologação; a homologação em si é coberta por smoke HTTP 200 automatizado (job `deploy`) + verificação independente desta validação (A.1). Ver A.6. |
| 4.3 — A11y mínima: contraste AA no botão, foco visível | ✅ PASS | Contraste `on-primary` `#0e0f0c` sobre `primary` `#9fe870` = **13.05:1** (cálculo WCAG independente; AA normal exige 4.5:1). `Hello.jsx` CTA com `focus-visible:ring-2 focus-visible:ring-ink`. Teste Dusk de foco por teclado + revisão do Designer. Ver A.7. |
| 4.4 — Nenhum segredo commitado | ✅ PASS | `git grep` de padrões de segredo retornou apenas referências `env(...)` de config e regras de validação de auth (starter), nenhum valor. `app/.env` e `app/.env.dusk.local` ignorados e não rastreados (`git ls-files` vazio; `git check-ignore` confirma). Deploy usa GitHub Secrets (`DEPLOY_SSH_KEY`, `DEPLOY_HOST`, `DEPLOY_USER`, `PROD_ENV`, `GITHUB_TOKEN`). Ver A.8. |

### Bloco 5 — Estado

| Item | Status | Evidência |
|---|---|---|
| 5.1 — `index.json` coerente: STORY-000/001/002 `done`; STORY-003 conclui o épico | ⚠️ PASS com ressalva | `index.json`: STORY-000 `done`, STORY-001 `done`, STORY-002 `done`; `epics[EPIC-000].validation_report` = `null` (este é o primeiro). **Ressalva**: `index.json` e `epic.md` mantêm `EPIC-000.status = "ready"` (não `in_review`) apesar das três estórias `done` — divergência de estado factual; a transição de status do épico é ato do PO. Ver A.9. |
| 5.2 — Notas do agente preenchidas em cada estória | ✅ PASS | STORY-000, STORY-001 e STORY-002 têm "Notas do agente" completas (documentos lidos, decisões, IDRs, cobertura, links de evidência). Ver A.9. |

### Bloco 6 — Veredito

**APPROVED** — todos os itens `pass` ou `pass com ressalva`; nenhum `fail`.

---

## Fails identificados

### Bloqueantes

Nenhum.

### Não-bloqueantes

Nenhum.

> Nenhum item foi classificado como `fail` segundo `verdict-criteria.md`. Os três `pass com ressalva` registram nuance factual (Bloco 1.3, 4.2 e 5.1) — não configuram fail: CA cumpridos, cobertura acima do mínimo, pipeline verde, funcionalidade acessível, sem segredos.

---

## Passes com ressalva

- **Bloco 1.3 — dev em um comando**: mecanismo (`make up`) verificado por inspeção do `Makefile` + `README` + evidência da STORY-000; **não re-executado de clone limpo nesta sessão** (ver Limitações).
- **Bloco 4.2 — E2E em homologação**: E2E em browser real (Dusk/Chrome, 7 cenários, verde) cobre a hello-world em **ambiente equivalente** (serve no runner do CI, permitido pela CA-5); a **homologação** é validada por smoke HTTP 200 automatizado + verificação independente (curl, HTTP 200 + tokens no CSS servido), não por um E2E de browser apontado para a URL de homologação.
- **Bloco 5.1 — estado do índice**: STORY-000/001/002 `done` e `validation_report` `null` (coerente), porém `EPIC-000.status` permanece `"ready"` no `index.json` e no `epic.md`.

---

## Observações factuais adicionais

> Registro neutro, sem recomendação.

- **Cobertura por arquivo (Bloco 4.1)**: o total de 87.3% está acima do mínimo. Arquivos com cobertura mais baixa no relatório (`EmailVerificationNotificationController` 0.0%, `EmailVerificationPromptController` 66.7%, `LoginRequest` 65.2%, `PasswordResetLinkController` 78.6%) são scaffolding de autenticação do starter Laravel/Breeze, não código novo introduzido pelas estórias do épico (que não adicionam núcleo de regra de negócio PHP — é fundação/hello-world). Não há núcleo de negócio novo a exigir o piso de 98%.
- **Modelo de promoção CI/CD**: o pipeline roda a suíte completa (testes + Dusk + build) no CI a cada push/PR e faz deploy de homologação no push da `main`. Isso difere do modelo descrito em `quality-standards.md` §2.2 (hook git pré-push versionado + CI leve em feature branch + promoção *tag-based* com `-rc` para homologação e gate humano em produção); não há hook versionado no repo (`git ls-files` sem match para husky/lefthook/githooks) nem promoção por tag. Nenhum item do `checklist.md` do EPIC-000 exige esse modelo; produção está explicitamente fora de escopo do épico (`epic.md`).

---

## Limitações da validação

- **`make up` de clone limpo**: não re-executei o comando a partir de um clone limpo nesta sessão. O ambiente local do Quantah roda via Laravel Sail com agentes concorrentes usando portas/stacks próprios; subir `make up` nas portas padrão (8000/5432) durante a sessão traria risco de colisão com stacks em execução. Verificação ficou na inspeção do `Makefile`/`README` + evidência registrada em `STORY-000-evidencia/CA-1-stack-local.md`.
- **E2E apontado para a URL de homologação**: não há run de E2E em browser real apontado para `https://quantah-homolog...`. A homologação foi verificada por smoke HTTP 200 (automatizado no `deploy`) e por probe independente desta validação (curl: HTTP 200, `/up` 200, Inter e `border-radius:24px` no CSS servido), não por navegação de browser automatizada contra a URL pública.
- **Migrações**: o épico não introduz migrações de domínio novas — apenas as três de scaffold do starter (`users`, `cache`, `jobs`). Bloco de reversibilidade de migração não teve item novo a exercer; `migrate --force` roda no deploy (run verde).

---

## Apêndice A — Evidências detalhadas

### A.1 — Homologação acessível (Bloco 1.1)

**Comandos** (branch `main`, commit `0412edf`):
```
$ curl -sS -o /dev/null -w "code=%{http_code} time=%{time_total}s size=%{size_download}\n" \
    https://quantah-homolog.34.39.229.117.sslip.io/
code=200 time=0.333310s size=29972
$ curl -sS -o /dev/null -w "code=%{http_code}\n" https://quantah-homolog.34.39.229.117.sslip.io/up
code=200
```
Corpo serve "Quantah" (25 ocorrências), referência a Inter (7). CSS servido (`/build/assets/app-Minrfr19.css`) contém `border-radius:24px` e `Inter`. TLS válido (Let's Encrypt via Caddy).

### A.2 — Deploy automatizado (Bloco 1.2)

`.github/workflows/ci-cd.yml` job `deploy`: `needs: [tests, dusk]`, `if: github.ref == 'refs/heads/main' && github.event_name == 'push'`. Passos: build+push da imagem (GHCR) → SSH → envio de compose/Caddyfile/.env → `docker compose pull && up -d --wait && migrate --force` → "Smoke test da homologação" (loop até HTTP 200 em `/up`). Nenhum passo manual. Run `28612051361` job "Deploy homologação" **success**.

### A.3 — Runs de CI verdes (Blocos 2.1, 2.3)

Run `28612051361` (push `main`, `0412edf`):
```
E2E (Dusk): success
Testes + build: success
Deploy homologação: success
```
PR #1 (`pull_request`): runs `28611061914`, `28610935539` — ambos **success** (gate roda em PR).

### A.4 — Pipeline barra merge em falha (Bloco 2.2)

`gh run list` mostra 2 runs **failure** na `main` antes do primeiro verde:
```
28608653025  failure  feat(CA-1/2/3): pipeline CI/CD ...        50s
28608816315  failure  fix(ci): rodar composer install ...      1m6s
```
O job `deploy` (`needs: [tests, dusk]`) não executa enquanto os jobs de teste falham → sem publicação em homologação com gate vermelho.

### A.5 — Design System aplicado (Blocos 3.1–3.3)

- `app/resources/js/Pages/Hello.jsx`: único `bg-primary` é o `<button data-testid="hello-cta">`; página `bg-canvas-soft`, card `bg-canvas`, título `font-display text-display-md font-black lg:text-display-xl`, CTA `rounded-xl ... text-on-primary`. Somente utilitários de token; sem hex/valor arbitrário.
- `app/tailwind.config.js`: cores (primary `#9fe870`, on-primary `#0e0f0c`, canvas/canvas-soft, ink/body/mute, semânticas), `fontFamily.display/sans = Inter`, `borderRadius.xl = 24px`, spacing base-4, breakpoints md/lg.
- Homolog referencia `fonts.bunny.net/css?family=inter:400,600,900&display=swap`.
- Fidelidade confirmada pelo Designer: `STORY-002-evidencia/revisao-designer.md` (22/22 cores, 12/12 tipografia, raio xl=24px, regras de ouro respeitadas em mobile 375px e desktop 1280px, "sem divergências").

### A.6 — Cobertura e E2E (Blocos 4.1, 4.2)

Run `28612051361`, job "Testes + build":
```
Tests: 38 passed (138 assertions)   Duration: 3.18s
────────────────────────────────────────────────────
                                         Total: 87.3 %
```
Gate `php artisan test --coverage --min=80` verde. Job "E2E (Dusk)" **success**. Testes E2E no repo: `tests/Browser/HelloWorldTest.php`, `tests/Browser/ThemeTest.php` (Chrome real via `dusk:chrome-driver --detect`, app servida por `php artisan serve` no runner).

### A.7 — Acessibilidade (Bloco 4.3)

Cálculo WCAG independente (relative luminance):
```
ratio(on-primary #0e0f0c sobre primary #9fe870) = 13.05:1   (AA normal exige 4.5:1)
```
`Hello.jsx` CTA: `focus-visible:outline-none focus-visible:ring-2 focus-visible:ring-ink focus-visible:ring-offset-2`. Foco por teclado coberto por `ThemeTest::test_primary_button_focus_is_visible` (Dusk) e revisão do Designer.

### A.8 — Segredos (Bloco 4.4)

`git ls-files app/.env app/.env.dusk.local .env` → vazio (não rastreados). `git check-ignore` confirma ambos ignorados. `git grep` de padrões de segredo (`APP_KEY=base64:`, chaves privadas, `ghp_`, `AIza…`, `password=…`) fora de `*.lock`/`docs` retornou apenas referências `env(...)` de config e regras `Password::defaults()`/`Hash::make` do starter de auth — nenhum valor secreto. Deploy consome GitHub Secrets: `DEPLOY_HOST`, `DEPLOY_SSH_KEY`, `DEPLOY_USER`, `PROD_ENV`, `GITHUB_TOKEN`.

### A.9 — Estado do índice e notas (Blocos 5.1, 5.2)

`index.json`: `stories[STORY-000/001/002].status = "done"`; `epics[EPIC-000].validation_report = null`; `epics[EPIC-000].status = "ready"` (divergência registrada — flip é ato do PO). "Notas do agente" completas em STORY-000 (spike/ADR-002/003 esboçados), STORY-001 (pipeline, ADR-007/008, cobertura 87.3%, links) e STORY-002 (tokens, IDR-001, Dusk 7 cenários, revisão do Designer).

---

## Histórico

- 2026-07-02 — relatório inicial submetido por validador (sessão 826f6e93). Veredito: **APPROVED**.
