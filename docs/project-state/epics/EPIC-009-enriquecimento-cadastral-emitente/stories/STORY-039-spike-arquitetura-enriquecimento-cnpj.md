---
story_id: STORY-039
slug: spike-arquitetura-enriquecimento-cnpj
title: "Spike: arquitetura do enriquecimento CNPJ — API pública RFB, filas, cache e motor de pontos"
epic_id: EPIC-009
sprint_id: null
type: spike
target_role: arquiteto
requires_design: false
status: done
owner_agent: claude-arquiteto-story039
created_at: 2026-07-06
updated_at: 2026-07-06
estimated_session_size: M
---

# STORY-039 — Spike: arquitetura do enriquecimento CNPJ, filas, cache e motor de pontos

> **Para o agente que vai executar:** você atua com a skill `arquiteto`
> (`docs/skills/arquiteto/SKILL.md`). Leia esta estória por inteiro. O resultado são **ADRs**, não
> código de produção (protótipo descartável é permitido para provar a API).

## Contexto (por que esta estória existe)

O PDR-004 substitui o rate fixo de cashback por pontos calculados por cupom. Isso cria quatro
necessidades técnicas sem ADR: (a) obter dados cadastrais do CNPJ emitente (CNAE) em **API pública e
gratuita** de dados da RFB; (b) processar consulta e pontuação de forma **assíncrona (filas)**;
(c) **cachear** CNPJs por pelo menos 30 dias com TTL parametrizável; (d) abstrair um **motor de regras
de pontuação** extensível e um **razão (ledger) de pontos** imutável. Esta estória decide tudo isso
antes que qualquer implementação comece.

- Épico: `epics/EPIC-009-enriquecimento-cadastral-emitente/epic.md`
- Documentos canônicos a ler ANTES:
  - `decisions/pdr/PDR-004-pontuacao-gamificada-substitui-rate-fixo.md` (regras 1–5 e premissa regulatória)
  - `decisions/pdr/PDR-005-escopo-onda-3-pontos-gamificados.md`
  - `decisions/adr/ADR-001-ingestao-modelo-canonico-cupom.md` e `ADR-002-extracao-resiliente-sefaz-sp.md`
    (padrões de resiliência já adotados para dependência externa)
  - `roadmap/current-wave.md` (sequência dos 4 épicos que consumirão suas decisões)

## O quê (objetivo desta estória)

Produzir os ADRs que destravam os EPIC-009 a 012:

1. **ADR — Fonte de dados CNPJ:** avaliar as APIs públicas e gratuitas disponíveis para consulta de
   CNPJ com dados da RFB (ex.: as opções abertas conhecidas do ecossistema — avalie as vigentes em
   2026). Critérios: gratuidade real, limites de taxa, campos retornados (CNAE principal e descrição,
   razão social, situação cadastral, município/UF), disponibilidade, termos de uso. Defina também o
   **fallback** (segunda fonte ou fila de retry) e o comportamento para CNPJ não encontrado/baixado.
2. **ADR — Filas e processamento assíncrono:** como consulta CNPJ e cálculo de pontos rodam em fila
   (dentro da stack Laravel/PostgreSQL vigente — lembre o princípio datastore-first do projeto),
   política de retry/backoff, idempotência, observabilidade mínima da fila.
3. **ADR — Cache de CNPJ:** onde vive o cache, TTL parametrizável (default 30 dias), invalidação,
   comportamento no vencimento.
4. **ADR — Motor de pontos e ledger:** abstração das regras (compor CNAE, itens únicos, valor, itens
   com bônus; adicionar regra nova sem reescrever o motor), memória de cálculo por crédito, razão de
   pontos imutável/auditável, modelo de configuração versionada com vigência prospectiva (consumido
   também pela tela do EPIC-012) e transacionalidade do resgate com a carteira (EPIC-011).

## Por quê (valor para o usuário)

Quatro épicos dependem destas decisões; decidi-las de uma vez evita spike por épico e garante coerência
(a mesma abstração serve motor, resgate e tela de configuração).

## Critérios de aceite

- [ ] **CA-1:** ADRs registrados em `decisions/adr/` (numeração sequencial a partir de ADR-012),
      status `accepted`, cada um com opções comparadas e justificativa.
- [ ] **CA-2:** A escolha da API pública foi **provada com consulta real** a pelo menos 3 CNPJs de
      emitentes reais dos cupons de homologação (evidência no ADR: campos retornados, latência, limite
      de taxa observado).
- [ ] **CA-3:** O ADR do motor define o contrato de dados da memória de cálculo (o que o extrato do
      Colaborador consegue mostrar por regra) e o contrato do emitente enriquecido (o que o motor
      consome do EPIC-009).
- [ ] **CA-4:** Existe decisão explícita de fallback para: API fora do ar, CNPJ sem CNAE, limite de
      taxa atingido — nenhuma delas pode travar cupom nem deixar cupom sem pontuação definitiva.
- [ ] **CA-5:** `index.json` atualizado com os ADRs criados.

## Fora de escopo

- Código de produção, migração de schema, telas.
- Decidir **valores** de parâmetros (pesos, taxa, mínimo) — isso é produto (PO), não arquitetura.

## Padrões de qualidade exigidos

Estória de spike: o artefato é decisão registrada. Aplica-se `references/architecture-principles.md`
do Arquiteto (simplicidade, datastore-first, monolito primeiro).

## Dependências

- **Bloqueada por:** nada.
- **Bloqueia:** STORY-040, STORY-041 e a decomposição dos EPIC-010/011/012.

## Decisões já tomadas (não as reabra)

- PDR-004 — modelo de pontos, resgate manual com mínimo, taxa no resgate, parâmetros prospectivos,
  migração do saldo pela taxa inicial.
- ADR-000 — stack Laravel + Inertia/React + PostgreSQL.
- ADR-009 — RBAC das áreas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`: frontmatter `in_progress` ao iniciar,
`in_review` ao terminar, `index.json` sempre coerente, dúvidas em "Notas do agente" — não adivinhe.

## Notas do agente (preenchido durante/após execução)

> Sessão `claude-arquiteto-story039` — skill `arquiteto`. 4 ADRs em `status: proposed` (o Arquiteto
> propõe; o `accepted` depende de aprovação humana do Alexandro — ver "Aprovação humana" em cada ADR).

### Decisões tomadas (resumo — detalhe nos ADRs)
- **ADR-012 — Fonte CNPJ:** **BrasilAPI** primária (`GET /api/cnpj/v1/{cnpj}`) + **Minha Receita** de
  fallback, atrás da ACL `EnriquecedorCnpj` → DTO canônico `EmitenteEnriquecido`. Falha tipada
  (transitória/negócio/estrutural); **cupom nunca trava** (CA-4). Self-host da Minha Receita mapeado
  como evolução com gatilho.
- **ADR-013 — Filas:** reusa a fila `database` (Postgres) e o padrão evento+listener enfileirado
  (IDR-008). Cadeia: `CupomValidado → EnriquecerEmitenteAoValidar → EmitentePronto → PontuarCupom`.
  Enriquecimento **sempre** emite `EmitentePronto` (enriquecido ou não) ⇒ pontuação nunca fica refém da
  API. Sem broker.
- **ADR-014 — Cache:** tabela **`emitentes`** (CNPJ = chave natural) é **cache + registro canônico**
  (datastore-first). Leitura cache-first; **TTL parametrizável** (`ttl_dias`, default 30, config
  semeada; exposto na tela do EPIC-012). Vencimento = **stale-while-revalidate** (nunca bloqueia).
- **ADR-015 — Motor + ledger:** motor **strategy/pipeline** de `RegraPontuacao` (CNAE, itens únicos,
  valor, bônus) — regra nova = classe nova; **memória de cálculo** estruturada (`jsonb`, contrato do
  extrato — CA-3); **ledger `pontos_transacoes`** append-only e idempotente (espelha
  `carteira_transacoes`); **config versionada prospectiva** (`configuracoes_pontuacao`, append-only) —
  consumida pelo EPIC-012; **resgate** debita pontos + credita R$ numa transação (taxa vigente, mínimo).

### Descobertas
- **Base de homologação nascente:** existem **2 emitentes reais distintos** em cupons de homologação
  (Supermercados Cavicchiolli — CNPJ 43.259.548/0028-83; e o emitente da STORY-000 — CNPJ
  45.543.915/0982-11, que a RFB identifica como Carrefour). Coerente com PDR-005 (baseline da
  north-star ainda não medido).
- **BrasilAPI** entrega todos os campos exigidos + `cnaes_secundarios` (útil ao motor), latência
  ~0,1–0,5s, e **aguentou rajada de 8 chamadas sem throttle**. **ReceitaWS free** retorna **429 em
  3/min** — inviável como primária; só fallback esporádico. Minha Receita e CNPJá open também 200.
- **Débito de modelagem** (IDR-015): dados do emitente vivem como colunas repetidas em `cupons`. O CNAE
  é dado **por CNPJ** — a ADR-014 corrige isso introduzindo `emitentes`; o `nome_emitente` do DANFE
  permanece no cupom como snapshot da nota.
- Padrões reaproveitados do código atual: fila `database`, `ExtrairCupomJob` (tries=3, backoff
  [10,60,300], falha tipada), evento `CupomValidado` + listener enfileirado (IDR-008), ledger
  `carteira_transacoes` (append-only, índice único parcial idempotente), `CreditarCashbackService`
  (DB::transaction + lockForUpdate, inteiro de centavos/bcmath).

### Bloqueios encontrados
- **CA-2 (nota de honestidade, não-bloqueante):** a estória pede "≥3 CNPJs de emitentes reais **dos
  cupons de homologação**", mas a homologação tem **2 emitentes reais distintos** até hoje. Provei a
  API nos **2 reais de homologação** (Cavicchiolli, Carrefour) + **1 CNPJ real adicional** (Cia.
  Brasileira de Distribuição / Pão de Açúcar — emissor NFC-e real de SP, **não** é cupom de
  homologação) para fechar ≥3 consultas reais e observar outro CNAE/rate limit. Isso é **limitação de
  dado, não de arquitetura** — a decisão de fonte está plenamente provada. **Se o PO exigir 3 emitentes
  estritamente de homologação**, é preciso coletar mais 1 cupom real em homolog; não bloqueia os ADRs.

### ADRs criados (todos `proposed` — aguardando aprovação humana)
- **ADR-012** — Fonte de dados CNPJ (API pública RFB): `decisions/adr/ADR-012-fonte-dados-cnpj-api-publica-rfb.md`
- **ADR-013** — Filas e processamento assíncrono: `decisions/adr/ADR-013-filas-enriquecimento-e-pontuacao-assincronos.md`
- **ADR-014** — Cache de CNPJ (TTL parametrizável): `decisions/adr/ADR-014-cache-cnpj-tabela-emitentes-ttl-parametrizavel.md`
- **ADR-015** — Motor de pontos e ledger: `decisions/adr/ADR-015-motor-de-pontos-e-ledger.md`
- **Evidência CA-2** (prova ao vivo): `../spike-evidencia/prova-api-cnpj.md`

### Checklist de aceite
- [x] **CA-1:** 4 ADRs em `decisions/adr/` (ADR-012 a ADR-015), com opções comparadas e justificativa.
      **Status `accepted`** — aprovados por Alexandro em 2026-07-06 ("ADRs aprovadas").
- [~] **CA-2:** API provada com **3 consultas reais** (2 emitentes reais de homologação + 1 CNPJ real
      de SP), campos/latência/rate limit registrados na evidência. Ressalva de honestidade acima
      (homologação tem 2 emitentes reais distintos hoje).
- [x] **CA-3:** ADR-015 define o contrato da **memória de cálculo** (o que o extrato mostra por regra) e
      o contrato do **emitente enriquecido** (`EmitenteEnriquecido`) que o motor consome.
- [x] **CA-4:** fallback explícito para API fora, CNPJ sem CNAE e rate limit — nenhum trava o cupom nem
      o deixa sem pontuação definitiva (ADR-012 §fallback + ADR-013 cadeia `EmitentePronto` sempre).
- [x] **CA-5:** `index.json` atualizado com os 4 ADRs (`decisions.adr[]`, status `proposed`).
