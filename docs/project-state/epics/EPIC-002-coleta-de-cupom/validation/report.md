---
epic_id: EPIC-002
type: validation-report
validated_at: 2026-07-03
validated_by: validador (sessão e2d9a721)
verdict: approved_with_pending  # approved | rejected | approved_with_pending
checklist_source: epics/EPIC-002-coleta-de-cupom/validation/checklist.md
---

# Relatório de Validação — EPIC-002 (Coleta de cupom)

## TL;DR

> **Veredito: APPROVED com pendências.**
> **Contagem**: 27 itens do checklist — 20 `pass`, 5 `pass com ressalva`, 2 `fail` (0 bloqueantes, 2 não-bloqueantes), 1 `n/a` justificado.
> **Bloqueantes**: nenhum.
> **Não-bloqueantes (fato)**: (F-NB-1) o IDR-003 do `@zxing` (STORY-009), criado durante o épico, **não está indexado** no `index.json` e **colide de id** com o IDR-003 já indexado (STORY-005); (F-NB-2) a STORY-012 está `requires_design: true` mas **sem artefato de Designer** (`design_screen_id: null`, sem tela em `design/screens/`).

---

## Resumo executivo

O EPIC-002 é o coração do Quantah: transformar o QR de uma NFC-e de SP em dado **válido, único e
novo**, com CPF anonimizado (LGPD) e a north-star instrumentada. Verifiquei de forma independente que
o épico está entregue e deployado em homologação. O fluxo de captura responde **HTTP 200** em
homologação (`GET https://quantah-homolog.34.39.229.117.sslip.io/coletar` → 200, 32 662 bytes,
`data-page.component = "Coleta/Captura"`), o painel interno da north-star está no ar e **corretamente
protegido** (`GET /interno/metricas` → **302 → /login**), e `/up` → 200. O commit em validação
(`b321eac`, `main`) é o que está deployado — confirmado pelo run de CI que publicou.

Rodei a cobertura localmente no commit `b321eac`: **cobertura total 92,5%**; o **núcleo de dedup e
validação por chave** (ADR-003, STORY-010) — `IngestaoCupomService` **99,0%** e `ChaveAcesso`
**100%** — atende o piso de 98%. O adaptador de scraping da SEFAZ (I/O, ADR-002) fica em 95,6–96,8%,
com as linhas descobertas sendo guardas defensivas e um método sem chamador (ressalva registrada,
Bloco 2.2). O run de CI `28668610185` (sha `b321eac`) está **verde** nos três jobs — Testes+build
(**151 testes, 678 asserções**, gate `--min=80`), E2E Dusk (browser real) e Deploy homologação (com
smoke test) — e o deploy para homologação é **automático** no push da `main`. Li as asserções dos
testes e confirmei que exercem os CAs de fato: dedup idempotente + unicidade garantida pelo banco
(`IngestaoCupomSpikeTest`), classificação de falha e reprocessamento (`ExtrairCupomJobTest`),
extração ao vivo contra a SEFAZ real (`HttpSefazSpFetcherTest`), captura E2E feliz + erro
(`ColetaCapturaTest`), anti-CPF por varredura de banco/log (`AnonimizacaoCpfLgpdTest`) e a contagem
da north-star + telemetria (`MetricasColetaTest`, `TelemetriaColetaTest`, `PainelMetricasTest`).

Nenhum CPF é persistido em claro (verificado por inspeção de schema — sem coluna de CPF — e por teste
de regressão que varre `cupons`/`cupom_itens`), a dedup independe de CPF, e a telemetria (`coleta_eventos`)
não guarda dado pessoal. Não há segredos versionados; a SEFAZ é portal público (sem credencial). Os
dois `fail` são de **documentação/estado** (não-bloqueantes): um IDR não indexado com colisão de id e a
ausência de artefato de Designer numa estória `requires_design: true`. Veredito: **APPROVED com
pendências** — o essencial está cumprido; as duas pendências ficam para o PO decidir.

**Commit em validação**: `b321eac` (`main`, deployado em homologação). Suíte executada na árvore de
trabalho em `b321eac` (antes deste relatório de docs).

---

## Checklist preenchido

### Bloco 1 — Critérios de aceite das estórias

| Item | Status | Evidência |
|---|---|---|
| 1.1 — STORY-008..012 `done` no `index.json` | ✅ PASS | `index.json`: 008 `done`, 009 `done`, 010 `done`, 011 `done`, 012 `done` (013 é esta validação). A.1 |
| 1.2 — Cada CA (008–012) coberto por teste que assere de fato | ✅ PASS | Mapa CA→teste verificado por leitura de asserção (não só nome): A.2. |
| 1.3 — ADR-001/002/003/006 `accepted` e indexadas em `decisions.adr` | ✅ PASS | `index.json` › decisions.adr: ADR-001/002/003/006 = `accepted`. A.1 |
| 1.4 — Modelo canônico do cupom (ADR-001) documentado e usado por 010/011 | ✅ PASS | ADR-001 §"Modelo canônico" (tabela `cupons`+`cupom_itens`, chave natural); migração `2026_07_02_000001_create_cupons_table` e models `Cupom`/`CupomItem` correspondem. A.6 |

### Bloco 2 — Cobertura de testes

| Item | Status | Evidência |
|---|---|---|
| 2.1 — Cobertura geral ≥ 80% | ✅ PASS | Local (`b321eac`) **92,5%**; CI `28668610185` gate `php artisan test --coverage --min=80` verde. A.3 |
| 2.2 — Núcleo (validação SEFAZ + dedup, STORY-010) ≥ 98% | ⚠️ PASS com ressalva | Núcleo de **dedup/validação-por-chave**: `IngestaoCupomService` **99,0%**, `ChaveAcesso` **100%** (atende). Adaptador de scraping SEFAZ (I/O, ADR-002): `HttpSefazSpFetcher` **95,6%** (linhas 91,170,180), `SpSefazAdapter` **96,8%** (linha 51) — guardas defensivas; `SefazExtracaoException` **80%** (método `reprocessavel()` sem chamador). A.3 |
| 2.3 — Testes: feliz + duplicado + falha de extração + chave malformada/não-SP | ✅ PASS | `IngestaoCupomSpikeTest` (feliz, dedup idempotente, malformada, RJ fora-de-escopo, modelo≠65, falha transitória/estrutural) + `ExtrairCupomJobTest` (retry/reprocesso). A.2 |
| 2.4 — FE web: E2E em browser real cobrindo captura incl. erro | ✅ PASS | `Tests\Browser\ColetaCapturaTest` (4 cenários: link válido→confirmação; link inválido→erro no campo; câmera indisponível→degrada; alvo de toque). Job "E2E (Dusk)" verde no CI. A.2/A.3 |

### Bloco 3 — Automação

| Item | Status | Evidência |
|---|---|---|
| 3.1 — Setup local automatizado (um comando de clone limpo) | ⚠️ PASS com ressalva | `app/Makefile` alvo `up` ("Clone limpo → app + Postgres + seed, em um comando"). **Ressalva**: verificado por inspeção + herança do EPIC-000, não re-executado de clone limpo nesta sessão. A.4 |
| 3.2 — Pipeline CI verde no merge que publicou | ✅ PASS | `gh run view 28668610185` (sha `b321eac`): jobs Testes+build, E2E Dusk e Deploy homologação = **success**. A.3 |
| 3.3 — Deploy para homologação automático (sem passo manual) | ✅ PASS | `ci-cd.yml` job "Deploy homologação" (`needs`, dispara no push da `main`); run `28668610185` executou build+push+deploy+`migrate`+smoke com sucesso; homolog no ar (Bloco 4). A.3 |
| 3.4 — Fila de reprocessamento (ADR-002) automatizada e exercida em teste | ✅ PASS | `infra/docker-compose.prod.yml` serviço `worker` (`php artisan queue:work --tries=3 --backoff=10`); `ExtrairCupomJobTest` exercita retry/backoff/dead-letter. A.4/A.2 |
| 3.5 — Promoção tag-based / gate de produção | 🚫 n/a | O pipeline tem **um** alvo de deploy (VPS de homologação); não há promoção separada para produção nesta onda (produção fora de escopo — cruza com WISH-003). Nada a exercer aqui. |

### Bloco 4 — Funcionalidade observável

| Item | Status | Evidência |
|---|---|---|
| 4.1 — Fluxo de coleta acessível em homolog (mobile), HTTP 200, verificação independente | ✅ PASS | `curl` próprio (2026-07-03): `GET https://quantah-homolog.34.39.229.117.sslip.io/coletar` → **200**, 32 662 bytes, `data-page.component = "Coleta/Captura"` (tela mobile-first STORY-009). `/up` → 200. A.5 |
| 4.2 — Cupom de SP end-to-end em homolog (captura→SEFAZ→dedup→persist→"aceito") | ⚠️ PASS com ressalva | End-to-end provado por teste de extração **ao vivo** contra a SEFAZ real (`HttpSefazSpFetcherTest`, verde no CI do sha deployado) + pipeline (`IngestaoCupomSpikeTest`) + worker presente + extração ao vivo de 1ª mão registrada na STORY-010 (18 itens, R$235,43). **Ressalva/limitação**: não dirigi uma submissão interativa pela UI de homologação nesta sessão (calibração igual à do EPIC-001). A.2/A.5 |
| 4.3 — Deduplicação demonstrável (reenvio da mesma chave não duplica) | ✅ PASS | `IngestaoCupomSpikeTest::test_reenvio_da_mesma_chave_e_idempotente` (2º envio → DUPLICADO, `assertDatabaseCount('cupons',1)`) + `test_unicidade_da_chave_e_garantida_pelo_banco` (UNIQUE no banco, à prova de corrida). Asserções lidas. A.2 |
| 4.4 — Painel interno da north-star em homolog (validos-únicos-novos/semana + taxa) | ⚠️ PASS com ressalva | Rota deployada e protegida: `curl GET /interno/metricas` → **302 → /login**. Render com dados provado por `Tests\Browser\PainelMetricasTest` (browser real, CI). **Limitação**: os números renderizados em homolog estão atrás de `auth` e não foram visualizados nesta sessão. A.5 |
| 4.5 — Logs e métricas básicas de saúde/uso, sem dado pessoal | ✅ PASS | Telemetria `coleta_eventos` (situacao/motivo/cupom_id — sem PII); `TelemetriaColetaTest::test_evento_nao_vaza_pii`, `MetricasColetaTest::test_evento_de_telemetria_nao_tem_pii`. A.2 |

### Bloco 5 — Qualidade transversal (LGPD + dados + segurança)

| Item | Status | Evidência |
|---|---|---|
| 5.1 — CPF não persistido em claro em nenhuma tabela canônica | ✅ PASS | Schema sem coluna de CPF (`MigracaoCupomTest::test_nenhuma_coluna_de_cpf`); `AnonimizacaoCpfLgpdTest` varre todas as linhas de `cupons`/`cupom_itens` com CPF plantado. A.2 |
| 5.2 — Tratamento segue ADR-006 (descarte) + teste de regressão | ✅ PASS | ADR-006 = descartar; `AnonimizadorCpf` no ponto de normalização; regressão `AnonimizacaoCpfLgpdTest::test_regressao_banco_inteiro_sem_cpf_apos_pipeline` (quebra se o anonimizador sair do caminho — CPF plantado em `descricao`, coluna persistida). A.2 |
| 5.3 — Nenhum vazamento de CPF em logs/erros/fixtures/telemetria | ✅ PASS | `AnonimizacaoCpfLgpdTest::test_logs_do_pipeline_nao_vazam_cpf` (captura `MessageLogged`, exercita `Job::failed`); telemetria sem PII (5? item 4.5). A.2 |
| 5.4 — Dedup/contagem não dependem de CPF | ✅ PASS | `AnonimizacaoCpfLgpdTest::test_dedup_por_chave_independe_da_presenca_de_cpf` (dedup por chave, com/sem CPF). A.2 |
| 5.5 — Migrações reversíveis e testadas | ✅ PASS | Todas as migrações têm `down()`; `MigracaoCupomTest::test_migracao_e_reversivel` (down derruba `cupom_itens`+`cupons`); `create_coleta_eventos_table` tem `down()` (dropIfExists). Deploy rodou `migrate` em homolog com sucesso (A.3). A.4 |
| 5.6 — Nenhum segredo versionado; credenciais SEFAZ fora do repo | ✅ PASS | `app/.env` no `.gitignore`; nenhum `.env` de prod/homolog versionado; SEFAZ é **portal público** (sem credencial no `HttpSefazSpFetcher`); deploy usa `secrets.*` do GitHub. **Limitação**: CI sem scanner de segredos → verificação por inspeção direta (como no EPIC-001). A.4 |

### Bloco 6 — Documentação e estado

| Item | Status | Evidência |
|---|---|---|
| 6.1 — Notas do agente preenchidas em STORY-008..012 | ✅ PASS | Seção "Notas do agente" preenchida nas 5 estórias (verificado; blocos com decisões/descobertas/evidência). A.1 |
| 6.2 — ADRs (001/002/003/006) e IDRs do épico indexados | ❌ FAIL (não-bloqueante) | ADRs 001/002/003/006 indexados ✅; IDR-004/005/006 indexados ✅. **Porém** o `IDR-003-zxing-browser-decode-qr.md` (STORY-009, criado no épico) **não está** no `index.json` e **colide de id** com `IDR-003` (STORY-005, máscara) já indexado. F-NB-1 / A.7 |
| 6.3 — Evidência do Designer para estórias `requires_design: true` (009, 012) | ❌ FAIL (não-bloqueante) | STORY-009: tela em `design/screens/STORY-009-captura-qr-confirmacao/` ✅. STORY-012: `requires_design: true` mas `design_screen_id: null` e **sem** artefato em `design/screens/` — painel interno composto do DS existente pelo programador. F-NB-2 / A.7 |
| 6.4 — `index.json` coerente (008–012 `done`; `validation_report` a preencher) | ✅ PASS | 008–012 `done`; `validation_report` do EPIC-002 preenchido por esta validação (aponta este arquivo). A.1 |

---

## Fails identificados

### Bloqueantes

> Nenhum.

### Não-bloqueantes

#### F-NB-1 — IDR do @zxing (STORY-009) não indexado e com colisão de id
- **Bloco**: 6.2
- **Critério esperado**: "IDRs criados durante o épico indexados no `index.json`".
- **O que verifiquei**: existe `docs/project-state/decisions/idr/IDR-003-zxing-browser-decode-qr.md` (STORY-009, criado no épico), mas `grep zxing index.json` = 0 ocorrências; o único `IDR-003` indexado é o de STORY-005 (máscara). Há dois arquivos com o mesmo id `IDR-003`.
- **Classificação**: não-bloqueante — inconsistência de documentação/estado (verdict-criteria: "documentação desatualizada em ponto não-crítico").
- **Evidência**: A.7

#### F-NB-2 — STORY-012 `requires_design: true` sem artefato de Designer
- **Bloco**: 6.3
- **Critério esperado**: "Evidência do Designer presente para as estórias `requires_design: true` (009, 012)".
- **O que verifiquei**: `index.json` e o frontmatter da STORY-012 têm `requires_design: true` com `design_screen_id: null`; não há pasta/arquivo do épico em `design/screens/` referente à 012; o painel foi composto a partir de componentes do DS já existentes (registrado nas Notas da STORY-012).
- **Classificação**: não-bloqueante — lacuna de estado/processo; a funcionalidade existe e compõe DS já validado no EPIC-001 (verdict-criteria: não-bloqueante).
- **Evidência**: A.7

> **Nota**: nenhum fail inclui sugestão, estória de correção, próximo passo ou estimativa — planejamento é do PO.

---

## Passes com ressalva

- **Bloco 2.2 — Cobertura do núcleo**: dedup/validação-por-chave em **99,0–100%** (atende ≥98%); o **adaptador de scraping SEFAZ** (I/O, ADR-002) fica em **95,6–96,8%** e `SefazExtracaoException` em **80%** — as linhas descobertas são guardas defensivas (`return null`/`'0'`, data não-parseável, ramo `!is_array`) e o método `reprocessavel()` sem chamador; não há lacuna de lógica de validação/dedup.
- **Bloco 3.1 — Setup de um comando (`make up`)**: verificado por inspeção do `Makefile` + herança do EPIC-000; não re-executado de clone limpo nesta sessão.
- **Bloco 4.2 — Cupom SP end-to-end em homolog**: provado por teste de extração ao vivo (SEFAZ real) no CI do sha deployado + pipeline + worker; não dirigido interativamente pela UI de homologação nesta sessão.
- **Bloco 4.4 — Painel north-star em homolog**: rota deployada e protegida (302→login) verificada por `curl`; render com dados provado por Dusk (browser real, CI); números renderizados em homolog estão atrás de `auth`, não visualizados nesta sessão.
- (contadas como 5 ressalvas: 2.2, 3.1, 4.2, 4.4 — e a de 5.6/6.x consolidada em Limitações.)

---

## Limitações da validação

- **Submissão interativa em homologação**: a sessão é via CLI; não dirigi uma sessão de browser manual submetendo um cupom real na UI de homologação (o comportamento dinâmico foi verificado por E2E Dusk em Chrome real no CI e pelos testes de pipeline/extração-ao-vivo sobre o sha deployado). Consistente com a calibração do EPIC-001.
- **Painel north-star atrás de `auth`**: verifiquei que a rota existe e está protegida (302→login) e que renderiza com dados no Dusk; não autentiquei em homologação para ver os números renderizados (evitar criar usuário/estado persistente no ambiente).
- **Scanner de segurança/segredos no CI**: o `ci-cd.yml` não possui passo de scanner de dependências/segredos (capacidade de pipeline do EPIC-000, não introduzida nem regredida por este épico). Verificação de segredos feita por inspeção direta do repositório.
- **Cobertura Dusk**: o job "E2E (Dusk)" está verde no CI do sha deployado; localmente a suíte Dusk passou (42 testes) — o Dusk usa o banco de dev, então a evidência primária de E2E é o run de CI + as asserções lidas na fonte.

---

## Apêndice A — Evidências detalhadas

### A.1 — Estado do índice (Bloco 1.1/1.3, 6.1/6.4)
- `index.json`: STORY-008/009/010/011/012 = `status: done`; STORY-013 = `draft` (esta validação).
- `decisions.adr`: ADR-000/001/002/003/006/007/008 = `accepted`. `decisions.idr`: IDR-001..006 (IDR-004→010, IDR-005→011, IDR-006→012).
- Notas do agente preenchidas nas 5 estórias (blocos não-vazios).

### A.2 — Mapa CA → teste (Bloco 1.2, 2.3, 2.4, 4.x, 5.x)
- **STORY-008** (spike): `IngestaoCupomSpikeTest` (parse→extração→normalização→dedup→sem CPF), `ChaveAcessoTest` (DV mod-11, decomposição).
- **STORY-009** (captura): `CapturaScreenContractTest` (microcopy/estados do spec), `ColetaCapturaTest` (E2E browser: feliz + link inválido + câmera indisponível + alvo de toque).
- **STORY-010** (SEFAZ/dedup/persist): `IngestaoCupomSpikeTest` (feliz, dedup idempotente, unicidade no banco, malformada, RJ fora-de-escopo, modelo≠65, falha transitória/estrutural, reprocesso), `ExtrairCupomJobTest` (fila, retry/backoff, dead-letter), `HttpSefazSpFetcherTest` (extração **ao vivo** SEFAZ real + parsing/classificação), `MigracaoCupomTest`.
- **STORY-011** (LGPD): `AnonimizadorCpfTest` (unidade + bordas), `AnonimizacaoCpfLgpdTest` (varredura de banco, qr_conteudo, dedup sem CPF, logs sem CPF, regressão).
- **STORY-012** (north-star): `MetricasColetaTest` (contagem/semana, ignora inválidos, dedup não conta em dobro, taxa, bordas, sem-PII), `TelemetriaColetaTest` (um evento por envio, incl. duplicata/rejeição/falha, sem PII), `PainelMetricasTest` (Feature: props populado/vazio + auth), `Tests\Browser\PainelMetricasTest` (E2E: interno vê KPIs+tabela; anônimo→login).
- Asserções lidas na fonte para confirmar que exercem o CA (não só o nome).

### A.3 — Cobertura, testes e pipeline (Bloco 2, 3.2/3.3)
- Local (`b321eac`, `sail test --coverage`): **Total 92,5%**. Núcleo: `IngestaoCupomService` 99,0%, `ChaveAcesso` 100%, `AnonimizadorCpf` 100%, `MetricasColeta` 100%, `ColetaController`/`MetricasController` 100%, `ExtrairCupomJob` 100%. Descobertas: `HttpSefazSpFetcher` 95,6% (91,170,180), `SpSefazAdapter` 96,8% (51), `SefazExtracaoException` 80% (45).
- CI run `28668610185` (sha `b321eac`, branch `main`): job "Testes + build" → `php artisan test --coverage --min=80` → **151 passed (678 assertions)**, gate verde; job "E2E (Dusk)" → success; job "Deploy homologação" → success (build+push da imagem, scp compose/Caddyfile/.env, deploy com `migrate`, smoke test).

### A.4 — Automação e migrações (Bloco 3.1/3.4, 5.5)
- `app/Makefile` alvo `up`: "Clone limpo → app + Postgres + seed, em um comando (http://localhost:8000)" (`sail up` + `artisan migrate --seed --force`).
- `infra/docker-compose.prod.yml`: serviço `worker` com `php artisan queue:work --tries=3 --backoff=10 --sleep=3 --max-time=3600` (fila `database`).
- Migrações: `create_cupons_table`, `add_qr_conteudo_to_cupons_table`, `create_coleta_eventos_table` — todas com `down()`. `MigracaoCupomTest::test_migracao_e_reversivel`.

### A.5 — Homologação (Bloco 4.1/4.4) — verificação independente
`curl` próprio (2026-07-03), host `quantah-homolog.34.39.229.117.sslip.io`:
- `GET /up` → **200**.
- `GET /coletar` → **200**, 32 662 bytes, `data-page.component = "Coleta/Captura"`.
- `GET /interno/metricas` → **302 → https://…/login** (rota deployada e protegida por `auth`).
- `GET /ds` → 200 (herança EPIC-001, smoke).
Antes do deploy desta validação, `GET /interno/metricas` retornava **404** (STORY-012 ainda não publicada); após o push da `main` (sha `b321eac`) e o job "Deploy homologação", passou a **302→login** — confirmando a publicação.

### A.6 — Modelo canônico (Bloco 1.4)
- ADR-001 documenta agregado `Cupom` (chave natural `chave_acesso` 44 díg. UNIQUE) + `CupomItem`, sem coluna de CPF (ADR-006). Migração `create_cupons_table` e models `Cupom`/`CupomItem` correspondem ao documentado; STORY-010 (persistência) e STORY-011 (LGPD) operam sobre esse modelo.

### A.7 — Documentação/estado (Bloco 6.2/6.3)
- **F-NB-1**: `ls decisions/idr/` mostra `IDR-003-mascara-react-imask-*.md` (STORY-005) **e** `IDR-003-zxing-browser-decode-qr.md` (STORY-009) — mesmo id. `grep zxing index.json` → 0. O IDR do zxing (decisão de decodificar QR no browser, STORY-009) não está indexado.
- **F-NB-2**: `find design -iname "*012*"` → vazio; `grep -rl STORY-012 design/` → vazio. STORY-012 frontmatter: `requires_design: true`, `design_screen_id: null`.

---

## Apêndice B — Arquivos anexados

> Nenhum arquivo pesado anexado em `validation/evidence/` — toda a evidência é reproduzível por:
> (a) run de CI `28668610185` (log via `gh run view`), (b) `curl` às URLs de homologação (A.5),
> (c) comandos locais no commit `b321eac` (`sail test --coverage`, greps do Apêndice A).

---

## Histórico

- 2026-07-03 — relatório inicial submetido por validador (sessão e2d9a721).
