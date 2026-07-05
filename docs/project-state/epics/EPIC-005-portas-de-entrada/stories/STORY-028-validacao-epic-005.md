---
story_id: STORY-028
slug: validacao-epic-005
title: Validação final do EPIC-005 (Portas de entrada)
epic_id: EPIC-005
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

# STORY-028 — Validação final do EPIC-005

> **Para o agente que vai executar (validador):** verificação de 1ª mão sobre o sha deployado em
> homologação. O produto é um **veredito** e um relatório; a transição de status do épico é do PO. Autore
> o checklist na execução (template `docs/skills/po/templates/validation-checklist.md`) e execute item a
> item. Fica `draft` até as estórias de dependência (025–027) avançarem — o PO promove para `ready`.

## Contexto (por que esta estória existe)

Fecha o épico com evidência independente de que existe uma **porta de entrada pública**: a landing B2C
apresenta a proposta e leva ao login, e a landing B2B (Quantah Intelligence) captura um lead que aparece no
Backoffice — tudo mobile-first, em pt-BR, sobre o DS, sem confiar apenas nos testes dos autores.

## Critérios de aceite (alto nível — autorar checklist na execução)

- [ ] Checklist em `validation/checklist.md` e relatório em `validation/report.md`.
- [ ] STORY-025, 026, 027 com `status: done` no `index.json`; cada CA exercido por teste com asserção real.
- [ ] Verificado de 1ª mão sobre o sha deployado: a **landing B2C** é pública, apresenta a proposta em
      pt-BR e o **CTA leva ao login** (EPIC-004); o **CTA B2B** leva à landing Quantah Intelligence.
- [ ] Verificado de 1ª mão: na **landing B2B**, um lead válido (nome, e-mail, empresa) é **capturado e
      persistido**; inválido e duplicado são tratados; o lead **aparece na lista do Backoffice** sob o
      papel operacional, e um usuário sem o papel é barrado.
- [ ] **pt-BR** confirmado nas superfícies do épico (sem resíduo de scaffolding em inglês, sem logo do
      Laravel); mensagens de erro/validação em português; formatos brasileiros onde aplicável.
- [ ] **a11y AA** e **mobile-first** verificados nas duas landings; só tokens do DS (sem cor/fonte fora dos
      tokens).
- [ ] Cobertura conforme quality-standards (≥ 80% geral; ≥ 98% na regra do lead e na guarda de acesso);
      **LGPD** mantida (PII do lead restrita ao papel operacional, sem vazamento no tratamento de duplicado).
- [ ] CI verde; sem segredos versionados; decisões (ADR/DDR/IDR) do épico indexadas;
      `EPIC-005.validation_report` apontando o relatório.

## Fora de escopo

- Recomendar/decidir a transição de status do épico (é do PO).
- Consertar falhas encontradas — registrar e devolver ao PO.

## Padrões de qualidade exigidos

Validação segue `docs/skills/po/references/quality-standards.md`, com ênfase em **§5.1 (pt-BR)**, **§5
(a11y)**, **§4 (LGPD — PII do lead)** e **§1 (E2E dos fluxos das duas landings e da barreira do Backoffice)**.
Validação não escreve código de produção — sem exigência de cobertura própria.

## Dependências

- **Bloqueada por:** STORY-025, STORY-026, STORY-027.
- **Bloqueia:** — (o PO decide a transição do épico com base no veredito).
- **Pré-requisitos de ambiente:** homologação com o sha do épico deployado; usuário com papel operacional e
  lead de teste semeados.

## Definição de Pronto (DoD)

- [ ] Relatório publicado com veredito; checklist preenchido item a item com evidência.
- [ ] `EPIC-005.validation_report` setado no `index.json`; `index.json` = `done` para esta estória.
- [ ] "Notas do agente" preenchidas. **Não** alterar o `status` do épico (é do PO).

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md` e a skill `validador`. Fronteira de papel: atualize
apenas `validation_report`; a transição EPIC-005 `in_review → done` é decisão do PO.

## Notas do agente (preenchido durante/após execução)

### Veredito
- **APPROVED** (2026-07-05). 33 pass, 4 pass com ressalva, 0 fail, 1 n/a justificado. Relatório completo em
  `validation/report.md`; checklist autorado na execução em `validation/checklist.md`.

### Evidências
- Sha deployado em homologação: `da7e2a0` (run de deploy 28737220705 = success). `HEAD` local: `b098baf`.
- Suíte: 295/295 Feature/Unit verdes (1264 asserções); cobertura total **95,2%**; núcleos do lead e da guarda
  do Backoffice a **100%** (≥98%). E2E Dusk das 4 telas do épico **11/11** verdes.
- 1ª mão em homologação: as duas landings (`/`, `/intelligence`) e a lista do Backoffice acessíveis; lead
  capturado pela landing B2B aparece na lista do Backoffice sob o papel operacional; anônimo barrado (302);
  inválido bloqueado; duplicado idempotente sem vazar.
- CI verde na main; deploy de homologação automatizado (`ci-cd.yml` job `deploy`, smoke em `/up`); IaC em
  `infra/gcp/`. LGPD: PII restrita ao operador, sem PII em log, aviso de privacidade sem checkbox (DDR-006).

### Ressalvas / limitações
- R1: flake de cold-start no 1º teste Dusk local (0/4 na re-execução isolada; CI verde) — ambiente, não regressão.
- R2: chaves i18n em inglês no payload (source-keys do mecanismo ADR-011); copy visível é 100% pt-BR.
- R3: `make up` presente e stack no ar, mas não re-testado em máquina limpa nesta sessão.
- R4: saúde básica via `/up` + `Interno/MetricasController`; sem dashboard de observabilidade dedicado nesta fase.
- n/a 3.4: sem ambiente de produção nesta fase MVP (homologação é o alvo do épico).
- Estado no momento da validação: EPIC-005 em `ready` (não `in_review`) e STORY-028 em `draft`; dependências
  025/026/027 `done`. A transição de status do épico é decisão do PO — **não** alterei o `status` do épico.
