# AGENTS.md — Quantah

Guia para agentes que trabalham **neste projeto**. (Este arquivo fala do projeto; não é o guia do
template que o gerou.)

## O que é

**Quantah** — plataforma de inteligência de preços via NFC-e que coleta cupons fiscais de forma
colaborativa (crowdsourcing) para construir uma base de preços do varejo em tempo quase real, monetizada
como inteligência de mercado (B2B).

Público: consumidores brasileiros que recebem NFC-e nas compras (coleta) e clientes B2B — indústria/CPG,
varejo e terceiros de dados (demanda). Personas: **Colaborador** (envia cupons) e **Analista B2B**
(consome inteligência de preços). Quem aprova: **Alexandro**.

A fonte de verdade de produto/negócio/marca é **`docs/visao.md`** — leia antes de decidir qualquer coisa
de produto.

**Fase atual: `MVP`.**

## Como o trabalho acontece

Este projeto usa um sistema de **skills por papéis**, em `docs/skills/`. Em `MVP`, atuam os 5 papéis
profissionais: **`po`**, **`arquiteto`**, **`designer`**, **`programador`**, **`validador`** — com TDD +
E2E e decisões registradas (ADR/PDR/DDR/IDR). O `idealizador` fica dormente (é da fase POC).

Carregue a skill do papel e siga o método dela — não cruze papéis. A configuração do projeto (stack,
caminhos, glossário) está em **`docs/skills/_project.md`**.

## Estrutura

- **`app/`** — o código do app: Laravel + Inertia/React + PostgreSQL. **Ainda não scaffoldado** — o
  esqueleto é criado pela `setup-ambiente` no Claude Code (ver "Como rodar").
- **`docs/visao.md`** — documento de visão (produto, negócio, marca).
- **`docs/skills/`** — os papéis e as sub-skills de stack (`stacks/`).
- **`docs/project-state/`** — o estado vivo (épicos, sprints, decisões, design, bugs…), com `index.json`.
- **`docs/especificacao/`** e **`docs/prototipo/`** — especificação durável e protótipo/handoff.

## Como rodar

O app viverá em `app/`. Para subir o preview, no **Claude Code** (na máquina real) diga: **"prepare o
ambiente e suba o preview"** — a sub-skill `docs/skills/setup-ambiente/` faz tudo (instala o que faltar,
monta o esqueleto Laravel + Inertia/React, prepara o Postgres) e abre **http://localhost:8000**. O
**Cowork** constrói (edita o código), o **Claude Code** roda o preview, e o **hot reload** conecta os
dois: ao editar, a tela recarrega ao vivo.

> **Por que o `app/` está vazio:** o scaffold precisa de PHP/Composer, que não existem no sandbox do
> Cowork (é o esperado). O primeiro comando no Claude Code (`setup-ambiente`) cria o esqueleto e o sobe.

## Primeiro passo de MVP

1. **PO** (`docs/skills/po/`) — Fluxo 0: visão, personas, north-star, lendo `docs/visao.md`; depois
   quebrar em épicos/estórias e semear o `index.json`.
2. **Arquiteto** (`docs/skills/arquiteto/`) — ratificar ou trocar a stack pelo **ADR-000** já criado
   (`docs/project-state/decisions/adr/ADR-000-stack-default.md`) e aprofundar os pontos **[→ Fase
   técnica]** da visão (extração/adaptadores SEFAZ, deduplicação, anti-fraude, matching de produtos,
   pagamento).

## Convenções

- O **idiomático da stack** está nas sub-skills de `docs/skills/stacks/` da stack ativa (postgres,
  laravel, inertia-react) — consulte antes de inventar.
- **Não versione** segredos nem artefatos pesados: `app/.env`, `app/.bin/`, `app/vendor/`,
  `app/node_modules/`, `*.sqlite`, `app/public/build/`. O `.gitignore` na raiz cobre isso.
- **Dev e teste em bancos separados** — a suíte roda contra o banco de teste, nunca o de dev (ver
  `docs/skills/stacks/database/database-method.md` e `docs/skills/stacks/laravel/SKILL.md`).
- Este repositório é **independente** — nasceu com um git novo, **sem** remote do template. Não adicione
  um remote por engano (risco de `push` no lugar errado).
- Mudou algo durável de produto/arquitetura/design? Registre no estado (`docs/project-state/`) conforme
  a skill do papel manda.
