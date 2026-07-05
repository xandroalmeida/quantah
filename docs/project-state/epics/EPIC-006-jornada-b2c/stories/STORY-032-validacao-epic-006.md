---
story_id: STORY-032
slug: validacao-epic-006
title: Validação final do EPIC-006 (Jornada do Coletador)
epic_id: EPIC-006
sprint_id: null
type: validation
target_role: validador
requires_design: false
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: M
---

# STORY-032 — Validação final do EPIC-006

> **Para o agente que vai executar (validador):** verificação de 1ª mão sobre o sha deployado em
> homologação. Execute o checklist item a item, rode a suíte, meça cobertura, teste o E2E no browser real e
> **percorra a jornada você mesmo** em homologação. Não invente resultados; em caso de `fail`, **não
> conserte** — registre e devolva ao PO. `draft` até as estórias 029–031 estarem `done`.

## Contexto (por que esta estória existe)

É a última etapa do EPIC-006. Ela verifica que a **jornada B2C completa pós-login** está viva e coesa em
homologação: o Coletador loga, chega à **home-hub**, e percorre **coletar → saldo atualiza → extrato →
iniciar saque** sem passar por página genérica, tudo mobile-first, em pt-BR e a11y AA.

- Épico: `epics/EPIC-006-jornada-b2c/epic.md`
- Estórias do épico: STORY-029 (home-hub), STORY-030 (navegação coesa), STORY-031 (jornada contínua).
- Skill do papel: `docs/skills/validador/` · Protocolo: `docs/skills/po/references/agent-task-format.md`.

## O quê (objetivo desta estória)

Produzir o **relatório de validação** do EPIC-006 (`validation/report.md`) com veredito, autorando na
execução o **checklist** (`validation/checklist.md`) a partir de `docs/skills/po/templates/validation-checklist.md`,
com evidência de 1ª mão sobre o sha deployado em homologação.

## Critérios de aceite (o que o validador entrega)

- [ ] **Checklist autorado** em `validation/checklist.md` (a partir do template), preenchido item a item com
      status `pass | fail | n/a` e evidência (link/log/screenshot).
- [ ] **Relatório publicado** em `validation/report.md` com veredito (`approved` | `approved_with_pending` |
      `reproved`), contagem, evidências e ressalvas/limitações.
- [ ] Verificado de 1ª mão em homologação: **login → home-hub** (não página genérica); **coletar** a partir
      da home; **saldo reflete a coleta**; **extrato** mostra o crédito; **iniciar saque** alcançável;
      contagem de **≤ 2 toques** para coletar e para saldo/histórico.
- [ ] Suíte de testes verde; **cobertura** ≥ 80% no código novo e ≥ 98% em núcleo/regra; **E2E mobile**
      (browser real) da jornada ponta a ponta passando.
- [ ] pt-BR em toda a jornada; a11y AA (contraste ≥ 4.5:1 + foco por teclado); nenhuma rota logada em
      página genérica/scaffolding.
- [ ] `EPIC-006.validation_report` setado no `index.json`; `index.json` = `done` para esta estória.
- [ ] "Notas do agente" preenchidas. **Não** alterar o `status` do épico (é do PO).

## Fora de escopo

- Corrigir defeitos encontrados — registre no relatório e (se aplicável) abra bug/estória de correção via PO.
- Alterar o `status` do épico — a transição `in_review → done` é decisão do PO.

## Padrões de qualidade exigidos

Validação não escreve código de produção — **não exige cobertura própria**. Exige que os padrões de
`docs/skills/po/references/quality-standards.md` sejam **verificados** nas estórias do épico (cobertura, E2E
em browser real, automação de deploy, pt-BR §5.1, a11y §5, LGPD).

## Dependências

- **Bloqueada por:** STORY-029, STORY-030, STORY-031 (todas `done` antes de validar).
- **Bloqueia:** o fechamento do épico (decisão do PO).
- **Pré-requisitos de ambiente:** homologação com o sha do épico deployado; Coletador de teste apto a
  coletar cupom válido novo e com saldo para habilitar o saque.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md` e a skill `validador`. Fronteira de papel: atualize
apenas `validation_report`; a transição EPIC-006 `in_review → done` é decisão do PO.

## Notas do agente (preenchido durante/após execução)

### Veredito
- <preenchido na execução>

### Evidências
- <preenchido na execução>

### Ressalvas / limitações
- <preenchido na execução>
