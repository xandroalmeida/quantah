---
story_id: STORY-002
slug: tema-tailwind-tokens-ds
title: Tema Tailwind a partir dos tokens do Design System (com Inter)
epic_id: EPIC-000
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: ready
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-002 — Tema Tailwind a partir dos tokens do DS

> **Para o agente que vai executar:** leia por inteiro antes de começar. Esta estória tem
> `requires_design: true` — o Designer valida a fidelidade do mapeamento em paralelo (PDR-002).

## Contexto (por que esta estória existe)

O design system canônico está documentado em tokens (cor, tipografia, spacing, raio, elevação,
motion, breakpoints), mas ainda não vive no código. Para que a vitrine (EPIC-001) e todas as
telas nasçam consistentes, os tokens precisam virar a configuração de tema do Tailwind, e a
fonte precisa ser a Inter (DDR-001). Esta estória aplica a fundação visual no hello-world.

- Épico: `epics/EPIC-000-foundation/epic.md`
- Documentos a ler ANTES:
  - `docs/especificacao/design-system.md` (referência canônica)
  - `docs/project-state/design/system/tokens.md` (valores exatos)
  - `docs/project-state/decisions/ddr/DDR-001-substituicao-fonte-display.md` (Inter)
  - `docs/skills/stacks/inertia-react/SKILL.md` (§ "Estilo / Design System na prática")

## O quê (objetivo desta estória)

Mapear os tokens do DS para `tailwind.config.js` (+ CSS variables quando fizer sentido), carregar
a fonte **Inter** (400/600/900), e aplicar a paleta/tipografia na página hello-world.

## Por quê (valor para o usuário)

A fundação visual consistente é o que faz o produto parecer Quantah e evita dívida de design.
Sem os tokens no tema, cada tela reinventaria valores crus — proibido pelo DS.

## Critérios de aceite

- [ ] **CA-1:** Os tokens de cor do DS (primary, on-primary, canvas, canvas-soft, ink, body,
      mute, semânticas) existem como utilitários Tailwind e **nenhum valor cru** de cor aparece
      no JSX da hello-world.
- [ ] **CA-2:** A escala tipográfica do DS está mapeada e a fonte **Inter** (400/600/900) é
      carregada; o display usa peso 900 (DDR-001).
- [ ] **CA-3:** Spacing, raio (com `xl` = 24px), elevação e breakpoints do DS estão no tema.
- [ ] **CA-4:** A hello-world exibe: um título display (Inter 900), um parágrafo em corpo, e um
      `button.primary` (verde `#9fe870`, texto `on-primary`, raio 24px) — todos via tokens.
- [ ] **CA-5:** Contraste do botão passa AA (texto `on-primary` sobre `primary`), foco visível.

## Fora de escopo

- Construir a biblioteca de componentes React do DS (é EPIC-001) — aqui só o tema + demonstração
  mínima no hello-world.
- Ilustração autoral de marca (DDR-002).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. E2E/checagem visual do hello-world com
tema aplicado; a11y mínima (contraste AA, foco) verificada; sem valor cru fora dos tokens.

## Dependências

- **Bloqueada por:** STORY-001 (hello-world + pipeline de pé).
- **Bloqueia:** EPIC-001 (a biblioteca de componentes consome este tema).
- **Pré-requisitos:** ambiente e homologação da STORY-001.

## Decisões já tomadas (não as reabra)

- PDR-001 (adoção do DS) e DDR-001 (Inter) e DDR-002 (diferenciação de marca).
- Regras de ouro do DS (verde só como CTA; raio 24px; sem 2º accent) — ver `design-system.md`.

## Liberdade técnica do agente

Você decide a organização do `tailwind.config.js`, uso de CSS variables e como carregar a fonte.
Você **não** redefine tokens — se algo do DS parecer faltar ou conflitar, é exceção de spec do
Designer ou novo DDR; **pare e registre**, não invente token.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; Designer confirmou fidelidade do mapeamento.
- [ ] Testes/checagens passando; a11y mínima verificada.
- [ ] Pipeline verde; hello-world com tema em homologação (evidência).
- [ ] IDR se houve decisão técnica relevante (ex.: estratégia de CSS vars).
- [ ] `index.json` atualizado: status = `in_review` ao abrir PR.
- [ ] Notas do agente preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. `requires_design: true` → alinhe cedo com
o Designer antes de cristalizar. Falta de token/conflito → `blocked` + escalar ao Designer.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 
### Descobertas
- 
### Bloqueios encontrados
- 
### IDRs criados
- 
### Cobertura final
- 
### Links de evidência
- 
