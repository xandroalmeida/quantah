---
id: WISH-004
slug: scanner-deps-e-segredos-no-ci
title: Análise de dependências vulneráveis e detecção de segredos no CI
status: triaged
origin: Validação EPIC-004 (relatório, F-NB-1 não-bloqueante) — 2026-07-05
tags: ["debito", "seguranca", "ci", "qualidade"]
spec_link: null
rejected_reason: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# WISH-004 — Análise de dependências vulneráveis e detecção de segredos no CI

## One-liner

O pipeline barra automaticamente um merge que introduza dependência com vulnerabilidade
conhecida ou que vaze um segredo no código.

## Problema / necessidade

Hoje o CI roda testes e cobertura, mas **não** tem etapa de análise de dependências
vulneráveis nem de detecção de segredos versionados. Isso contraria
`quality-standards.md` §2.2/§4. Na validação do EPIC-004 nenhum segredo versionado nem
vulnerabilidade crítica foi observado de 1ª mão, mas a ausência do gate significa que uma
regressão futura passaria despercebida — o controle depende de inspeção manual a cada revisão.

## Valor esperado

Fecha a dívida transversal de segurança carregada desde a Onda 1 (já listada como risco da
WAVE-2026-02). Protege o loop de contas/carteira/saque contra dependência comprometida e
evita exposição de credencial (OAuth Google, chaves de app). Liga ao princípio de qualidade e
compliance dos quality-standards, não a uma métrica de north-star.

## Referências

- Origem: `docs/project-state/epics/EPIC-004-acesso-e-areas/validation/report.md` (F-NB-1).
- Padrões: `docs/skills/po/references/quality-standards.md` §2.2 (dependências) e §4 (segredos).
- Onda: `docs/project-state/roadmap/current-wave.md` — "Riscos da onda" (scanner de segredos/deps no CI).
- Relacionado: WISH-003 (reconciliar modelo CI/CD com quality-standards §2.2).

## Restrições conhecidas

- LGPD: sem dado pessoal envolvido (é gate de pipeline).
- Integração externa: pode usar ferramentas do próprio provedor de CI (ex.: dependency scanning,
  secret scanning) ou OSS (ex.: gitleaks/trufflehog + auditoria de deps do gerenciador de pacotes);
  decisão fica para a spec.
- Limites: cuidar de falsos-positivos para não bloquear o fluxo do time; definir política de
  severidade que barra o merge (ex.: só crítico/alto no início).

## Notas / histórico

- `2026-07-05` — Captura inicial. Origem: validação EPIC-004 (F-NB-1). Status `triaged`: contexto
  entendido, sem compromisso de sprint ainda. Relaciona-se a WISH-003.
