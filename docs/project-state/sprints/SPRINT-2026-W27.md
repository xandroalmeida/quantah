---
sprint_id: SPRINT-2026-W27
wave: WAVE-2026-01
status: closed
start_date: 2026-07-02
end_date: 2026-07-02
closed_early: true
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

## Mudanças no escopo do sprint

| Data | O que mudou | Motivo | Custo (estória solta/movida) |
|---|---|---|---|
| 2026-07-02 | STORY-006 (vitrine, `L`) e STORY-007 (validação) **puxadas para dentro** do sprint | STORY-004/005 concluíram cedo e destravaram 006; com a vitrine pronta, a validação do épico coube na janela | Nenhum — escopo expandiu sem soltar estória; o épico inteiro fechou |

## Fechamento do sprint

Sprint **encerrado no mesmo dia** (02/07) por **overdelivery**: o goal (botões + inputs) foi
atingido cedo e o time seguiu, entregando também a vitrine e a validação — fechando o **EPIC-001
inteiro** dentro da janela. Encerro o sprint em vez de mantê-lo aberto ocioso; o próximo abre sobre
o EPIC-002 (que ainda precisa ser decomposto).

### O que foi entregue
- **STORY-004** (botões) e **STORY-005** (inputs) — o compromisso do sprint, `done`.
- **STORY-006** (cards/badges/snackbar/empty-state/skeleton/nav + **vitrine `/ds`**) — puxada, `done`.
- **STORY-007** (validação do épico) — puxada, `done`, veredito **`approved`**.
- Observável em homologação: vitrine `/ds` no ar (HTTP 200), lista mínima de componentes com estados.

### O que ficou para trás (e por quê)
- Nada. O sprint entregou além do comprometido.

### Métricas
- Velocidade: 4 estórias `done` (2 comprometidas + 2 puxadas) num único dia de execução.
- Cobertura PHP: 87,3% (gate `--min=80` verde); código novo JSX coberto por contrato-em-fonte + Dusk.
- Pipeline: verde no commit deployado (run `28627157436`); deploy de homologação automático.
- Bloqueios: 1 (checklist de validação ausente) — destravado pelo PO antes da validação começar.

### Aprendizados
- **Estimativa conservadora demais para agentes.** Planejei 006/007 para o próximo sprint; o time
  fechou tudo em 1 dia. Próximo planejamento pode comprometer um épico de porte similar inteiro.
- **DoR do checklist de validação:** a STORY-007 travou por não haver `validation/checklist.md`.
  Aprendizado: o checklist do épico deve existir **antes** de a validação entrar no sprint.

### Ajustes para o próximo sprint
- Ao decompor um épico, **autorar o `validation/checklist.md` junto** (não deixar para a véspera da validação).
- Dimensionar o próximo sprint assumindo maior throughput (candidato: EPIC-002 após decomposição).
