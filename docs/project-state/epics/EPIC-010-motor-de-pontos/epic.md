---
epic_id: EPIC-010
slug: motor-de-pontos
title: Motor de pontos — cupom válido gera pontos por regras compostas, em fila
wave: WAVE-2026-03
status: draft
owner_role: po
created_at: 2026-07-06
updated_at: 2026-07-06
target_completion: 2026-07-12
---

# EPIC-010 — Motor de pontos

## Por que existimos (problema do usuário)

O rate fixo remunera igual o cupom que repete dado já coletado e o cupom que abre categoria nova. Para
o Colaborador Engajado ("caçador"), não há jogo: nada a otimizar, nada a caçar. O PDR-004 decidiu que
cada cupom válido gera **pontos** calculados por regras que refletem o valor do dado — e este épico
constrói esse motor.

## Resultado esperado (outcome)

Ao fim deste épico, todo cupom válido e deduplicado gera pontos de forma **assíncrona (fila)**, por um
**motor de regras abstrato e parametrizável** que compõe no mínimo: CNAE do emitente (EPIC-009),
quantidade de itens únicos, valor do cupom e itens com pontos extras. O Colaborador vê os pontos no
extrato com a memória de cálculo ("por que esse cupom valeu N pontos").

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: 100% dos cupons válidos com pontos creditados em até 1h do enriquecimento (fila
  saudável), determinísticos e reproduzíveis (mesmo cupom + mesmos parâmetros = mesmos pontos).
- Métrica de produto: memória de cálculo compreensível — cada crédito decomposto por regra no extrato.
- Métrica de qualidade: cobertura ≥ 98% no motor (é regra de negócio nuclear).

## Entregável visível no fim do épico

- [ ] Colaborador envia cupom e vê os pontos creditados no extrato, decompostos por regra — em
      homologação.
- [ ] Cupom cujo emitente ficou sem CNAE (falha definitiva do enriquecimento) pontua pelas demais
      regras — nunca fica sem pontuação por causa da dependência externa.
- [ ] Parâmetros das regras (pesos por CNAE, pontos por item único, faixas por valor, lista de itens
      com bônus) vivem como configuração semeada e versionada — a tela chega no EPIC-012.

## Fora de escopo (explicitamente)

- Conversão pontos → R$, resgate e migração do saldo legado (EPIC-011).
- Tela de configuração (EPIC-012).
- Badges, níveis, ranking (onda futura).
- Remuneração em R$ direta por cupom — deixa de existir com a virada (PDR-004), mas o **desligamento**
  do crédito antigo acontece junto com a migração, no EPIC-011 (até lá, homologação pode conviver com
  os dois em paralelo para comparação).

## Referências da especificação

- `docs/project-state/decisions/pdr/PDR-004-pontuacao-gamificada-substitui-rate-fixo.md` — regras 1, 2 e 4.
- `docs/visao.md` — §5 (proposta de valor ao Colaborador), §8.2 e premissa regulatória do PDR-004.
- EPIC-009 — contrato do emitente enriquecido (CNAE).
- `docs/project-state/decisions/adr/ADR-001` e `ADR-003` — modelo canônico do cupom e deduplicação
  (pontos só para cupom válido e único).

## Dependências

- **Bloqueia:** EPIC-011, EPIC-012.
- **Bloqueado por:** EPIC-009 (CNAE disponível) — parcial: o spike STORY-039 já deve desenhar a
  abstração do motor, e regras não-CNAE podem começar antes se o sequenciamento do sprint pedir.
- **Decisões arquiteturais necessárias:** abstração do motor de regras (cobertas pelo spike STORY-039);
  modelo do razão (ledger) de pontos — imutável, auditável (spike também cobre).

## Estórias

(Fluxo B ao abrir o épico — decomposição só quando EPIC-009 estiver encaminhado.)

## Validação final

Critérios em `validation/checklist.md` (a criar na decomposição). **Definição de épico concluído:**
todas as estórias `done` + relatório do validador `approved` + demonstrável em homologação.

## Histórico

- 2026-07-06 — criado por PO (abertura da WAVE-2026-03, PDR-004/PDR-005)
