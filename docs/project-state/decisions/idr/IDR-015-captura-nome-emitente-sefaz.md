---
idr_id: IDR-015
slug: captura-nome-emitente-sefaz
title: Nome do estabelecimento — coluna nova nullable, parse do topo do DANFE, fallback na UI, sem backfill
status: accepted  # proposed | accepted | superseded
decided_at: 2026-07-05
decided_by: programador
owner_agent: claude-programador
related_story: STORY-034
related_adrs: [ADR-001, ADR-002, ADR-006]
related_idrs: []
related_ddrs: []
supersedes: null
superseded_by: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# IDR-015 — Captura do nome do estabelecimento na extração SEFAZ

> Implementation Decision Record. Registra **onde** o nome do emitente é lido, **como** é persistido e
> **como** a ausência degrada, para que outros adaptadores estaduais e a UI sigam o mesmo contrato.

## Contexto

O modelo canônico (ADR-001) guardava só `cnpj_emitente` (derivado da chave), não o nome do
estabelecimento. O histórico mostrava "Cupom de R$ X" sem onde a compra foi feita (STORY-034). O nome do
emitente é dado **público do estabelecimento**, não PII do consumidor (ADR-006). Precisei decidir a
coluna, o ponto de extração e o comportamento quando a fonte não traz o nome.

## Decisão

> **Decidi: adicionar `nome_emitente` (string nullable) em `cupons` via migration; capturá-lo no
> `HttpSefazSpFetcher` a partir do topo do DANFE (`<div class="txtTopo">NOME</div>`); propagá-lo por
> `CupomExtraido.nomeEmitente` (opcional, default null) → `SpSefazAdapter` → `IngestaoCupomService`.
> Sem backfill de cupons antigos. Ausência degrada com fallback "Estabelecimento não identificado" na
> UI (listagem e detalhe), com o CNPJ formatado como identificador secundário.**

## Por quê

- **Coluna nullable + sem backfill:** vale para cupons novos; os antigos e as fontes sem nome degradam
  na borda — extensão do ADR-001, não redesenho da ingestão (ADR-002).
- **Parse por `class="txtTopo"`:** é o marcador estável do nome no DANFE de SP (mais robusto que o
  `id="u20"`, que é posicional). Ausente → null, nunca inventa dado.
- **Opcional no DTO:** `nomeEmitente` entra com default null e **fora** da checagem estrutural do
  adaptador — payloads/fontes sem o campo continuam válidos (não viram falha estrutural).
- **Não é PII (ADR-006):** nome de estabelecimento é público; a defesa do AnonimizadorCpf segue rodando.

## Alternativas consideradas

- **Coluna NOT NULL / backfill:** descartada — quebraria cupons antigos e a ingestão de fontes sem nome;
  fallback na UI resolve sem migração de dados.
- **Parse por `id="u20"`:** descartada — id posicional é mais frágil que a classe semântica `txtTopo`.
- **Guardar razão social só no read-model (sem persistir):** descartada — o dado vem da extração
  assíncrona; precisa ser persistido para a listagem/detalhe lerem sem reprocessar.

## Consequências

### Para outros agentes
- Novos adaptadores estaduais devem preencher `nome_emitente` quando a fonte tiver; ausência é válida
  (null) e a UI já degrada.
- Fallback canônico do estabelecimento: `ExtratoCarteira::ESTABELECIMENTO_FALLBACK`
  ("Estabelecimento não identificado"); CNPJ formatado por `Formato::cnpj()`.
- Posse do cupom para telas do Coletador é via `CupomAtribuicao` (cupons não têm `user_id`, ADR-006) —
  a rota de detalhe faz `abort_unless(dono, 404)`.

### Para o projeto
- +coluna `cupons.nome_emitente` (nullable). Sem dependência nova.

### Trade-offs aceitos
- Cupons anteriores à mudança ficam sem nome (fallback) — aceitável; backfill é dívida se necessário.

## Como verificar

- `HttpSefazSpFetcherTest::test_parseia_danfe_real_de_sp` (nome do fixture), `NomeEmitenteTest`
  (persistência + ausência→null), `ExtratoCarteiraTest` (estabelecimento + fallback), `CupomDetalheTest`
  (cabeçalho/itens/posse/estados/sem-CPF), `CupomDetalheE2eTest` (listagem → detalhe em 1 toque).

## Tipo

- [x] **Convenção interna**: contrato de captura do nome do emitente + fallback.

---

## Histórico

- 2026-07-05 — criada como `accepted` por programador (sessão claude-programador) durante STORY-034.
