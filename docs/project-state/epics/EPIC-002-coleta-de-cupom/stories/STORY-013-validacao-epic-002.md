---
story_id: STORY-013
slug: validacao-epic-002
title: Validação final do EPIC-002 (Coleta de cupom)
epic_id: EPIC-002
sprint_id: null
type: validation
target_role: validador
requires_design: false
design_screen_id: null
status: done
owner_agent: validador-e2d9a721
created_at: 2026-07-02
updated_at: 2026-07-03
estimated_session_size: M
---

# STORY-013 — Validação final do EPIC-002

> **Para o validador:** valide de forma independente contra `validation/checklist.md` (já autorado
> pelo PO — ver abaixo). Verifique a coleta em homologação por evidência própria; não confie só em relatos.

## Contexto (por que esta estória existe)

Fecha o coração da onda: confirmar, com evidência independente, que a coleta funciona ponta a ponta em
homologação — cupom de SP vira dado válido, único e novo, com CPF anonimizado e north-star instrumentada.

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- **Checklist:** `epics/EPIC-002-coleta-de-cupom/validation/checklist.md` (autorado pelo PO nesta
  decomposição — sem bloqueio de pré-condição desta vez).
- Critérios de veredito: `docs/skills/validador/references/verdict-criteria.md`.

## O quê (objetivo desta estória)

Executar a validação final do EPIC-002 e emitir relatório com veredito (`approved`/`rejected`),
seguindo o rigor das validações anteriores (evidência de 1ª mão).

## Por quê (valor para o usuário)

Garante que o baseline da north-star sai de uma coleta de fato confiável (válida, única, nova e em
conformidade com LGPD), não de uma promessa.

## Critérios de aceite

- [ ] **CA-1:** STORY-008..012 estão `done`; cada CA exercido por teste automatizado.
- [ ] **CA-2:** Fluxo de coleta acessível em homologação (mobile), verificado de forma independente.
- [ ] **CA-3:** Cupom de SP vira **válido, único e novo** (dedup idempotente comprovada); CPF **não**
      persistido em claro; north-star instrumentada e visível.
- [ ] **CA-4:** Cobertura conforme `quality-standards.md` (núcleo de validação/dedup ≥98%); pipeline
      verde no merge que publicou; migrações reversíveis testadas.
- [ ] **CA-5:** Relatório em `validation/report.md` e `index.json` aponta o relatório com o veredito;
      transição de status do épico fica a cargo do PO.

## Fora de escopo

- Corrigir defeitos encontrados — vira estória de correção; a validação apenas relata.

## Padrões de qualidade exigidos

Segue `docs/skills/validador/*` e `quality-standards.md`. Evidência independente obrigatória.

## Dependências

- **Bloqueada por:** STORY-008, STORY-009, STORY-010, STORY-011, STORY-012 (todas `done`).
- **Bloqueia:** fechamento do EPIC-002 (e destrava o EPIC-003).

## Decisões já tomadas (não as reabra)

- ADR-000/001/002/003/006/007/008; PDR-001/002; definição da north-star.

## Definição de Pronto (DoD)

- [ ] Checklist executado item a item com evidência.
- [ ] Relatório com veredito em `validation/report.md`.
- [ ] `index.json` atualizado: `epics[EPIC-002].validation_report` aponta o relatório; story `done`.
- [ ] "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `docs/skills/validador/*` e `agent-task-format.md`. Veredito segue `verdict-criteria.md`.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- **Veredito: APPROVED com pendências.** Zero fails bloqueantes; 2 fails não-bloqueantes
  (documentação/estado). 20 pass, 5 pass com ressalva, 1 n/a. Relatório completo em
  `validation/report.md`.
- **Não alterei o status do épico** — por CA-5 desta estória, a transição fica a cargo do PO. Preenchi
  apenas `EPIC-002.validation_report` e `STORY-013.status = done` no `index.json`.
- **Pré-condição de deploy**: no início, STORY-011/012 estavam commitadas na `main` local mas **não
  publicadas** (homolog em STORY-010, `/interno/metricas` → 404). Com aprovação humana, a `main` foi
  publicada (push → CI verde → deploy automático), e então validei contra o homolog completo. Registro
  factual: a validação ocorreu após esse deploy (sha `b321eac`).

### Descobertas
- **F-NB-1**: `IDR-003-zxing-browser-decode-qr.md` (STORY-009, criado no épico) **não indexado** no
  `index.json` e **colide de id** com o IDR-003 (STORY-005, máscara).
- **F-NB-2**: STORY-012 é `requires_design: true` mas sem artefato de Designer (`design_screen_id: null`,
  sem tela em `design/screens/`); painel composto do DS existente pelo programador.
- **Ressalva de cobertura**: núcleo de dedup/validação-por-chave em 99–100% (atende ≥98%); o adaptador
  de scraping SEFAZ (I/O) em 95,6–96,8% com linhas descobertas defensivas — sem lacuna de lógica.

### Bloqueios encontrados
- Nenhum bloqueio de validação. O único impedimento inicial (código não publicado) foi resolvido com o
  deploy autorizado antes de validar.

### Links de evidência
- Relatório: `epics/EPIC-002-coleta-de-cupom/validation/report.md`.
- CI run `28668610185` (sha `b321eac`): Testes+build (151 testes/678 asserções, gate --min=80), E2E Dusk
  e Deploy homologação — todos verdes.
- Homolog (curl 2026-07-03): `/up` 200, `/coletar` 200 (component `Coleta/Captura`),
  `/interno/metricas` 302→/login.
- Cobertura local (`b321eac`): total 92,5%; `IngestaoCupomService` 99,0%, `ChaveAcesso` 100%.
