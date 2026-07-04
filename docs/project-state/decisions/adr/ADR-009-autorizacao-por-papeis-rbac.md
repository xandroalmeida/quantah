---
adr_id: ADR-009
slug: autorizacao-por-papeis-rbac
title: Autorização por papéis (RBAC) — controle de acesso ao backoffice
status: accepted  # proposed | accepted | superseded | rejected | deferred
decided_at: 2026-07-03
decided_by: arquiteto
approved_by: Alexandro
supersedes: null
superseded_by: null
related_adrs: [ADR-000, ADR-005, ADR-006]
related_pdrs: []
related_epics: [EPIC-003]
created_at: 2026-07-03
updated_at: 2026-07-03
---

# ADR-009 — Autorização por papéis (RBAC)

## Contexto

A STORY-017 (resgate/saque, ADR-005) introduz um **backoffice**: uma superfície administrativa onde um
**operador** faz o KYC mínimo, executa o PIX manualmente e marca o saque como pago. Isso cria, pela
primeira vez no Quantah, a necessidade de **distinguir tipos de usuário** e **restringir acesso** a
partes da aplicação — até aqui todo usuário autenticado era um Colaborador com os mesmos direitos.

O produto tem, por natureza, **múltiplos perfis** (`_project.md` §1): Colaborador (coleta), Analista B2B
(consome inteligência) e, agora, papéis operacionais internos (operador de saque; futuramente admin). A
`security-architecture.md` do Arquiteto pede segregação de interface para superfícies administrativas
(cruza com ADR-005: "backoffice precisa de auth forte e audit log"). Precisamos de um modelo de
autorização que sirva o backoffice de saque **e** os perfis que virão, sem reescrever depois.

## Decisão

> **Adotamos autorização por papéis (RBAC): um usuário tem zero ou mais `roles`, e o acesso a recursos
> protegidos é concedido por papel, via Gates/Policies do Laravel.**

Modelo mínimo e extensível:

- Tabela **`roles`** (`nome` único — ex.: `operador`) + pivot **`role_user`** (N:N usuário↔papel).
- `User::hasRole('operador')` e um **Gate** (`operar-saques`) que autoriza quando o usuário tem o papel;
  as rotas de backoffice ficam atrás desse Gate (middleware `can:operar-saques`).
- O papel `operador` é semeado; a atribuição a um usuário é operação administrativa (seed/tinker no MVP).

A granularidade é **por papel** (RBAC clássico). **Não** introduzimos agora uma matriz de permissões
(`permissions`, `role_permission`) — seria especulação (YAGNI) para um único recurso protegido. Quando um
segundo eixo de permissão fino aparecer, evolui-se o modelo (papel → permissões) sem quebrar o contrato
`hasRole`/Gate.

## Opções consideradas

- **A — RBAC por papéis (escolhida):** `roles` + `role_user` + Gate. Padrão consagrado, nativo do Laravel
  (Gates/Policies), extensível aos perfis do produto. Custo baixo, sem dependência externa.
- **B — Flag booleana `is_operador` no usuário:** mais simples ainda, mas não modela os múltiplos perfis
  do produto (viraria uma coluna booleana por perfil) e não escala para Analista B2B/admin. Descartada por
  não acompanhar a natureza multi-perfil do Quantah.
- **C — Pacote de RBAC/permissões externo (ex.: spatie/laravel-permission):** robusto, mas traz matriz de
  permissões e dependência transversal que o MVP não precisa agora (princípio #1/#11). Reavaliável se o
  modelo de permissões ficar complexo — aí vira ADR própria.

## Justificativa

O produto é multi-perfil por construção; um modelo de **papéis** (não flags) é o piso honesto e é o que o
Laravel já suporta de forma idiomática (Gates/Policies) sem dependência nova. Fica extensível (adicionar
`analista_b2b`, `admin` é inserir linha em `roles`) e reversível para uma matriz de permissões se/quando
a complexidade justificar. A alternativa da flag economizaria pouco e cobraria caro na próxima superfície
protegida; o pacote externo antecipa complexidade sem demanda.

## Consequências

### Positivas
- Backoffice de saque (STORY-017) protegido por Gate desde o primeiro dia; audit-friendly (`processado_por`).
- Base de autorização pronta para os próximos perfis (Analista B2B, admin) sem retrabalho estrutural.
- Zero dependência externa; usa Gates/Policies nativos.

### Negativas / trade-offs aceitos
- Sem permissões finas agora — se surgir necessidade de "operador que só visualiza" vs "que paga", será
  preciso evoluir para papel→permissões (mudança aditiva, planejada).
- Atribuição de papel é operação administrativa manual no MVP (sem tela de gestão de usuários) — aceitável
  no piloto.

### Para o time
- **Padrão de autorização:** recurso restrito = **Gate/Policy checando papel**, nunca `if ($user->...)`
  espalhado (anti-padrão de RBAC caseiro — `stacks/laravel/SKILL.md`). Rotas administrativas atrás de
  `can:<ability>`.
- **Impacto em estórias:** STORY-017 materializa `roles`/`role_user`/Gate `operar-saques` + papel
  `operador`. Estórias futuras de perfis reusam este modelo.

## Aprovação humana

- **Status final:** ✅ aceita
- **Aprovado por:** Alexandro (direcionou a escolha de RBAC por papéis em sessão de Cowork, STORY-017).
- **Data:** 2026-07-03

## Histórico

- 2026-07-03 — criada e **aceita** (decisão direcionada por Alexandro na STORY-017).
