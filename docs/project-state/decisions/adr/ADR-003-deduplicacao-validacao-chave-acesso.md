---
adr_id: ADR-003
slug: deduplicacao-validacao-chave-acesso
title: Deduplicação e validação pela chave de acesso de 44 dígitos
status: accepted  # proposed | accepted | superseded | rejected | deferred
decided_at: 2026-07-02
decided_by: arquiteto
approved_by: Alexandro
supersedes: null
superseded_by: null
related_adrs: [ADR-000, ADR-001, ADR-002, ADR-006]
related_pdrs: [PDR-002]
related_epics: [EPIC-002]
created_at: 2026-07-02
updated_at: 2026-07-02
---

# ADR-003 — Deduplicação e validação pela chave de acesso

## Contexto

O incentivo do Quantah é **por volume** (cashback, gamificação) e cupons de terceiros são
**desejáveis** (§4.3). A única salvaguarda necessária — e, segundo a visão, **suficiente** — contra
contagem/pagamento em dobro é a **deduplicação pela chave de acesso de 44 dígitos**, única por cupom
(§6.4). Além disso, a §6.1 observa que **a própria chave já carrega informação estruturada** — UF,
ano/mês, CNPJ do emitente e modelo do documento — de modo que **parte da validação e da dedup pode
ser feita só a partir da URL/chave, sem nem acessar o portal**.

Esta ADR decide **o que se valida e deduplica apenas pela chave**, antes da extração cara e frágil
(ADR-002), e **como a idempotência é garantida**. É a regra de negócio mais crítica do épico (o
checklist de validação exige cobertura ≥98% no núcleo de dedup, STORY-010), e por isso merece decisão
arquitetural própria, não improviso na implementação.

Restrições: só SP nesta onda (`cUF = 35`); NFC-e é `modelo = 65`; a dedup **não pode depender de CPF**
(ADR-006 — funciona sem ele, já que a chave é a identidade). O PDR-002 (recorrência/limite) tem
parentesco conceitual — consulta por chave sem virar gargalo — resolvido aqui com índice único.

## Forças (drivers) da decisão

- **F1 — Dedup à prova de corrida:** dois envios simultâneos da mesma chave não podem gerar dois
  registros. A garantia tem de ser **do banco**, não só do app.
- **F2 — Barato antes de caro:** rejeitar chave malformada/não-SP/modelo errado **sem** tocar o portal
  economiza a extração frágil e protege a fonte (ADR-002 F7).
- **F3 — Idempotência total:** reenviar a mesma chave é no-op observável ("já recebido"), incluindo no
  reprocessamento (ADR-002).
- **F4 — Validação em profundidade (segurança):** a chave tem **dígito verificador (mod 11)**; validar
  o DV filtra digitação/forja triviais antes de gastar extração.
- **F5 — Independência de PII (#privacidade, ADR-006):** a chave é a identidade; CPF não entra na
  dedup.
- **F6 — Datastore-first / simplicidade (#1, #3):** a garantia é um **índice único** no Postgres +
  upsert idempotente; nada além.

## O que dá para validar só pela chave (sem portal)

A chave de 44 dígitos decompõe-se em: `cUF(2) AAMM(4) CNPJ(14) mod(2) serie(3) nNF(9) tpEmis(1)
cNF(8) cDV(1)`. Validações **locais** (pré-portal):

| Verificação | Regra | Falha → |
|---|---|---|
| Formato | exatamente 44 dígitos numéricos | `rejeitado` (malformada) |
| Dígito verificador | `cDV` bate com mod 11 dos 43 primeiros | `rejeitado` (inválida/forjada) |
| UF (escopo) | `cUF = 35` (SP) nesta onda | `rejeitado` (fora de escopo) |
| Modelo | `mod = 65` (NFC-e) | `rejeitado` (não é NFC-e) |
| Duplicidade | `chave_acesso` já existe | **idempotente** (retorna o existente) |

Só o que **passa** todas segue para a fila de extração (ADR-002). Autenticidade real (o cupom existe e
está válido na SEFAZ) só o portal confirma — é a validação da extração, não pré-portal.

## Opções consideradas

### Opção A — Índice único no Postgres + upsert idempotente + validação pré-portal pela chave
- **Resumo:** `UNIQUE(chave_acesso)` na tabela `cupons`. A ingestão faz `INSERT ... ON CONFLICT
  (chave_acesso) DO NOTHING` (ou `firstOrCreate`) **dentro de transação**; se a chave já existe,
  retorna o cupom existente sem duplicar nem re-enfileirar. Antes do insert, um **value object
  `ChaveAcesso`** valida formato, DV (mod 11), UF e modelo — rejeitando cedo o que não serve.
- **Como atende aos princípios:**
  - ✅ Datastore-first: a unicidade é constraint do banco (à prova de corrida, F1).
  - ✅ Simplicidade: um índice, um upsert, um VO puro (testável sem banco).
  - ✅ Independe de PII (F5); barato antes de caro (F2); idempotente (F3).
- **Prós concretos:** corrida resolvida no banco; validação pura e 100% testável (cobertura ≥98%
  trivial); rejeição barata protege o portal.
- **Contras concretos:** exige tratar o `ON CONFLICT`/exceção de unicidade como caminho **normal**
  (não como erro), para o reenvio ser no-op limpo.

### Opção B — Dedup só na aplicação (checar `SELECT` antes de inserir)
- **Resumo:** `if (existe) return; else insert;` sem constraint única.
- **Como atende aos princípios:** ❌ F1 — janela de corrida entre o SELECT e o INSERT gera duplicata
  sob concorrência; ⚠️ depende de "todo caminho lembrar de checar".
- **Contras:** falha exatamente sob o cenário que mais importa (dois envios simultâneos do "caçador").

### Opção C — Dedup por hash do conteúdo extraído
- **Resumo:** deduplicar depois de extrair, por hash dos itens.
- **Contras:** cara (exige extrair antes de saber se é duplicata — desperdiça o portal); frágil
  (mesma nota pode ter representações diferentes); ignora que a chave **já é** a identidade única
  (§6.4). Descartada.

## Matriz comparativa

| Critério (força) | Peso | Opção A (unique + upsert) | Opção B (app-only) | Opção C (hash conteúdo) |
|---|---|---|---|---|
| F1 — à prova de corrida | alto | ✅ constraint do banco | ❌ janela de corrida | ⚠️ |
| F2 — barato antes de caro | alto | ✅ rejeita pré-portal | ⚠️ | ❌ extrai antes |
| F3 — idempotência | alto | ✅ no-op no reenvio | ⚠️ | ❌ |
| F4 — DV mod 11 | médio | ✅ no VO | ⚠️ | ⚠️ |
| F5 — independe de CPF | alto | ✅ | ✅ | ✅ |
| F6 — datastore-first/simples | alto | ✅ 1 índice + upsert | ⚠️ | ❌ |

## Decisão proposta

> **Optamos pela Opção A.**

A deduplicação é garantida por um **índice único `UNIQUE(chave_acesso)` no Postgres** combinado com
**upsert idempotente** (`ON CONFLICT DO NOTHING` / `firstOrCreate`) em transação: reenviar a mesma
chave retorna o cupom existente, sem segundo registro nem segundo job. Antes de persistir, um **value
object `ChaveAcesso`** valida **formato (44 dígitos), dígito verificador (mod 11), UF (`35` = SP) e
modelo (`65` = NFC-e)**, rejeitando cedo o que não serve — protegendo a extração cara (ADR-002). A
autenticidade real fica com a extração no portal. A dedup **não usa CPF** (ADR-006). "Válido, único e
novo" (north-star, STORY-012) = `status = validado` (autenticado na SEFAZ) **e** primeira ocorrência
da chave.

## Justificativa

A visão já diz que a chave de 44 dígitos é a salvaguarda principal e suficiente (§6.4) e que muito se
valida só pela chave (§6.1) — a Opção A é a tradução direta disso. Colocar a unicidade no banco (não
no app) é o único jeito de resolver o cenário que realmente importa: dois envios simultâneos da mesma
nota (comum no "caçador" que reenvia). A validação por VO puro dá a cobertura ≥98% exigida sem esforço
e move a rejeição para antes do portal. As alternativas falham exatamente onde a regra é crítica
(corrida em B; custo/fragilidade em C). Trade-off aceito: tratar conflito de unicidade como fluxo
normal — barato e explícito.

## Consequências

### Positivas (o que ganhamos)
- Zero contagem em dobro, inclusive sob concorrência; reenvio é no-op limpo.
- Rejeição barata de chave inválida/fora de escopo antes de gastar extração.
- Núcleo de regra testável a ≥98% sem banco (VO) + um teste de integração do upsert.

### Negativas / trade-offs aceitos
- A validação pela chave **não** garante autenticidade (só o portal garante) — por isso `pendente`
  até a extração validar. É correto e explícito, não um furo.
- Se um dia a onda incluir outro estado, o VO precisa aceitar outros `cUF` — mudança localizada.

### Neutras
- Cupom `rejeitado` por chave inválida é registrado (para métrica de qualidade), sem PII.

### Para o time
- **Impacto em estórias:** STORY-010 implementa o índice único, o upsert e o VO com a cobertura ≥98%;
  STORY-012 conta "válido-único-novo" sobre `status` + unicidade da chave.
- **ADRs relacionados:** ADR-001 (a chave é a chave natural do modelo), ADR-002 (idempotência no
  reprocesso), ADR-006 (dedup sem CPF).
- **Spike de validação:** sim — o spike desta STORY-008 prova dedup idempotente + rejeição de chave
  malformada e não-SP.

## Plano de verificação

- **Como verificar conformidade:** migração cria `UNIQUE(chave_acesso)`; testes cobrem DV correto/
  incorreto, UF SP vs não-SP, modelo 65 vs outro, formato inválido, e **reenvio idempotente** (um só
  registro, um só job). Um teste de concorrência (dois inserts da mesma chave) confirma que a
  constraint segura.
- **Sinais de revisão (quando reabrir):** inclusão de novo estado (ampliar `cUF` aceito); mudança da
  política de escopo (produto → PO); se a SEFAZ mudar a estrutura da chave (improvável — padrão
  nacional modelo 65).
- **Spike de validação:** STORY-008 (esta).

---

## Aprovação humana

- **Status final:** ✅ aceita
- **Aprovado por:** Alexandro
- **Data:** 2026-07-02
- **Forma do aceite:** aprovação explícita em sessão de Cowork (papel Arquiteto), lote do spike STORY-008.
- **Condicionantes do aceite:** nenhuma.

---

## Histórico

- 2026-07-02 — criada como `proposed` por Arquiteto (spike STORY-008 do EPIC-002).
- 2026-07-02 — **aceita** por Alexandro → `accepted`.
