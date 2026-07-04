---
epic_id: EPIC-003
type: validation-report
validated_at: 2026-07-04
validated_by: validador (claude-validador-story018)
verdict: approved
checklist_source: epics/EPIC-003-carteira-e-cashback/validation/checklist.md
deployed_sha: 9c77cbc
---

# Relatório de Validação — EPIC-003 (Carteira e cashback)

## TL;DR

> **Veredito**: **APPROVED**.
> **Contagem**: 26 passes (4 com ressalva), 0 fails (0 bloqueantes, 0 não-bloqueantes), 1 n/a justificado.
> **Bloqueantes (resumo factual)**: nenhum. O loop de recompensa está verificado de 1ª mão sobre o
> sha deployado (`9c77cbc`): crédito de 0,1% correto e idempotente, reconciliação saldo × ledger sem
> divergência, carteira acessível em homologação e caminho de saque (PIX assistido, ADR-005) presente.

---

## Resumo executivo

O EPIC-003 fecha o loop de recompensa: cupom válido-único-novo (EPIC-002) credita **0,1% do valor**
na carteira do Colaborador, o saldo em reais é visível numa tela mobile com histórico, e existe um
caminho de resgate por **PIX assistido** operado atrás de um papel `operador` (ADR-005/ADR-009).

Verifiquei sobre o sha deployado (`9c77cbc`, CI run 28704414576 verde). A suíte completa roda
**237 testes PHPUnit / 955 asserções, todos verdes**, com o **núcleo de cálculo de cashback**
(`CalculadoraCashback`, `CreditarCashbackService`) e o **núcleo do dinheiro do saque**
(`SolicitarSaqueService`, `SaqueService`, `Cpf`) a **100%** — acima do gate de 98% do épico —, e
cobertura geral de **94,3%**. Rodei de 1ª mão em **browser real (Dusk)** os fluxos de carteira e
saque (9 testes / 23 asserções verdes) e confirmei que a carteira responde em homologação
(`/up` HTTP 200; `/carteira` 302→`/login`; `/login` 200). A reconciliação `saldo == SUM(ledger)` é
garantida por construção (lançamento no ledger e atualização do cache de saldo na mesma transação,
sob lock) e coberta por teste. O CPF do saque (novo dado pessoal, KYC) é mascarado na lista do
backoffice e nunca gravado/logado em claro fora do detalhe (KYC). Registro 4 ressalvas factuais e
2 limitações de validação abaixo; nenhuma é bloqueante.

---

## Checklist preenchido

### Bloco 1 — Critérios de aceite das estórias

| Item | Status | Evidência |
|---|---|---|
| 1.1 — STORY-014..017 `status: done` no `index.json` | ✅ | `index.json` (STORY-014/016/017 `done`; STORY-015 movida `in_review→done` pelo PO em 2026-07-04, commit `91291c8`). Ver Apêndice A.1. |
| 1.2 — Cada CA (014–017) exercido por teste com asserção real | ✅ | Mapas CA→teste nas "Notas do agente" das 4 estórias, cruzados com a suíte verde (237 testes / 955 asserções). Apêndice A.2. |
| 1.3 — STORY-015: crédito 0,1% ao validar, idempotente por cupom, ledger append-only, arredondamento centavos | ✅ | `CalculadoraCashbackTest` (feliz/bordas/inválido), `CreditarCashbackServiceTest::test_credito_e_idempotente_por_cupom`; código `CreditarCashbackService` (lock + índice único parcial). Apêndice A.3. |
| 1.4 — STORY-016: saldo em reais + histórico, estados, dados do extrato (nada hardcoded) | ✅ | `ExtratoCarteiraTest`, `CarteiraControllerTest`; E2E `CarteiraTest` (preenchido/vazio). Apêndice A.4. |
| 1.5 — STORY-017: resgate PIX assistido, débito idempotente/reconciliável, estados, máquina de estados, KYC | ✅ | `SolicitarSaqueServiceTest`, `OperarSaqueServiceTest`, `SolicitarSaqueHttpTest`, `BackofficeSaquesHttpTest`; E2E `SolicitarSaqueTest`+`BackofficeSaquesTest`. Apêndice A.5. |
| 1.6 — ADR-005 e ADR-009 `accepted` e indexados | ✅ | `index.json` › `decisions.adr` (ADR-005, ADR-009 = `accepted`). |

### Bloco 2 — Cobertura de testes

| Item | Status | Evidência |
|---|---|---|
| 2.1 — Cobertura geral do código novo ≥ 80% | ✅ | `sail artisan test --coverage` → **Total 94,3%** (deployed sha `9c77cbc`). Apêndice A.6. |
| 2.2 — Núcleo de cálculo de cashback ≥ 98% | ✅ | `CalculadoraCashback` **100%**, `CreditarCashbackService` **100%**, `ExtratoCarteira` **100%**, listener `CreditarCashbackAoValidar` **100%**. Apêndice A.6. |
| 2.3 — Núcleo do dinheiro do saque coberto no mesmo padrão | ✅ | `SolicitarSaqueService` **100%**, `SaqueService` **100%**, `Cpf` **100%**. Apêndice A.6. |
| 2.4 — Testes cobrem feliz + idempotência + no-op + saldo insuficiente/mínimo + estorno + bordas | ✅ | `test_credito_e_idempotente_por_cupom`, `test_sem_atribuicao_nao_credita`, `test_rejeita_saldo_insuficiente`, `test_aceita_o_valor_minimo_exato`, `test_rejeitar_duas_vezes_nao_estorna_de_novo`, `test_fracao_exatamente_meio_arredonda_para_cima`. |
| 2.5 — E2E em browser real (Dusk) p/ carteira e saque, incl. erro e 403 | ✅ | Rodei de 1ª mão: `CarteiraTest`+`SolicitarSaqueTest`+`BackofficeSaquesTest` → **9/9 verdes, 23 asserções** (Apêndice A.7); suíte Dusk completa verde no CI run 28704414576 (job "E2E (Dusk)"). |
| — Ressalva: cobertura de *glue* de models abaixo de 80% | ⚠️ | `Models/CupomAtribuicao` 0%, `Models/Role` 0%, `Models/Carteira` 66,7%, `CarteiraTransacao` 75%, `Saque` 75% — métodos de relação/scope Eloquent, fora do núcleo de cálculo. Não derruba o geral (94,3%) nem o gate de núcleo (100%). |

### Bloco 3 — Automação

| Item | Status | Evidência |
|---|---|---|
| 3.1 — Setup local automatizado (um comando) | ✅ | `app/Makefile` alvo `up` (clone limpo → app + Postgres + seed, `http://localhost:8000`). |
| 3.2 — Pipeline CI verde no push do épico | ✅ | CI run **28704414576** (sha `9c77cbc`) = success; jobs "Testes + build", "E2E (Dusk)", "Deploy homologação" todos success. Apêndice A.8. |
| 3.3 — Deploy p/ homologação automático após push | ✅ | `.github/workflows/ci-cd.yml` job "Deploy homologação" dispara no push da `main`; smoke `/up` no run 28704414576. |
| 3.4 — Listener de crédito enfileirado, automatizado e idempotente | ✅ | Evento `CupomValidado`→`CreditarCashbackAoValidar` (listener enfileirado, IDR-008); `CashbackNaValidacaoTest::test_reprocessamento_nao_duplica_credito`. |
| 3.5 — Migrações da base de pagamento reversíveis e idempotentes | ✅ | `2026_07_03_000003_create_base_pagamento_tables.php`, `..._000004_create_roles_tables.php`, `..._000005_create_saques_table.php` — cada uma com `down()`. Apêndice A.9. |

### Bloco 4 — Funcionalidade observável

| Item | Status | Evidência |
|---|---|---|
| 4.1 — Carteira acessível em homologação (mobile), HTTP | ✅ | Checagem própria 2026-07-04: `GET /carteira` → **HTTP 302 → /login** (página existe, atrás de auth); `/login` → 200. Apêndice A.10. |
| 4.2 — Cupom válido credita 0,1% e saldo cresce, visível na carteira | ✅⚠️ | Verificado pelo fluxo ponta-a-ponta automatizado sobre o sha deployado (`CashbackNaValidacaoTest::test_cupom_validado_credita_o_coletor` + E2E `CarteiraTest::test_preenchido_mostra_saldo_e_historico`). Superfície viva em homologação (302 auth). Walkthrough autenticado do crédito diretamente na UI de homologação não foi executado — ver Limitações. |
| 4.3 — Reconciliação saldo × ledger sem divergência | ✅ | `CreditarCashbackServiceTest::test_reconciliacao_saldo_igual_soma_do_ledger`; `SolicitarSaqueServiceTest::test_reconciliacao_saldo_igual_soma_do_ledger_apos_reserva`; `OperarSaqueServiceTest::test_reconciliacao_apos_estorno`. Garantido por construção (ledger + saldo na mesma transação sob lock). Apêndice A.3. |
| 4.4 — Caminho de saque presente em homologação, atrás do papel `operador` | ✅ | Rota `/backoffice/saques` sob `auth`+`can:operar-saques`; E2E `BackofficeSaquesTest` (conduzir até pago; `test_nao_operador_e_barrado` → 403) verde de 1ª mão. |
| 4.5 — Logs e métricas básicas de saúde/uso (sem dado pessoal) | ✅⚠️ | Health `/up` HTTP 200 em homologação; painel north-star (STORY-012) existente. Dashboard de métricas RED (p50/p95/p99) não verificado de 1ª mão — ver Limitações. |

### Bloco 5 — Qualidade transversal (LGPD + dados + segurança)

| Item | Status | Evidência |
|---|---|---|
| 5.1 — CPF do saque mascarado na lista, completo só no detalhe (KYC); validado por VO `Cpf` | ✅ | `Backoffice\SaquesController` L36 `Cpf::mascarar($s->cpf)` na lista; L53 CPF completo no detalhe. `CpfTest` (DV mod 11) 100%. Testes `test_cpf_mascarado_na_lista` (backoffice). Apêndice A.11. |
| 5.2 — Nenhum vazamento de CPF em logs/erros/fixtures/telemetria | ✅ | Busca por CPF/chave_pix em chamadas de log em `app/Domain`, `app/Http`, `app/Models`: nenhum caminho de log grava CPF (só comentários da anonimização de coleta). Apêndice A.11. |
| 5.3 — Base de pagamento segregada da analítica; crédito/extrato não cruzam (ADR-006) | ✅ | `CupomAtribuicao`/`Carteira`/`CarteiraTransacao` referenciam `cupom_id` como uuid lógico, **sem FK dura** cruzando bases; `ExtratoCarteira` junta por `whereIn` sem cruzar a segregação (Notas STORY-016). |
| 5.4 — Migrações reversíveis e testadas | ✅ | Ver 3.5; `down()` presente em todas; migrações aplicadas no ambiente de homologação (deploy run 28704414576 + smoke verde). |
| 5.5 — Sem segredo/credencial versionado | ✅⚠️ | `.env` não rastreado no git; segredos injetados por GitHub Actions secrets (`DEPLOY_SSH_KEY`, `PROD_ENV`, `DEPLOY_HOST`...). Busca por literais de segredo em `app/config/database/routes`: nenhum. **Ressalva/Limitação**: o CI não tem scanner dedicado de segredos/deps (gitleaks/audit); verificação foi por inspeção direta. Apêndice A.12. |
| 5.6 — Autorização (ADR-009): backoffice atrás de `auth`+`can:operar-saques`; não-operador 403 | ✅ | `GateOperadorTest`; `BackofficeSaquesHttpTest::test_nao_operador_recebe_403`/`test_nao_operador_nao_executa_acao`; E2E `BackofficeSaquesTest::test_nao_operador_e_barrado`. |

### Bloco 6 — Documentação e estado

| Item | Status | Evidência |
|---|---|---|
| 6.1 — Notas do agente preenchidas em STORY-014..017 | ✅ | Seções "Notas do agente" presentes e detalhadas nas 4 estórias (mapas CA→teste, decisões, IDRs). |
| 6.2 — ADRs (005/009) e IDRs (008/009) do épico indexados | ✅ | `index.json` › `decisions.adr` (ADR-005, ADR-009) e `decisions.idr` (IDR-008, IDR-009), todos `accepted`. |
| 6.3 — Evidência do Designer p/ estórias `requires_design` (016, 017) | ✅ | `index.json` › `design.screens`: SCREEN-STORY-016, SCREEN-STORY-017-solicitar-saque, SCREEN-STORY-017-backoffice-saques = `shipped`, com `prototype_last_validated_at` 2026-07-03/04. |
| 6.4 — `index.json` coerente; `validation_report` do EPIC-003 a preencher | ✅ | STORY-014..017 `done`; EPIC-003 `in_review` com `validation_report: null` antes desta validação (preenchido por este relatório na Etapa 7). |

---

## Fails identificados

### Bloqueantes

Nenhum.

### Não-bloqueantes

Nenhum.

> Nenhum fail inclui "sugestão", "estória de correção", "próximo passo" ou estimativa — não há fails a classificar.

---

## Passes com ressalva

- **Bloco 2 (cobertura de models de *glue*)**: `Models/CupomAtribuicao` 0%, `Models/Role` 0%,
  `Models/Carteira` 66,7%, `CarteiraTransacao` 75%, `Saque` 75%. São métodos de relação/scope Eloquent,
  **fora do núcleo de cálculo**. O gate do épico (98% no núcleo de cashback) está em 100% e a cobertura
  geral (94,3%) supera o mínimo de 80%. Registro factual, não afeta o veredito.
- **Bloco 4.2**: a correção do crédito de 0,1% e a visibilidade do saldo foram verificadas pelo fluxo
  ponta-a-ponta automatizado (feature + E2E) sobre o sha deployado e pela superfície viva da carteira
  em homologação (302 auth); não fiz login autenticado na UI de homologação para observar o número
  crescer manualmente (ver Limitações).
- **Bloco 4.5**: health check `/up` (200) e painel north-star presentes; não verifiquei de 1ª mão um
  dashboard de métricas RED (latência p50/p95/p99, taxa de erro) — mesma limitação registrada nos
  épicos anteriores desta onda.
- **Bloco 5.5**: sem segredo versionado (`.env` não rastreado; injeção por secrets do CI), mas o
  pipeline **não** tem scanner dedicado de segredos/dependências vulneráveis; a verificação foi por
  inspeção direta do repositório.

---

## Limitações da validação

- **Scanner de segurança no CI ausente**: o pipeline não roda gitleaks/trufflehog nem `composer/npm
  audit`. Verifiquei segredos por inspeção direta (`.env` não versionado; busca por literais; secrets
  injetados pelo CI). Item 5.5 marcado `pass` com esta ressalva.
- **Smoke autenticado em homologação**: percorri a carteira e o saque em **browser real (Dusk)** no
  ambiente de teste, sobre o sha deployado, e confirmei a superfície viva de homologação por HTTP
  (302 auth em `/carteira`, `/up` 200). Não executei um login manual na UI de homologação para observar
  o crédito/saque com dados semeados — a semente de homologação (`db:seed` idempotente do run
  28704414576) cria o usuário/operador, mas o walkthrough autenticado na UI de homologação não foi
  parte desta sessão. A correção funcional está coberta pela suíte automatizada + E2E sobre o mesmo sha.
- **Observabilidade RED**: não há dashboard de métricas de latência/erro verificável de 1ª mão nesta
  onda; apenas health check e painel north-star.

---

## Apêndice A — Evidências detalhadas

### A.1 — Estado das estórias no índice

- `index.json`: STORY-014 `done`, STORY-015 `done`, STORY-016 `done`, STORY-017 `done`, STORY-018 `in_progress`.
- STORY-015 estava `in_review` no início desta sessão; o PO a aprovou (`in_review→done`) como
  pré-condição da validação — commit `91291c8` ("chore(po): aprova STORY-015 ..."). EPIC-003 movido
  `ready→in_review` no mesmo commit.

### A.2 — CA → teste (fonte)

Mapas declarados nas "Notas do agente" de STORY-015 (§Implementação concluída), STORY-016 (§Designer→
Programador), STORY-017 (§UI implementada). Cada CA aponta testes nominais; a suíte completa
(237 testes) roda verde, confirmando que os testes existem e passam — ver A.6.

### A.3 — Núcleo do crédito e reconciliação (leitura de código + teste)

- `app/Domain/Cashback/CreditarCashbackService.php`: no-op se cupom não validado / sem atribuição /
  crédito 0; `DB::transaction` com `Carteira::lockForUpdate()`; guarda de idempotência
  (`where cupom_id, tipo=credito_cashback ->exists()`); lançamento no ledger + `increment('saldo_centavos')`
  na MESMA transação → `saldo == SUM(ledger)` por construção.
- Testes: `tests/Feature/Cashback/CreditarCashbackServiceTest.php::test_reconciliacao_saldo_igual_soma_do_ledger`,
  `::test_credito_e_idempotente_por_cupom`, `::test_sem_atribuicao_nao_credita`.

### A.4 — Carteira (read-model + contrato)

- `tests/Feature/Carteira/ExtratoCarteiraTest.php` (saldo formatado, ordenação, isolamento entre
  carteiras, fallback de crédito sem cupom), `tests/Feature/Carteira/CarteiraControllerTest.php`
  (contrato Inertia, saldo zero/extrato vazio, exige auth).

### A.5 — Saque (reserva + máquina de estados + HTTP + autorização)

- `tests/Feature/Saque/SolicitarSaqueServiceTest.php`, `OperarSaqueServiceTest.php`,
  `SolicitarSaqueHttpTest.php`, `BackofficeSaquesHttpTest.php`.

### A.6 — Cobertura (comando reproduzível)

- Comando: `./vendor/bin/sail artisan test --coverage` (deployed sha `9c77cbc`).
- Resultado: `{"tool":"phpunit","result":"passed","tests":237,"passed":237,"assertions":955}`; **Total 94,3%**.
- Núcleo: `Domain/Cashback/CalculadoraCashback` 100%, `CreditarCashbackService` 100%,
  `ExtratoCarteira` 100%, `Listeners/CreditarCashbackAoValidar` 100%; `Domain/Saque/SolicitarSaqueService`
  100%, `SaqueService` 100%, `Cpf` 100%; controllers `Carteira`/`Saque`/`Backoffice\Saques` 100%.
- Descobertas de *glue* (fora do núcleo): `Models/CupomAtribuicao` 0%, `Models/Role` 0%,
  `Models/Carteira` 66,7% (L37-43), `CarteiraTransacao` 75% (L50), `Saque` 75% (L50).

### A.7 — E2E em browser real (Dusk) — execução própria

- Comando: `./vendor/bin/sail artisan dusk tests/Browser/CarteiraTest.php tests/Browser/SolicitarSaqueTest.php tests/Browser/BackofficeSaquesTest.php`.
- Resultado: `{"tool":"phpunit","result":"passed","tests":9,"passed":9,"assertions":23}`.

### A.8 — CI/CD (deployed sha)

- `gh run view 28704414576`: title "fix(infra): deploy semeia homolog ...", headSha
  `9c77cbce8df51a10fd339759e5aa1e6c61581305`, conclusion **success**. Jobs: "Testes + build" success,
  "E2E (Dusk)" success, "Deploy homologação" success.

### A.9 — Migrações reversíveis

- `database/migrations/2026_07_03_000003_create_base_pagamento_tables.php` (carteiras, carteira_transacoes,
  cupom_atribuicoes), `..._000004_create_roles_tables.php` (roles, role_user),
  `..._000005_create_saques_table.php` (saques + `saque_id` no ledger) — cada uma com `function down()`.

### A.10 — Homologação viva (checagem própria, 2026-07-04)

- `curl https://quantah-homolog.34.39.229.117.sslip.io/up` → **HTTP 200**.
- `curl https://.../carteira` (sem seguir redirect) → **HTTP 302**, `Location: .../login` (auth-gate).
- `curl https://.../login` → **HTTP 200**.

### A.11 — LGPD / CPF (KYC do saque)

- `app/Http/Controllers/Backoffice/SaquesController.php` L36 `Cpf::mascarar($s->cpf)` (lista), L53 CPF
  completo no detalhe (KYC). `app/Domain/Saque/Cpf.php::mascarar`.
- Busca por CPF/chave_pix em chamadas de log (`app/Domain`, `app/Http`, `app/Models`): apenas
  comentários da anonimização de coleta (`AnonimizadorCpf`, `SpSefazAdapter`); nenhum log grava CPF.

### A.12 — Segredos

- `git ls-files` não retorna `.env` (não rastreado). `.github/workflows/ci-cd.yml`: segredos vêm de
  `secrets.*` (DEPLOY_SSH_KEY, PROD_ENV, DEPLOY_HOST, DEPLOY_USER, GITHUB_TOKEN). Busca por literais de
  segredo em `app/config/database/routes`: nenhum resultado.

---

## Apêndice B — Arquivos anexados

> Evidência pesada mantida no scratchpad da sessão (logs de execução); os fatos relevantes estão
> transcritos acima. Não versionei binários/logs extensos no repositório.

- Log de cobertura PHPUnit (237 testes, Total 94,3%) — execução local sobre `9c77cbc`.
- Log de Dusk EPIC-003 (9 testes verdes) — execução local.

---

## Histórico

- 2026-07-04 — relatório inicial submetido por validador (sessão `claude-validador-story018`).
  Veredito **APPROVED**. Validação de 1ª mão sobre o sha deployado `9c77cbc`.
