---
story_id: STORY-040
slug: consulta-assincrona-cnpj-cache
title: Consulta assíncrona ao CNPJ do emitente com cache parametrizável
epic_id: EPIC-009
sprint_id: null
type: implementation
target_role: programador
requires_design: false
status: in_review
owner_agent: claude-programador-story040
created_at: 2026-07-06
updated_at: 2026-07-06
estimated_session_size: M
---

# STORY-040 — Consulta assíncrona ao CNPJ do emitente com cache parametrizável

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. Se algo estiver
> ambíguo, registre em "Notas do agente" e pause em vez de adivinhar.

## Contexto (por que esta estória existe)

O motor de pontos (EPIC-010) precisa do CNAE do estabelecimento emitente de cada cupom. O spike
STORY-039 decidiu a fonte (API pública de dados da RFB), a arquitetura de fila e a estratégia de cache.
Você implementa o serviço de enriquecimento: dado um CNPJ, obter e persistir os dados cadastrais, via
fila, respeitando o cache.

- Épico: `epics/EPIC-009-enriquecimento-cadastral-emitente/epic.md`
- Documentos canônicos a ler ANTES de codificar:
  - ADRs criados pela STORY-039 (fonte CNPJ, filas, cache) — em `decisions/adr/`
  - `decisions/pdr/PDR-004-pontuacao-gamificada-substitui-rate-fixo.md` (regra 2)

## O quê (objetivo desta estória)

Implementar o serviço de enriquecimento cadastral: consulta assíncrona (fila) à API pública decidida no
ADR, persistência dos dados do emitente (no mínimo: razão social, CNAE principal com descrição,
situação cadastral, município/UF), com cache cujo TTL é **parametrizável (default 30 dias)**.

## Por quê (valor para o usuário)

Sem o CNAE, o motor de pontos não diferencia categorias — e a promessa do PDR-004 (remunerar mais o
dado que vale mais) não se cumpre. O cache protege a operação dos limites da API gratuita.

## Critérios de aceite

- [ ] **CA-1:** Dado um CNPJ nunca consultado, quando o enriquecimento é solicitado, então a consulta
      ocorre de forma assíncrona (fila) e os dados cadastrais ficam persistidos e consultáveis.
- [ ] **CA-2:** Dado um CNPJ já consultado dentro do TTL, quando o enriquecimento é solicitado
      novamente, então **nenhuma** chamada externa ocorre e os dados vêm do cache.
- [ ] **CA-3:** Dado um CNPJ consultado com TTL vencido, quando solicitado, então nova consulta externa
      ocorre e o cache é renovado.
- [ ] **CA-4:** O TTL é parametrizável por configuração (default 30 dias) sem mudança de código.
- [ ] **CA-5:** Dado que a API externa falha (timeout, 5xx, limite de taxa), então a política de
      retry/backoff do ADR é aplicada e a falha nunca gera erro visível ao Colaborador nem perde a
      solicitação.
- [ ] **CA-6:** Dado um CNPJ não encontrado ou sem CNAE, então o resultado é registrado com estado
      distinto (conforme fallback do ADR), consultável pelo consumidor do serviço.

## Fora de escopo

- Disparo automático a partir do pipeline do cupom e exibição no Backoffice (STORY-041).
- Cálculo de pontos (EPIC-010).
- Tela para editar o TTL (EPIC-012).

## Padrões de qualidade exigidos

Esta estória segue `docs/skills/po/references/quality-standards.md`. Resumo aplicável:

- Cobertura ≥ 80% no código novo; ≥ 98% no que for regra de negócio (política de cache/fallback).
- Testes não podem depender da API externa real (dublê/fake); um teste de contrato pode ser marcado
  para execução manual/CI noturno se o ADR indicar.
- Sem código não testado; automação de qualquer setup novo (migração, config).

## Dependências

- **Bloqueada por:** STORY-039 (ADRs).
- **Bloqueia:** STORY-041.
- **Pré-requisitos de ambiente:** homologação operante; fila decidida no ADR disponível no ambiente.

## Decisões já tomadas (não as reabra)

- PDR-004 (regra 2) — consulta em fila, cache ≥ 30 dias parametrizável.
- ADRs da STORY-039 — fonte, fila, cache, fallback.
- ADR-000 — stack.

## Liberdade técnica do agente

Você decide estrutura de código, design dos testes e refatorações locais — dentro dos ADRs. Você NÃO
decide: fonte de dados, política de cache/retry (ADRs), critérios de aceite (PO). Sem ADR cobrindo algo
necessário → pare e registre.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam, com testes.
- [ ] Coberturas atingidas; pipeline CI verde; deploy em homologação verificado.
- [ ] IDR registrado se houve decisão de baixo nível com impacto futuro.
- [ ] `index.json` e esta estória atualizados ("Notas do agente" preenchidas).

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md` (frontmatter/status/índice como na STORY-039).

## Notas do agente (preenchido durante/após execução)

> Sessão `claude-programador-story040` — skill `programador` + sub-skill `stacks/laravel`.

### Plano (antes de codar)
**Documentos lidos:** STORY-040 inteira; ADR-012 (fonte/ACL), ADR-013 (fila), ADR-014 (cache/emitentes);
PDR-004 regra 2; código atual (`SefazExtracaoException`, `HttpSefazSpFetcher`, `ExtrairCupomJob`,
`Cupom` model, `config/coleta.php`).

**Entendimento:** implementar o **serviço de enriquecimento cadastral** — dado um CNPJ, obter os dados
da RFB via API pública **em fila**, persistir/cachear na tabela `emitentes` (ADR-014, cache = registro
canônico), com TTL parametrizável (default 30d). Fora de escopo: disparo pelo pipeline do cupom e
Backoffice (STORY-041), pontos (EPIC-010), tela de TTL (EPIC-012). **Backend puro — sem FE → sem E2E.**

**Arquitetura (dentro dos ADRs):**
- `App\Domain\Enriquecimento`: DTO `EmitenteEnriquecido`, porta `EnriquecedorCnpj`, `EnriquecimentoException`
  (transitória/estrutural — "negócio" volta como *status* no DTO), `RfbOpenDataEnriquecedor` (base do
  shape RFB) → `BrasilApiEnriquecedor` (primária) + `MinhaReceitaEnriquecedor` (fallback),
  `FallbackEnriquecedor` (decorator), `EnriquecimentoService` (cache-first: `solicitar` + `enriquecer`).
- `App\Models\Emitente` (tabela `emitentes`, cnpj = chave natural), migration, `config/enriquecimento.php`
  (`ttl_dias`), `App\Jobs\EnriquecerEmitenteJob` (ShouldQueue, tries=3, backoff [30,120,300]),
  comando `enriquecimento:cnpj {cnpj}` (exercício em homolog), bind da porta no `AppServiceProvider`.

**Mapa CA → testes (escritos ANTES do código):**
- **CA-1** consulta assíncrona + persistência: `EnriquecimentoServiceTest::test_cnpj_novo_consulta_e_persiste`,
  `EnriquecerEmitenteJobTest::test_job_enriquece_e_persiste`, `..._solicitar_despacha_job_em_cache_miss`.
- **CA-2** cache-hit sem chamada externa: `..._cnpj_fresco_nao_chama_externo` (contador do fake = 0),
  `..._solicitar_nao_despacha_em_cache_fresco`.
- **CA-3** TTL vencido re-consulta e renova: `..._cnpj_vencido_reconsulta_e_renova_cache`.
- **CA-4** TTL por config: `..._ttl_lido_da_config` (config 1 dia → registro de 2 dias vence).
- **CA-5** falha externa não perde/erro ao usuário: `BrasilApiEnriquecedorTest` (timeout/5xx/429→transitória),
  `EnriquecerEmitenteJobTest::test_transitoria_reprocessa` + `test_failed_marca_nao_enriquecido`.
- **CA-6** não encontrado / sem CNAE → estado distinto: `BrasilApiEnriquecedorTest::test_404_nao_encontrado`,
  `..._200_sem_cnae`, `EnriquecimentoServiceTest::test_persiste_status_distinto`.
- Mapeamento RFB→DTO e classificação: `BrasilApiEnriquecedorTest` (feliz, 404, sem-cnae, 5xx, 429,
  timeout, contrato inesperado→estrutural); `FallbackEnriquecedorTest` (primária transitória→secundária;
  ambas falham→propaga; primária ok→secundária não chamada).

### Decisões tomadas (locais, dentro dos ADRs)
- **Contexto `App\Domain\Enriquecimento`** com porta `EnriquecedorCnpj` (ACL) → DTO `EmitenteEnriquecido`.
  Falha **transitória/estrutural** vira `EnriquecimentoException`; **negócio** (não encontrado / sem CNAE)
  volta como **status no DTO** (não é exceção) — simplifica o serviço e o mapeamento.
- **Base `RfbOpenDataEnriquecedor`** compartilhada entre BrasilAPI e Minha Receita: as duas servem o mesmo
  shape do dump aberto da RFB, então o mapeamento response→DTO e a classificação de falha vivem num lugar
  só (evita duplicação real de intenção; cada fonte concreta só informa URL + nome).
- **`emitentes` como cache + registro canônico** (ADR-014): `estaFresco()` = tem `enriquecido_em` dentro do
  TTL. Respostas **definitivas** (enriquecido/sem_cnae/não_encontrado) gravam `enriquecido_em` (cacheáveis);
  **`nao_enriquecido`** (falha transitória esgotada / estrutural) fica com `enriquecido_em = null` → **não
  vira cache**, é reconsultável na próxima solicitação. `registrarNaoEnriquecido` **não rebaixa** um
  registro já fresco.
- **TTL por `config('enriquecimento.ttl_dias')`** (default 30, env `ENRIQUECIMENTO_TTL_DIAS`) — parametrizável
  sem deploy (CA-4); tela fica para o EPIC-012.
- **Job** `EnriquecerEmitenteJob` (fila `database`, `tries=3`, backoff `[30,120,300]`): transitória relança
  (retry/backoff), estrutural alerta+`nao_enriquecido` sem retry, `failed()` marca `nao_enriquecido`.
- **Binding** da porta no `AppServiceProvider` = `FallbackEnriquecedor(BrasilApi, MinhaReceita)`; testes
  trocam por um fake via container.
- **CNPJ normalizado** (só dígitos) na fronteira do serviço — máscara é UX.

### Descobertas
- Smoke ao vivo contra a **BrasilAPI real** (CNPJ 43.259.548/0028-83 — emitente de homologação): 1ª chamada
  enriqueceu e persistiu (razão, CNAE 4711302, ITU/SP, 5 CNAEs secundários); 2ª chamada (CNPJ **mascarado**)
  veio do **cache** (1 linha, sem duplicar, `enriquecido_em` intacto). Prova cache-hit + normalização.
- Convenção do projeto: os models de domínio usam só `HasUuids` (sem override para uuid7) — segui o padrão
  do `Cupom` para consistência (a sub-skill sugere uuid7, mas o código vigente não o faz).

### Bloqueios encontrados
- Nenhum. Todos os CAs cobertos pelos ADRs da STORY-039.

### IDRs criados
- Nenhum. As decisões locais ficam dentro dos ADR-012/013/014; nenhuma introduz padrão transversal novo
  (a base `RfbOpenDataEnriquecedor` é organização interna do contexto).

### Cobertura final
- **100%** em todos os arquivos novos (`Domain/Enriquecimento/*`, `Models/Emitente`, `Jobs/EnriquecerEmitenteJob`,
  `Console/Commands/EnriquecerCnpjCommand`). Suíte completa: **373 testes verdes**. Total do projeto: **95,9%**
  (gate `--min=80` verde). Pint limpo.

### Mapa CA → teste (final)
- **CA-1** `EnriquecimentoServiceTest::test_cnpj_novo_consulta_e_persiste`, `..._solicitar_despacha_job_em_cache_miss`,
  `EnriquecerEmitenteJobTest::test_job_enriquece_e_persiste`, `EnriquecerCnpjCommandTest::test_enriquece_e_persiste_com_sucesso`.
- **CA-2** `..._cnpj_fresco_nao_chama_externo` (contador do fake = 0), `..._solicitar_nao_despacha_em_cache_fresco`.
- **CA-3** `..._cnpj_vencido_reconsulta_e_renova_cache`.
- **CA-4** `..._ttl_lido_da_config`.
- **CA-5** `BrasilApiEnriquecedorTest` (timeout/5xx/429/4xx→transitória), `EnriquecerEmitenteJobTest`
  (`test_transitoria_reprocessa`, `test_estrutural_nao_reprocessa_e_marca_nao_enriquecido`,
  `test_failed_marca_nao_enriquecido`, `test_falha_nunca_toca_o_cupom_nem_lanca_ao_usuario`),
  `EnriquecimentoServiceTest::test_registrar_nao_enriquecido_fica_reconsultavel`.
- **CA-6** `BrasilApiEnriquecedorTest::test_404_vira_status_nao_encontrado`, `..._200_sem_cnae_vira_status_sem_cnae`,
  `EnriquecimentoServiceTest::test_persiste_status_nao_encontrado`.
- Mapeamento/fonte/fallback: `BrasilApiEnriquecedorTest` (feliz + bordas cnaes_secundarios),
  `MinhaReceitaEnriquecedorTest`, `FallbackEnriquecedorTest`.

### Links de evidência
- Commits: `test(STORY-040)` (vermelho) → `feat(STORY-040)` (verde) na `main`.
- Smoke ao vivo: `php artisan enriquecimento:cnpj 43259548002883` (saída no histórico da sessão).
- **Sem E2E:** estória é backend puro, não toca FE web (o Backoffice é STORY-041) → gate de E2E não se aplica.

### Pendências de DoD (fora da minha alçada nesta sessão)
- **CI verde + deploy homolog verificado:** os commits estão na `main` **local**; o push (que dispara o
  CI/CD e o deploy para homologação) segue a regra "só commitar/push quando o usuário pedir" — aguardo o
  push para fechar como `done`.
