---
epic_id: EPIC-012
slug: configuracao-mecanismo-backoffice
title: Configuração do mecanismo de pontos no Backoffice — parâmetros com vigência e auditoria
wave: WAVE-2026-03
status: draft
owner_role: po
created_at: 2026-07-06
updated_at: 2026-07-06
target_completion: 2026-07-17
---

# EPIC-012 — Configuração do mecanismo (Backoffice)

## Por que existimos (problema do usuário)

A força do modelo de pontos (PDR-004) é ser **dirigível**: calibrar o incentivo por categoria, produto
e custo sem deploy. Enquanto os parâmetros viverem só como configuração semeada, calibrar exige
programador — e o Alexandro (operador do negócio) precisa ajustar a economia do sistema conforme o
custo/cupom e o comportamento de coleta reais.

## Resultado esperado (outcome)

Ao fim deste épico, um usuário do Backoffice administra em tela própria todos os parâmetros dos dois
mecanismos: **regras de pontuação** (pesos por CNAE, pontos por item único, faixas por valor do cupom,
lista de itens com pontos extras), **conversão** (taxa N pontos = X reais, mínimo de resgate) e
**operação** (TTL do cache de CNPJ). Toda mudança vale só dali pra frente e fica registrada (quem,
quando, valor anterior → novo).

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: mudança de parâmetro na tela reflete no próximo cupom processado/resgate efetuado,
  sem deploy e sem afetar pontos já creditados (verificado por E2E).
- Métrica de auditoria: 100% das mudanças com histórico consultável no Backoffice.
- Métrica de segurança: tela acessível apenas a papel autorizado (RBAC — ADR-009); tentativa sem papel
  é negada e registrada.

## Entregável visível no fim do épico

- [ ] Tela de configuração no Backoffice com os três grupos de parâmetros (pontuação, conversão,
      operação) — em homologação.
- [ ] Simulador simples na tela: dado um cupom exemplo (valor, nº de itens, CNAE), quanto pontuaria com
      os parâmetros em edição — antes de salvar.
- [ ] Histórico de mudanças visível (quem, quando, o quê).
- [ ] Guarda-corpos de sanidade: valores impossíveis rejeitados (taxa zero/negativa, mínimo negativo,
      TTL abaixo do piso definido na spec da estória).

## Fora de escopo (explicitamente)

- Reprocessamento retroativo de pontos (decidido contra no PDR-004, regra 4).
- Agendamento de vigência futura de parâmetros ("valer a partir de...") — mudança vale ao salvar.
- Relatórios analíticos de custo/cupom (candidato para a onda do piloto).

## Referências da especificação

- `docs/project-state/decisions/pdr/PDR-004-pontuacao-gamificada-substitui-rate-fixo.md` — regras 4 e 5.
- `docs/project-state/decisions/adr/ADR-009-autorizacao-por-papeis-rbac.md` — papéis do Backoffice.
- `docs/especificacao/design-system.md` e `docs/DESIGN-wise.md` — padrão visual.
- EPIC-009/010/011 — os parâmetros que esta tela expõe.

## Dependências

- **Bloqueia:** nada (fecha a onda).
- **Bloqueado por:** EPIC-009, EPIC-010 e EPIC-011 (os parâmetros precisam existir e estar em uso).
- **Decisões arquiteturais necessárias:** modelo de versionamento de configuração (o spike STORY-039
  deve indicar o caminho; se não, spike na decomposição).

## Estórias

(Fluxo B ao abrir o épico. `requires_design: true` provável — tela nova no Backoffice.)

## Validação final

Critérios em `validation/checklist.md` (a criar na decomposição). **Definição de épico concluído:**
todas as estórias `done` + relatório do validador `approved` + demonstrável em homologação.

## Histórico

- 2026-07-06 — criado por PO (abertura da WAVE-2026-03, PDR-004/PDR-005)
