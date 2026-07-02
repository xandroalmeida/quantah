---
id: WISH-003
slug: reconciliar-modelo-cicd-quality-standards
title: Reconciliar o modelo de promoção CI/CD com quality-standards §2.2
status: triaged
origin: Validação EPIC-000 (relatório, observação sem recomendação) — 2026-07-02
tags: [debito, processo, decisao]
spec_link: null
rejected_reason: null
created_at: 2026-07-02
updated_at: 2026-07-02
---

# WISH-003 — Reconciliar o modelo de promoção CI/CD com quality-standards §2.2

## One-liner

O time decide, explicitamente, entre adotar o modelo de promoção descrito no padrão de qualidade
ou atualizar o padrão para refletir o pipeline que roda hoje — sem divergência silenciosa.

## Problema / necessidade

O pipeline atual roda a suíte completa (testes + Dusk + build) a cada push/PR e faz **deploy de
homologação no push da `main`**. Isso difere do modelo em `quality-standards.md` §2.2 (hook git
pré-push **versionado** + CI leve em feature branch + promoção **por tag** com `-rc` para
homologação e **gate humano** em produção). Não há hook versionado no repo nem promoção por tag.
Nenhum item do checklist do EPIC-000 exige esse modelo e produção estava fora de escopo — então
não é um defeito, mas é uma divergência documento↔realidade que convém resolver antes de crescer.

## Valor esperado

Evita que o padrão de qualidade e o pipeline real contem histórias diferentes (fonte de confusão
e de decisões erradas mais tarde). O resultado é um único modelo de promoção confiável — condição
para, mais adiante, desenhar o caminho de produção com gate humano.

## Referências

- Relatório: `epics/EPIC-000-foundation/validation/report.md` (seção "Observações", modelo de promoção CI/CD).
- `docs/skills/po/references/quality-standards.md` §2.2.
- `.github/workflows/ci-cd.yml` (pipeline vigente).

## Restrições conhecidas

- A decisão provavelmente vira um **PDR/ADR** (processo/infra), não uma estória de código isolada.
- Toca o caminho de produção, hoje fora de escopo da Onda 1 — sincronizar com o roadmap.

## Notas / histórico

- `2026-07-02` — Captura inicial. Origem: observação neutra da validação do EPIC-000 (sem recomendação).
