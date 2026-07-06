---
pdr_id: PDR-004
slug: pontuacao-gamificada-substitui-rate-fixo
title: Pontuação gamificada substitui o rate fixo de cashback
status: accepted
decided_at: 2026-07-06
decided_by: PO (Alexandro / Claude)
supersedes: null
superseded_by: null
related_epics: [EPIC-009, EPIC-010, EPIC-011, EPIC-012]
related_adrs: []
---

# PDR-004 — Pontuação gamificada substitui o rate fixo de cashback

## Contexto

Desde a Onda 1, a remuneração do Colaborador é um **rate fixo**: R$ 1,00 a cada R$ 1.000,00 em cupons
válidos (0,1% — visão §8.1, glossário). O rate fixo provou o loop `cupom → saldo`, mas é uma alavanca
pobre: remunera apenas pelo **valor** do cupom, sem distinguir o que interessa ao negócio — cobertura de
categorias (CNAE), diversidade de itens, produtos estratégicos para o lado B2B. A visão (§5, §8.2) já
previa gamificação como alavanca de recorrência; a Onda 2 entregou a jornada B2C onde ela pode viver.

Queremos que o incentivo **dirija a coleta** para onde o dado vale mais, mantendo o custo de aquisição
sob controle e a mecânica divertida (acumular pontos) para o Colaborador.

## Opções consideradas

### Opção 1 — Manter rate fixo e adicionar gamificação decorativa (pontos sem valor)
- Descrição: cashback continua 0,1%; pontos/badges apenas simbólicos.
- Prós: zero risco de transição; gamificação já era "livre" na visão §8.2.
- Contras: duas moedas paralelas confundem; pontos sem valor não dirigem comportamento; rate fixo continua não diferenciando o dado que importa.

### Opção 2 — Pontos como única unidade de acúmulo, com conversão paramétrica em R$
- Descrição: cada cupom válido gera **pontos** calculados por um motor de regras configurável (CNAE do
  emitente, quantidade de itens únicos, valor do cupom, itens com pontos extras). Pontos convertem em R$
  por taxa paramétrica ("N pontos valem X reais") no momento do **resgate manual**, acima de um mínimo.
- Prós: uma moeda só; alavanca fina de incentivo por categoria/produto; custo de aquisição controlável
  pelos parâmetros; mecânica de acúmulo é o gancho de recorrência da visão §5.
- Contras: mais complexo (motor de regras, enriquecimento CNPJ, configuração); exige migração do saldo
  legado; comunicação cuidadosa com o Colaborador.

### Opção 3 — Status quo (nada mudar)
- Consequência: incentivo continua cego ao valor do dado; gamificação fica adiada; custo por cupom
  fixo mesmo para dado redundante.

## Decisão

> **Optamos pela Opção 2.**

O rate fixo de 0,1% deixa de existir. Todo cupom válido passa a gerar **pontos**, calculados de forma
assíncrona por um motor de regras configurável; pontos convertem em R$ apenas via **resgate manual com
mínimo**, pela taxa vigente no momento do resgate.

## Regras de produto decididas

1. **Dois mecanismos separados e parametrizáveis:**
   - **Cupom → pontos:** motor de regras abstrato que compõe, no mínimo: CNAE do estabelecimento
     emitente, quantidade de itens únicos no cupom, valor do cupom e itens com pontos extras (bônus por
     produto). Novas regras devem poder entrar sem reescrever o motor.
   - **Pontos → R$:** taxa paramétrica "N pontos = X reais", com **mínimo de pontos para resgate**.
     A taxa aplicada é a **vigente no momento do resgate** (não travada no ganho).
2. **Enriquecimento cadastral do emitente:** o CNAE vem de consulta ao CNPJ em **API pública e gratuita
   da RFB**, feita de forma **assíncrona (fila)**, com **cache de pelo menos 30 dias (parametrizável)**.
   O cálculo de pontos também roda em fila — creditar pontos nunca bloqueia o envio do cupom.
3. **Migração do saldo legado:** o saldo em R$ acumulado pelo rate fixo é **convertido em pontos** pela
   taxa de conversão inicial, no corte da virada, como evento auditável e visível no extrato do
   Colaborador. Após o corte, não existe mais crédito direto em R$ por cupom.
4. **Parâmetros valem só dali pra frente:** mudanças de configuração (regras, taxa, mínimo, TTL do
   cache) afetam apenas cupons processados e resgates efetuados **após** a mudança. Pontos já creditados
   são imutáveis. Toda mudança de parâmetro tem histórico (quem, quando, o quê).
5. **Configuração no Backoffice:** os parâmetros dos dois mecanismos são administráveis em tela própria
   da área Backoffice (RBAC — ADR-009).

## Premissa regulatória (importante)

A visão §8.2 classifica gamificação como livre **porque não entrega prêmio material**. Pontos que
convertem em dinheiro **não são** essa gamificação pura: são a **unidade de medição da remuneração pelo
serviço de coleta** — a mesma tese jurídica do cashback (§8.1, serviço prestado, não sorteio). Para a
tese se sustentar: nenhum elemento de sorte/aleatoriedade no ganho ou na conversão; regras e taxa de
conversão públicas e determinísticas; resgate é direito do Colaborador, não premiação. Badges/ranking
sem valor material continuam livres e fora deste PDR.

## Justificativa

O produto B2B paga por **cobertura e diversidade** do dado, não pelo valor de face dos cupons. Pontos
paramétricos alinham o incentivo do Colaborador ao valor do dado (north-star: cupons válidos, únicos e
novos/semana) e transformam a remuneração no próprio mecanismo de gamificação — o gancho de recorrência
que a Onda 3 persegue.

## Consequências

### Positivas
- Incentivo dirigível por categoria (CNAE), diversidade (itens únicos) e produto (bônus).
- Custo de aquisição do dado controlável por parâmetro, sem deploy.
- Acúmulo de pontos como mecânica central de engajamento.

### Negativas / trade-offs aceitos
- Complexidade nova: motor de regras, fila, enriquecimento CNPJ, tela de configuração.
- Dependência de API pública de terceiros para o CNAE (mitigada por fila + cache + regra de fallback).
- Migração do saldo legado exige comunicação clara — risco de percepção de perda se mal explicada.
- Taxa no resgate (não travada) transfere risco de variação ao Colaborador; exige transparência na UI.

### Para o time técnico
- ADRs que esta decisão demanda (Arquiteto, via spike): escolha da API pública RFB e contrato de dados;
  arquitetura de filas (consulta CNPJ e cálculo de pontos); estratégia de cache com TTL parametrizável;
  abstração do motor de regras de pontuação; modelo de razão (ledger) de pontos e migração do saldo.
- Impacto em épicos: EPIC-009 (enriquecimento CNPJ), EPIC-010 (motor de pontos), EPIC-011 (conversão,
  resgate e migração), EPIC-012 (configuração no Backoffice).
- Glossário do projeto (`docs/skills/_project.md` §4) e visão §8.1 ficam **superados neste ponto** por
  este PDR até a especificação consolidada registrar o novo vocabulário (Pontos, Taxa de conversão,
  Resgate, Mínimo de resgate).

## Sinais de revisão

- Se o custo por cupom válido sair da banda planejada por 2 semanas seguidas → recalibrar parâmetros.
- Se a taxa de resgate frustrar Colaboradores (reclamações/queda de envio pós-virada) → reavaliar
  travamento parcial da taxa.
- Se qualquer regra nova introduzir elemento aleatório no ganho/conversão → parar e reavaliar a
  premissa regulatória com jurídico antes de lançar.
- Se a API pública da RFB se mostrar instável a ponto de atrasar pontuação além de 24h → Arquiteto
  reavalia fonte de dados.
