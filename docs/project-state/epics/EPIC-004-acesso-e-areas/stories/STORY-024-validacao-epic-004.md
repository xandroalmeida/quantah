---
story_id: STORY-024
slug: validacao-epic-004
title: Validação final do EPIC-004 (Acesso e áreas)
epic_id: EPIC-004
sprint_id: null
type: validation
target_role: validador
requires_design: false
design_screen_id: null
status: ready
owner_agent: null
created_at: 2026-07-04
updated_at: 2026-07-04
estimated_session_size: M
---

# STORY-024 — Validação final do EPIC-004

> **Para o agente que vai executar (validador):** verificação de 1ª mão sobre o sha deployado. O produto
> é um **veredito** e um relatório; a transição de status do épico é do PO. Autore o checklist na
> execução (template `docs/skills/po/templates/validation-checklist.md`) e execute item a item.

## Contexto (por que esta estória existe)

Fecha o épico com evidência independente de que o Coletador entra por uma porta de marca (Google ou
e-mail/senha), que as três áreas estão segmentadas e isoladas por guardas, e que não há resíduo de
inglês nem do logo do Laravel — tudo em homologação, sem confiar apenas nos testes dos autores.

## Critérios de aceite (alto nível — autorar checklist na execução)

- [ ] Checklist em `validation/checklist.md` e relatório em `validation/report.md`.
- [ ] STORY-019..023 com `status: done` no `index.json`; cada CA exercido por teste com asserção real.
- [ ] Verificado de 1ª mão sobre o sha deployado: **login por e-mail/senha** e **login por Google**
      funcionam; **cadastro** cria conta de Coletador; **recuperação de senha** funciona — em homologação.
- [ ] **Segmentação de áreas** verificada: Coletador barrado do Backoffice (403/redirect), Backoffice com
      entrada própria não anunciada, área B2B reservada — com E2E em browser real.
- [ ] **pt-BR** confirmado nas superfícies do épico: sem strings de scaffolding em inglês, sem logo do
      Laravel; mensagens de erro/validação em português; formatos brasileiros onde aplicável.
- [ ] Cobertura conforme quality-standards (≥ 80% geral; ≥ 98% no núcleo de contas/autorização).
- [ ] CI verde; **sem segredos versionados** (OAuth via secrets); LGPD mantida.
- [ ] ADRs/IDRs do épico indexados; `EPIC-004.validation_report` apontando o relatório.

## Fora de escopo

- Recomendar/decidir a transição de status do épico (é do PO).
- Consertar falhas encontradas — registrar e devolver ao PO.

## Padrões de qualidade exigidos

Validação segue `docs/skills/po/references/quality-standards.md` (a verificar), com ênfase em **§5.1
(pt-BR)**, **§4 (segurança/LGPD — segredos OAuth)** e **§1 (E2E dos fluxos de acesso e da barreira de
áreas)**.

## Dependências

- **Bloqueada por:** STORY-019, STORY-020, STORY-021, STORY-022, STORY-023.
- **Bloqueia:** — (o PO decide a transição do épico com base no veredito).
- **Pré-requisitos de ambiente:** homologação com o sha do épico deployado; usuário/área de teste semeados.

## Definição de Pronto (DoD)

- [ ] Relatório publicado com veredito; checklist preenchido item a item com evidência.
- [ ] `EPIC-004.validation_report` setado no `index.json`; `index.json` = `done` para esta estória.
- [ ] "Notas do agente" preenchidas. **Não** alterar o `status` do épico (é do PO).

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md` e a skill `validador`. Fronteira de papel: atualize
apenas `validation_report`; a transição EPIC-004 `in_review → done` é decisão do PO.

## Notas do agente (preenchido durante/após execução)

> Validação de 1ª mão sobre o sha `e1d97f0` (HEAD da `main` = sha deployado em homolog), 2026-07-05.
> Checklist autorado em `validation/checklist.md`; relatório completo em `validation/report.md`.

### Veredito

**APPROVED com pendências** (`approved_with_pending`). 0 fails bloqueantes; 1 fail não-bloqueante
(F-NB-1: CI sem scanner de dependências/segredos). Contagem: 18 pass, 6 pass com ressalva, 1 fail
não-bloqueante, 2 n/a. A transição de status do EPIC-004 é decisão do PO (não alterada aqui).

### Evidências

- Suíte local (1ª mão): **277 unit+feature, 1100 asserções, 0 falhas; cobertura global 95,0%**; núcleo de
  contas (`UpsertGoogleUser`) e de autorização (Gate `operar-saques`) a **100%**.
- E2E browser real (Dusk): **69/69** na reexecução (1ª execução 68/69 por flake de ordenação; teste
  isolado 5/5 — funcionalidade íntegra).
- CI verde na `main`: run **`28727597678`** (sha `e1d97f0`) success.
- Homolog 1ª mão (`quantah-homolog.34.39.229.117.sslip.io`): `/login` 200 (marca Quantah, pt-BR, sem logo
  Laravel), `/intelligence` 200 (B2B reservado), `/backoffice/saques` sem login → 302 `/login` (barreira ativa).
- Segredos: nenhum versionado; OAuth/SMTP via secrets injetados no deploy.
- ADR-010/ADR-011/IDR-010/DDR-004 indexados; "Notas do agente" completas em STORY-019..023.

### Ressalvas / limitações

- **F-NB-1 (não-bloqueante)**: `ci-cd.yml` sem análise de dependências vulneráveis nem detecção de segredos
  (quality-standards §2.2/§4); nenhum segredo versionado/vuln crítica observado por verificação manual.
- Ressalvas: cobertura de ramos de scaffolding Breeze fora do núcleo (<98%); flake de ordenação no Dusk;
  observabilidade (só health `/up`); `down()` da migração não exercido em homolog; sem spec LGPD consolidada
  para o perfil Google (introduzido sob ADR-010/PDR-003 aceitos pelo PO); promoção é push→homolog (não
  tag-based §2.2).
- Limitações: EPIC-004 estava `in_progress` (não `in_review`) — prossegui pois as 5 estórias de dependência
  estão `done` e o PO pediu a execução; login Google/e-mail *ao vivo em homolog* foi verificado por Alexandro
  (notas STORY-022), não reexecutado contra o Google real nesta sessão; sem acesso a dashboard de logs de
  homolog para auditar PII em log de forma exaustiva.
