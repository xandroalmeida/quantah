---
epic_id: EPIC-006
slug: jornada-b2c
title: Jornada do Coletador — home-hub e fluxo completo pós-login
wave: WAVE-2026-02
status: ready
owner_role: po
created_at: 2026-07-04
updated_at: 2026-07-05
target_completion: 2026-08-15
---

# EPIC-006 — Jornada do Coletador (B2C)

## Por que existimos (problema do usuário)

Depois de logar, o Coletador cai numa **página genérica** — não há um lugar que reúna o que ele veio
fazer (coletar cupom), o que ele ganhou (saldo) e o que ele pode fazer com isso (histórico, prêmios,
saque). As peças existem da Onda 1 (coleta, carteira, saque), mas soltas: falta a **costura** que
transforma funcionalidades isoladas numa jornada de produto. Sem isso, o Colaborador não tem clareza do
ciclo "coletei → ganhei → resgato", e a recorrência (que alimenta a north-star) fica frágil.

## Resultado esperado (outcome)

Ao fim deste épico, o **Coletador logado chega a uma home-hub mobile** que costura a jornada — coletar
cupom, ver o saldo crescer, consultar histórico e resgatar — navegando com clareza entre coleta e
carteira, sem passar por nenhuma página genérica.

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: a partir da home-hub, o Coletador inicia uma coleta e consulta o saldo/histórico em
  ≤ 2 toques cada, em homologação, num fluxo contínuo (coletar → ver saldo atualizado → abrir extrato →
  iniciar saque).
- Métrica de qualidade: jornada coberta por E2E em browser real (mobile) ligando as telas da Onda 1;
  100% pt-BR; a11y AA; sem página genérica remanescente pós-login.

## Entregável visível no fim do épico

- [ ] **Home-hub do Coletador** (mobile-first, DS, pt-BR) como destino pós-login — destaque para
      **coletar cupom**, **saldo da carteira**, atalho de **histórico** e **prêmios/saque**.
- [ ] **Navegação coesa** entre coleta ↔ carteira ↔ saque (reusando EPIC-002/003), sem a página genérica.
- [ ] Fluxo ponta a ponta demonstrável em homologação: entrar → home-hub → coletar → saldo atualiza →
      extrato → iniciar saque.

## Fora de escopo (explicitamente)

- Gamificação (pontos/ranking/níveis) — Onda futura (visão §8.2).
- "Você pagou mais caro/barato que a média" e outros ganchos de valor de dado — Onda futura.
- Notificações push / e-mail de engajamento.
- Novas regras de cashback ou de saque (reusa as da Onda 1 como estão).

## Referências da especificação

- `docs/visao.md` §3.1 (princípios de produto), §5.1 (MVP coleta e incentivo), §4.1 (personas de coleta).
- `docs/project-state/decisions/pdr/PDR-003-escopo-onda-2-de-poc-a-produto.md`.
- Telas da Onda 1: `design/screens/SCREEN-STORY-016-carteira-saldo-historico`,
  `SCREEN-STORY-009-captura-qr-confirmacao`, `SCREEN-STORY-017-solicitar-saque`.
- `docs/skills/po/references/quality-standards.md` §1 (testes/E2E), §5 (a11y), **§5.1 (pt-BR)**.

## Dependências

- **Bloqueia:** nada (é a entrega final de valor B2C da onda).
- **Bloqueado por:** EPIC-004 (Coletador autenticado e áreas). Reusa EPIC-002 (coleta) e EPIC-003
  (carteira/saque) já entregues. Pode correr em paralelo ao EPIC-005 depois que o EPIC-004 fechar.
- **Decisões arquiteturais necessárias:** nenhuma nova prevista (composição de telas existentes sobre a
  área B2C autenticada do EPIC-004). Requer artefato do Designer para a home-hub (`requires_design`).

## Estórias

Decompostas no Fluxo B (2026-07-05). Estórias verticalmente fatiadas, mapeando os três entregáveis do
épico; a última é a validação. Nenhum ADR novo previsto (composição de telas existentes); UI nova entra
pelo modelo paralelo (`requires_design`, PDR-002) com o brief em `design-handoff.md`.

- [ ] **STORY-029** — home-hub do Coletador como destino pós-login: saldo da carteira (EPIC-003) + CTA
      destacado de coleta (EPIC-002), mobile-first, pt-BR (`requires_design`). Bloqueia 030, 031, 032.
- [ ] **STORY-030** — navegação coesa da área B2C: atalhos da home para extrato/histórico e prêmios/saque
      (EPIC-003) e coleta, retorno consistente, **≤ 2 toques**, sem página genérica (`requires_design`).
      Bloqueada por 029. Bloqueia 031, 032.
- [ ] **STORY-031** — jornada contínua ponta a ponta: saldo reflete a coleta recém-feita e loop
      coletar → saldo → extrato → iniciar saque, com E2E mobile em browser real. Bloqueada por 029 e 030.
      Bloqueia 032.
- [ ] **STORY-032** (validação) — validação final do épico (`target_role: validador`). Bloqueada por
      029–031. `draft` até as estórias de dependência avançarem.

### Ordem sugerida e paralelismo

STORY-029 primeiro (base da jornada). STORY-030 depende dela (navega a partir da home). STORY-031 costura o
loop e só faz sentido com 029 e 030 prontas. STORY-032 fecha o épico. Designer e Programador pegam 029 e 030
juntos (modelo paralelo); 031 não tem tela nova.

## Validação final

Critérios em `validation/checklist.md`. Relatório do validador em `validation/report.md`.

**Definição de épico concluído:** todas as estórias `done` + validação `approved` + Coletador logado
chega à home-hub e percorre coletar → saldo → extrato → saque em homologação, sem página genérica.

## Histórico

- 2026-07-04 — criado por PO (Fluxo A, WAVE-2026-02). Status `draft` — decompor após EPIC-004.
- 2026-07-05 — **decomposto (Fluxo B)** por PO, após EPIC-004 e EPIC-005 `done`. 4 estórias criadas:
  STORY-029 (home-hub), STORY-030 (navegação coesa), STORY-031 (jornada contínua ponta a ponta),
  STORY-032 (validação). Três estórias de implementação mapeando os três entregáveis do épico (a fatia da
  jornada contínua ganhou estória própria por ser a prova da métrica primária) + a validação. Épico →
  `ready`. Brief de design em `design-handoff.md` (STORY-029/030 são `requires_design`). Sem ADR novo.
