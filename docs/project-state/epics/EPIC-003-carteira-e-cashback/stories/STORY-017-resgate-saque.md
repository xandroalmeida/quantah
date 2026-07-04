---
story_id: STORY-017
slug: resgate-saque
title: Resgate/saque do saldo (escopo conforme ADR-005)
epic_id: EPIC-003
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: in_review
owner_agent: claude-story017
design_screen_id: SCREEN-STORY-017-solicitar-saque
created_at: 2026-07-03
updated_at: 2026-07-03
estimated_session_size: M
---

# STORY-017 — Resgate/saque do saldo

> **Para o agente que vai executar:** o **escopo depende do ADR-005** (STORY-014). No MVP pode ser
> **PIX assistido/manual**, não necessariamente saque automatizado com KYC completo.

## Contexto (por que esta estória existe)

O loop só fecha se houver um **caminho para o dinheiro sair**. A visão admite resgate simplificado
no MVP (`docs/visao.md` §5.1). O contorno exato (automatizado x assistido, KYC mínimo) vem do
ADR-005. Sem resgate, o saldo é número na tela sem promessa cumprida.

## Critérios de aceite (alto nível — refinar após ADR-005)

- Caminho de resgate existente em homologação, no escopo decidido pelo ADR-005.
- Débito do saldo **idempotente e reconciliável** (extrato bate com carteira; sem duplo saque).
- Estados de erro/pendência tratados (ex.: saque em processamento, falha de PIX).
- Se automatizado: KYC mínimo do ADR-005 aplicado. Se assistido: fluxo operacional documentado.

## Design (requires_design)

Tela/fluxo do Colaborador → exige artefato de Designer (mesmo critério da STORY-016). A profundidade
depende do escopo do ADR-005 (assistido pode ser fluxo enxuto).

## Fora de escopo

- KYC completo e automação total, se o ADR-005 empurrar para a Onda 2.

## Dependências

- **Bloqueado por:** STORY-014 (ADR-005 define o escopo), STORY-015 (saldo a movimentar).
- **Bloqueia:** STORY-018.

## Definição de pronto

Resgate no escopo do ADR-005 demonstrável em homologação; débito reconciliável; artefato de
Designer presente; testes verdes; `index.json` = `done`; Notas do agente preenchidas.

## Notas do agente

### 2026-07-03 — Backend pronto; design aguardando validação (claude-story017)

**Decisões do dono (Alexandro):** RBAC completo (papéis) → **ADR-009**; **mínimo de saque R$ 5,00**;
ambas as telas com **design completo**.

**Backend entregue e coberto (núcleo 100%), em TDD:**
- **ADR-009** (RBAC): `roles` + `role_user` + `User::hasRole` + Gate `operar-saques` + seed do papel
  `operador` (atribuído ao usuário de dev). `GateOperadorTest` verde.
- **Cpf** (VO): validação de DV mod 11 + normalização, sem dependência externa. `CpfTest` verde (100%).
- **Migrations:** `saques` (status CHECK, valor>0, cpf/chave_pix, comprovante, processado_por) + `saque_id`
  no ledger. Reversíveis.
- **`SolicitarSaqueService`** (reserva): mínimo R$5, KYC (CPF válido = chave PIX), **saldo sob lock**,
  débito atômico (`debito_saque`), reconciliável. 100% coberto.
- **`SaqueService`** (máquina de estados): assumir→em_analise, aprovar, pagar (comprovante), rejeitar→
  **estorno**; transições guardadas (idempotência: não estorna duas vezes). 100% coberto.
- Suíte do domínio: 27 testes verdes (Cpf 7 + Gate 3 + solicitar 9 + operar 8).

**Design produzido (aguardando validação humana — checkpoint obrigatório):**
- `design/screens/STORY-017-solicitar-saque/` (Colaborador): valor + **campo único CPF** (=chave PIX);
  estados form/erro/sucesso/sem-saldo.
- `design/screens/STORY-017-backoffice-saques/` (operador, atrás do Gate): lista/detalhe + ações da
  máquina de estados + comprovante; CPF mascarado.

**Pausado antes da UI** de propósito: não implemento página React antes do sinal do Designer/dono
(evita retrabalho — é o motivo do checkpoint). Faltam: fluxo do Colaborador (botão Sacar na /carteira +
form), backoffice (rotas atrás de `can:operar-saques` + páginas), E2E, fechamento e deploy.

### 2026-07-04 — UI implementada (Alexandro liberou com "continue") → in_review

**Design validado** por Alexandro ("continue"); ambas as telas `ready` (`prototype_last_validated_at:
2026-07-04`). **Decisão de UX** (fiel ao ADR-005): a chave PIX é do tipo CPF, então a tela do Colaborador
usa **um único campo CPF** (=chave PIX) — o controller usa esse valor como CPF e como chave.

**UI entregue (TDD + E2E):**
- **Colaborador:** botão "Sacar" na /carteira → `/carteira/saque` (form: valor + CPF), `SolicitarSaqueRequest`
  (normaliza valor/cpf), `SaqueController`, página `Saque/Solicitar`. Sucesso → volta à carteira com snackbar
  (flash) e saldo reservado.
- **Backoffice:** `/backoffice/saques` atrás de `auth` + `can:operar-saques` (ADR-009). `Backoffice\SaquesController`
  (lista com filtro + detalhe + ações), páginas `Backoffice/Saques/{Index,Detalhe}`. CPF **mascarado** na lista
  (`Cpf::mascarar`), completo no detalhe (KYC). Rejeitar estorna.
- **IDR-009:** validação de CPF própria (VO `Cpf`), sem pacote — divergência do default da stack registrada.

**Cobertura:** núcleo do dinheiro/estados **100%** (`SolicitarSaqueService`, `SaqueService`, `Cpf`);
camada HTTP (`SaqueController`, `SaquesController`, `SolicitarSaqueRequest`) **100%**. Suíte completa:
**237 PHPUnit + 52 Dusk** verdes.

**Mapa CA → teste:**
- **CA — caminho de resgate no escopo do ADR-005 (PIX assistido):** `SolicitarSaqueServiceTest` (reserva),
  `OperarSaqueServiceTest` (máquina de estados), `SolicitarSaqueHttpTest` (Colaborador), `BackofficeSaquesHttpTest`
  (operador). **E2E:** `SolicitarSaqueTest` (solicitar) + `BackofficeSaquesTest` (conduzir até pago).
- **CA — débito idempotente e reconciliável (sem duplo saque):** `SolicitarSaqueServiceTest::test_reconciliacao_*`,
  `test_rejeita_saldo_insuficiente`, `test_aceita_o_valor_minimo_exato`; `OperarSaqueServiceTest::test_rejeitar_duas_vezes_nao_estorna_de_novo`,
  `test_reconciliacao_apos_estorno`, `test_nao_paga_pulando_a_analise` (transições guardadas). Saldo≥0 = CHECK no banco.
- **CA — estados de erro/pendência:** `SolicitarSaqueHttpTest` (abaixo do mínimo, acima do saldo, CPF inválido);
  `BackofficeSaquesHttpTest::test_transicao_invalida_volta_com_erro`, `test_pagar_exige_comprovante`. **E2E:**
  `SolicitarSaqueTest` (erro por campo), `BackofficeSaquesTest::test_nao_operador_e_barrado` (403).
- **CA — KYC mínimo (assistido):** `CpfTest` (DV), `SolicitarSaqueServiceTest::test_rejeita_chave_pix_que_nao_confere_com_o_cpf`
  (titularidade = chave PIX tipo CPF); operação manual documentada no ADR-005 (backoffice).
- **Autorização (ADR-009):** `GateOperadorTest`, `BackofficeSaquesHttpTest::test_nao_operador_recebe_403`/`test_nao_operador_nao_executa_acao`.

**TDD evidenciado:** commits `test(STORY-017): … (vermelho)` precedem `feat(STORY-017): … (verde)`.

**Como testar (dev):** `make fresh` (semeia `test@example.com` **como operador**). Faça login →
credite saldo (ver STORY-016) → `/carteira` → **Sacar** → solicite. Depois `/backoffice/saques` → assuma →
aprove → informe comprovante → **Marcar pago**. Rejeitar estorna o saldo.
