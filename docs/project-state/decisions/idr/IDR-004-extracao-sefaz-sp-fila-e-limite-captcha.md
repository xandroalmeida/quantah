---
idr_id: IDR-004
slug: extracao-sefaz-sp-fila-e-limite-captcha
title: Extração SEFAZ-SP na fila Postgres com fetcher HTTP defensivo; captcha limita o happy-path ao vivo
status: accepted
decided_at: 2026-07-03
decided_by: programador
owner_agent: claude-programador
related_story: STORY-010
related_adrs: [ADR-001, ADR-002, ADR-003, ADR-006]
related_idrs: [IDR-003]
supersedes: null
superseded_by: null
created_at: 2026-07-03
updated_at: 2026-07-03
---

# IDR-004 — Extração SEFAZ-SP: fila Postgres + fetcher defensivo; limite do captcha

## Contexto

A STORY-010 implementa o núcleo servidor do épico: extrair o cupom da SEFAZ-SP, validar, deduplicar e
persistir no modelo canônico. As decisões de arquitetura já estão fixadas nas ADRs (ADR-001 modelo/
fronteira, ADR-002 extração resiliente com fila, ADR-003 dedup por chave, ADR-006 CPF). Restam as
decisões de **implementação** dentro dessas ADRs.

Um fato dirige tudo: o **portal público da NFC-e de SP exige captcha** na consulta do cupom (a própria
visão, §6.2, cita captcha como risco). Um GET automático não retorna o DANFE — retorna a página de
verificação. Ou seja: **a extração ao vivo bem-sucedida não é automatizável hoje** sem resolver captcha
(fora de escopo — decisão de produto/custo) ou migrar para a fonte oficial (evolução, §6.2).

## Decisão

> **Implementei o pipeline de extração assíncrono na fila do Postgres (ADR-002) com um fetcher HTTP
> real, porém defensivo: ele detecta captcha / layout inesperado e classifica como falha ESTRUTURAL
> (cupom vira `falha` reprocessável + alerta), em vez de persistir dado inventado. O caminho feliz de
> extração é provado por fixture/fake (ADR-002: "SEFAZ mockável em teste"), com cobertura 100% no
> núcleo de validação/dedup. A extração ao vivo com sucesso fica explicitamente gated pelo captcha.**

Concretamente:
- `ExtrairCupomJob` (fila `database`, `SELECT ... SKIP LOCKED`) com `tries=3` e backoff `[10,60,300]`.
  Transitória → relança (retry); estrutural → `falha` sem retry (alerta); negócio → `rejeitado`.
  `failed()` deixa o cupom em `falha` (dead-letter). Worker adicionado ao `docker-compose.prod.yml`.
- `capturar()` (STORY-009) enfileira o Job; `ingerir()` é o caminho síncrono para CLI/testes;
  `processarExtracao()` é o passo compartilhado.
- `HttpSefazSpFetcher`: `Http::timeout` + UA próprio; classifica conexão/5xx/429 → transitória,
  "não encontrada/cancelada" → negócio, captcha/HTML desconhecido → estrutural. **Não** tem parser de
  DANFE ainda — sem amostra real do HTML autenticado, um parser seria ficção; a decisão é falhar seguro.
- Comando `php artisan coleta:extrair {chave}` para exercitar o pipeline em homologação.

## Justificativa

- **Integridade > teatro:** escrever um parser de HTML que não posso validar (e que, na prática, bate no
  captcha) seria fingir uma capacidade que não existe. Falhar de forma classificada e reprocessável é o
  comportamento correto e já previsto na ADR-002.
- **O núcleo de valor está testado:** validação de chave (DV mod 11, escopo SP/NFC-e), dedup idempotente
  (unique + upsert, à prova de corrida) e classificação de falha têm **100%** de cobertura, com fake
  determinístico — que é exatamente o que a ADR-002 autoriza ("SEFAZ mockável em teste").
- **Reversível/evoluível:** quando houver captcha-solving ou fonte oficial, basta uma nova implementação
  de `SefazSpFetcher` (ou de `SefazAdapter`) — nada do modelo canônico, da fila ou da dedup muda.

## Consequências e limitação a escalar ao PO

- **Limitação (para o PO / Arquiteto decidir):** o critério do épico "um cupom de SP percorre end-to-end
  em homologação até `validado`" (checklist EPIC-002 / STORY-013) **não fecha ao vivo** enquanto o
  captcha não for resolvido. Em homologação, uma chave real hoje termina em `falha` (estrutural, captcha)
  — reprocessável e alertável, mas não `validado`. Caminhos possíveis (decisão de produto/arquitetura):
  (a) serviço de captcha-solving (custo recorrente), (b) **migração para a fonte oficial** (web service/
  XML/credenciamento — o próprio §6.2 já aponta como evolução), (c) aceitar que o MVP demonstra o
  pipeline com fixture e adia o ao-vivo. **Recomendo abrir essa decisão com o PO/Arquiteto** antes da
  validação final do épico (STORY-013).
- O worker de fila passa a rodar em homologação/produção (novo serviço no compose).
- Nenhum segredo/credencial de SEFAZ versionado (o portal é público; a fonte oficial, quando vier, usa
  secrets do ambiente).

## Verificação

- Núcleo (validação/dedup): 100% (`ChaveAcesso`, `IngestaoCupomService`, `ResultadoIngestao`,
  `ExtrairCupomJob`); total do projeto 91.5% (gate 80%).
- `ExtrairCupomJobTest` cobre sucesso, transitória (retry), estrutural, negócio, idempotência, dispatch,
  reprocesso; `HttpSefazSpFetcherTest` cobre a classificação com `Http::fake` (sem rede).
- `coleta:extrair` disponível para exercício manual em homologação.
