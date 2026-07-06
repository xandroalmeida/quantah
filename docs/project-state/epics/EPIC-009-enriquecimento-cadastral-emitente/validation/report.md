---
epic_id: EPIC-009
type: validation-report
validated_at: 2026-07-06
validated_by: validador (sessão db3634ec)
verdict: approved_with_pending  # approved | rejected | approved_with_pending
checklist_source: epics/EPIC-009-enriquecimento-cadastral-emitente/validation/checklist.md
---

# Relatório de Validação — EPIC-009

> **2ª passada (pós-deploy).** A 1ª passada (mesma data) reprovou o épico porque o código não estava em
> homologação nem havia passado por CI. Sob autorização do PO, o épico foi enviado (`origin/main` →
> `d14b289`) e deployado em homologação pela tag `v0.1.1-rc-14`; esta versão do relatório substitui a
> anterior. O histórico factual da 1ª passada está preservado na seção "Histórico".

## TL;DR

> **Veredito**: **APPROVED com pendências** (1 pendência não-bloqueante).
> **Contagem**: 12 passes, 2 passes com ressalva, 1 fail não-bloqueante (0 bloqueantes), 0 n/a.
> **Pendência (resumo factual)**: o caminho **enriquecido ao vivo em homologação** (worker real → API
> pública → emitente enriquecido/cache visível no Backoffice) não foi demonstrado por observação direta;
> os 4 cupons presentes em homologação são anteriores ao deploy do épico e, por design, não são
> re-enriquecidos, e não houve como injetar um cupom novo (NFC-e real) de forma headless. O mecanismo
> está deployado, com worker de fila ativo, CI verde (unit+feature+E2E Dusk) sobre o sha deployado, e o
> estado "pendente" renderiza corretamente no Backoffice de homologação.

---

## Resumo executivo

O EPIC-009 entrega o enriquecimento cadastral do emitente (CNPJ via API pública da RFB, em fila, com
cache parametrizável) e sua exibição no Backoffice. **No plano de código a entrega é madura e
verificável** e **está agora deployada em homologação**: a suíte completa roda verde (390 testes, 1744
asserções), a cobertura total é 96,2% com **100% em todos os arquivos novos do épico**, o E2E Dusk do
Backoffice de cupons passa (3 cenários), os CAs das STORY-040/041 têm testes com asserções fortes, o TDD
está evidente no histórico e não há segredos no código. O pipeline `ci-cd.yml` rodou **verde nos três
jobs** (Testes+build, E2E Dusk, Deploy homologação) sobre o sha `d14b289`, e o deploy publicou a versão
`v0.1.1-rc-14` na VM de homologação (smoke HTTPS `/up` = 200 no próprio pipeline e reconfirmado por mim).

A revalidação independente confirmou: homologação está no ar **na versão `v0.1.1-rc-14`** (endpoint
`/version`), o Backoffice de cupons é acessível pelo operador (login por sessão → HTTP 200) e protegido
por RBAC (não autenticado → 302 `/login`), e o estado **"pendente"** do enriquecimento é exibido
corretamente para os cupons existentes. Há um worker de fila ativo no compose de homologação
(`php artisan queue:work`).

**A única pendência** é que **nenhum emitente enriquecido foi observado ao vivo em homologação**. Os 4
cupons presentes no ambiente foram validados **antes** do deploy do épico; como o enriquecimento não é
retroativo (fora de escopo, por design), eles permanecem `pendente` — comportamento correto, não
defeito. Demonstrar o caminho enriquecido exigiria um cupom novo pós-`rc-14` (que o worker processaria),
o que depende de submeter uma NFC-e real; não há como injetá-la de forma headless nesta sessão, nem
acesso SSH à VM para disparar o comando `enriquecimento:cnpj`. Consequentemente, a integração "worker
deployado → API pública → emitente enriquecido" permanece **não observada em ambiente deployado** (foi
provada por teste automatizado e por smoke local na STORY-040, mas o E2E Dusk semeia o estado final do
emitente em vez de exercitar a fila+API reais). Classifico isso como **fail não-bloqueante**: o
mecanismo está deployado e verificado no sha, o worker está ativo e a renderização está correta; falta
apenas a demonstração ao vivo, que requer um cupom real.

---

## Checklist preenchido

### Bloco 1 — Critérios de aceite (por estória)

| Item | Status | Evidência |
|---|---|---|
| 1.1 — STORY-039/040/041 `done` no `index.json`; ADRs do spike `accepted` | ⚠️ | ADR-012/013/014/015 = `accepted`. STORY-039 = `done`. STORY-040/041 = `in_review`; épico = `ready`. Pela invariante 5 (`indexing.md`), `done` só é possível **após** relatório aprovado — `in_review` é o estado pré-validação esperado. Ver Ressalvas. |
| 1.2 — STORY-040 (serviço): consulta assíncrona; cache dentro do TTL (0 chamada externa); TTL parametrizável; retry/backoff do ADR; não encontrado/sem CNAE em estado distinto | ✅ | Apêndice A.1 (mapa CA→teste, asserções fortes) e A.3 (suíte verde). `config/enriquecimento.php` → `ttl_dias` via env; Job `tries=3`, `backoff=[30,120,300]`. |
| 1.3 — STORY-041 (pipeline + Backoffice): disparo pós-validação sem afetar latência; detalhe exibe razão social, CNAE (código+descrição), município/UF, situação; estados pendente/indisponível claros; emitente único por CNPJ | ✅ | Apêndice A.2 (listener em `CupomValidado`; unicidade `Emitente::count()==1`; `ApresentacaoEmitente` cobre estados) e A.4 (Dusk verde). |

### Bloco 2 — Cobertura de testes

| Item | Status | Evidência |
|---|---|---|
| 2.1 — Cobertura ≥ 80% no código novo | ✅ | Todos os arquivos novos do épico em **100,0%**; total do projeto **96,2%**, gate `--min=80` verde. Apêndice A.3. |
| 2.2 — Cobertura ≥ 98% em regra de cache/fallback | ✅ | `EnriquecimentoService`, `FallbackEnriquecedor`, `BrasilApiEnriquecedor`, `RfbOpenDataEnriquecedor`, `ApresentacaoEmitente` = 100%. Apêndice A.3. |
| 2.3 — TDD evidenciado no histórico | ✅ | `test(STORY-040)`→`feat(STORY-040)`; `test(STORY-041)`→`feat(STORY-041)`. |
| 2.4 — Independência da API externa (dublês) + E2E Dusk; feliz + falha + TTL vencido + CNPJ inexistente | ✅ | `FakeEnriquecedor` (contador de chamadas); `Http::fake`/`Queue::fake`; Dusk `BackofficeCuponsTest`. Apêndice A.1/A.4. |
| 2.5 — Suíte completa verde **sobre o sha deployado** | ✅ | CI rodou **verde** (Testes+build **e** E2E Dusk) sobre `d14b289`, que é o sha publicado como `v0.1.1-rc-14`. Run `28798681977` (success). Apêndice A.6. |

### Bloco 3 — Transversais da WAVE-2026-03

| Item | Status | Evidência |
|---|---|---|
| 3.1 — Falha do enriquecimento nunca visível ao Colaborador nem perde cupom | ✅ | `EnriquecerEmitenteJobTest::test_falha_nunca_toca_o_cupom_nem_lanca_ao_usuario`, `..._failed_marca_nao_enriquecido`, `..._transitoria_reprocessa`, `..._estrutural_nao_reprocessa_e_marca_nao_enriquecido`. |
| 3.2 — Backoffice pt-BR, padrão DS, RBAC (ADR-009) | ✅ | RBAC verificado **em homologação**: não autenticado → 302 `/login`; operador autenticado → HTTP 200 (Apêndice A.6). Testes: `test_nao_operador_recebe_403_*`; Dusk `nao_operador_e_barrado`. CNAE formatado pt-BR `4711-3/02 — ...`. |
| 3.3 — Nenhuma credencial/segredo de API em código ou log | ✅ | `grep` por `api_key/token/secret/password/bearer` em `Domain/Enriquecimento`, `EnriquecerEmitenteJob`, `config/enriquecimento.php` → 0 ocorrências. Fontes públicas sem chave (ADR-012). |
| 3.4 — Fila com observabilidade mínima do ADR (profundidade/falhas consultáveis) | ⚠️ | Fila `database`: profundidade em `jobs`, falhas em `failed_jobs`; worker ativo no compose de homolog (`queue:work`). Sinais adicionais do ADR-013 §F6 (taxa de enriquecimento/cache-hit/fila parada) sem superfície de métrica dedicada (dashboards → EPIC-012). Mínimo atendido; ver Ressalvas. |

### Bloco 4 — Funcionalidade observável

| Item | Status | Evidência |
|---|---|---|
| 4.1 — Demonstração em homologação: entregável acessível; detalhe do cupom no Backoffice | ✅ | Homolog no ar em `v0.1.1-rc-14` (`/version`); Backoffice de cupons acessível pelo operador (HTTP 200 autenticado); 4 cupons listados com estado de enriquecimento renderizado. Apêndice A.6. |
| 4.2 — Caminho enriquecido + cache demonstrado **ao vivo em homologação** (cupom novo → emitente enriquecido; 2º cupom mesmo CNPJ → sem chamada externa) | ❌ | Não observado ao vivo: os 4 cupons de homolog são anteriores ao `rc-14` e, por design, não são re-enriquecidos (todos `pendente`); não houve como injetar um cupom novo (NFC-e real) nem acesso SSH à VM. Mecanismo deployado + worker ativo + CI E2E verde no sha, mas a integração worker→API real→emitente enriquecido não foi observada em ambiente deployado. Ver F-NB-1. |
| 4.3 — Sem regressão do loop da Onda 1/2 (coletar → saldo → extrato → saque) | ✅ | Suíte completa (390) cobre coleta/carteira/saque sem regressão; homolog no ar e saudável (`/up`=200) na versão do épico; CI verde no sha deployado. |

> Blocos 5 e 6 do template não constam no `checklist.md` do épico (o PO materializou 4 blocos). Itens
> aplicáveis cobertos acima (segredos em 3.3; "Notas do agente" preenchidas em STORY-040/041; ADRs
> 012-015 indexados e `accepted`).

---

## Fails identificados

### Bloqueantes

Nenhum. Os dois bloqueantes da 1ª passada (F-B-1 "não acessível em homologação" e F-B-3 "CI não rodou
sobre o sha") foram **resolvidos** pelo deploy `v0.1.1-rc-14`: ver Apêndice A.6.

### Não-bloqueantes

#### F-NB-1 — Caminho enriquecido + cache não demonstrado ao vivo em homologação
- **Bloco**: 4.2 / STORY-042 CA-4.
- **Critério esperado**: "cupom novo → emitente enriquecido no Backoffice; segundo cupom do mesmo CNPJ → sem chamada externa (evidência de cache) — em homologação."
- **O que verifiquei**: em homologação (rc-14) há 4 cupons, **todos com enriquecimento `pendente`** (CNPJs 00.286.528/0018-17, 61.412.110/1212-97, 71.451.926/0005-75, 43.259.548/0028-83). Esses cupons foram validados antes do deploy do épico; como o enriquecimento não é retroativo (fora de escopo), permanecem pendentes — comportamento correto. Não consegui submeter um cupom novo (exige NFC-e real; o scraper bate na SEFAZ-SP e a submissão é deduplicada) nem acessar a VM por SSH para disparar `enriquecimento:cnpj`. Logo, nenhum emitente **enriquecido** foi observado ao vivo, e a integração worker→API pública→persistência não foi exercitada em ambiente deployado (o E2E Dusk semeia o estado final do emitente em vez de usar a fila+API reais — ver Nota da STORY-041).
- **Classificação**: não-bloqueante — o mecanismo está deployado, o worker de fila está ativo, o CI (unit+feature+E2E) está verde sobre o sha deployado, e o estado `pendente` renderiza corretamente; falta a demonstração ao vivo do caminho enriquecido, que requer um cupom real. Não é defeito observado, e sim outcome não observado por limitação de acesso/dados no ambiente.
- **Evidência**: Apêndice A.6; Limitações.

> **Nota**: nenhum fail inclui "sugestão", "estória de correção", "próximo passo" ou estimativa de
> tamanho — planejamento é do PO.

---

## Passes com ressalva

- **Bloco 1.1** — ADRs 012-015 `accepted` e STORY-039 `done`, mas STORY-040/041 estão `in_review` e o
  épico `ready`. Pela invariante 5 (`indexing.md`), `done` só é possível após relatório aprovado;
  `in_review` é o estado pré-validação correto. Fato registrado para o PO conciliar a transição de
  status conforme o veredito.
- **Bloco 3.4** — observabilidade mínima da fila atendida (`jobs`/`failed_jobs`, worker ativo); os
  sinais adicionais do ADR-013 §F6 (taxa de enriquecimento, cache-hit, fila parada como métrica) não têm
  superfície própria neste épico (previstos para EPIC-012).

---

## Limitações da validação

- **Demonstração ao vivo do caminho enriquecido**: só é possível com um cupom novo pós-`rc-14`
  (submissão de NFC-e real, que aciona a SEFAZ-SP e a fila) ou acesso SSH à VM para `enriquecimento:cnpj`.
  Nenhum dos dois estava disponível de forma headless nesta sessão. Reportado como F-NB-1, não simulado.
- **Integração real em ambiente deployado** (worker de homolog → BrasilAPI/Minha Receita → persistência):
  provada por teste automatizado e por smoke **local** (Notas da STORY-040), mas não observada em
  homologação. O E2E Dusk cobre a renderização do Backoffice, semeando o emitente final, sem exercitar a
  fila+API reais.
- Homolog inspecionada apenas por HTTP público autenticado (sem SSH; `DEPLOY_HOST` é secret de CI).

---

## Apêndice A — Evidências detalhadas

### A.1 — CAs da STORY-040 (serviço) cobertos por teste com asserção forte

**Contexto**: Bloco 1.2 / STORY-040. Inspeção de `EnriquecimentoServiceTest.php` e `EnriquecerEmitenteJobTest.php`:
- CA-1 `test_cnpj_novo_consulta_e_persiste` (chamadas=1, status `enriquecido`, `assertDatabaseHas`), `test_solicitar_despacha_job_em_cache_miss` (`Queue::assertPushed`).
- CA-2 `test_cnpj_fresco_nao_chama_externo` (`assertSame(0, $fake->chamadas)`), `test_solicitar_nao_despacha_em_cache_fresco` (`Queue::assertNothingPushed`).
- CA-3 `test_cnpj_vencido_reconsulta_e_renova_cache` (chamadas=1, `enriquecido_em` renovado).
- CA-4 `test_ttl_lido_da_config` (`Config::set('enriquecimento.ttl_dias',1)` → reconsulta).
- CA-5 `test_config_de_retry_segue_o_adr` (`tries=3`, `backoff=[30,120,300]`), `test_transitoria_reprocessa`, `test_estrutural_nao_reprocessa_e_marca_nao_enriquecido`, `test_failed_marca_nao_enriquecido`, `test_falha_nunca_toca_o_cupom_nem_lanca_ao_usuario`.
- CA-6 `test_persiste_status_nao_encontrado` (status `nao_encontrado`, CNAE null, `enriquecido_em` setado).

### A.2 — CAs da STORY-041 (pipeline + Backoffice)

**Contexto**: Bloco 1.3 / STORY-041. `EnriquecerEmitenteAoValidarTest.php`, `CuponsBackofficeTest.php`, `ApresentacaoEmitenteTest.php`:
- CA-1 `test_handle_solicita_enriquecimento_do_cnpj_do_cupom` + `test_pipeline_real_enriquece_o_emitente_ao_validar`.
- CA-2 `test_emitente_ja_em_cache_nao_redispara` (`Queue::assertNothingPushed`).
- CA-3 `test_detalhe_mostra_emitente_enriquecido` (Inertia: razão social, CNAE `4711-3/02 — ...`, município/UF/situação).
- CA-4 `test_detalhe_sem_emitente_mostra_pendente` (`emitente.estado == 'pendente'`).
- CA-5 `test_cupons_do_mesmo_cnpj_compartilham_emitente` + `test_dois_cupons_do_mesmo_cnpj_compartilham_um_emitente` (`Emitente::count()==1`).

### A.3 — Suíte completa + cobertura (execução local)

**Reprodução**: container `quantah-laravel.test-1` (PHP 8.5.7, pcov), working tree do sha `d14b289`.
```
docker exec quantah-laravel.test-1 php artisan test --coverage --min=80
```
**Resultado**: `Tests: 390 passed (1744 assertions)`, 0 falhas; cobertura `Total: 96.2 %`; gate `--min=80`
verde. Arquivos do épico (`Domain/Enriquecimento/*`, `Jobs/EnriquecerEmitenteJob`, `Models/Emitente`,
`Backoffice/CuponsController`) = 100%.

### A.4 — E2E Dusk do Backoffice de cupons (local)

```
docker exec quantah-laravel.test-1 php artisan dusk tests/Browser/BackofficeCuponsTest.php
```
**Resultado**: `Tests: 3 passed (8 assertions)` — `operador ve cupom e emitente enriquecido`,
`cupom sem enriquecimento mostra pendente`, `nao operador e barrado`.

### A.5 — Estado de git antes do deploy (contexto da 1ª passada)

```
HEAD=d14b289 (main); origin/main=0399a6e; 11 commits à frente.
v0.1.1-rc-13 → 0399a6e (tag de homolog anterior ao épico).
```
Notas de STORY-040/041 registravam "commits na `main` local; aguarda o push" como pendência de DoD.

### A.6 — Deploy `v0.1.1-rc-14` e revalidação em homologação

**Ações de deploy (autorizadas pelo PO)**:
```
git push origin main            # 0399a6e..d14b289
git tag v0.1.1-rc-14 d14b289 && git push origin v0.1.1-rc-14
```
**CI/CD** (run `28798681977`, https://github.com/xandroalmeida/quantah/actions/runs/28798681977):
- `Testes + build` → **success**
- `E2E (Dusk)` → **success**
- `Deploy homologação` → **success** (build imagem → GHCR → SSH VM → `compose pull/up` → `migrate --force`
  + `db:seed --force` → smoke HTTPS `/up`=200)

**Revalidação independente** (homolog `https://quantah-homolog.34.39.229.117.sslip.io`):
```
GET /version   → {"version":"v0.1.1-rc-14","asset":"d49ad6eda248"}
GET /up        → HTTP 200
GET /backoffice/cupons (não autenticado) → 302 → /login        (RBAC)
POST /login (test@example.com/password)  → 302 → /inicio        (operador seed)
GET /backoffice/cupons (autenticado)     → HTTP 200, 4 cupons listados
```
Estado de enriquecimento dos 4 cupons em homolog: **todos `pendente`** (razão/CNAE nulos) — anteriores
ao `rc-14`, sem enriquecimento retroativo (por design). Worker de fila presente no
`infra/docker-compose.prod.yml` (`php artisan queue:work --tries=3 --backoff=10 --sleep=3 --max-time=3600`).

**Conexão com critério**: resolve F-B-1/F-B-3 da 1ª passada (entregável acessível em homolog na versão do
épico; CI verde sobre o sha deployado). Deixa em aberto F-NB-1 (caminho enriquecido não observado ao vivo).

---

## Apêndice B — Arquivos anexados

> Nenhuma evidência pesada anexada. Saídas de teste/cobertura/Dusk/CI e estado de homolog estão
> transcritas nos apêndices A.3–A.6 (reproduzíveis pelos comandos e URLs listados).

---

## Histórico

- 2026-07-06 — 1ª passada submetida por validador (sessão db3634ec): **REJECTED** — 2 bloqueantes
  (código não deployado em homologação; CI não executado sobre o sha do épico).
- 2026-07-06 — deploy `v0.1.1-rc-14` autorizado pelo PO; 2ª passada (esta versão): **APPROVED com
  pendências** — bloqueantes resolvidos; resta F-NB-1 (caminho enriquecido não demonstrado ao vivo em
  homologação).
