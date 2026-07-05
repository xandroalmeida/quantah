---
epic_id: EPIC-006
type: validation-checklist
created_at: 2026-07-05
authored_by: validador (sessão claude-story032) — autorado na execução por instrução da STORY-032
---

# Checklist de validação — EPIC-006 (Jornada do Coletador)

> Para o **validador**: execute cada item em ordem. Para cada um, registre `pass | pass com ressalva | fail | n/a`
> e evidência (hash, link, log, output, observação). Não invente resultados. Em falha, **não conserte** —
> registre e devolva ao PO. Instanciado do template `docs/skills/po/templates/validation-checklist.md` com os
> CAs concretos das STORY-029/030/031 e os padrões transversais (pt-BR §5.1, a11y §5, DS PDR-001, LGPD §4).

## 1. Critérios de aceite das estórias

- [ ] 1.1 — STORY-029, 030 e 031 com `status: done` no `index.json`.
- [ ] 1.2 — **029/CA-1**: destino pós-login (e-mail/senha, Google, verificação de e-mail) é a home-hub, não página genérica.
- [ ] 1.3 — **029/CA-2**: home-hub exibe o saldo real da carteira em formato BR (R$ 0,00).
- [ ] 1.4 — **029/CA-3**: CTA primário "Coletar cupom" leva ao fluxo de captura (`/coletar`).
- [ ] 1.5 — **029/CA-4**: DS (saldo em `card.feature-dark`, verde único accent, `brand.mark`), mobile-first, pt-BR i18n, sem logo Laravel.
- [ ] 1.6 — **029/CA-5**: home-hub só na área autenticada — anônimo → `/login`.
- [ ] 1.7 — **029/CA-6**: a11y AA (contraste; foco por teclado; `brand.mark`/ícones decorativos `aria-hidden`).
- [ ] 1.8 — **030/CA-1**: atalho histórico/extrato abre o extrato em **≤ 2 toques** da home.
- [ ] 1.9 — **030/CA-2**: atalho prêmios/saque abre a tela de solicitar saque em **≤ 2 toques** da home.
- [ ] 1.10 — **030/CA-3**: de qualquer tela (coleta, extrato, saque), retorno consistente à home (sem beco).
- [ ] 1.11 — **030/CA-4**: nenhuma rota logada exibe página genérica/scaffolding (sem dashboard genérico, sem logo Laravel).
- [ ] 1.12 — **030/CA-5+CA-6**: navegação no DS, mobile-first, pt-BR; a11y AA (foco, alvos ≥48px, contraste).
- [ ] 1.13 — **031/CA-1**: após coleta válida que gera crédito, o saldo da home reflete o novo crédito sem novo login.
- [ ] 1.14 — **031/CA-2**: a coleta recém-creditada aparece no extrato da carteira.
- [ ] 1.15 — **031/CA-3**: com saldo suficiente, iniciar saque é alcançável e a tela fica pronta para prosseguir.
- [ ] 1.16 — **031/CA-4+CA-5**: coletar e saldo/histórico ≤2 toques; E2E browser real (mobile) da jornada ponta a ponta.
- [ ] 1.17 — **031/CA-6**: jornada em pt-BR e no DS; nenhuma etapa cai em página genérica.
- [ ] 1.18 — Cada CA acima é exercido por ≥1 teste automatizado com **asserção real** (sem skip, sem nome-só).

## 2. Cobertura de testes

- [ ] 2.1 — Cobertura do código novo do épico ≥ **80%**.
- [ ] 2.2 — Núcleo/regra tocado (leitura de saldo `ExtratoCarteira`; crédito `CreditarCashbackService`) ≥ **98%**.
- [ ] 2.3 — E2E em browser real (mobile) cobrindo os fluxos tocados (home-hub, navegação, jornada ponta a ponta).
- [ ] 2.4 — Testes cobrem além do caminho feliz (guarda de acesso, estado zero, retorno, ausência de scaffolding).

## 3. Automação

- [ ] 3.1 — Setup local automatizado (um comando: `make up`).
- [ ] 3.2 — Pipeline CI verde na branch principal após o épico.
- [ ] 3.3 — Deploy automático para homologação disparado pelo pipeline (só na main, com tudo verde).
- [ ] 3.4 — Deploy para produção automatizado (mesmo gated) — verificar aplicabilidade nesta fase MVP.
- [ ] 3.5 — Provisionamento dos ambientes via Infra-as-Code.

## 4. Funcionalidade observável (1ª mão em homologação)

- [ ] 4.1 — A home-hub e as telas da jornada estão acessíveis em homologação no sha deployado.
- [ ] 4.2 — Jornada ponta a ponta (entrar → home-hub → coletar → saldo atualiza → extrato → iniciar saque) percorrida em browser real.
- [ ] 4.3 — A rota antiga genérica (`/dashboard`) não existe mais; nenhuma rota logada acessível sem auth.
- [ ] 4.4 — Saúde básica (health endpoint) coletada.

## 5. Qualidade transversal

- [ ] 5.1 — **pt-BR** (§5.1) em toda a jornada; sem resíduo de scaffolding em inglês; formatos BR (R$).
- [ ] 5.2 — **a11y AA** e **mobile-first** (§5): contraste, foco por teclado, alvos ≥48px, `aria-current`/labels.
- [ ] 5.3 — Só **tokens do DS** (verde como único accent de CTA; `card.feature-dark` pontual).
- [ ] 5.4 — **LGPD** (§4): o épico não coleta dado pessoal novo (composição de telas existentes); saldo/extrato sem PII de pagamento nova.
- [ ] 5.5 — Migração de banco introduzida pelo épico — verificar (esperado: nenhuma; reusa esquema existente).
- [ ] 5.6 — Nenhum segredo versionado introduzido pelo épico.

## 6. Documentação e decisões

- [ ] 6.1 — Decisões do épico (DDR-007, IDR-011) e aplicáveis (DDR-004/005, ADR-010/011, IDR-010, PDR-003) indexadas no `index.json`.
- [ ] 6.2 — "Notas do agente" preenchidas em STORY-029/030/031.
- [ ] 6.3 — `design.screens[]` das telas do épico (SCREEN-029, SCREEN-030) presentes e consistentes (`shipped`).

## 7. Veredito

- [ ] **APPROVED** — todos `pass`/`pass com ressalva`/`n/a` justificado.
- [ ] **REJECTED** — pelo menos um `fail` bloqueante.

Relatório final em `report.md`.
