---
report_date: 2026-07-04
sprint_id: null
wave: WAVE-2026-01
audience: humano-stakeholder
---

# Status Quantah — 2026-07-04 (fechamento do EPIC-003)

## TL;DR (3 linhas)

- **Onde estamos:** EPIC-003 (Carteira e cashback) **concluído e validado** (`approved`). Com ele, os
  **4 épicos da Onda 1 estão `done`** — o loop cupom → saldo está fechado em homologação.
- **O que mudou:** o cashback credita 0,1% sobre o cupom válido, o saldo em reais é visível na carteira
  mobile com histórico, e existe caminho de saque (PIX assistido, ADR-005) atrás do papel `operador`.
- **Próxima entrega visível / decisão sua:** a onda atingiu o objetivo — **fechar a WAVE-2026-01 e
  planejar a próxima** é a próxima decisão de produto (ver "Decisões aguardando você").

## Onda atual

- **Onda:** WAVE-2026-01 — provar a coleta em SP (com incentivo).
- **Progresso da onda:** **4 de 4 épicos concluídos** (EPIC-000, EPIC-001, EPIC-002, EPIC-003). Todas
  as estórias da onda (STORY-000..018) estão `done`. **Objetivo da onda atingido**: cupom escaneado →
  validado/deduplicado/persistido → cashback creditado, tudo em homologação.

## Épicos

### Concluídos nesta onda
- **EPIC-000 — Foundation** — 2026-07-02 — validação `approved`.
- **EPIC-001 — Design System em código** — 2026-07-02 — validação `approved`.
- **EPIC-002 — Coleta de cupom** — 2026-07-03 — validação `approved_with_findings` (0 bloqueantes; F-NB-1
  resolvido, F-NB-2 waived via DDR-003).
- **EPIC-003 — Carteira e cashback** — **2026-07-04** — validação **`approved`** (sha `9c77cbc`):
  `epics/EPIC-003-carteira-e-cashback/validation/report.md`.

### Próximos
- Nenhum épico aberto. A definição da próxima onda depende do fechamento da WAVE-2026-01 (decisão do PO).

## Sprint corrente

- **Sprint:** nenhum aberto. O último formal (SPRINT-2026-W27) foi fechado em 02/07; EPIC-002 e EPIC-003
  correram fora de sprint nomeado, por throughput dos agentes.

## O que o usuário pode ver agora em homologação

Base: `https://quantah-homolog.34.39.229.117.sslip.io` (sha `9c77cbc`; `/up` HTTP 200).

- ✅ Vitrine do Design System (`/ds`, `/ds/buttons`, `/ds/inputs`).
- ✅ Coleta de cupom — captura do QR da NFC-e, validação SEFAZ-SP, deduplicação por chave, persistência
  canônica e anonimização de CPF (EPIC-002).
- ✅ **Carteira (`/carteira`, mobile, atrás de auth)** — saldo em reais + histórico de cupons/créditos.
- ✅ **Cashback automático** — cupom válido credita 0,1% do valor, idempotente por cupom, ledger
  append-only, reconciliação saldo × ledger por construção.
- ✅ **Saque por PIX assistido** — solicitação pelo Colaborador + backoffice (`/backoffice/saques`) atrás
  do papel `operador` (não-operador recebe 403).

## Qualidade

- Suíte: **237 testes PHPUnit / 955 asserções**, verdes no sha deployado `9c77cbc`.
- Cobertura geral do código novo: **94,3%** (meta 80%).
- Cobertura no núcleo de cálculo de cashback e no núcleo do dinheiro do saque: **100%** (gate do épico: 98%).
- E2E: **9 cenários Dusk** (carteira/saque/backoffice) verdes em browser real; suíte Dusk completa verde
  no CI run **28704414576**.
- Pipeline: **verde** (build + E2E + deploy homologação automático).

## Decisões registradas no período

- ADR-005 (escopo do saque — PIX assistido, KYC mínimo, modelo da carteira), ADR-009 (RBAC do backoffice).
- IDR-008 (crédito via evento `CupomValidado` + listener enfileirado), IDR-009 (validação de CPF por VO próprio).

## Bloqueios e riscos abertos

- Sem bloqueios ativos. Ressalvas não-bloqueantes do relatório de validação, para carregar como dívida
  consciente (não travam a onda): cobertura de *glue* de models < 80% (fora do núcleo); CI **sem scanner
  dedicado de segredos/dependências** (gitleaks/audit) — verificado por inspeção; dashboard de métricas RED
  (p50/p95/p99) não verificado de 1ª mão; walkthrough autenticado do crédito na UI de homologação não
  executado (coberto por E2E/feature sobre o mesmo sha).

## Olhando à frente

### Próximos 7–14 dias
- **Fechar a WAVE-2026-01** com um snapshot de aprendizado (comparar com o status de abertura de 02/07):
  a hipótese da north-star agora tem motor — coleta + incentivo vivos em homologação.
- **Planejar a próxima onda** (roadmap `next-wave.md`): candidatos naturais são medir a north-star no
  piloto, endereçar as ressalvas de segurança/observabilidade (scanner no CI, dashboard RED) e o caminho
  de produção com gate humano.

### Decisões aguardando você (Alexandro)
- **Fechar a WAVE-2026-01?** — todos os 4 épicos estão `done` e o objetivo da onda foi atingido. Fechar a
  onda (`active → closed`) é decisão de produto e destrava o planejamento da próxima. *Impacto se não
  decidir:* a onda fica `active` sem trabalho aberto e o roadmap seguinte não avança.
- **Escopo da próxima onda** — piloto/medição vs. endurecer segurança/observabilidade vs. caminho de
  produção. Sem urgência técnica, mas define o próximo foco.

### Riscos abertos
- **Risco:** ausência de scanner de segredos/deps no CI pode deixar passar credencial ou dependência
  vulnerável à medida que o time cresce. **Probabilidade:** média. **Impacto:** alto. **Mitigação:**
  adicionar gitleaks + `composer/npm audit` ao pipeline (candidato à próxima onda). **Owner:** PO/Arquiteto.
- **Risco:** sem observabilidade RED, uma regressão de latência/erro em homologação passa despercebida.
  **Probabilidade:** média. **Impacto:** médio. **Mitigação:** dashboard mínimo de métricas. **Owner:** PO.

### Próximos marcos previstos
- **Imediato:** fechamento da WAVE-2026-01 (após sua confirmação) + abertura do planejamento da próxima onda.
- **Janela D+14 do deploy:** primeira leitura da north-star (Colaboradores com saldo > 0 no piloto).

## Apêndice — links rápidos

- Índice do projeto: `docs/project-state/index.json`
- Onda atual: `docs/project-state/roadmap/current-wave.md`
- Validação EPIC-003: `docs/project-state/epics/EPIC-003-carteira-e-cashback/validation/report.md`
- Épico: `docs/project-state/epics/EPIC-003-carteira-e-cashback/epic.md`
