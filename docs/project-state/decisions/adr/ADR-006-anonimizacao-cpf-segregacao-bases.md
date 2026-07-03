---
adr_id: ADR-006
slug: anonimizacao-cpf-segregacao-bases
title: Anonimização de CPF na coleta e segregação de bases (LGPD)
status: proposed  # proposed | accepted | superseded | rejected | deferred
decided_at: null
decided_by: arquiteto
approved_by: null
supersedes: null
superseded_by: null
related_adrs: [ADR-000, ADR-001, ADR-002, ADR-003]
related_pdrs: [PDR-002]
related_epics: [EPIC-002]
created_at: 2026-07-02
updated_at: 2026-07-02
---

# ADR-006 — Anonimização de CPF na coleta e segregação de bases (LGPD)

## Contexto

O consumidor pode ter incluído **CPF na nota**. No instante em que isso acontece, o retorno da SEFAZ
passa a conter **dado pessoal** (LGPD). A visão é categórica (§3.1, §7): *"o objetivo do produto não é
identificar o consumidor"*, *"privacidade por design"* e *"anonimizar o CPF desde a coleta"* — o CPF
**não é necessário** para o valor do produto, que é o **preço do item**, não quem comprou. O checklist
de validação do épico exige, de forma bloqueante: **CPF nunca persistido em claro** em nenhuma tabela
do modelo canônico; **nenhum vazamento** de CPF em logs, erros, fixtures ou telemetria; **dedup e
contagem não dependem de CPF** (ADR-003); e **teste de regressão** que falha se algum caminho voltar a
gravar CPF em claro (STORY-011 CA-1..5).

Esta ADR decide **o tratamento estrutural do CPF na coleta** e a **segregação entre a base analítica**
(cupons/itens/preços — o produto) e a **base de pagamento** (carteira/saque — EPIC-003, com o mínimo
de PII necessário por obrigação fiscal). Ela pendura no ponto de **normalização** definido na ADR-001
e restringe o que a extração (ADR-002) pode logar. Vale `security-architecture.md` (classificação de
dados, LGPD por design, mascaramento em log, retenção).

Ponto de honestidade arquitetural: para o produto, **a alternativa mais simples e mais segura é
simplesmente não guardar o CPF** — descartá-lo na normalização. Só se houver uma necessidade concreta
e aprovada (ex.: anti-fraude que precise correlacionar contribuições por titular) é que se cogita um
**pseudônimo irreversível** — e mesmo assim a dedup do épico (ADR-003) é por chave, não por CPF.

## Forças (drivers) da decisão

- **F1 — Privacidade por design (§3.1, §7):** minimização — não coletar/reter o que não agrega valor.
  CPF não agrega (o valor é o preço).
- **F2 — Zero CPF em claro, zero vazamento (STORY-011 CA-1/CA-4):** nem em tabela, nem em log, erro,
  fixture ou telemetria. Garantia estrutural + automatizada (teste de regressão).
- **F3 — Dedup/contagem independem de CPF (F5 da ADR-003):** o produto funciona sem CPF — a chave é a
  identidade.
- **F4 — Base legal defensável (LGPD Art. 7/Art. 18):** o tratamento tem base legal clara e suporta
  os direitos do titular; a não-coleta é a base legal mais forte (não há dado a expor/eliminar).
- **F5 — Segregação de bases (§7):** dado pessoal de pagamento (futuro) fica **separado** da base
  analítica; o entregável B2B é agregado/anônimo (nunca dado individual).
- **F6 — Automatizável > documentável (#9):** a proteção é código + teste que falha, não um aviso em
  doc.

## Opções consideradas

### Opção A — Descartar o CPF na normalização (não persistir), base analítica sem PII — *recomendada*
- **Resumo:** no ponto de normalização (ADR-001), um **`AnonimizadorCpf`** remove qualquer CPF do
  retorno **antes** de qualquer persistência ou log. O modelo canônico (`cupons`/`cupom_itens`)
  **não tem coluna para CPF**. Nenhum caminho grava, loga ou serializa CPF. A base analítica nasce
  livre de PII; a base de pagamento (EPIC-003) é outra história, segregada.
- **Como atende aos princípios:**
  - ✅ Minimização máxima: o dado que não existe não vaza, não precisa de retenção nem de resposta a
    pedido de eliminação (base legal mais forte — F4).
  - ✅ Automatizável: teste de regressão que falha se qualquer coluna/log receber padrão de CPF.
  - ✅ Simplicidade (#1): nada de gestão de chave/pseudônimo.
- **Prós concretos:** elimina uma classe inteira de risco LGPD; dedup/contagem já independem de CPF;
  trivial de provar por inspeção (não há onde procurar CPF).
- **Contras concretos:** se no futuro surgir necessidade real de correlacionar contribuições por
  titular, não haverá dado — mas isso é uma decisão de produto (PO) que exigiria nova base legal e
  provavelmente pseudônimo (Opção B), reaberta então.

### Opção B — Pseudonimizar o CPF com HMAC irreversível + pepper server-side (não em claro)
- **Resumo:** em vez de descartar, guardar `HMAC-SHA256(cpf, pepper)` num campo dedicado e **segregado**
  (não na tabela do cupom), com o `pepper` como segredo de app (nunca versionado). Permite
  correlacionar sem revelar o CPF.
- **Como atende aos princípios:** ⚠️ ainda é dado pessoal pseudonimizado (LGPD trata pseudônimo como
  dado pessoal — precisa base legal, retenção, direito de eliminação); ⚠️ gestão de segredo (pepper) e
  risco de reversão por dicionário (CPF tem espaço pequeno — 11 dígitos com DV → força bruta viável se
  o pepper vazar). Mitigável (pepper forte, rotação), mas é complexidade e risco que **o MVP não
  precisa**.
- **Prós:** correlação por titular possível sem CPF em claro.
- **Contras:** complexidade e risco sem demanda comprovada; não é exigido por nenhuma estória da onda;
  a dedup do épico não precisa dele.

### Opção C — Guardar CPF cifrado (at-rest) para "caso precise"
- **Resumo:** cifrar o CPF e guardar.
- **Como atende aos princípios:** ❌ contradiz minimização (§7) e o CA-1 (o dado continua sendo dado
  pessoal recuperável); ❌ cria dever de retenção/eliminação e superfície de vazamento à toa.
- **Contras:** guardar o que não se usa é o anti-padrão que a visão manda evitar. Descartada.

## Matriz comparativa

| Critério (força) | Peso | Opção A (descartar) | Opção B (HMAC pepper) | Opção C (cifrar) |
|---|---|---|---|---|
| F1 — minimização | alto | ✅ não existe | ⚠️ pseudônimo persiste | ❌ dado persiste |
| F2 — zero vazamento | alto | ✅ nada a vazar | ⚠️ hash pode ser atacado | ❌ superfície criada |
| F3 — dedup sem CPF | alto | ✅ | ✅ | ✅ |
| F4 — base legal | alto | ✅ não-coleta (mais forte) | ⚠️ exige base + retenção | ❌ mais frágil |
| F5 — segregação | alto | ✅ base analítica sem PII | ✅ se segregado | ⚠️ |
| F6 — automatizável | médio | ✅ teste de regressão trivial | ⚠️ | ⚠️ |
| Simplicidade (#1) | alto | ✅ | ❌ gestão de pepper | ❌ gestão de chave |

## Decisão proposta

> **Optamos pela Opção A — descartar o CPF na coleta.**

No ponto de normalização (ADR-001), um **`AnonimizadorCpf`** **remove** qualquer CPF do retorno da
SEFAZ **antes** de qualquer persistência ou log. O **modelo canônico não tem coluna de CPF** e
**nenhum caminho** (persistência, log, erro, fixture, telemetria) carrega CPF. A base **analítica**
(cupons/itens/preços) nasce **livre de PII**; a base de **pagamento** (carteira/saque — EPIC-003) será
**segregada**, guardando apenas o mínimo de dado pessoal exigido por obrigação fiscal, com base legal
própria — nunca misturada à base analítica. Os entregáveis B2B são sempre **agregados/anônimos**
(§7). Se, no futuro, surgir necessidade **de produto** de correlacionar contribuições por titular, a
decisão é **reaberta** (Opção B, com base legal nova aprovada pelo PO) — não se antecipa complexidade
nem risco agora.

## Justificativa

A visão manda anonimizar o CPF desde a coleta porque ele não agrega valor (§7) — e a forma mais forte
de anonimizar é **não guardar**: o dado inexistente não vaza, não precisa de retenção, não gera dever
de eliminação, e satisfaz por construção todos os CAs de STORY-011. A dedup do épico é por chave
(ADR-003), então descartar o CPF não custa nada ao produto. Pseudônimo (B) e cifra (C) resolvem
problemas que a onda não tem, criando gestão de segredo e superfície de vazamento sem demanda — o
oposto do princípio #1 e da minimização da LGPD. A reversibilidade (#7) é preservada: se o produto um
dia precisar, reabre-se com base legal apropriada.

## Threat model leve

- **Adversário:** quem obtém acesso indevido à base analítica (dump, query, log, backup) ou a um
  entregável B2B, buscando identificar consumidores.
- **O que quer:** ligar compras a um CPF (perfilamento, revenda de PII).
- **Como impedimos:** o CPF **não está lá** — descartado na normalização; nenhuma coluna, log ou
  telemetria o contém; entregáveis B2B são agregados. Não há CPF para vazar.
- **Como sabemos se falhou:** teste de regressão que falha se qualquer coluna persistida ou linha de
  log casar com padrão de CPF; inspeção de schema (ausência de coluna) e de logs no CI/homologação.

## Consequências

### Positivas (o que ganhamos)
- Classe inteira de risco LGPD eliminada na origem; base legal mais forte (não-coleta).
- Prova trivial por inspeção (não há onde o CPF estaria); regressão automatizada.
- Dedup/contagem seguem funcionando (independem de CPF, ADR-003).

### Negativas / trade-offs aceitos
- Perde-se a possibilidade de correlacionar contribuições por titular — não exigida na onda; reabrível
  como decisão de produto com nova base legal.

### Neutras
- A base de pagamento (EPIC-003) terá seu próprio ADR de PII/segregação; esta ADR só fixa que ela é
  **separada** da analítica.

### Para o time
- **Impacto em estórias:** STORY-011 implementa o `AnonimizadorCpf` no ponto de normalização + o teste
  de regressão anti-CPF; STORY-010 garante que o schema não tem coluna de CPF; STORY-002/ADR-002
  garantem que a extração não loga CPF.
- **ADRs relacionados:** ADR-001 (ponto de normalização), ADR-002 (não logar CPF), ADR-003 (dedup sem
  CPF). Base de pagamento → ADR futuro no EPIC-003.
- **Spike de validação:** sim — o spike desta STORY-008 prova que um retorno **com CPF** é normalizado
  **sem** persistir CPF em lugar algum.

## Plano de verificação

- **Como verificar conformidade:** teste de regressão que injeta um cupom com CPF e assert que nenhuma
  coluna persistida nem nenhum log contém o CPF (busca por padrão de 11 dígitos/CPF). Inspeção de
  migração confirma ausência de coluna de CPF. Revisão de que fixtures não contêm CPF real.
- **Sinais de revisão (quando reabrir):** necessidade **de produto** (PO) de correlação por titular
  (→ Opção B com base legal nova); mudança regulatória; inclusão de fluxo que legitimamente precise de
  PII do consumidor (→ segregar, não misturar).
- **Spike de validação:** STORY-008 (esta).

---

## Aprovação humana

- **Status final:** ⬜ pendente
- **Aprovado por:** —
- **Data:** —
- **Forma do aceite:** —
- **Condicionantes do aceite:** —

---

## Histórico

- 2026-07-02 — criada como `proposed` por Arquiteto (spike STORY-008 do EPIC-002).
