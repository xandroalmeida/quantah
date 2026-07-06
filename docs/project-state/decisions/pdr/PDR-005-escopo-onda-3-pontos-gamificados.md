---
pdr_id: PDR-005
slug: escopo-onda-3-pontos-gamificados
title: Escopo da Onda 3 — remuneração por pontos gamificados ponta a ponta
status: accepted
decided_at: 2026-07-06
decided_by: PO (Alexandro / Claude)
supersedes: null
superseded_by: null
related_epics: [EPIC-009, EPIC-010, EPIC-011, EPIC-012]
related_adrs: []
---

# PDR-005 — Escopo da Onda 3 (WAVE-2026-03)

## Contexto

A WAVE-2026-02 fechou com os 5 épicos done: produto apresentável, identidade/acesso, jornada B2C mobile
e PWA instalável (EPIC-008 com gate manual de device pendente com o PO — não bloqueia). O rascunho de
next-wave apontava "recorrência/engajamento B2C" como candidato. O Alexandro decidiu (PDR-004) trocar o
rate fixo de cashback por um modelo de pontos gamificado — mudança estrutural na proposta de valor ao
Colaborador, que passa a ser o tema central da onda.

## Opções consideradas

### Opção 1 — Onda focada no piloto/baseline, pontos depois
- Prós: mede a north-star com o mecanismo atual antes de mudá-lo.
- Contras: mediríamos um incentivo que já decidimos matar; retrabalho de comunicação com pilotos.

### Opção 2 — Onda focada em pontos ponta a ponta (escolhida)
- Prós: piloto futuro já nasce com o incentivo definitivo; a migração de saldo acontece com poucos
  usuários (equipe), quando é barata.
- Contras: adia a leitura de baseline da north-star em algumas semanas.

### Opção 3 — Fazer os dois na mesma onda
- Contras: onda grande demais; violaria o planejamento em ondas enxutas.

## Decisão

> **Optamos pela Opção 2.** A WAVE-2026-03 entrega a remuneração por pontos ponta a ponta em
> homologação: enriquecimento CNPJ → motor de pontos → conversão/resgate/migração → configuração no
> Backoffice.

Piloto/baseline, endurecimento transversal, B2B autenticado e caminho de produção rolam para o rascunho
da próxima onda.

## Justificativa

Mudar o incentivo depois do piloto custa caro (recomunicação, migração com mais usuários, dados de
baseline invalidados). Mudar antes custa apenas o adiamento da leitura — e o modelo de pontos é a
alavanca de recorrência que o piloto quer medir.

## Consequências

### Positivas
- Piloto da próxima onda mede o incentivo definitivo.
- Migração de saldo com base de usuários mínima.

### Negativas / trade-offs aceitos
- Baseline da north-star adiado uma onda.
- Dívidas da Onda 2 (WISH-004/005/006, gate device do EPIC-008) seguem em aberto.

### Para o time técnico
- Sequência da onda: EPIC-009 → EPIC-010 → EPIC-011 → EPIC-012 (detalhe em `roadmap/current-wave.md`).
- Primeiro passo é estória de spike do Arquiteto (fila, API RFB, cache, motor de regras, ledger).

## Sinais de revisão

- Se o spike do Arquiteto revelar que a API pública da RFB inviabiliza o CNAE no prazo da onda →
  replanejar: motor de pontos sem componente CNAE primeiro (valor/itens/bônus), CNAE em onda seguinte.
- Se surgir demanda B2B quente durante a onda → PO reavalia prioridade entre EPIC-012 e B2B autenticado.
