---
story_id: STORY-008
slug: spike-ingestao-modelo-extracao
title: Spike de arquitetura — ingestão, modelo canônico do cupom, extração SEFAZ-SP, dedup e LGPD
epic_id: EPIC-002
sprint_id: null
type: spike
target_role: arquiteto
requires_design: false
design_screen_id: null
status: ready
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: L
---

# STORY-008 — Spike de arquitetura da coleta

> **Para o agente que vai executar (arquiteto):** este é um spike de decisão. O produto é
> **ADRs + um esqueleto verticalmente fino** que prova o caminho, não a feature completa. Registre
> as decisões como ADRs no índice; não implemente a coleta inteira aqui.

## Contexto (por que esta estória existe)

O EPIC-002 é o coração do Quantah, mas as decisões arquiteturais da coleta ainda não existem. Antes
de implementar captura, validação e persistência, o arquiteto precisa fixar: como o cupom é ingerido
e modelado, como extrair da SEFAZ-SP de forma resiliente, como deduplicar/validar pela chave de
44 dígitos, e como tratar CPF (LGPD). Sem isso, as estórias de implementação decidiriam arquitetura
sozinhas — proibido.

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- Documentos a ler ANTES: `docs/visao.md` §6 (mecânica de coleta e qualidade do dado) e §7 (LGPD);
  `docs/project-state/decisions/adr/ADR-000-stack-default.md` (stack vigente); `docs/skills/arquiteto/*`.

## O quê (objetivo desta estória)

Produzir as decisões arquiteturais da coleta como ADRs e um **spike vertical fino** (uma chave de
acesso de exemplo percorre ingestão → extração → modelo canônico → dedup) que comprove viabilidade:

- **ADR-001** — módulo de ingestão + **modelo canônico do cupom** (entidades/campos, chave natural).
- **ADR-002** — **extração resiliente SEFAZ-SP** (scraping do portal público; fila de reprocessamento,
  tratamento de captcha/instabilidade, monitoramento) com desenho de **adaptador por estado** (só SP na onda).
- **ADR-003** — **deduplicação e validação por chave de acesso** (44 dígitos; o que dá para validar só
  pela URL/chave — UF, ano/mês, CNPJ, modelo — antes de acessar o portal).
- **ADR-006** — **anonimização de CPF e segregação de bases** (LGPD): o que se guarda, o que se
  descarta/anonimiza, base legal.

## Por quê (valor para o usuário)

Decisões erradas aqui contaminam todo o épico (dado duplicado, extração frágil, exposição de dado
pessoal). O spike derisca o coração do produto antes do investimento de implementação.

## Critérios de aceite

- [ ] **CA-1:** ADR-001, ADR-002, ADR-003 e ADR-006 criados, com contexto/decisão/consequências, e
      **indexados** em `index.json` (`decisions.adr`), status `accepted` (ou `proposed` + o que falta).
- [ ] **CA-2:** Existe o **modelo canônico do cupom** documentado (entidades, campos mínimos, chave
      natural = chave de acesso), suficiente para as estórias 010/011 implementarem sem reabrir decisão.
- [ ] **CA-3:** Spike vertical demonstra, de ponta a ponta e para **uma** chave de exemplo de SP:
      parse da chave/URL → extração de ao menos os campos-âncora → normalização no modelo canônico →
      deduplicação idempotente (reenvio da mesma chave não duplica).
- [ ] **CA-4:** A estratégia de extração cobre **falha e reprocessamento** (o que acontece quando o
      portal falha/rate-limita) — desenhada na ADR-002 e exercida minimamente no spike.
- [ ] **CA-5:** Tratamento de CPF definido (ADR-006): o spike **não persiste CPF em claro**.

## Fora de escopo

- UI de coleta (STORY-009), fluxo completo de validação/persistência de produção (STORY-010),
  anonimização end-to-end (STORY-011), instrumentação (STORY-012).
- Adaptadores de outros estados; matching de produtos/GTIN (ADR-004, fora da onda).

## Padrões de qualidade exigidos

Segue `quality-standards.md`. O spike pode ser fino, mas o que ficar versionado tem teste; nenhuma
credencial/segredo commitado; nenhum CPF em claro persistido.

## Dependências

- **Bloqueada por:** EPIC-000 (ambiente) e EPIC-001 (UI) — `done`.
- **Bloqueia:** STORY-009, STORY-010, STORY-011, STORY-012 (todas dependem das ADRs/modelo).

## Decisões já tomadas (não as reabra)

- ADR-000 (stack Laravel + Inertia/React + PostgreSQL), ADR-007 (infra), ADR-008 (Dusk).
- Escopo: só SP nesta onda; dedup por chave de 44 dígitos é a salvaguarda principal (visao §6.4).

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 atendidos; ADRs criados e indexados.
- [ ] Spike vertical roda com teste; sem segredo/CPF em claro versionado.
- [ ] `index.json` atualizado: story `done`; ADRs em `decisions.adr`.
- [ ] "Notas do agente" preenchida (incluindo o que ficou como recomendação para 009–012).

## Protocolo do agente (obrigatório)

Siga `docs/skills/arquiteto/*` e `agent-task-format.md`. Decisão arquitetural → ADR (não IDR).
Se uma decisão for de produto (não técnica), **pare e escale ao PO**.

## Notas do agente (preenchido durante/após execução)

> **Status:** spike executado; produto entregue (4 ADRs `proposed` + spike vertical com teste verde).
> A estória fica em `in_review` **aguardando aprovação humana das ADRs** (protocolo do arquiteto: ADR só
> vira `accepted` com aceite explícito de Alexandro). Ao aprovar, ADRs → `accepted` e STORY-008 → `done`.

### Decisões tomadas (ADRs) — todas `proposed`, aguardando aceite
- **ADR-001 — Ingestão + modelo canônico do cupom** (persistência/topológico). Módulo `App\Domain\Coleta`
  com fronteira `IngestaoCupomService`, interface `SefazAdapter` (adaptador por estado), e agregado
  `Cupom` (raiz) + `CupomItem` cuja **chave natural é `chave_acesso` (44 dígitos, UNIQUE)**. Ingestão
  **assíncrona e idempotente**; modelo **sem coluna de CPF**. Contém a tabela do modelo canônico que
  010/011 devem usar sem reabrir decisão. → CA-2.
- **ADR-002 — Extração resiliente SEFAZ-SP** (integração). Fila **no Postgres** (`database` queue,
  `FOR UPDATE SKIP LOCKED`) + worker + adaptador-ACL por estado. Falha **tipada**: transitória
  (retry/backoff + dead-letter), estrutural (layout/captcha → **parar e alertar**, sem retry), negócio
  (rejeita). Rate limit próprio; snapshot bruto sem PII. Datastore-first, sem broker. → CA-4.
- **ADR-003 — Dedup + validação por chave** (persistência). `UNIQUE(chave_acesso)` + upsert idempotente
  (à prova de corrida, garantia do banco). Validação **só pela chave, pré-portal**: formato, **DV mod 11**,
  UF=35 (SP), modelo=65 (NFC-e). "Válido-único-novo" = `validado` + primeira ocorrência. Independe de CPF. → CA-3.
- **ADR-006 — Anonimização de CPF + segregação** (persistência/segurança). Decisão: **descartar** o CPF na
  normalização (minimização máxima — o dado que não existe não vaza). Modelo analítico sem PII; base de
  pagamento (EPIC-003) segregada. Threat model + teste de regressão anti-CPF. → CA-5.

### Descobertas
- A chave de 44 dígitos permite rejeitar **malformada / DV inválido / não-SP / não-NFC-e** antes de gastar
  a extração frágil (visao §6.1) — implementado no VO `ChaveAcesso` + escopo no `IngestaoCupomService`.
- A stack já entrega o que a extração resiliente precisa **sem serviço extra**: `QUEUE_CONNECTION=database`
  (Postgres) já é o default do `.env` do projeto — coerente com o princípio datastore-first.
- Distinguir falha **transitória** de **estrutural** (layout/captcha) é o ponto-chave da resiliência: a
  primeira se resolve com retry; a segunda tem de **parar e alertar** (não martelar o portal público).

### Recomendações para as estórias de implementação (009–012)
- **STORY-009 (captura/UI):** chamar **apenas** `IngestaoCupomService::ingerir($chaveOuUrl, $origem)`; nunca
  tocar o Eloquent do cupom. Estados de UI: `pendente` (recebido) → `validado` (aceito) / `falha` / `rejeitado`.
- **STORY-010 (persistência/validação):** trocar a extração síncrona do spike por um **Job enfileirado**
  (ADR-002) com retry/backoff/reprocessamento; implementar o `SpSefazAdapter` **real** (scraping + throttle);
  manter a cobertura do núcleo de dedup/validação ≥98% (o VO já é 100% testável sem banco).
- **STORY-011 (LGPD):** o `AnonimizadorCpf` já existe e é aplicado no ponto de normalização; adicionar o
  **teste de regressão** que falha se qualquer coluna/log/telemetria voltar a conter CPF (o spike já tem o
  embrião: `test_cpf_nunca_e_persistido_em_claro`).
- **STORY-012 (north-star):** contar "válido-único-novo" = `status='validado'` sobre `chave_acesso` distinta;
  taxa de sucesso = `validado / (validado+falha+rejeitado)`; nenhum sinal com PII.
- **Teste arquitetural (recomendado):** proibir imports dos modelos `Cupom`/`CupomItem` fora de `App\Domain\Coleta`.

### Bloqueios encontrados
- Nenhum bloqueio técnico. **Pendência de processo:** as 4 ADRs precisam do aceite humano (Alexandro) para
  virarem `accepted` e a estória fechar como `done`. Não há decisão de produto pendente (o escopo — só SP,
  dedup por chave, anonimizar CPF — já estava fixado na visão).

### Links de evidência
- ADRs: `docs/project-state/decisions/adr/ADR-001..003,006-*.md` (indexadas em `index.json` › `decisions.adr`, `proposed`).
- Spike vertical: `app/app/Domain/Coleta/**` (VO `ChaveAcesso`, `SefazAdapter`/`SpSefazAdapter`,
  `AnonimizadorCpf`, `IngestaoCupomService`), modelos `app/app/Models/Cupom.php`/`CupomItem.php`,
  migração `app/database/migrations/2026_07_02_000001_create_cupons_table.php`.
- Testes (verde, 18/18 · 73 asserts; suíte total 82/82): `app/tests/Unit/Coleta/*`,
  `app/tests/Feature/Coleta/IngestaoCupomSpikeTest.php`, fake `app/tests/Support/Coleta/FakeSefazSpFetcher.php`.
  Comando: `make test` (ou `./vendor/bin/sail artisan test tests/Unit/Coleta tests/Feature/Coleta`).
- Chave de exemplo de SP usada no spike: `35260112345678000195650010001234561000000019` (DV mod 11 válido).
