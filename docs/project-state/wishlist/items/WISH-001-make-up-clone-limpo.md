---
id: WISH-001
slug: make-up-clone-limpo
title: Provar "sobe com um comando" (make up) a partir de clone limpo
status: triaged
origin: Validação EPIC-000 (relatório, ressalva Bloco 1.3) — 2026-07-02
tags: [debito, enablement, dx]
spec_link: null
rejected_reason: null
created_at: 2026-07-02
updated_at: 2026-07-02
---

# WISH-001 — Provar "sobe com um comando" (make up) a partir de clone limpo

## One-liner

Um desenvolvedor novo clona o repositório e sobe o ambiente completo (app + banco + seed) com
um único comando, sem passos manuais — verificado de verdade, de um clone limpo.

## Problema / necessidade

A validação do EPIC-000 confirmou o CA "ambiente sobe com um comando" por **inspeção** do
`app/Makefile`/`README` e pela evidência registrada na STORY-000, mas **não re-executou `make up`
de um clone limpo** nesta sessão (risco de colisão de portas com stacks concorrentes rodando).
Fica uma lacuna: o mecanismo está descrito e foi exercido uma vez, mas não há uma verificação
reproduzível e recente do fluxo ponta a ponta a partir do zero.

## Valor esperado

Reduz atrito de onboarding e dívida de fundação: garante que qualquer pessoa (ou agente) consegue
subir o Quantah rapidamente e de forma determinística. Sustenta a promessa de "automação, não
passo manual" dos padrões de qualidade.

## Referências

- Relatório: `epics/EPIC-000-foundation/validation/report.md` (Bloco 1.3 e "Limitações da validação").
- Evidência existente: `epics/EPIC-000-foundation/stories/STORY-000-evidencia/CA-1-stack-local.md`.
- `app/Makefile` (alvo `up`) e `app/README.md` (§ "Ambiente de desenvolvimento — um comando").

## Restrições conhecidas

- Ambiente local via Laravel Sail; agentes concorrentes usam portas/stacks próprios — a verificação
  precisa isolar portas (ex.: perfil/porta dedicados) para não colidir com stacks em execução.

## Notas / histórico

- `2026-07-02` — Captura inicial. Origem: ressalva factual da validação do EPIC-000.
