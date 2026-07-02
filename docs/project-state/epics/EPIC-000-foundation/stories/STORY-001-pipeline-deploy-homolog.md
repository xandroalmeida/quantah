---
story_id: STORY-001
slug: pipeline-deploy-homolog
title: Pipeline CI/CD e deploy automatizado de homologação com hello-world
epic_id: EPIC-000
sprint_id: null
type: implementation
target_role: programador
requires_design: false
status: ready
owner_agent: null
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

- [ ] **CA-1:** Dado um push na branch principal, quando o pipeline roda, então ele executa a
      suíte de testes e o build, e **falha o merge** se algum quebrar.
- [ ] **CA-2:** Dado um merge com pipeline verde, quando o deploy roda, então a página hello-world
      fica acessível na **URL de homologação** sem nenhum passo manual.
- [ ] **CA-3:** Dado um clone limpo, quando se roda **um comando**, então app + Postgres sobem
      localmente com dados de seed (ambiente de dev automatizado).
- [ ] **CA-4:** A página hello-world é servida via Inertia (rota → página React) e responde 200.
- [ ] **CA-5:** Há ao menos um teste E2E em browser real que abre a hello-world em homologação
      (ou ambiente equivalente) e verifica o conteúdo.

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

- [ ] CA-1 a CA-5 passam.
- [ ] Testes unitários + E2E escritos e passando; cobertura ≥80% no novo.
- [ ] Pipeline verde no PR; deploy de homologação verificado (link em evidência).
- [ ] Ambiente local em um comando documentado no README do `app/`.
- [ ] IDR registrado se houve decisão técnica relevante.
- [ ] `index.json` atualizado: status = `in_review` ao abrir PR.
- [ ] Notas do agente preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`: ao iniciar `status: in_progress` +
`owner_agent` + `index.json`; TDD; se travar por falta de ADR de infra/E2E → `status: blocked`
e escale. Ao terminar → `status: in_review`, PR aberto, `index.json` atualizado.

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
- Unitários: 
- E2E: 
### Links de evidência
- PR / Pipeline / Homologação: 
