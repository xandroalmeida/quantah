---
idr_id: IDR-006
slug: telemetria-de-coleta-e-denominador-da-taxa
title: Telemetria de coleta em tabela append-only e denominador da taxa de sucesso
status: accepted
decided_at: 2026-07-03
decided_by: programador
owner_agent: claude-programador-story012
related_story: STORY-012
related_adrs: [ADR-001, ADR-003, ADR-006]
related_idrs: [IDR-005]
supersedes: null
superseded_by: null
created_at: 2026-07-03
updated_at: 2026-07-03
---

# IDR-006 — Telemetria de coleta append-only e denominador da taxa de sucesso

## Contexto

A STORY-012 instrumenta a north-star do MVP: **cupons válidos, únicos e novos por semana**
(`product/north-star.md`) e a **taxa de sucesso de envio** (enviados → válidos). O numerador
(válido-único-novo) sai direto do modelo canônico — cupons `validado`, únicos e novos **por
construção** (chave UNIQUE, ADR-003). O problema é o **denominador**: a definição de "enviados"
inclui **duplicatas e inválidos**, e esses desfechos **não viram linha em `cupons`** (a dedup e a
rejeição de parse os descartam). Sem instrumentar, não há como contá-los.

## Decisão

> **Registro um evento append-only por tentativa de envio numa tabela `coleta_eventos`
> (datastore-first, sem PII) e defino a taxa de sucesso = `validado` (de `cupons`) ÷ `enviados`
> (de `coleta_eventos`). A semana é a de COLETA (`created_at`), no fuso de São Paulo.**

- **`coleta_eventos`**: `situacao` (capturado|aceito|duplicado|rejeitado|falha_extracao), `motivo`
  (sub-sinal do funil quando há recusa/falha) e `cupom_id` (uuid técnico, nulo nas rejeições de
  parse). Gravado na fronteira de ingestão (`IngestaoCupomService::capturar/ingerir`) — **um por
  chamada**, inclusive nos desfechos sem cupom. O reprocessamento assíncrono **não** gera evento
  (não é um novo envio).
- **Taxa**: `validados ÷ enviados` por semana e no geral; **nula** quando não houve envio (sem
  divisão por zero).
- **Semana**: `date_trunc('week', created_at AT TIME ZONE 'America/Sao_Paulo')` — segunda-feira,
  horário local (só SP nesta onda).

## Por quê

- **Fidelidade à definição** (`north-star.md`): "enviados" precisa incluir duplicata/inválido; só
  a tabela de eventos captura o que o modelo canônico (corretamente) descarta.
- **Datastore-first (princípio #3):** telemetria no próprio Postgres, sem serviço de analytics
  externo — o volume do MVP não justifica outro armazenamento. Reabre-se com número, se preciso.
- **Numerador do modelo canônico (ADR-003):** válido-único-novo continua sendo `count(validado)`;
  a unicidade/novidade é garantia estrutural da chave UNIQUE, não um cálculo sujeito a erro.
- **Privacidade (ADR-006, [[anonimizacao-cpf-no-conteudo-do-qr]]):** eventos só carregam sinais de
  funil e o uuid do cupom — nenhum dado do consumidor. Coberto por teste anti-PII.
- **Fuso local:** "coletados por semana" é uma leitura de negócio brasileira; bucketizar em SP
  evita que a virada de semana caia no meio da madrugada por causa do UTC.

## Alternativas consideradas

- **Denominador só do `cupons` (validados ÷ total persistido):** mais simples, mas **exclui**
  duplicata e rejeição de parse — infla a taxa e contradiz a definição. Reprovada.
- **Log estruturado em arquivo/stderr:** não é consultável pelo painel sem outra ferramenta;
  telemetria em tabela é consultável com o mesmo Postgres. Os logs seguem para erro/saúde pontual.
- **date_trunc em UTC:** mais simples, mas desloca a semana ~3h — enganoso para leitura semanal de
  um produto de SP.

## Consequências

### Para outros agentes
- Todo novo ponto de entrada de envio deve emitir um evento pela mesma fronteira — não conte
  "enviados" a partir de `cupons`.
- Sub-sinais do funil (% duplicata, % inválido, % falha) já saem de `coleta_eventos.situacao`
  (`resumo().por_situacao`) — base pronta para as alavancas da árvore de métricas.

### Para o projeto
- Uma tabela append-only nova (índices em `created_at` e `situacao`); zero dependência externa.
- No fluxo assíncrono de produção, o evento nasce como `capturado` (desfecho pendente) — a
  validação posterior é refletida em `cupons.status` (numerador), não num segundo evento.

### Trade-offs aceitos
- `coleta_eventos` cresce 1 linha por envio (inclui duplicatas) — aceitável no volume do MVP;
  retenção/rollup vira decisão com número se o volume pedir.
- Fuso fixo em SP (só SP nesta onda); expansão nacional revisará o bucket por UF/fuso.

## Como verificar

- `Tests\Feature\Coleta\MetricasColetaTest` (contagem/semana, taxa, bordas, sem-PII),
  `TelemetriaColetaTest` (um evento por envio, inclusive duplicata/rejeição, sem PII),
  `Tests\Feature\Interno\PainelMetricasTest` (props do painel) e o E2E
  `Tests\Browser\PainelMetricasTest`.

## Tipo

- [x] **Padrão transversal**: como a coleta é instrumentada e como a taxa é definida/calculada.
- [ ] **Workaround**
- [ ] **Convenção interna**
- [ ] **Otimização**
- [ ] **Refatoração estrutural**

---

## Histórico

- 2026-07-03 — criada como `accepted` por programador (sessão claude-programador-story012)
  durante STORY-012.
