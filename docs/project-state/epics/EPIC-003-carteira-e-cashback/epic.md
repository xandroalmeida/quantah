---
epic_id: EPIC-003
slug: carteira-e-cashback
title: Carteira e cashback — a recompensa que fecha o loop
wave: WAVE-2026-01
status: ready
owner_role: po
created_at: 2026-07-02
updated_at: 2026-07-03
target_completion: 2026-10-15
---

# EPIC-003 — Carteira e cashback

## Por que existimos (problema do usuário)

O Colaborador só envia cupons se receber algo de volta. O cashback (R$ 1 a cada R$ 1.000 em
cupons válidos) é o incentivo que torna a coleta racional — e é o que a **hipótese da north-star**
precisa para ser testada de verdade. Sem carteira e crédito visível, a coleta do EPIC-002 não
tem motor. Este épico fecha o loop: enviar cupom vira saldo.

## Resultado esperado (outcome)

Ao fim deste épico, o **Colaborador vê seu saldo em reais crescer** a cada cupom válido enviado
(0,1% do valor), consulta o histórico de cupons e créditos, e tem um caminho para resgatar o
saldo — tudo em homologação.

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: saldo creditado corretamente (R$ 1 / R$ 1.000 sobre cupons válidos) e visível.
- Métrica de apoio: nº de Colaboradores com saldo > 0 no piloto.
- Métrica de qualidade: **98% de cobertura nas regras de cálculo de cashback** (núcleo/regra de
  negócio, conforme `quality-standards.md`); reconciliação saldo × cupons sem divergência.

## Entregável visível no fim do épico

- [ ] Carteira com saldo em reais e histórico de cupons/créditos, em homologação, mobile.
- [ ] Crédito automático de 0,1% sobre o valor dos cupons válidos enviados (EPIC-002).
- [ ] Caminho de resgate/saque (pode ser simplificado no MVP — ver Fora de escopo e ADR-005).

## Fora de escopo (explicitamente)

- Gamificação (pontos/ranking) — Onda 2.
- Sorteio (gate desligado) — fora desta onda.
- Saque totalmente automatizado com KYC completo, se o ADR-005 indicar que é caro para a onda:
  neste caso, entra **resgate simplificado** (ex.: PIX manual/assistido) e a automação vai para
  a Onda 2. O essencial da onda é o **crédito de saldo**, não o saque automatizado.

## Referências da especificação

- `docs/visao.md` §5.1 (carteira e cashback), §8.1 (cashback é remuneração livre, sem gate).
- `docs/especificacao/` — a consolidar (regra de cálculo, domínio da carteira).
- ADR-005 (pagamento/PIX + KYC de saque) — a criar via spike.

## Dependências

- **Bloqueia:** nada nesta onda (é o último épico).
- **Bloqueado por:** EPIC-002 (o crédito incide sobre o cupom válido) e EPIC-001 (componentes).
- **Decisões arquiteturais necessárias:** ADR-005 — integração de pagamento/PIX e KYC mínimo de
  saque (abrir spike no início do épico; escopo do saque depende do resultado).

## Estórias

Decomposto em 2026-07-03 (PO, Fluxo B). Começa pelo **spike** (STORY-014) que produz o ADR-005 e
define o escopo do saque — só então a estória de resgate ganha contorno. O crédito de cashback
(STORY-015) não depende do spike (incide sobre o cupom válido do EPIC-002) e é a fundação do saldo.

- [x] **STORY-014** (spike) — pagamento/PIX + escopo de saque → **ADR-005** (`accepted`) ·
      `done` · destravou STORY-017 (saque = PIX assistido no MVP; automação via PSP na Onda 2).
- [ ] **STORY-015** — cálculo e crédito automático de cashback (0,1% sobre cupom válido) ·
      `in_review` · fundação do saldo entregue: carteira + ledger append-only + crédito
      idempotente (evento `CupomValidado`→listener), coleta autenticada com atribuição ao
      Colaborador (IDR-008). Núcleo 100% coberto.
- [x] **STORY-016** — tela de carteira: saldo em reais + histórico de cupons/créditos, mobile
      (`requires_design`) · `done` · spec+protótipo validados por Alexandro; saldo (card de marca) +
      histórico + estados, nav bottom/top; read-model 100% coberto; deploy homolog verde (run 28690992037).
- [x] **STORY-017** — resgate/saque do saldo (PIX assistido, ADR-005) · `done` · reserva sob lock +
      máquina de estados (assumir→aprovar→pagar; rejeitar→estorno) + backoffice atrás do papel `operador`
      (ADR-009/IDR-009). Núcleo 100%; deploy homolog verde (run 28704231515).
- [ ] **STORY-018** (validação) — validação final do épico · `draft` · bloqueada por STORY-014..017.

## Validação final

Critérios em `validation/checklist.md`. Relatório em `validation/report.md`.

**Definição de épico concluído:** saldo credita corretamente sobre cupons válidos e é visível em
homologação; regra de cashback com 98% de cobertura; caminho de resgate existe; validação
`approved`.

## Histórico

- 2026-07-02 — criado por PO (Fluxo A, WAVE-2026-01).
- 2026-07-03 — EPIC-002 concluído; épico iniciado e **decomposto em estórias** (STORY-014..018,
  Fluxo B). Status `draft → ready`. Próximo: executar STORY-014 (spike de pagamento/PIX).
