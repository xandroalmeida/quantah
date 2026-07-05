# AGENTS.md — Quantah

Guia para agentes que trabalham **neste projeto**. (Este arquivo fala do projeto; não é o guia do
template que o gerou.)

## O que é

**Quantah** — plataforma de inteligência de preços via NFC-e que coleta cupons fiscais de forma
colaborativa (crowdsourcing) para construir uma base de preços do varejo em tempo quase real, monetizada
como inteligência de mercado (B2B). O nome tem duplo sentido: "**quanto** custa" + "**quantidade** de dados".

O produto tem **dois lados**: quem oferta o dado (**Colaborador** — envia cupons, movido por cashback) e
quem consome/paga por ele (**Analista B2B** — inteligência de preços). O app de consumidor é o mecanismo de
coleta; o painel B2B (Quantah Intelligence) é onde está a receita. Quem aprova: **Alexandro**.

A fonte de verdade de produto/negócio/marca é **`docs/visao.md`** — leia antes de decidir qualquer coisa
de produto.

**Fase atual: `MVP`.**

## Como o trabalho acontece

Este projeto usa um sistema de **skills por papéis**, em `docs/skills/`. Em `MVP`, atuam os 5 papéis
profissionais: **`po`**, **`arquiteto`**, **`designer`**, **`programador`**, **`validador`** — com TDD +
E2E e decisões registradas (ADR/PDR/DDR/IDR). O `idealizador` fica dormente (é da fase POC).

Carregue a skill do papel e siga o método dela — não cruze papéis. A configuração do projeto (stack,
caminhos, glossário, personas) está em **`docs/skills/_project.md`** — leia primeiro numa sessão nova.

O trabalho é organizado em **waves → épicos → estórias**, com o estado vivo em `docs/project-state/`.

## Onde estamos

- **WAVE-2026-01** — "Provar a coleta em SP" — **fechada**. Loop `cupom → saldo em cashback` vivo em
  homologação (EPICs 000–003).
- **WAVE-2026-02** (ativa) — "De POC a produto": landing pages B2C/B2B, identidade/acesso do Colaborador
  (login Google + e-mail/senha), segmentação das 3 áreas (B2C/B2B/Backoffice) e jornada B2C mobile em
  pt-BR (EPICs 004–007).

O ponto de entrada do estado é **`docs/project-state/index.json`** (`current_wave`, épicos e status).
Consulte-o antes de assumir o que está pronto.

## Estrutura

- **`app/`** — o código, já scaffoldado: **Laravel 13 + Inertia/React + PostgreSQL** (Vite + Tailwind no
  front, **Laravel Dusk** para E2E). Lógica de negócio isolada em `app/app/Domain/` (ex.: `Cashback`);
  ações em `app/app/Actions/`. Roda em Docker via **Laravel Sail** (ver "Como rodar").
- **`infra/`** — deploy da homologação: `docker-compose.prod.yml`, `Caddyfile`, provisionamento `gcp/`.
- **`.github/workflows/ci-cd.yml`** — CI (testes + build + E2E) e deploy por tag (ver "Deploy").
- **`docs/visao.md`** — documento de visão (produto, negócio, marca).
- **`docs/skills/`** — os papéis e as sub-skills de stack (`stacks/`), mais `_project.md`.
- **`docs/project-state/`** — o estado vivo (waves, épicos, sprints, decisões, design, bugs…) e `index.json`.
- **`docs/especificacao/`** e **`docs/prototipo/`** — especificação durável e protótipo/handoff.
- **`docs/DESIGN-wise.md`** — a linguagem visual (interpretação inspirada na Wise).

## Como rodar

Tudo por Docker/Sail, orquestrado pelo **`app/Makefile`** — não precisa de PHP na máquina. De dentro de
`app/`:

- **`make up`** — sobe app + Postgres, instala deps, migra e semeia, em **http://localhost:8000**
  (usuário de seed: `test@example.com`). `make down` / `make restart` gerenciam os containers.
- **`make test`** — suíte unit + feature. **`make e2e`** — E2E em browser real (Dusk via Selenium).
- **`make fresh`** / **`make seed`** — recria/repovoa o banco. **`make shell`** — shell no container.
- **`make help`** lista todos os alvos.

No **Claude Code** (máquina real) você também pode dizer **"prepare o ambiente e suba o preview"** — a
sub-skill `docs/skills/setup-ambiente/` cobre o primeiro setup. Ao editar o código, o hot reload recarrega
a tela ao vivo.

## Deploy

O deploy **não** é automático no push da `main` — é disparado por **tag** de git. Homologação usa
`vMAJOR.MINOR.PATCH-rc-N` (ex.: `v0.1.1-rc-0`); produção usará `vMAJOR.MINOR.PATCH` (ainda não ativo).
**MAJOR e MINOR só sobem quando o Alexandro pedir**; o fluxo normal mexe só no `rc` (e no `patch` ao abrir
um ciclo). O passo a passo e as regras de versão estão em **`docs/deploy.md`** — leia antes de tagar.

## Convenções

- O **idiomático da stack** está nas sub-skills de `docs/skills/stacks/` da stack ativa (postgres,
  laravel, inertia-react) — consulte antes de inventar.
- **Não versione** segredos nem artefatos pesados: `app/.env`, `app/vendor/`, `app/node_modules/`,
  `*.sqlite`, `app/public/build/`. O `.gitignore` na raiz cobre isso.
- **Dev e teste em bancos separados** — a suíte roda contra o banco de teste, nunca o de dev (ver
  `docs/skills/stacks/database/database-method.md` e `docs/skills/stacks/laravel/SKILL.md`).
- O **remote** é `origin → github.com:xandroalmeida/quantah` (independente do template que gerou o repo).
  Push só nele; não adicione outros remotes por engano.
- Mudou algo durável de produto/arquitetura/design? Registre no estado (`docs/project-state/`) conforme
  a skill do papel manda.
