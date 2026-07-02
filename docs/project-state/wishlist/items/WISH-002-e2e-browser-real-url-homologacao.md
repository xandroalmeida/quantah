---
id: WISH-002
slug: e2e-browser-real-url-homologacao
title: E2E de browser real apontado para a URL de homologação
status: triaged
origin: Validação EPIC-000 (relatório, ressalva Bloco 4.2) — 2026-07-02
tags: [debito, teste, qualidade]
spec_link: null
rejected_reason: null
created_at: 2026-07-02
updated_at: 2026-07-02
---

# WISH-002 — E2E de browser real apontado para a URL de homologação

## One-liner

Um teste de browser real (Dusk/headless) navega contra a **URL pública de homologação** e confirma
o comportamento observável ali, não só contra a app servida no runner do CI.

## Problema / necessidade

Hoje o E2E em browser real (Dusk) roda contra a app servida no runner (`php artisan serve`,
ambiente equivalente — permitido pela CA-5 da STORY-001). A **homologação em si** é coberta por
smoke HTTP 200 automatizado + probe independente (curl), mas **não** por navegação de browser
apontada para `https://quantah-homolog...`. Há uma pequena lacuna entre "passou no equivalente"
e "passou na homologação real".

## Valor esperado

Aumenta a confiança de que o que está publicado em homologação funciona de fato num browser real
(hidratação, assets, tema), fechando a diferença entre ambiente-equivalente e ambiente-publicado.
Ganha relevância quando começarem os fluxos de usuário reais (Coleta, Carteira).

## Referências

- Relatório: `epics/EPIC-000-foundation/validation/report.md` (Bloco 4.2 e "Limitações da validação").
- Job `deploy` (smoke HTTP 200) e job `dusk` em `.github/workflows/ci-cd.yml`.

## Restrições conhecidas

- Rodar Dusk contra URL remota exige config de `APP_URL`/driver remoto e credenciais de ambiente;
  avaliar custo/flake antes de tornar gate obrigatório (pode começar como smoke não-bloqueante).

## Notas / histórico

- `2026-07-02` — Captura inicial. Origem: ressalva factual da validação do EPIC-000.
