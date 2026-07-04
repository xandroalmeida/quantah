---
epic_id: EPIC-004
slug: acesso-e-areas
title: Acesso e áreas — identidade do Coletador e segmentação de públicos
wave: WAVE-2026-02
status: ready
owner_role: po
created_at: 2026-07-04
updated_at: 2026-07-04
target_completion: 2026-07-25
---

# EPIC-004 — Acesso e áreas

## Por que existimos (problema do usuário)

Hoje o Coletador entra por uma tela de login com o **logo do Laravel** e cai numa página genérica: o
produto parece um scaffolding, não o Quantah. Não há separação entre os três públicos (Coletador B2C,
cliente B2B, operação interna) — todos compartilham a mesma porta. E o cadastro só existe no formato
herdado (e-mail/senha em inglês), sem login social, o que adiciona fricção para o Colaborador Casual,
que é a maioria e precisa entrar em segundos.

Sem uma identidade de acesso própria e sem áreas segmentadas, não dá para ligar landing pages a um
destino real, nem construir uma jornada B2C coesa, nem evoluir o B2B depois sem retrabalho.

## Resultado esperado (outcome)

Ao fim deste épico, o **Coletador entra no Quantah por uma tela de marca** — com **Google ou
e-mail/senha** — e cada público (Coletador, B2B, Backoffice) tem sua **porta de entrada segmentada**,
tudo em **pt-BR**.

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: um novo Coletador conclui cadastro e login (Google ou e-mail/senha) e chega
  autenticado à sua área, em homologação, sem ver nenhuma string de scaffolding em inglês nem o logo do
  Laravel.
- Métrica de qualidade: núcleo de autenticação/autorização coberto por testes (incl. E2E de login
  Google simulado, login e-mail/senha, e barreira de área — usuário de uma área não acessa a outra);
  zero texto de interface fora do mecanismo de i18n na superfície entregue.

## Entregável visível no fim do épico

- [ ] Tela de **login/cadastro do Coletador no padrão visual do DS** (sem logo do Laravel), em pt-BR,
      acessível na URL de homologação.
- [ ] **Login com Google** e **login com e-mail/senha** funcionando (cadastro, entrada, recuperação de
      senha) para o Coletador, em homologação.
- [ ] **Três áreas segmentadas** com pontos de entrada distintos: Coletador (B2C, autenticado), Quantah
      Intelligence (B2B, reservada — sem login ainda) e Backoffice Operacional (autenticado + RBAC, sem
      CTA público). Guardas garantem isolamento entre áreas.
- [ ] **Mecanismo de i18n (pt-BR)** estabelecido e as superfícies já existentes (login, vitrine `/ds`,
      carteira, backoffice de saques) **varridas para pt-BR**, sem resíduo de inglês.

## Fora de escopo (explicitamente)

- Login/conta **B2B** autenticado — reservado na arquitetura, mas sem features nesta onda (ver PDR-003).
- A **captação de lead B2B** em si (formulário) — entra no EPIC-005 (portas de entrada).
- A **home-hub e a jornada pós-login** — entra no EPIC-006.
- Outros provedores sociais além do Google.

## Referências da especificação

- `docs/visao.md` §4 (personas dos dois lados), §11.3 (arquitetura de marca app + B2B).
- `docs/project-state/decisions/pdr/PDR-003-escopo-onda-2-de-poc-a-produto.md` — escopo e decisões da onda.
- `docs/project-state/design/system/` — tokens, componentes, voz e tom (Designer).
- `docs/skills/po/references/quality-standards.md` §4 (segurança/LGPD), §5 (a11y), **§5.1 (pt-BR)**.
- ADR-009 (RBAC do backoffice) — base para as guardas de área.

## Dependências

- **Bloqueia:** EPIC-005 (a landing B2C precisa de um login real para o CTA) e EPIC-006 (a jornada exige
  o Coletador autenticado).
- **Bloqueado por:** nada (primeiro épico da onda).
- **Decisões arquiteturais necessárias:** **a criar via spike** (`target_role: arquiteto`) no início do
  épico — login social Google (OAuth) + modelo de contas/verificação, segmentação de áreas e guardas das
  3 faces, e escolha do mecanismo de i18n (pt-BR). Produz o(s) ADR(s) correspondente(s).

## Estórias

(Preenchido no Fluxo B. Começa por um **spike** de arquitetura; primeira estória de implementação é a
tela de login de marca; a última é a validação.)

- [ ] STORY-XXX (spike) — arquitetura de acesso: OAuth Google + contas + segmentação de áreas + i18n
- [ ] STORY-XXX — login/cadastro do Coletador no padrão visual (substitui o logo do Laravel), em pt-BR
- [ ] STORY-XXX — login com e-mail/senha (cadastro, entrada, recuperação) em pt-BR
- [ ] STORY-XXX — login com Google
- [ ] STORY-XXX — segmentação das 3 áreas + guardas + entrada do Backoffice
- [ ] STORY-XXX — fundação de i18n + varredura pt-BR das superfícies existentes
- [ ] STORY-XXX (validação) — Validação final do épico

## Validação final

Critérios em `validation/checklist.md`. Relatório do validador em `validation/report.md`.

**Definição de épico concluído:** todas as estórias `done` + relatório de validação `approved` +
Coletador entra por login de marca (Google ou e-mail/senha) em homologação, áreas segmentadas e sem
resíduo de inglês/Laravel.

## Histórico

- 2026-07-04 — criado por PO (Fluxo A, WAVE-2026-02). Status `ready` — próximo a decompor (Fluxo B).
