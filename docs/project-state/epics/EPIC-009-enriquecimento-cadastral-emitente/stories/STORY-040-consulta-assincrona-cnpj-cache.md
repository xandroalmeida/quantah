---
story_id: STORY-040
slug: consulta-assincrona-cnpj-cache
title: Consulta assíncrona ao CNPJ do emitente com cache parametrizável
epic_id: EPIC-009
sprint_id: null
type: implementation
target_role: programador
requires_design: false
status: ready
owner_agent: null
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

### Decisões tomadas
-

### Descobertas
-

### Bloqueios encontrados
-

### IDRs criados
-

### Cobertura final
-

### Links de evidência
-
