---
story_id: STORY-037
slug: validacao-epic-007
title: Validação final do EPIC-007 (Refinamento da experiência B2C mobile)
epic_id: EPIC-007
sprint_id: null
type: validation
target_role: validador
requires_design: false
design_screen_id: null
status: done
owner_agent: claude-validador
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-037 — Validação final do EPIC-007

> **Para o agente que vai executar:** `target_role: validador`. Só rode quando STORY-033 a 036 estiverem
> `done`. Valida o épico contra a "Definição de épico concluído" e os padrões de qualidade — em
> **homologação, no celular**.

## Contexto (por que esta estória existe)

Fecha o EPIC-007 com uma verificação independente de que os refinamentos de experiência mobile entregam o
outcome e não regridem a jornada B2C (EPIC-006). Segue o padrão das validações anteriores
(STORY-032/028/024).

- Épico: `epics/EPIC-007-refinamento-experiencia-b2c-mobile/epic.md`
- Checklist: `epics/EPIC-007-refinamento-experiencia-b2c-mobile/validation/checklist.md`
- Relatório: `epics/EPIC-007-refinamento-experiencia-b2c-mobile/validation/report.md`

## O quê (objetivo desta estória)

Executar a validação final do épico: rodar/inspecionar os E2E, verificar CA das estórias em homologação
(mobile) e emitir veredito (`approved` / `approved_with_findings` / `changes_requested`) com ressalvas
registradas.

## Critérios de aceite (o que a validação precisa confirmar)

- [ ] **CA-1 (casca):** `nav.bottom` fixo e visível em **todas** as telas logadas (Início, Escanear,
      Carteira, Perfil) no celular, sem rolar e sem overflow; "Entrar" inteiro na landing; modo standalone
      abre sem barra do navegador (STORY-033).
- [ ] **CA-2 (cupom):** listagem mostra **estabelecimento + data**; toque abre **detalhe com itens**; nome
      do emitente capturado em cupons novos (com fallback quando ausente) (STORY-034).
- [ ] **CA-3 (validade):** cupom fora da janela é **rejeitado com motivo**; limite lido de **config**
      (default 7); dentro da janela segue normal; fronteira testada (STORY-035).
- [ ] **CA-4 (coerência):** sem opção de **excluir conta** (UI e rota); menu de escanear **renomeado**
      (STORY-036).
- [ ] **CA-5 (transversal):** 100% pt-BR; a11y AA (alvos ≥48px, foco, aria); pipeline verde; sem regressão
      da jornada do EPIC-006.

## Fora de escopo

- Implementar correções (é papel das estórias 033–036; findings viram ressalvas/estórias).

## Padrões de qualidade exigidos

Segue `quality-standards.md` (§1 testes/E2E, §5 a11y, §5.1 pt-BR). Verificação em browser real mobile sobre
o sha deployado em homologação.

## Dependências

- **Bloqueada por:** STORY-033, STORY-034, STORY-035, STORY-036.
- **Bloqueia:** fechamento do EPIC-007.

## Definição de Pronto (DoD)

- [ ] Checklist preenchido; `report.md` com veredito e ressalvas.
- [ ] `index.json`: story `done` e `validation_report` do épico registrado.
- [ ] Ressalvas não-bloqueantes viram wishlist/estórias; bloqueantes retornam às estórias.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md` e o método do validador. Não corrija código; registre findings.

## Notas do agente (preenchido durante/após execução)

### Veredito

**APPROVED.** 19 passes, 3 passes com ressalva, 0 fails, 2 n/a justificados. Estórias 033–036 `done`;
suíte completa verde (Pest 323/323; Dusk 90/90); cobertura total 95,2% e código novo ≥ 87,5%.

### Ressalvas (não-bloqueantes)

- **R1** — Rejeição por prazo é aferida na extração assíncrona; a confirmação de captura não mostra o
  motivo de forma síncrona em produção (microcopy pronta/ancorada — IDR-013).
- **R2** — Homologação no aparelho não exercitada (PO manteve commits locais, repo sem remote).
- **R3** — Foco índigo do Breeze nos formulários do Perfil permanece (dívida R5 do EPIC-006, fora de escopo).

### Bloqueios

Nenhum.

### Links de evidência

- Relatório: `validation/report.md`; checklist: `validation/checklist.md`.
- Suíte: `sail artisan test` (323) + `sail artisan dusk` (90); cobertura `--coverage` 95,2%.
