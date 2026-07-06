---
epic_id: EPIC-009
slug: enriquecimento-cadastral-emitente
title: Enriquecimento cadastral do emitente — CNPJ via API pública da RFB, fila e cache
wave: WAVE-2026-03
status: ready
owner_role: po
created_at: 2026-07-06
updated_at: 2026-07-06
target_completion: 2026-07-09
---

# EPIC-009 — Enriquecimento cadastral do emitente

## Por que existimos (problema do usuário)

O novo modelo de remuneração (PDR-004) pontua o cupom conforme **o que ele vale para o dado** — e a
primeira dimensão disso é a categoria do estabelecimento (CNAE). Hoje o cupom carrega apenas o CNPJ do
emitente (parte da chave de acesso); não sabemos se veio de um supermercado, uma farmácia ou um posto.
Sem o cadastro do emitente, o motor de pontos (EPIC-010) nasce cego, e o lado B2B perde uma dimensão de
segmentação valiosa.

## Resultado esperado (outcome)

Ao fim deste épico, todo cupom processado tem os dados cadastrais do emitente (razão social, CNAE
principal, situação cadastral, município/UF) obtidos de **API pública e gratuita** de dados da RFB, por
consulta **assíncrona em fila**, com **cache de pelo menos 30 dias (parametrizável)** — sem nunca
bloquear ou atrasar o fluxo de envio do cupom para o Colaborador.

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: ≥ 95% dos cupons processados em homologação com emitente enriquecido (CNAE
  presente) em até 24h do envio.
- Métrica de eficiência: para CNPJs já vistos dentro do TTL, zero chamadas à API externa (taxa de
  acerto do cache observável).
- Métrica de qualidade: falha da API externa não gera erro visível ao Colaborador nem perda de cupom.

## Entregável visível no fim do épico

- [ ] No Backoffice, o detalhe de um cupom exibe os dados cadastrais do emitente (razão social, CNAE e
      descrição, município/UF, situação) — em homologação.
- [ ] Reenvio de cupom do mesmo CNPJ dentro do TTL não gera nova consulta externa (evidência por
      log/contador no Backoffice ou relatório da validação).
- [ ] TTL do cache parametrizável (default 30 dias) como configuração semeada — a tela chega no
      EPIC-012.

## Fora de escopo (explicitamente)

- Cálculo de pontos (EPIC-010) — este épico só disponibiliza o insumo.
- Tela de configuração de parâmetros (EPIC-012).
- Enriquecimento retroativo em massa de cupons antigos além do necessário para a migração da onda
  (decisão de retroatividade pertence ao EPIC-011, se preciso).
- Qualquer fonte de dados paga ou raspagem de site da RFB.

## Referências da especificação

- `docs/visao.md` — §6 (extração/modelo do cupom), §7 (dado como ativo).
- `docs/project-state/decisions/pdr/PDR-004-pontuacao-gamificada-substitui-rate-fixo.md` — regras 1–2.
- `docs/project-state/decisions/adr/ADR-001-ingestao-modelo-canonico-cupom.md` — onde o CNPJ do
  emitente vive hoje.
- `docs/skills/_project.md` §4 — glossário (chave de acesso contém o CNPJ do emitente).

## Dependências

- **Bloqueia:** EPIC-010 (motor de pontos usa o CNAE), EPIC-012 (TTL configurável na tela).
- **Bloqueado por:** nada — primeiro épico da onda.
- **Decisões arquiteturais necessárias:** escolha da API pública (contrato, limites de taxa),
  arquitetura de fila e retry, estratégia de cache com TTL parametrizável, comportamento de fallback
  (CNPJ não encontrado / API fora). **Não existem ADRs para isso — a STORY-039 é o spike.**

## Estórias

- [x] STORY-039 (spike, arquiteto) — Arquitetura do enriquecimento CNPJ: API pública RFB, filas, cache e fallback (done — ADR-012 a ADR-015 accepted)
- [ ] STORY-040 — Consulta assíncrona ao CNPJ com cache parametrizável
- [ ] STORY-041 — Emitente enriquecido no pipeline do cupom e visível no Backoffice
- [ ] STORY-042 (validação) — Validação final do épico

## Validação final

Critérios em `validation/checklist.md`. Relatório do validador em `validation/report.md`.

**Definição de épico concluído:** todas as estórias `done` + relatório de validação `approved` +
funcionalidade demonstrável em homologação.

## Histórico

- 2026-07-06 — criado por PO (abertura da WAVE-2026-03, PDR-004/PDR-005)
