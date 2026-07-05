---
epic_id: EPIC-004
type: validation-checklist
created_at: 2026-07-05
authored_by: validador (sessão 2026-07-05)
source_template: docs/skills/po/templates/validation-checklist.md
---

# Checklist de validação — EPIC-004 (Acesso e áreas)

> Autorado pelo validador na execução (o épico não trazia checklist prévio), a partir do template do PO e
> dos CAs concretos das estórias STORY-019..023. Para cada item: status `pass | pass com ressalva | fail |
> n/a` + evidência. Não consertar nada — registrar e devolver ao PO. Resultado consolidado em `report.md`.

## 1. Critérios de aceite das estórias

- [ ] 1.1 — STORY-019..023 com `status: done` no `index.json`.
- [ ] 1.2 — STORY-020 CA-1..CA-5 (mecanismo i18n, varredura pt-BR, validação pt-BR, formatos BR, E2E) exercidos por teste.
- [ ] 1.3 — STORY-021 CA-1..CA-6 (DS sem logo Laravel/pt-BR, cadastro, login+erro global, reset sem enumeração, placeholder Google, E2E jornada) exercidos por teste.
- [ ] 1.4 — STORY-022 CA-1..CA-5 (Google cria conta, vínculo por e-mail, erro/cancelamento pt-BR, segredos fora do versionamento, E2E) exercidos por teste.
- [ ] 1.5 — STORY-023 CA-1..CA-5 (segmentação/entradas, Coletador barrado 403, entrada não anunciada, B2B reservado, E2E barreira pt-BR) exercidos por teste.
- [ ] 1.6 — Cada teste cobre o CA de fato (asserção real, não só nome), sem `skip`.

## 2. Cobertura de testes

- [ ] 2.1 — Cobertura geral do código novo ≥ 80%.
- [ ] 2.2 — Núcleo de contas/autorização ≥ 98%.
- [ ] 2.3 — Há E2E cobrindo cada fluxo de usuário tocado (login e-mail/senha, cadastro, reset, Google, barreira de áreas, i18n).
- [ ] 2.4 — FE web: E2E roda em browser real (não simulado por unit).
- [ ] 2.5 — Testes cobrem caminho feliz + inválidos + exceções + bordas.

## 3. Automação

- [ ] 3.1 — Setup local automatizado (um comando).
- [ ] 3.2 — Pipeline CI verde na `main` após o épico.
- [ ] 3.3 — Deploy automático para homologação após merge.
- [ ] 3.4 — Deploy para produção automatizado (mesmo que gated).
- [ ] 3.5 — Provisionamento dos ambientes via Infra-as-Code.

## 4. Funcionalidade observável (homologação)

- [ ] 4.1 — Entregável do `epic.md` acessível em homologação (login de marca, Google, 3 áreas, pt-BR).
- [ ] 4.2 — Fluxo end-to-end percorrível em homologação (cadastro/login/Google/reset; barreira de áreas).
- [ ] 4.3 — Logs e métricas básicas (saúde da app) coletados.

## 5. Qualidade transversal

- [ ] 5.1 — Scanner de segurança/dependências do CI sem alerta crítico introduzido.
- [ ] 5.2 — Migrações reversíveis e testadas.
- [ ] 5.3 — LGPD: dado pessoal novo (perfil Google) alinhado com o PO / aviso de privacidade.
- [ ] 5.4 — Segredos: nenhum no código (OAuth/SMTP via secrets injetados).
- [ ] 5.5 — Logs sem PII/segredos.

## 6. Documentação

- [ ] 6.1 — README/documentação atualizada onde relevante.
- [ ] 6.2 — ADRs/IDRs do épico (ADR-010, ADR-011, IDR-010, DDR-004) indexados no `index.json`.
- [ ] 6.3 — "Notas do agente" preenchidas em cada estória.
- [ ] 6.4 — Diagramas atualizados quando aplicável.

## 7. Veredito

- [ ] **APROVADO** — todos `pass`/`pass com ressalva`/`n/a` justificado.
- [ ] **APROVADO com pendências** — só fails não-bloqueantes.
- [ ] **REPROVADO** — ao menos um fail bloqueante.

Resultado final em `report.md`.
