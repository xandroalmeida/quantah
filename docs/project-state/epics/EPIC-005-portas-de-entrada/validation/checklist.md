---
epic_id: EPIC-005
type: validation-checklist
created_at: 2026-07-05
authored_by: validador (sessão story-028) — autorado na execução por instrução da STORY-028
---

# Checklist de validação — EPIC-005 (Portas de entrada)

> Para o **validador**: execute cada item em ordem. Para cada um, registre `pass | pass com ressalva | fail | n/a`
> e evidência (hash, link, log, output, observação). Não invente resultados. Em falha, **não conserte** —
> registre e devolva ao PO. Instanciado do template `docs/skills/po/templates/validation-checklist.md` com os
> CAs concretos das STORY-025/026/027 e os padrões transversais do épico (pt-BR §5.1, a11y §5, LGPD §4, DS PDR-001).

## 1. Critérios de aceite das estórias

- [ ] 1.1 — STORY-025, 026 e 027 com `status: done` no `index.json`.
- [ ] 1.2 — **STORY-025/CA-1**: rota pública `/` serve a landing B2C sem redirect ao login (anônimo e autenticado).
- [ ] 1.3 — **STORY-025/CA-2**: DS (tokens; verde como único accent), sem resíduo de scaffolding (sem logo/Hello do Laravel), 100% pt-BR incl. "Cada nota conta.".
- [ ] 1.4 — **STORY-025/CA-3**: CTA primário leva ao login/cadastro (EPIC-004).
- [ ] 1.5 — **STORY-025/CA-4**: CTA B2B leva à landing Quantah Intelligence (`/intelligence`).
- [ ] 1.6 — **STORY-025/CA-5+CA-6**: mobile-first + a11y AA; E2E em browser real (mobile) anônimo → proposta pt-BR → CTA → /login.
- [ ] 1.7 — **STORY-026/CA-1**: rota pública `/intelligence` serve a landing B2B (DS, pt-BR, "Do cupom ao insight.", tom analítico).
- [ ] 1.8 — **STORY-026/CA-2**: lead válido (nome, e-mail, empresa) é persistido + confirmação pt-BR.
- [ ] 1.9 — **STORY-026/CA-3**: envio inválido (campo ausente / e-mail inválido) bloqueia por campo em pt-BR, **sem persistir**.
- [ ] 1.10 — **STORY-026/CA-4**: e-mail duplicado é idempotente (não cria 2º lead, não sobrescreve, não vaza terceiro).
- [ ] 1.11 — **STORY-026/CA-5+CA-6**: mobile-first + a11y AA; E2E browser real feliz + ao menos um desvio.
- [ ] 1.12 — **STORY-027/CA-1+CA-2**: Backoffice lista leads (nome, e-mail, empresa, data) sob o papel operacional.
- [ ] 1.13 — **STORY-027/CA-3**: usuário sem papel (Coletador → 403; anônimo → redirect /login) é barrado.
- [ ] 1.14 — **STORY-027/CA-4**: estado vazio (pt-BR) + ordenação determinística (mais recentes primeiro).
- [ ] 1.15 — **STORY-027/CA-5**: E2E browser real — operador vê lead recém-capturado; sem papel é barrado.
- [ ] 1.16 — Cada CA acima é exercido por ≥1 teste automatizado com **asserção real** (não só nome parecido, sem skip).

## 2. Cobertura de testes

- [ ] 2.1 — Cobertura geral do código novo ≥ **80%**.
- [ ] 2.2 — Núcleo da regra do lead (`CapturarLead`) ≥ **98%**.
- [ ] 2.3 — Núcleo da guarda de acesso (Backoffice/RBAC ADR-009) ≥ **98%**.
- [ ] 2.4 — E2E em browser real cobrindo os 3 fluxos (B2C→login, captação B2B, lista Backoffice + barreira).
- [ ] 2.5 — Testes cobrem caminho feliz + inválido + duplicado + bordas (não só feliz).

## 3. Automação

- [ ] 3.1 — Setup local automatizado (um comando: `make up`).
- [ ] 3.2 — Pipeline CI verde na branch principal após o épico.
- [ ] 3.3 — Deploy automático para homologação disparado pelo pipeline (só na main, com tudo verde).
- [ ] 3.4 — Deploy para produção automatizado (mesmo gated) — **verificar aplicabilidade nesta fase MVP**.
- [ ] 3.5 — Provisionamento dos ambientes via Infra-as-Code (não cliques manuais).

## 4. Funcionalidade observável (1ª mão em homologação)

- [ ] 4.1 — As duas landings e a lista do Backoffice estão acessíveis em homologação no sha deployado.
- [ ] 4.2 — Fluxo E2E percorrido de 1ª mão: landing B2C pública → login; captação B2B (válido/duplicado/inválido); lead visível no Backoffice sob papel operacional; anônimo barrado.
- [ ] 4.3 — Saúde/observabilidade básica coletada (health endpoint / métricas internas).

## 5. Qualidade transversal

- [ ] 5.1 — **pt-BR** confirmado nas superfícies do épico (sem resíduo de scaffolding em inglês; erros/validação em pt-BR).
- [ ] 5.2 — **a11y AA** e **mobile-first** verificados nas duas landings (contraste, foco, alvo de toque, rótulos).
- [ ] 5.3 — Só **tokens do DS** (sem cor/fonte fora dos tokens).
- [ ] 5.4 — **LGPD** (§4): PII do lead restrita ao papel operacional; duplicado sem vazamento; sem PII em log; minimização + aviso de privacidade.
- [ ] 5.5 — Migração de banco (`leads`) reversível e testada em homologação.
- [ ] 5.6 — Nenhum segredo versionado introduzido pelo épico.

## 6. Documentação e decisões

- [ ] 6.1 — Decisões do épico (DDR-005, DDR-006) e ADRs aplicáveis (ADR-009, ADR-011, PDR-003, IDR-010) indexadas no `index.json`.
- [ ] 6.2 — "Notas do agente" preenchidas em STORY-025/026/027.
- [ ] 6.3 — `design.screens[]` das telas do épico presentes/consistentes.

## 7. Veredito

- [ ] **APPROVED** — todos `pass`/`pass com ressalva`/`n/a` justificado.
- [ ] **REJECTED** — pelo menos um `fail` bloqueante.

Relatório final em `report.md`.
