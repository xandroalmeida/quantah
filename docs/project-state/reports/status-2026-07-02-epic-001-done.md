---
report_date: 2026-07-02
sprint_id: SPRINT-2026-W27
wave: WAVE-2026-01
audience: humano-stakeholder
---

# Status Quantah — 2026-07-02 (fechamento do EPIC-001)

## TL;DR (3 linhas)

- **Onde estamos:** fundação (EPIC-000) e Design System em código (EPIC-001) **concluídos e
  validados**; metade da Onda 1 fechada.
- **O que mudou:** o EPIC-001 inteiro foi entregue em um dia (botões, inputs, superfícies/feedback/
  nav e a **vitrine `/ds`**), com validação independente **`approved`**.
- **Próxima entrega visível:** começa a **coleta de cupom** (EPIC-002) — depende de decompor o épico
  em estórias (pendente do PO).

## Onda atual

- **Onda:** WAVE-2026-01 — provar a coleta em SP (com incentivo).
- **Progresso da onda:** **2 de 4 épicos concluídos** (EPIC-000, EPIC-001). As 8 estórias definidas
  até aqui estão `done`; EPIC-002 e EPIC-003 ainda não foram decompostos.

## Épicos

### Concluídos nesta onda
- **EPIC-000 — Foundation** — concluído em 2026-07-02 — validação `approved`:
  `epics/EPIC-000-foundation/validation/report.md`.
- **EPIC-001 — Design System em código** — concluído em 2026-07-02 — validação `approved`:
  `epics/EPIC-001-design-system-codigo/validation/report.md`.

### Próximos
- **EPIC-002 — Coleta de cupom** — começa após decomposição em estórias (próximo passo do PO).
- **EPIC-003 — Carteira e cashback** — depois de EPIC-002.

## Sprint corrente

- **Sprint:** SPRINT-2026-W27 — **fechado** no mesmo dia (02/07) por overdelivery.
- **Objetivo:** botões + inputs do DS. **Resultado:** goal atingido **e** épico inteiro fechado
  (006/007 puxadas). 4 estórias `done`, 0 para trás.
- **Próximo sprint:** ainda não aberto — abre sobre o EPIC-002 após decomposição.

## O que o usuário pode ver agora em homologação

- ✅ Hello-world com o tema do DS (EPIC-000) — `https://quantah-homolog.34.39.229.117.sslip.io/`.
- ✅ **Vitrine do Design System** — `/ds` (HTTP 200), com `/ds/buttons` e `/ds/inputs`: botões,
  7 inputs, cards, badges, snackbar, empty-state, skeleton e navegação, com seus estados.

## Qualidade

- Cobertura PHP: **87,3%** (gate `--min=80` verde). Código novo é JSX, coberto por contrato-em-fonte
  (Feature) + Dusk em browser real — política vigente (IDR-002/003).
- E2E: 36 cenários Dusk em browser real (contraste AA medido no rgb, alvo ≥48px, foco visível,
  `aria-live`/`aria-invalid` reais).
- Pipeline: **verde** no commit deployado (run `28627157436`); deploy de homologação automático.

## Decisões registradas no período

- PDR-001 (adoção do DS), PDR-002 (paralelismo Designer↔Programador), DDR-001 (Inter).
- IDR-001 (tokens no `theme.extend`), IDR-002/003 (estratégia de teste do FE: contrato-em-fonte + Dusk).

## Bloqueios e riscos abertos

- Sem bloqueios ativos. Dívida rastreada na wishlist: **WISH-001** (`make up` de clone limpo),
  **WISH-002** (E2E de browser real na URL de homologação), **WISH-003** (reconciliar modelo CI/CD
  com `quality-standards.md` §2.2). Nenhuma bloqueia a Onda 1.

## Olhando à frente

### Próximos 7–14 dias
- **Decompor o EPIC-002 (Coleta de cupom)** em estórias — inclui autorar o `validation/checklist.md`
  do épico junto (aprendizado do sprint).
- Abrir o próximo sprint sobre EPIC-002; dimensioná-lo assumindo o throughput observado.
- Começar a implementação da coleta (QR da NFC-e → cupom válido/único/novo).

### Decisões aguardando você (Alexandro)
- **Caminho de produção** (fora da Onda 1 por decisão vigente): quando desenhar a promoção para
  produção com gate humano. Sem urgência; cruza com WISH-003.
- **Priorização EPIC-002 vs EPIC-003:** confirmar que a coleta vem antes da carteira (ordem atual do
  roadmap) — default mantido salvo indicação.

### Riscos abertos
- **Risco:** o throughput alto dos agentes pode mascarar dívida se a decomposição não vier com CAs e
  checklist firmes. **Probabilidade:** média. **Impacto:** médio. **Mitigação:** DoR mais rígido
  (checklist do épico junto na decomposição). **Owner:** PO.

### Próximos marcos previstos
- **~julho/2026:** fim do EPIC-002 (entrega visível: coleta de cupom funcionando em homologação).
- Virada de sprint: `SPRINT-2026-W28` (após decomposição do EPIC-002).

## Apêndice — links rápidos

- Índice do projeto: `docs/project-state/index.json`
- Onda atual: `docs/project-state/roadmap/current-wave.md`
- Sprint fechado: `docs/project-state/sprints/SPRINT-2026-W27.md`
- Validação EPIC-001: `docs/project-state/epics/EPIC-001-design-system-codigo/validation/report.md`
