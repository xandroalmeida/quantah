---
story_id: STORY-007
slug: validacao-epic-001
title: Validação final do EPIC-001 (Design System em código)
epic_id: EPIC-001
sprint_id: null
type: validation
target_role: validador
requires_design: false
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-007 — Validação final do EPIC-001

> **Para o validador:** valide o épico de forma independente contra o checklist. Não confie apenas
> em relatos das estórias — verifique a vitrine em homologação por evidência própria.

## Contexto (por que esta estória existe)

Fecha o EPIC-001: confirmar, com evidência independente, que a biblioteca de componentes do DS
existe em código, com estados e a11y mínima, e que a vitrine está no ar em homologação.

- Épico: `epics/EPIC-001-design-system-codigo/epic.md`
- Checklist: `epics/EPIC-001-design-system-codigo/validation/checklist.md` (a criar antes da validação)
- Critérios de veredito: `docs/skills/validador/references/verdict-criteria.md`

## O quê (objetivo desta estória)

Executar a validação final do EPIC-001 e emitir um relatório com veredito (`approved` / `rejected`),
seguindo o mesmo rigor da validação do EPIC-000.

## Por quê (valor para o usuário)

Garante que Coleta e Carteira vão nascer sobre uma base de componentes de fato pronta e consistente,
não sobre uma promessa.

## Critérios de aceite

- [x] **CA-1:** Todos os componentes da lista mínima existem, com estados, e aparecem na vitrine.
- [x] **CA-2:** Vitrine acessível em homologação por HTTPS (HTTP 200), verificada de forma independente.
- [x] **CA-3:** A11y mínima confirmada (contraste AA, foco visível, alvo ≥48px) e zero valor cru
      fora dos tokens (guarda verde).
- [x] **CA-4:** Cobertura conforme `quality-standards.md`; pipeline verde no merge que publicou.
- [x] **CA-5:** Relatório emitido em `validation/report.md` e `index.json` aponta o relatório com o
      veredito; transição de status do épico fica a cargo do PO.

## Fora de escopo

- Corrigir defeitos encontrados — isso vira estória de correção; a validação apenas relata.

## Padrões de qualidade exigidos

Segue `docs/skills/validador/*` e `quality-standards.md`. Evidência independente obrigatória
(não apenas relato das estórias).

## Dependências

- **Bloqueada por:** STORY-004, STORY-005, STORY-006 (todas `done`).
- **Bloqueia:** fechamento do EPIC-001.

## Decisões já tomadas (não as reabra)

- PDR-001, PDR-002 (paralelismo Designer↔Programador), DDR-001.

## Definição de Pronto (DoD)

- [x] Checklist do épico executado item a item com evidência.
- [x] Relatório com veredito em `validation/report.md`.
- [x] `index.json` atualizado: `epics[EPIC-001].validation_report` aponta o relatório; story `done`.
- [x] "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `docs/skills/validador/*` e `agent-task-format.md`. Veredito segue `verdict-criteria.md`.

## Notas do agente (preenchido durante/após execução)

> **Owner:** `claude-validador-story007` (sessão 0dae5a52). Papel: Validador independente.
> **Veredito: APPROVED** — 17 `pass`, 3 `pass com ressalva`, 0 `fail`, 4 `n/a` justificados.

### Pré-condição destravada
- O `validation/checklist.md` do EPIC-001 **não existia** ao iniciar. O Validador não cria checklist
  (input do PO). Foi decidido com o dono (PO) que o **PO autora o checklist antes** — feito no commit
  `b7b5b55` (`docs(PO): checklist de validação do EPIC-001`). Só então a validação começou, mantendo a
  fronteira de papel.

### Decisões tomadas
- Cobertura (Bloco 2.1) marcada `pass com ressalva`: mede linha **PHP** (87,3% ≥ 80%); o código novo é
  JSX, coberto por contrato-em-fonte (Feature) + Dusk (browser), por **IDR-002/003** vigente — respeitado,
  não reinterpretado.
- Núcleo 98% (2.2), promoção tag-based (3.4), migrações (5.7) e — para verificação por scanner — segurança
  de dependências marcados `n/a` justificados: épico de UI sem módulo de regra/migração; pipeline de
  promoção/scanner é escopo do EPIC-000 (já validado), não tocado aqui.
- Não alterei o `status` do **épico** (segue `ready`) — transição é do PO (CA-5). Atualizei apenas
  `epics[EPIC-001].validation_report` e o status desta estória de validação para `done`.

### Descobertas
- CI (`ci-cd.yml`) não tem passo de scanner de dependências/segredos — registrado como **Limitação**;
  segredos verificados por inspeção direta (nenhum versionado).
- O grep do HTML servido de `/ds` não lista nomes de componentes (app Inertia/React: DOM montado no
  cliente). Render/a11y verificados por E2E Dusk em browser real (verde no CI) + leitura das asserções
  + `curl` independente (HTTP 200, `data-page: DesignSystem/Showcase`).

### Bloqueios encontrados
- Nenhum durante a execução. Único bloqueio inicial (checklist ausente) destravado pelo PO (acima).

### Links de evidência
- Relatório: `epics/EPIC-001-design-system-codigo/validation/report.md`.
- Checklist (input do PO): `epics/EPIC-001-design-system-codigo/validation/checklist.md`.
- CI do commit deployado: run `28627157436` (commit `7a7d06a`) — 64 unit+feature (87,3%), 36 Dusk.
- Verificação independente de homolog: `https://quantah-homolog.34.39.229.117.sslip.io/ds` → HTTP 200.
- Contrato de tokens local: `php artisan test --filter=DesignSystem` → 35 passed (212 assertions).
