---
story_id: STORY-012
slug: instrumentacao-north-star
title: Instrumentação da north-star — cupons válidos, únicos e novos por semana
epic_id: EPIC-002
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
design_waiver: DDR-003
status: done
owner_agent: claude-programador-story012
created_at: 2026-07-02
updated_at: 2026-07-03
estimated_session_size: M
---

> **Exceção de design (F-NB-2 / DDR-003):** esta estória é `requires_design: true` mas foi
> **dispensada** de artefato de Designer — o painel `/interno/metricas` é interno, de leitura, e
> compõe componentes já aprovados do DS. Decisão registrada em
> `decisions/ddr/DDR-003-excecao-design-story012-painel-metricas.md` (aprovada por Alexandro, PO).

# STORY-012 — Instrumentação da north-star

> **Para o agente que vai executar:** leia por inteiro. O painel é interno (`requires_design: true`
> leve — compõe DS existente). O foco é a **métrica correta**, não uma tela sofisticada.

## Contexto (por que esta estória existe)

A onda existe para gerar o **primeiro baseline** da north-star: cupons **válidos, únicos e novos por
semana**. Sem instrumentar a contagem e mostrá-la, não sabemos se a coleta está funcionando nem
fechamos o piloto.

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- Documentos a ler ANTES: `docs/project-state/product/north-star.md`; `docs/visao.md` §6.4 (dedup);
  ADR-001/003 (modelo e dedup — base da contagem); componentes do DS.

## O quê (objetivo desta estória)

Instrumentar e expor a métrica: uma **rota/painel interno** que mostra a contagem de cupons
válidos-únicos-novos por semana, além da **taxa de sucesso de envio** (enviados → válidos-únicos-novos).

## Por quê (valor para o usuário)

É o instrumento de medição da onda — o que transforma "coletamos cupons" em "sabemos quanto e com que
qualidade", permitindo decidir o futuro do produto com dado.

## Critérios de aceite

- [x] **CA-1:** Existe uma contagem, por semana, de cupons **válidos, únicos e novos** derivada do
      modelo canônico (dedup por chave), correta em testes com cenários conhecidos.
- [x] **CA-2:** Existe a **taxa de sucesso de envio** (enviados → válidos-únicos-novos) calculada e visível.
- [x] **CA-3:** Rota/painel **interno** em homologação exibe as métricas, compondo componentes do DS.
- [x] **CA-4:** A contagem não conta em dobro (idempotente com a dedup da STORY-010) e ignora inválidos.
- [x] **CA-5:** Métricas básicas de saúde/uso ficam registradas (log/telemetria) sem vazar dado pessoal.

## Fora de escopo

- Dashboards para clientes B2B (Quantah Intelligence) — evolução.
- Gancho "pagou mais/menos que a média" (Onda 2); cashback (EPIC-003).

## Padrões de qualidade exigidos

Segue `quality-standards.md`. Testes da lógica de contagem (feliz + duplicado + inválido); a11y mínima
no painel; sem valor cru fora dos tokens; sem vazamento de dado pessoal em métricas/logs.

## Dependências

- **Bloqueada por:** STORY-010 (persistência/classificação válido-único-novo).
- **Bloqueia:** STORY-013 (validação — item da métrica).

## Decisões já tomadas (não as reabra)

- ADR-001/003; definição da north-star (`product/north-star.md`). Só SP.

## Definição de Pronto (DoD)

- [x] CA-1 a CA-5 passam; lógica de contagem testada em cenários conhecidos.
- [x] Painel interno acessível em homologação; pipeline verde.
- [x] IDR se houve decisão técnica; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Dúvida sobre a **definição** da métrica → `blocked` + escalar ao PO
(é decisão de produto).

## Notas do agente (preenchido durante/após execução)

### Documentos lidos
- `product/north-star.md` (definição: válido/único/novo + árvore de métricas + taxa de sucesso),
  visão §6.4 (dedup), ADR-001 (modelo canônico + fronteira de leitura), ADR-003 (dedup por chave),
  ADR-006 (sem PII). Código da STORY-010: `Cupom`, `IngestaoCupomService`, `ResultadoIngestao`.
  DS: `Card`/`Badge`/`EmptyState` + tokens do `tailwind.config.js`.

### Entendimento consolidado
- **Numerador** (válido-único-novo) vem do modelo canônico: `count(status='validado')`. Único e novo
  são garantidos por construção (chave UNIQUE, ADR-003) — não é cálculo, é estrutura. **Denominador**
  (enviados) inclui duplicata/inválido, que NÃO viram cupom → precisa de telemetria própria.

### Decisões tomadas
- **IDR-006**: telemetria em tabela append-only `coleta_eventos` (datastore-first, sem PII), um
  evento por tentativa de envio na fronteira de ingestão; **taxa = validados ÷ enviados**; semana de
  **coleta** (`created_at`) no **fuso de SP**. O denominador honra a definição do `north-star.md`
  (inclui duplicata/inválido), em vez de contar só o que persiste em `cupons`.
- Painel **atrás de `auth`** (interno). Authz por papel de admin não existe ainda no MVP (contas
  chegam no EPIC-003) → gate por autenticação é o passo defensável agora; refino é evolução.
- Reprocessamento assíncrono **não** emite evento (não é novo envio) — evita inflar "enviados".

### Descobertas
- O bucket semanal no Postgres **não pode parametrizar o fuso**: `AT TIME ZONE ?` faz o SELECT e o
  GROUP BY divergirem (parâmetros distintos) e o Postgres exige `created_at` no GROUP BY. Fuso
  inline como literal (constante da app, sem injeção) resolve.
- Ambiente de teste resolve o Vite pelo **manifest** (sem dev server rodando) → página nova exige
  `npm run build`. A migração da tabela precisou rodar também no **banco de dev** (o Dusk usa o
  `quantah`, não o de teste).
- `taxa=1.0` serializa como inteiro `1` no JSON — o contrato que o front recebe; o teste e o
  `formatarTaxa` tratam int/float/null.

### Mapa CA → teste
- **CA-1/CA-4:** `MetricasColetaTest` (contagem/semana, ignora não-validados, dedup não conta em dobro).
- **CA-2:** `MetricasColetaTest` (taxa, borda sem envios) + `TelemetriaColetaTest` (um evento por envio,
  incluindo duplicata/rejeição/falha).
- **CA-3:** `PainelMetricasTest` (Feature: props populado/vazio) + `Tests\Browser\PainelMetricasTest`
  (E2E: interno vê KPIs+tabela compondo DS; anônimo → login).
- **CA-5:** `MetricasColetaTest::test_evento_de_telemetria_nao_tem_pii` + `TelemetriaColetaTest::test_evento_nao_vaza_pii`.

### Bloqueios encontrados
- Nenhum. A definição da north-star estava fixada em `product/north-star.md`; o único ponto sutil
  (o que entra no denominador) foi resolvido honrando o documento e registrado em IDR-006.

### Links de evidência
- Suíte completa verde: **151 testes PHPUnit / 678 asserções** + **42 testes Dusk / 156 asserções**
  (browser real); Pint limpo; build do Vite OK.
- Commits (TDD): `test` RED → `feat` GREEN para métricas/telemetria; painel `feat(CA-3)`; E2E `test(CA-3)`.
- IDR: `decisions/idr/IDR-006-telemetria-de-coleta-e-denominador-da-taxa.md`.
- Painel em homolog: rota `interno.metricas` → `/interno/metricas` (atrás de `auth`).
