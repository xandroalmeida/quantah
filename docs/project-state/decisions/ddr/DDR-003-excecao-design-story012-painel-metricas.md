---
id: DDR-003
title: Exceção de design — STORY-012 (painel de métricas) dispensa artefato de Designer
status: accepted
created_at: 2026-07-03
decided_at: 2026-07-03
approved_by: Alexandro
supersedes: ~
superseded_by: ~
related_ddrs: [DDR-001, DDR-002]
related_adrs: [ADR-000]
related_pdrs: [PDR-001]
related_stories: [STORY-012]
scope: local
affects_screens: [SCREEN-STORY-012-painel-metricas]
---

# DDR-003 — Exceção de design para a STORY-012 (painel interno de métricas)

## Contexto

A validação do EPIC-002 (STORY-013) registrou como fato não-bloqueante a **F-NB-2**: a STORY-012
está marcada `requires_design: true` mas foi entregue **sem artefato de Designer**
(`design_screen_id: null`, sem tela em `design/screens/`). O painel `/interno/metricas` foi
composto pelo Programador a partir de componentes já existentes do Design System (EPIC-001), sem
uma spec de tela produzida pelo Designer.

A própria STORY-012 já sinalizava a natureza leve do requisito de design: *"O painel é interno
(`requires_design: true` leve — compõe DS existente). O foco é a métrica correta, não uma tela
sofisticada."*

Este DDR formaliza a **exceção**: decide que, para esta estória, não é necessário produzir um
artefato de Designer, e explica por quê — encerrando a F-NB-2 como decisão consciente do PO, não
como dívida pendente.

Documentos lidos: relatório de validação do EPIC-002 (`validation/report.md`, §F-NB-2), STORY-012
(frontmatter + Notas do agente), PDR-001 (adoção do DS).

## Forças (drivers)

- **Natureza do artefato** (alta): painel **interno**, atrás de login, para leitura de uma métrica —
  não é superfície do Colaborador nem carrega marca. Compõe componentes/tokens já aprovados do DS.
- **Custo/velocidade no MVP** (média): produzir spec de Designer para uma tela interna de leitura
  não muda o resultado (a métrica correta) e atrasa sem ganho observável.
- **Rastreabilidade do processo** (média): deixar `requires_design: true` sem artefato e sem
  decisão explícita gera ruído de auditoria (foi o que a F-NB-2 apontou). A exceção precisa ficar
  registrada, não implícita.
- **Consistência visual** (baixa): por compor apenas o DS existente, o risco de inconsistência é
  baixo mesmo sem spec dedicada.

## Opções consideradas

### Opção A — Formalizar a exceção (recomendada)
Registrar que a STORY-012 dispensa artefato de Designer por ser painel interno composto do DS
existente; manter a tela como está e encerrar a F-NB-2 como decisão do PO.
- **Prós:** honra o trabalho já entregue e verificado; custo zero; deixa a decisão auditável;
  não bloqueia o fechamento do épico.
- **Contras:** abre precedente — mitigado pelo critério de escopo abaixo (só telas internas de
  leitura que compõem o DS).

### Opção B — Produzir o artefato de Designer retroativamente
Abrir tarefa para o Designer especificar a tela do painel a posteriori.
- **Prós:** fecha o checklist `requires_design` pela via literal.
- **Contras:** esforço sem mudança de resultado; a tela já existe e foi validada; atrasa o
  encerramento do épico por conformidade de processo, não por valor.

### Status quo — deixar `requires_design: true` sem decisão
- **Contras:** é exatamente o estado que a F-NB-2 sinalizou; ambíguo em auditoria futura.

## Decisão

> **Adotada:** Opção A — a STORY-012 fica **dispensada** de artefato de Designer. O painel
> `/interno/metricas` é aceito como composição do DS existente feita pelo Programador. A F-NB-2 é
> encerrada como **exceção consciente do PO** (`waived`), não como dívida.

Para preservar a rastreabilidade, a STORY-012 passa a apontar este DDR como `design_waiver` e o
frontmatter deixa de exigir artefato via `design_screen_id`.

## Consequências

### Positivas
- Encerra a única pendência aberta do EPIC-002 sem retrabalho; épico pode fechar limpo.
- Cria um precedente **registrado e delimitado** para telas internas de leitura.

### Negativas / trade-offs assumidos
- Uma estória `requires_design: true` fica sem artefato de Designer. Aceito pelo escopo restrito
  (interno, leitura, compõe DS) e documentado aqui.

### Impacto no processo
- Recomendação para o futuro: telas **internas de leitura** que apenas compõem o DS podem nascer
  como `requires_design: false` (design leve), evitando a ambiguidade que gerou a F-NB-2.

## Critérios de escopo (para não virar precedente amplo)

Esta exceção vale **apenas** para telas que satisfaçam **todas** as condições: (1) internas / atrás
de autenticação; (2) de leitura, sem fluxo de entrada de dados do usuário final; (3) compostas
exclusivamente de componentes/tokens já aprovados do DS. Qualquer tela do Colaborador, com fluxo
de entrada, ou que introduza padrão visual novo, **exige** artefato de Designer normalmente.

## Aprovação humana

| Campo | Valor |
|---|---|
| Apresentado em | 2026-07-03 |
| Aprovado por | Alexandro |
| Data da aprovação | 2026-07-03 |
| Observações do aprovador | Exceção aprovada em sessão de Cowork (papel PO): painel interno de leitura, compõe DS existente; sem retrabalho de Designer. Encerra F-NB-2. |

> Sem este bloco preenchido, o DDR não pode ir para `accepted`.
