---
epic_id: EPIC-011
slug: conversao-resgate-migracao
title: Conversão e resgate — N pontos valem X reais, com migração do saldo legado
wave: WAVE-2026-03
status: draft
owner_role: po
created_at: 2026-07-06
updated_at: 2026-07-06
target_completion: 2026-07-15
---

# EPIC-011 — Conversão, resgate e migração

## Por que existimos (problema do usuário)

Pontos sem caminho para o bolso são gamificação decorativa — e a promessa central ao Colaborador é ser
remunerado pelo serviço de coleta. Este épico fecha o circuito: **N pontos valem X reais**, o
Colaborador resgata quando quiser (acima de um mínimo), e o saldo em R$ acumulado no modelo antigo é
convertido em pontos para que exista **uma moeda só**.

## Resultado esperado (outcome)

Ao fim deste épico, o Colaborador resgata pontos manualmente (acima do mínimo configurado) pela taxa
vigente no momento do resgate, o valor cai no saldo em R$ da carteira (fluxo de saque existente da Onda
1), e o saldo legado de todos os Colaboradores foi migrado para pontos pela taxa inicial, como evento
auditável e visível no extrato.

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: fluxo resgate ponta a ponta (pontos → saldo R$ → saque PIX assistido) funcionando
  em homologação, E2E verde.
- Métrica de integridade: migração conserva valor — soma dos saldos legados em R$ = soma dos pontos
  migrados × taxa inicial, com conciliação registrada; nenhum ponto criado ou perdido fora do razão.
- Métrica de produto: extrato conta a história completa (crédito de pontos, migração, resgate, saque).

## Entregável visível no fim do épico

- [ ] Colaborador com pontos ≥ mínimo resgata e vê o saldo em R$ atualizado na carteira — em
      homologação.
- [ ] Colaborador com pontos < mínimo vê claramente quanto falta para poder resgatar.
- [ ] Taxa de conversão vigente e mínimo visíveis para o Colaborador antes de confirmar o resgate.
- [ ] Saldo legado convertido aparece no extrato como evento de migração datado e explicado.
- [ ] Crédito direto em R$ por cupom desligado após a migração — cupom novo só gera pontos.

## Fora de escopo (explicitamente)

- Mudanças no fluxo de saque PIX (ADR-005 permanece).
- Taxa travada no momento do ganho — decidido contra no PDR-004.
- Conversão automática ao atingir o mínimo — resgate é ato do Colaborador.
- Tela de administração da taxa/mínimo (EPIC-012).

## Referências da especificação

- `docs/project-state/decisions/pdr/PDR-004-pontuacao-gamificada-substitui-rate-fixo.md` — regras 1, 3
  e 4; premissa regulatória (resgate é direito, sem sorte).
- `docs/project-state/decisions/adr/ADR-005-escopo-saque-pix-assistido-kyc-carteira.md` — carteira e
  saque que o resgate alimenta.
- EPIC-010 — razão (ledger) de pontos.

## Dependências

- **Bloqueia:** EPIC-012 (taxa e mínimo na tela).
- **Bloqueado por:** EPIC-010 (pontos precisam existir).
- **Decisões arquiteturais necessárias:** transacionalidade resgate × carteira; procedimento de
  migração (corte, idempotência, conciliação) — se o spike STORY-039 não cobrir, abrir spike próprio na
  decomposição.

## Estórias

(Fluxo B ao abrir o épico.)

## Validação final

Critérios em `validation/checklist.md` (a criar na decomposição). **Definição de épico concluído:**
todas as estórias `done` + relatório do validador `approved` + demonstrável em homologação.

## Histórico

- 2026-07-06 — criado por PO (abertura da WAVE-2026-03, PDR-004/PDR-005)
