---
story_id: STORY-001
slug: pipeline-deploy-homolog
title: Pipeline CI/CD e deploy automatizado de homologação com hello-world
epic_id: EPIC-000
sprint_id: null
type: implementation
target_role: programador
requires_design: false
status: done
owner_agent: claude-programador-story001
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: L
---

# STORY-001 — Pipeline CI/CD e deploy de homologação

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. Contém
> tudo o que você precisa. Ambiguidade → registre em "Notas do agente" e pause.

## Contexto (por que esta estória existe)

O princípio de entrega em produção desde o dia 1 exige homologação no dia 1. O `app/` já está
scaffoldado (Laravel + Inertia/React), mas não há pipeline nem deploy. Esta estória entrega o
"trilho": todo merge roda testes + build e publica automaticamente uma página viva em
homologação — a base sobre a qual todos os épicos seguintes sobem.

- Épico: `epics/EPIC-000-foundation/epic.md`
- Documentos a ler ANTES:
  - `docs/skills/po/references/quality-standards.md` (§2 Automação)
  - `docs/skills/stacks/laravel/SKILL.md`, `docs/skills/stacks/inertia-react/SKILL.md`
  - `docs/project-state/decisions/adr/ADR-000-stack-default.md`

## O quê (objetivo desta estória)

Implementar um pipeline de CI/CD que roda a suíte de testes e o build a cada push/PR e faz
**deploy automatizado para homologação** de uma página "hello world" servida via Inertia.

## Por quê (valor para o usuário)

Sem trilho automatizado, cada entrega vira passo manual e frágil — e o usuário nunca vê o
produto cedo. Este é o pré-requisito de "cada épico entrega algo visível em homologação".

## Critérios de aceite

- [x] **CA-1:** Dado um push na branch principal, quando o pipeline roda, então ele executa a
      suíte de testes e o build, e **falha o merge** se algum quebrar. → jobs `tests` + `dusk`
      no `ci-cd.yml`; comprovado por 2 runs vermelhos (env/build) antes do verde.
- [x] **CA-2:** Dado um merge com pipeline verde, quando o deploy roda, então a página hello-world
      fica acessível na **URL de homologação** sem nenhum passo manual. → job `deploy` verde;
      **https://quantah-homolog.34.39.229.117.sslip.io** respondendo 200 com TLS Let's Encrypt.
- [x] **CA-3:** Dado um clone limpo, quando se roda **um comando**, então app + Postgres sobem
      localmente com dados de seed. → `make up` verificado em clone limpo (HTTP 200 na :8000, seed OK).
- [x] **CA-4:** A página hello-world é servida via Inertia (rota → página React) e responde 200.
      → `routes/web.php` `/` → `Hello.jsx`; `HelloWorldTest` (Feature) verde.
- [x] **CA-5:** Há ao menos um teste E2E em browser real que abre a hello-world em homologação
      (ou ambiente equivalente) e verifica o conteúdo. → `tests/Browser/HelloWorldTest` (Dusk,
      Chrome real), verde local e no CI.

## Fora de escopo

- Tema/tokens do DS (é STORY-002).
- Ambiente de produção final (homologação é o alvo desta onda).
- Qualquer feature de produto (coleta, carteira).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`. Resumo: ≥80% cobertura no código novo;
E2E em browser real para o fluxo entregue (CA-5); **tudo automatizado** — nada de "passo manual";
sem segredo commitado (usar variáveis de ambiente/secrets do CI).

## Dependências

- **Bloqueada por:** STORY-000 (spike confirma a stack sobe).
- **Bloqueia:** STORY-002, STORY-003 e os épicos seguintes.
- **Pré-requisitos:** conta/infra de homologação (se ainda não existir, registrar em Notas e
  escalar ao PO/Arquiteto — a escolha de provedor é ADR de Infra, não decisão da estória).

## Decisões já tomadas (não as reabra)

- ADR-000 (stack) → `decisions/adr/ADR-000-stack-default.md`
- PDR-002 (escopo da onda) → `decisions/pdr/PDR-002-escopo-onda-1.md`
- Ferramenta de E2E: é decisão de **ADR do Arquiteto** (Dusk default do ecossistema Laravel vs
  Playwright). Se não houver ADR, **pare e escale** — não escolha sozinho.

## Liberdade técnica do agente

Você decide estrutura do workflow de CI, scripts de build/deploy, organização do código da
página. Você **não** decide provedor de infra/homologação nem ferramenta de E2E (ADR do
Arquiteto) — se faltar, escale.

## Definição de Pronto (DoD)

- [x] CA-1 a CA-5 passam.
- [x] Testes unitários + E2E escritos e passando; cobertura ≥80% (total 87.3%).
- [x] Pipeline verde; deploy de homologação verificado (link em evidência). *(Sem PR: por decisão
      do dono, trabalho direto na `main`; o gate roda no push da `main`.)*
- [x] Ambiente local em um comando documentado no README do `app/` (`make up`).
- [ ] IDR registrado se houve decisão técnica relevante. → **N/A**: as decisões de peso viraram
      **ADR-007** (infra) e **ADR-008** (E2E), do Arquiteto; nenhuma decisão local exigiu IDR.
- [x] `index.json` atualizado: status = `done` (sem PR; direto na `main`).
- [x] Notas do agente preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`: ao iniciar `status: in_progress` +
`owner_agent` + `index.json`; TDD; se travar por falta de ADR de infra/E2E → `status: blocked`
e escale. Ao terminar → `status: in_review`, PR aberto, `index.json` atualizado.

## Notas do agente (preenchido durante/após execução)

> Executada em 2026-07-02 pelo papel **Programador** (`owner_agent: claude-programador-story001`).
> **Pré-requisito de infra resolvido:** o Arquiteto fechou **ADR-007** (VPS genérica no GCP) e
> **ADR-008** (E2E = Laravel Dusk), ambos `accepted` por Alexandro. A VM de homolog já foi provisionada
> (`infra/gcp/provision.sh`). Trabalho direto na `main` (sem PR, por decisão do dono); branch renomeada
> `master → main`.

### Documentos lidos
- Estória inteira; `epic.md`; ADR-000, ADR-007, ADR-008; skill do Programador; `_project.md`;
  evidência do STORY-000 (stack sobe ponta a ponta; Sail compartilhado entre agentes → stack isolado).

### Entendimento consolidado
- Entregar o "trilho": CI (testes+build) que barra merge, deploy automático de homolog no merge, uma
  página hello-world Inertia (rota→React, 200), dev local em 1 comando com seed, e E2E em browser real.
- Ambiente: PHP só no Sail; outro agente roda `app-*` no ar → subi stack isolado `quantah-s001`
  (portas 8001/5443/5174) para rodar a suíte sem colisão.

### Plano (3–5 bullets)
1. **CA-4** (TDD): teste Feature vermelho de `GET /` → 200 + Inertia component `Hello` com props;
   depois rota + página `Hello.jsx`.
2. **CA-5** (TDD): instalar Dusk (ADR-008) + selenium no compose; teste de browser vermelho que abre `/`
   e verifica o conteúdo do hello-world (caminho feliz + robustez de carregamento).
3. **CA-1**: workflow CI (GitHub Actions) — Postgres service, `composer install`, `npm ci`, build,
   `artisan test` e Dusk; barra o merge se quebrar.
4. **CA-2**: workflow de deploy no merge → build imagem prod (Dockerfile) → GHCR → SSH na VM →
   `compose pull` + `migrate --force` + `up -d`. Caddy serve HTTPS no host sslip.io.
5. **CA-3**: `make up` (um comando) do clone limpo → sobe app+Postgres+seed. Documentar no README.

### Mapa CA → testes
- **CA-4** → `Tests\Feature\HelloWorldTest`: `test_hello_world_is_served_via_inertia_and_returns_200`
  (feliz), `test_hello_world_exposes_app_name_and_environment` (conteúdo/props),
  `test_unknown_route_returns_404` (borda/negativo do roteamento).
- **CA-5** → `Tests\Browser\HelloWorldTest` (Dusk): `test_visitor_sees_quantah_hello_world` (feliz em
  browser real), `test_hello_world_hydrates_react_and_shows_environment` (Inertia/React hidrata).
- **CA-1/CA-2/CA-3** → verificados por execução do pipeline/deploy/script (automação, não unit).

### Decisões tomadas
- **Web server da imagem de produção:** nginx + php-fpm (supervisor) numa imagem self-contained na
  porta 80; TLS fica no Caddy (reverse proxy). Mantém a imagem portável (decisão local, ADR-007 já
  fixa o padrão VPS+Compose).
- **`route:cache` omitido no entrypoint** de produção: há rotas com Closure (`/`, `/dashboard`) não
  serializáveis. `config:cache` + `view:cache` cobrem o ganho.
- **Pull da imagem no host via `GITHUB_TOKEN`** do próprio workflow (sem PAT); imagem no GHCR
  (`ghcr.io/xandroalmeida/quantah`).
- **Deploy direto na `main` sem PR** (decisão do dono); branch `master → main` renomeada.
### Descobertas
- Outro agente mantém o stack `app-*` (selenium/mailpit/minio) no ar; usei stack isolado `quantah-s001`.
- `public/hot` obsoleto (apontava `:5173` morto) quebrava assets no browser — removido; build por manifest.
- Zona `southamerica-east1-b` esgotada (`ZONE_RESOURCE_POOL_EXHAUSTED`) → VM criada em `-c`.
- Billing "Créditos RHHUB" no limite de projetos → VM no projeto `command-center-8026a` (já na billing).
### Bloqueios encontrados
- Nenhum bloqueio duro. Pré-requisito de ADR de infra/E2E resolvido pelo Arquiteto (ADR-007/008,
  aceitos por Alexandro) antes de implementar.
### IDRs criados
- Nenhum (as decisões de peso viraram ADR-007 e ADR-008).
### Cobertura final
- Unitários/Feature: **87.3%** total (`php artisan test --coverage --min=80` verde). `HelloWorldTest`
  cobre CA-4: feliz (200+Inertia), conteúdo (props), acesso público, 404.
- E2E: `tests/Browser/HelloWorldTest` (Dusk) — 2 cenários (vê a hello-world; React hidrata) em Chrome real.
### Links de evidência
- Pipeline: run CI/CD verde `28608916155` (jobs `tests`, `dusk`, `deploy` ✓).
- Homologação: **https://quantah-homolog.34.39.229.117.sslip.io** (200, TLS Let's Encrypt).
- Provisionamento: `infra/gcp/provision.sh` (VM `quantah-homolog`, `southamerica-east1-c`, IP 34.39.229.117).
