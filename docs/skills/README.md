# Sistema de skills por papel — template reutilizável

Este diretório é um **template reutilizável** de skills que definem como agentes de IA atuam num
projeto de software. Cada skill representa um **papel** com fronteiras claras de decisão. Tudo que é
específico de um produto (nome, personas, stack, vocabulário, caminhos) é parametrizado como
marcador e resolvido a partir de um único arquivo de configuração (o `_project.md`). Neste projeto os
marcadores já foram assados (resolvidos) para os valores do Quantah.

## Como começar: o comando `init-project`

A forma recomendada de iniciar um projeto novo é a skill [`init-project/`](init-project/SKILL.md). Ela
**entrevista** você e **gera** o projeto a partir deste template. Logo no início ela pergunta **qual dos
dois caminhos**:

- **POC (idealizador)** — o projeto nasce como prova de conceito tocada por uma pessoa não-técnica
  (fase `POC`, stack fixa Laravel+Livewire+SQLite).
- **MVP (engenharia desde o dia 1)** — o projeto nasce com o time profissional (fase `MVP`, perfil de
  stack à escolha).

O `init-project` preenche o [`_project.md`](_project.md), **assa** os valores nas skills (find/replace
dos marcadores), cria a árvore de estado e instala o **esqueleto do app que já sobe**. Ao fim, passa a bola
para o `idealizador` (POC) ou para o `po` (MVP).

### Instanciação manual (alternativa)

Se preferir fazer à mão: copie esta pasta para o repositório (ex.: `docs/skills/`), preencha o
[`_project.md`](_project.md) e crie a árvore de estado sob `docs/project-state/`. Os marcadores
são resolvidos lendo o `_project.md` — neste projeto eles já foram assados.

## Duas fases: POC (idealizador) → MVP (time profissional)

Um projeto pode nascer da cabeça de uma pessoa **não-técnica**. Por isso o sistema tem duas fases, e a
fase (declarada em [`_project.md`](_project.md) › Fase) decide **quais skills estão ativas**:

- **Fase `POC`** — só o [`idealizador/`](idealizador/SKILL.md) atua. Uma pessoa sem conhecimento de
  programação, guiada pelo agente (que escreve o código), transforma a ideia num **app que de fato roda**
  (Laravel + Livewire + **SQLite**), grava em banco, tem testes mínimos e cobre fluxos alternativos
  mínimos. Objetivo: provar **viabilidade comercial**. Os 5 papéis profissionais ficam dormentes.
- **Gate POC→MVP** — quando o humano decide que provou-se viável, muda a fase para `MVP`. O **pacote de
  handoff** do idealizador vira o protótipo/spec que o PO lê; o SQLite migra para o banco do perfil.
- **Fase `MVP`** — o time profissional (os 5 papéis abaixo) assume e transforma o POC validado em produto
  de verdade.

## Duas camadas: papéis (método) + stacks (tecnologia)

O sistema tem duas camadas separadas de propósito:

- **Papéis** (`po/`, `arquiteto/`, `designer/`, `programador/`, `validador/`) — o **método**, agnóstico
  de tecnologia. Como cada papel pensa, decide e onde estão suas fronteiras. Não muda quando a stack muda.
- **Sub-skills de stack** (`stacks/`) — a **opinião de tecnologia**, atômica e plugável. O idiomático de
  cada tech, que os papéis consultam conforme a stack ativa do projeto.

Trocar de tecnologia = trocar a sub-skill ativa no [`_project.md`](_project.md), sem tocar nos papéis.

### Comandos de apoio

| Skill | Função |
|---|---|
| [`init-project/`](init-project/SKILL.md) | **Inicialização** — entrevista e gera um projeto novo a partir do template (caminho POC ou MVP). Roda uma vez, no começo. |
| [`setup-ambiente/`](setup-ambiente/SKILL.md) | **Preparo da máquina** — detecta e instala o toolchain (POC: Laravel Herd; MVP: Docker+Sail; fallback nativo). Chamado pelo init, ou quando o app não sobe. |

#### Cowork × Claude Code (mesma pasta)

O agente atua em duas superfícies que compartilham a **mesma pasta conectada**: o **Cowork** (chat) **constrói** — o agente escreve e edita o código — e o **Claude Code** (terminal na máquina real) **roda o preview persistente** (`composer run dev`), porque o shell do Cowork é um sandbox efêmero (sem `sudo`, processos morrem a cada chamada). O **hot reload** (Vite/HMR + Livewire) conecta os dois: ao editar no Cowork, o preview no Claude Code recarrega ao vivo. O **deploy** (link durável num host) é um passo separado e futuro, não o preview de desenvolvimento. Detalhe em [`setup-ambiente/`](setup-ambiente/SKILL.md) (ver `references/cowork-e-claude-code.md`).

### Skill de POC (fase `POC`)

| Skill | Papel | Artefato próprio |
|---|---|---|
| [`idealizador/`](idealizador/SKILL.md) | **Idealizador** — pessoa não-técnica que, guiada pelo agente, transforma a ideia num POC que roda e prova viabilidade | POC rodando + pacote de handoff |

### Skills de papel (fase `MVP`)

| Skill | Papel | Artefato próprio |
|---|---|---|
| [`po/`](po/SKILL.md) | **Product Owner** — decide o quê, por que, para quem, em que ordem; mantém o estado do projeto | PDR, épicos, estórias |
| [`arquiteto/`](arquiteto/SKILL.md) | **Arquiteto** — decisões técnicas de alto nível (stack via ADR, padrões, modelo de dados macro) | ADR |
| [`designer/`](designer/SKILL.md) | **Designer** — UX/UI das telas; mantém Design System; spec de tela mobile-first | DDR, screen spec |
| [`programador/`](programador/SKILL.md) | **Programador** — executa estórias, implementa com TDD+E2E | IDR |
| [`validador/`](validador/SKILL.md) | **Validador** — valida fim de épico, produz relatório `approved`/`rejected` | relatório de validação |

### Sub-skills de stack

| Sub-skill | Camada | Default em |
|---|---|---|
| [`stacks/database/postgres/`](stacks/database/postgres/SKILL.md) | Banco | todos os perfis (exceto POC) |
| [`stacks/database/sqlite/`](stacks/database/sqlite/SKILL.md) | Banco | POC/protótipo |
| [`stacks/laravel/`](stacks/laravel/SKILL.md) | Backend | todos os perfis |
| [`stacks/livewire/`](stacks/livewire/SKILL.md) | UI web padrão | perfil web-padrão |
| [`stacks/inertia-react/`](stacks/inertia-react/SKILL.md) | UI web rica | perfil web-rica |
| [`stacks/flutter/`](stacks/flutter/SKILL.md) | FE mobile | perfil mobile |

Perfis prontos (banco · backend · frontend): **web-padrão** = postgres·laravel·livewire ·
**web-rica** = postgres·laravel·inertia-react · **mobile** = postgres·laravel·flutter ·
**POC** = sqlite·laravel·(à escolha). Detalhes e como adicionar uma stack nova: [`_project.md`](_project.md).

## Fronteiras de papel (resumo)

```
PO ──────── O QUÊ + POR QUÊ + QUANDO + QUALIDADE EXIGIDA
            │
            │  (escreve estória)
            │
   ┌────────┴────────┐
   ▼                 ▼
Designer       Programador ──── COMO em baixo nível (implementação)
(UX/UI, DS,    (em paralelo                │
DDR, spec      com o Designer              │  (consulta ADRs vigentes
de tela)       na mesma estória)           │   do Arquiteto)
   │                 │                     ▼
   └────────┬────────┘              Arquiteto ──── COMO em alto nível
            │                                      (stack, padrões, ADRs)
            ▼  (entrega código + testes + spec coerente)
       Validador ───── VERIFICA tudo no fim do épico
```

Um papel **nunca** cruza para a área do outro. O PO não programa, o Programador não decide produto, o Arquiteto não escreve testes E2E, o Designer não escolhe stack nem altera CA da estória, o Validador não conserta nada.

**Designer e Programador trabalham em paralelo** na mesma estória de UI — rabisco inicial do Designer + sync curto antes do código começar (ver `designer/references/collaboration-with-developer.md`). O Designer **revisa** o PR contra o spec, mas **não** emite veredito independente — isso é do Validador.

**O Arquiteto entra antes** quando o PO abre estória de spike arquitetural, e suas ADRs vigentes restringem o que Designer e Programador podem decidir.

### Papel vs pessoa

A separação é entre **atos, não entre pessoas**. Em time pequeno, a mesma pessoa veste vários papéis
em momentos diferentes — a disciplina é **declarar a troca** e não confundir os papéis no mesmo
momento. Cada papel continua dono do seu artefato (ADR/PDR/DDR/IDR/relatório), em locais separados.

## Como o agente carrega uma skill

Quando uma sessão começa, o agente:

1. Lê o [`_project.md`](_project.md) para resolver os valores do projeto.
2. Lê esta página.
3. Identifica seu papel pela conversa (ou pela estória atribuída).
4. Carrega a skill correspondente (ex.: invoca a skill `po`).
5. A skill traz instruções operacionais e referências detalhadas.

## Estado do projeto

O estado vivo (épicos, estórias, sprints, decisões) fica em `docs/project-state/` — caminho definido no
`_project.md`.
