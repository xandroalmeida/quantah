---
id: WISH-005
slug: chaves-i18n-em-ingles-no-payload
title: Chaves i18n em inglês no payload das páginas (source-keys expostas)
status: triaged
origin: Validação EPIC-005 (relatório, ressalva R2 não-bloqueante) — 2026-07-05
tags: ["debito", "i18n", "qualidade", "polish"]
spec_link: null
rejected_reason: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# WISH-005 — Chaves i18n em inglês no payload das páginas

## One-liner

As chaves de origem (source-keys) do mecanismo de i18n aparecem em inglês no payload das páginas,
embora todo o texto **visível** ao usuário esteja 100% em pt-BR.

## Problema / necessidade

O mecanismo de i18n (ADR-011, estabelecido no EPIC-004) usa **source-keys em inglês** como identificadores
das mensagens. Na validação do EPIC-005 observou-se que essas chaves viajam no payload das páginas. Não há
impacto no que o usuário lê — a copy renderizada é integralmente pt-BR e a a11y/contraste passam —, mas a
presença de termos em inglês no payload é um resíduo que enfraquece a impressão de qualidade "pt-BR de ponta
a ponta" da onda e pode confundir inspeções futuras (ex.: SEO, auditoria de idioma, snapshot de payload).

## Valor esperado

Fecha uma ponta solta do requisito transversal de localização da WAVE-2026-02 (pt-BR sem resíduo em inglês,
`quality-standards.md` §5.1). É **polish/dívida**, não bug (nenhuma expectativa de usuário é violada — o
texto visível está correto). Liga ao princípio de qualidade, não a uma métrica de north-star.

## Referências

- Origem: `docs/project-state/epics/EPIC-005-portas-de-entrada/validation/report.md` (ressalva R2).
- Mecanismo: ADR-011 (i18n) → `decisions/adr/` · estória do mecanismo: STORY-020 (EPIC-004).
- Padrões: `docs/skills/po/references/quality-standards.md` §5.1 (pt-BR).
- Onda: `docs/project-state/roadmap/current-wave.md` — "Requisito transversal desta onda".

## Restrições conhecidas

- LGPD: sem dado pessoal envolvido.
- Não regressivo: qualquer mudança deve preservar a copy visível 100% pt-BR e não quebrar o mecanismo do
  ADR-011 (decisão do Arquiteto/Programador na promoção).
- Escopo a decidir na triagem/spec: mascarar/traduzir as source-keys no payload servido, ou aceitar como
  característica do mecanismo e apenas documentar.

## Notas / histórico

- `2026-07-05` — Captura inicial. Origem: validação EPIC-005 (R2, não-bloqueante). Status `triaged`:
  contexto entendido, sem compromisso de sprint. É polish/dívida de i18n, não bug.
