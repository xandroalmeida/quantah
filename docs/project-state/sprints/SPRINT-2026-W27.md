---
sprint_id: SPRINT-2026-W27
wave: WAVE-2026-01
status: active
start_date: 2026-07-02
end_date: 2026-07-08
goal: "Abrir o EPIC-001: botões e inputs do DS em código, com estados e testes, rumo à vitrine."
---

# SPRINT-2026-W27

## Objetivo do sprint

Com a fundação (EPIC-000) fechada e aprovada, este sprint inicia o **EPIC-001 — Design System em
código**. A meta é ter os dois componentes mais fundamentais — **botões** e **inputs** — vivendo em
código com todos os estados e testes, via tokens do DS, já plugáveis na futura vitrine. É o primeiro
passo concreto para que Coleta e Carteira nasçam compondo componentes prontos.

## Estórias incluídas

| ID | Título | Épico | Tamanho | Status atual |
|---|---|---|---|---|
| STORY-004 | Botões do DS (primary/secondary/tertiary/danger/icon) + estados | EPIC-001 | M | ready |
| STORY-005 | Inputs do DS (text/masked/datetime/select/checkbox/radio/switch) + estados | EPIC-001 | M | ready |

**Ordem sugerida:** começar por STORY-004 (botão é o molde do DS e destrava a vitrine); STORY-005
pode correr em paralelo. Ambas `requires_design: true` — Designer valida a fidelidade em paralelo
(PDR-002).

**Fora deste sprint (próximo):** STORY-006 (cards/badges/feedback/nav + **vitrine**, tamanho `L`,
bloqueada por 004/005) e STORY-007 (validação do épico).

## Compromisso visível ao fim do sprint

Em homologação, botões e inputs do DS renderizando **todas as variantes e estados** (default/hover/
focus/pressed/disabled/loading/error), com contraste AA, foco visível e zero valor cru fora dos
tokens. Pode ser um subconjunto do épico (a vitrine consolidada vem no próximo sprint) — está ok.

## Riscos identificados na abertura

| Risco | Probabilidade | Impacto | Mitigação | Owner |
|---|---|---|---|---|
| Specs de componente do Designer atrasarem e segurarem as estórias `requires_design` | média | alto | Alinhamento cedo com Designer no dia 1; modelo paralelo do PDR-002 (implementa 1:1 dos tokens canônicos, fidelidade confirmada em paralelo) | PO |
| STORY-005 (7 tipos de input) crescer além de `M` | média | médio | Se estourar, sinalizar ao PO para quebrar (ex.: texto/máscara numa estória, seleção/toggles noutra) | Programador |
| Primeiro sprint de componentes: estimativa ainda não calibrada | alta | baixo | Escopo conservador (2 estórias M); aprendizado registrado na retro | PO |

## Mudanças no escopo do sprint (preencher se houver mid-sprint changes)

| Data | O que mudou | Motivo | Custo (estória solta/movida) |
|---|---|---|---|
| — | — | — | — |

## Fechamento do sprint (preencher no encerramento)

### O que foi entregue
- 

### O que ficou para trás (e por quê)
- 

### Aprendizados
- 

### Ajustes para o próximo sprint
- 
