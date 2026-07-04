---
report_date: 2026-07-04
sprint_id: null
wave: WAVE-2026-01
audience: humano-stakeholder
type: wave-close
---

# Fechamento da WAVE-2026-01 — Provar a coleta em SP (com incentivo)

## TL;DR (3 linhas)

- **A Onda 1 fechou com o objetivo atingido:** o loop **cupom → saldo** está vivo em homologação —
  coleta do QR da NFC-e, validação/deduplicação/persistência, cashback de 0,1% e carteira com histórico
  e saque assistido.
- **4/4 épicos `done` e validados** (EPIC-000/001/003 `approved`; EPIC-002 `approved_with_findings`,
  0 bloqueantes). Da abertura (02/07) ao fechamento (04/07): onda curta e densa, entregue por agentes.
- **O que falta virar próximo foco:** a plataforma ainda tem **cara de POC** — sem porta de entrada
  (landing pages), login fora do padrão visual, sem segmentação de públicos e sem fluxo B2C completo
  pós-login. É o tema da próxima onda.

## Objetivo da onda × resultado

| | Definido na abertura (02/07) | Resultado (04/07) |
|---|---|---|
| **Objetivo** | Loop Colaborador escaneia QR → cupom validado/deduplicado/persistido → cashback creditado, em homologação | **Atingido.** Tudo vivo em homologação sobre o sha `9c77cbc`. |
| **Hipótese** | Fricção mínima + recompensa geram volume de cupons para uma base densa | **Loop e instrumento de medição existem**; a hipótese de *volume* ainda não foi testada com usuários reais (piloto não rodou). |
| **North-star** | Cupons NFC-e válidos, únicos e novos/semana | **Instrumentada** (STORY-012); baseline ainda a coletar em piloto. |

## Épicos entregues

- **EPIC-000 — Foundation** — ambiente 1-comando, CI/CD verde, deploy automático de homologação, tokens do DS vivos. `approved`.
- **EPIC-001 — Design System em código** — biblioteca de componentes React + vitrine `/ds`. `approved`.
- **EPIC-002 — Coleta de cupom** — captura do QR, validação SEFAZ-SP, deduplicação por chave, persistência canônica, anonimização de CPF, north-star instrumentada. `approved_with_findings` (F-NB-1 resolvido, F-NB-2 waived via DDR-003).
- **EPIC-003 — Carteira e cashback** — cashback 0,1% idempotente, carteira mobile com histórico, saque PIX assistido atrás do papel `operador`. `approved`.

## O que aprendemos (retrospectiva curta)

- **Throughput dos agentes é alto e comprime o calendário.** A onda inteira coube em ~3 dias; sprint
  nomeado (W27) só fez sentido no EPIC-001. Sinal para **colapsar onda/sprint** enquanto o time é
  pequeno (coerente com `agile-workflow.md` §hierarquia colapsável).
- **A qualidade se sustentou** com TDD + E2E real: núcleos de negócio a 100%, cobertura geral 94,3%,
  Dusk verde em browser real. O gate de cobertura de núcleo (98%) provou-se um bom guarda-corpo.
- **Dívida consciente carregada** (não bloqueou a onda, vira candidato da próxima): **sem scanner de
  segredos/deps no CI** (gitleaks/audit), **sem dashboard de observabilidade RED**, cobertura de *glue*
  de models baixa, walkthrough autenticado em homologação não exercido manualmente.
- **A plataforma provou o mecanismo, não o produto.** Falta a camada que transforma o POV técnico em
  algo apresentável: entrada (landing), identidade visual no login, separação de públicos e um fluxo
  B2C que não termine numa página genérica. É exatamente o gap que a próxima onda ataca.

## Baseline da north-star

Ainda **não há baseline numérico** — não rodou piloto com usuários reais. O instrumento existe
(painel north-star, STORY-012). A leitura de baseline fica condicionada a um piloto, que depende de a
plataforma ter porta de entrada e fluxo B2C apresentável (próxima onda).

## Decisões da onda (registradas)

- **PDR:** PDR-001 (DS Wise-derived), PDR-002 (escopo da Onda 1).
- **ADR:** 000 (stack), 001–003 (ingestão/extração/dedup), 005 (saque PIX assistido), 006 (LGPD/CPF),
  007 (infra VPS), 008 (Dusk E2E), 009 (RBAC).
- **IDR:** 001–009. **DDR:** 001 (Inter), 002 (diferenciação de marca), 003 (exceção de design STORY-012).

## Próximo passo

Abrir a próxima onda (Fluxo A) com foco em **dar cara de produto**: landing pages B2C e B2B, login no
padrão visual, segmentação em 3 áreas (B2C / B2B / Backoffice), fluxo B2C completo pós-login
(mobile-first) com login Google, e localização pt-BR como requisito transversal. Escopo e sequência
serão fixados no PDR de abertura e em `roadmap/current-wave.md`.

## Apêndice — links rápidos

- Índice do projeto: `docs/project-state/index.json`
- Abertura da onda: `reports/status-2026-07-02.md`
- Fechamento do EPIC-003: `reports/status-2026-07-04-epic-003-done.md`
