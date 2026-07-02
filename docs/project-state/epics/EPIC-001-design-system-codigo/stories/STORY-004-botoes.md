---
story_id: STORY-004
slug: botoes
title: Botões do DS (primary/secondary/tertiary/danger/icon) com estados e spec
epic_id: EPIC-001
sprint_id: SPRINT-2026-W27
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

# STORY-004 — Botões do DS

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. Se algo
> estiver ambíguo, registre a dúvida em "Notas do agente" e pause em vez de adivinhar.
> `requires_design: true` — o Designer valida a fidelidade em paralelo (PDR-002).

## Contexto (por que esta estória existe)

O tema Tailwind com os tokens do DS já está de pé (STORY-002), mas os componentes ainda não vivem
em código. O botão é o componente mais usado e o portador da regra de ouro do DS (verde `primary`
só como CTA). Implementá-lo primeiro destrava a vitrine e serve de molde para os demais.

- Épico: `epics/EPIC-001-design-system-codigo/epic.md`
- Documentos a ler ANTES:
  - `docs/especificacao/design-system.md` (botões e regras de ouro)
  - `docs/project-state/design/system/components.md` (spec de `button.*`)
  - `docs/project-state/design/system/tokens.md` (valores canônicos)
  - `docs/skills/stacks/inertia-react/SKILL.md` (§ componente→React, token→Tailwind)

## O quê (objetivo desta estória)

Implementar o componente `Button` do DS em React (`Components/`) com as variantes
**primary, secondary, tertiary, danger e icon**, cada uma com os estados
**default / hover / focus / pressed / disabled / loading**, tudo via tokens (zero valor cru).

## Por quê (valor para o usuário)

Botão consistente é o que faz cada tela do Quantah parecer o mesmo produto e evita dívida de
design. Sem ele, Coleta e Carteira reinventariam o CTA — quebrando a regra de ouro do DS.

## Critérios de aceite

- [ ] **CA-1:** Existe `Button` reutilizável com as 5 variantes (primary/secondary/tertiary/danger/
      icon); a variante `primary` usa o verde `primary` + texto `on-primary` (é o único CTA).
- [ ] **CA-2:** Cada variante cobre os estados default/hover/focus/pressed/disabled/loading, todos
      derivados de tokens — **nenhum valor cru** de cor/spacing/raio no componente.
- [ ] **CA-3:** A11y mínima: contraste AA (≥4.5:1) em cada variante, **foco visível** por teclado,
      e alvo de toque **≥48px** de altura (exceto onde a spec do Designer definir diferente).
- [ ] **CA-4:** Estado `loading` desabilita o clique e expõe estado acessível (ex.: `aria-busy`);
      `disabled` não dispara `onClick`.
- [ ] **CA-5:** O componente aparece na vitrine (kitchen sink) exibindo todas as variantes e estados
      — mesmo que a página da vitrine seja finalizada na STORY-006, o `Button` já é plugável nela.

## Fora de escopo

- Demais componentes (inputs, cards etc.) — outras estórias do épico.
- Telas de produto (Coleta, Carteira).
- Ilustração autoral de marca (DDR-002).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. Cobertura ≥80% no código novo; testes de
render/estado por variante; checagem a11y (contraste AA + foco) automatizada onde viável; sem valor
cru fora dos tokens.

## Dependências

- **Bloqueada por:** STORY-002 (tema Tailwind com tokens) — já `done`.
- **Bloqueia:** STORY-006 (vitrine consome os componentes), STORY-007 (validação do épico).
- **Pré-requisitos:** ambiente e homologação da fundação (EPIC-000).

## Decisões já tomadas (não as reabra)

- PDR-001 (adoção do DS), DDR-001 (Inter), IDR-001 (tokens no `theme.extend`).
- Regras de ouro do DS: verde só como CTA; raio 24px; sem 2º accent. Não redefina token — falta/
  conflito de token vira exceção de spec do Designer ou novo DDR: **pare e registre**.

## Liberdade técnica do agente

Você decide a API de props do `Button`, a estrutura de pastas em `Components/`, e o design dos
testes. Você **não** redefine tokens nem os critérios de aceite.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; **Designer confirmou fidelidade** (spec de `button.*`).
- [ ] Testes escritos e passando na cobertura exigida; a11y mínima verificada.
- [ ] Pipeline de CI verde; deploy de homologação realizado e verificado.
- [ ] IDR registrado se houve decisão técnica relevante.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`. `requires_design: true` → alinhe cedo com o
Designer. Falta de token/conflito → `blocked` + escalar ao Designer.

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
