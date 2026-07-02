---
epic_id: EPIC-001
slug: design-system-codigo
title: Design System em código — biblioteca de componentes React
wave: WAVE-2026-01
status: done
owner_role: po
created_at: 2026-07-02
updated_at: 2026-07-02
target_completion: 2026-08-15
---

# EPIC-001 — Design System em código

## Por que existimos (problema do usuário)

O DS canônico existe em documento (tokens, componentes, padrões), mas ainda não em código. Sem a
biblioteca de componentes React implementada, cada tela de produto reinventaria botão, input,
card — gerando inconsistência e dívida. Este épico transforma o DS documentado em componentes
reutilizáveis, para que Coleta e Carteira nasçam consistentes e rápidas.

## Resultado esperado (outcome)

Ao fim deste épico, o Programador constrói qualquer tela do Quantah **compondo componentes do DS
já prontos** (não markup solto), e há uma página de referência ("vitrine") em homologação que
mostra todos os componentes e seus estados.

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: os componentes da lista mínima do DS implementados, com estados (default/
  hover/focus/pressed/disabled/loading/error) e testados.
- Métrica de qualidade: cobertura conforme `quality-standards.md`; a11y mínima (contraste AA,
  foco visível, alvo ≥48px) verificada; zero valor cru de cor/spacing fora dos tokens.

## Entregável visível no fim do épico

- [ ] Página "vitrine" (kitchen sink) em homologação exibindo os componentes do DS e seus estados.
- [ ] Componentes da lista mínima disponíveis em `Components/` (botões, inputs, cards, badges,
      snackbar, empty-state, skeleton, nav).
- [ ] Cada componente com spec de tela/componente do Designer referenciada.

## Fora de escopo (explicitamente)

- Telas de produto (Coleta, Carteira) — só a biblioteca de componentes aqui.
- Componentes ainda não necessários à Onda 1 (ex.: tabelas B2B do Quantah Intelligence).
- Ilustração autoral de marca (DDR-002) — entra quando a 1ª tela com ilustração abrir.

## Referências da especificação

- `docs/especificacao/design-system.md` e `docs/project-state/design/system/*` — o DS canônico.
- `docs/project-state/decisions/ddr/DDR-001-*` e `DDR-002-*` — fonte e diferenciação de marca.
- `docs/skills/stacks/inertia-react/SKILL.md` — de-para token→Tailwind, componente→React.

## Dependências

- **Bloqueia:** EPIC-002, EPIC-003 (as telas usam estes componentes).
- **Bloqueado por:** EPIC-000 (precisa do ambiente + tema Tailwind de pé).
- **Decisões arquiteturais necessárias:** nenhuma nova prevista; se surgir necessidade de lib
  headless de componentes, é ADR do Arquiteto (não decidir na estória).

## Estórias

Decomposto em 2026-07-02 (PO). Cada componente/grupo é estória com `requires_design: true`
(Designer valida em paralelo, PDR-002).

- [x] **STORY-004** — botões (primary/secondary/tertiary/danger/icon) + spec · `done`
- [x] **STORY-005** — inputs (text/masked/datetime/select/checkbox/radio/switch) + spec · `done`
- [x] **STORY-006** — cards, badges, snackbar, empty-state, skeleton, nav + vitrine · `done`
- [x] **STORY-007** (validação) — validação final do épico · `done` (veredito `approved`)

## Validação final

Critérios em `validation/checklist.md`. Relatório em `validation/report.md`.

**Definição de épico concluído:** vitrine em homologação; componentes mínimos com estados e
testes; a11y mínima verificada; validação `approved`.

## Histórico

- 2026-07-02 — criado por PO (Fluxo A, WAVE-2026-01).
- 2026-07-02 — decomposto em STORY-004..007 (PO); SPRINT-2026-W27 aberto.
- 2026-07-02 — validação `approved` (STORY-007, validador 0dae5a52). Épico `done` (transição do PO,
  CA-5): vitrine `/ds` no ar, lista mínima de componentes com estados/a11y, CI verde (cobertura
  87,3%). Ressalvas registradas no relatório de validação.
