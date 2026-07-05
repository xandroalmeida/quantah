---
epic_id: EPIC-005
slug: portas-de-entrada
title: Portas de entrada — landing B2C e landing B2B
wave: WAVE-2026-02
status: done
owner_role: po
created_at: 2026-07-04
updated_at: 2026-07-05
target_completion: 2026-08-08
---

# EPIC-005 — Portas de entrada

## Por que existimos (problema do usuário)

A plataforma não tem porta de entrada pública. Quem chega não encontra uma página que explique o que é o
Quantah, por que vale a pena coletar cupons, nem como entrar — e um potencial cliente B2B não tem para
onde olhar para entender a oferta de inteligência de preços ou manifestar interesse. Sem landing pages,
não há como divulgar, atrair Coletadores para o piloto, nem começar a formar uma lista de demanda B2B.

## Resultado esperado (outcome)

Ao fim deste épico, um **visitante entende a proposta do Quantah e tem um caminho claro de entrada**:
o Coletador vai da **landing B2C** para o cadastro/login (EPIC-004), e um interessado B2B deixa seu
contato pela **landing Quantah Intelligence** — tudo mobile-first e em pt-BR.

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: a landing B2C converte o clique no CTA em início de cadastro/login; a landing B2B
  registra um lead (nome, e-mail, empresa) que aparece no Backoffice — ambos verificáveis em homologação.
- Métrica de qualidade: páginas sobre o DS (sem cor/fonte fora dos tokens), a11y AA, 100% pt-BR;
  captação de lead com validação e persistência testada (feliz + inválido + duplicado).

## Entregável visível no fim do épico

- [x] **Landing B2C** pública (mobile-first, DS, pt-BR) com a proposta de valor ("Cada nota conta."),
      **CTA para entrar/cadastrar** (→ login do EPIC-004) e **CTA para a página B2B**.
- [x] **Landing B2B — Quantah Intelligence** pública com a proposta ("Do cupom ao insight.") e **CTA de
      captação de lead** (formulário: nome, e-mail, empresa).
- [x] Lead B2B **persistido e visível no Backoffice** (lista simples, sob o papel operacional).

## Fora de escopo (explicitamente)

- Login/área **B2B autenticada** — apenas captação de lead nesta onda (PDR-003).
- SEO avançado, blog, páginas institucionais além das duas landings.
- Automação de e-mail marketing / nurturing do lead (só captura + visibilidade no backoffice).

## Referências da especificação

- `docs/visao.md` §2 (problema/oportunidade), §10 (monetização), §11 (marca, taglines, arquitetura B2C/B2B).
- `docs/project-state/decisions/pdr/PDR-003-escopo-onda-2-de-poc-a-produto.md`.
- `docs/project-state/design/system/` — DS, voz e tom.
- `docs/skills/po/references/quality-standards.md` §5 (a11y), **§5.1 (pt-BR)**.
- ADR-009 (RBAC) — a lista de leads vive atrás do papel operacional.

## Dependências

- **Bloqueia:** nada (é uma face pública; o piloto depende dela para divulgação).
- **Bloqueado por:** EPIC-004 (o CTA da landing B2C aponta para o login de marca). Pode iniciar em
  paralelo ao fim do EPIC-004, mas fecha depois que o login existir.
- **Decisões arquiteturais necessárias:** nenhuma nova prevista (reusa o modelo de dados/persistência e
  o RBAC existentes). Se o spike do EPIC-004 mudar o roteamento das áreas, respeitar.

## Estórias

Decompostas no Fluxo B (2026-07-05). Estórias verticalmente fatiadas; a última é a validação. EPIC-005 e
EPIC-006 destravaram com o EPIC-004 `done` e podem correr em paralelo (face pública × experiência logada).

- [x] **STORY-025** — landing B2C ("Cada nota conta."), CTA de entrada (→ login do EPIC-004) + CTA para o
      B2B, mobile-first, pt-BR (`requires_design`). Bloqueia STORY-028.
- [x] **STORY-026** — landing B2B (Quantah Intelligence, "Do cupom ao insight.") com captação de lead
      (nome, e-mail, empresa) → validação + persistência + deduplicação (`requires_design`, LGPD).
      Bloqueia STORY-027 e STORY-028.
- [x] **STORY-027** — lista de leads B2B no Backoffice, sob o papel operacional (RBAC do ADR-009).
      Bloqueada por STORY-026. Bloqueia STORY-028.
- [x] **STORY-028** (validação) — Validação final do épico (`target_role: validador`). Bloqueada por
      025–027. Veredito **APPROVED** (relatório + checklist).

### Ordem sugerida e paralelismo

STORY-025 e STORY-026 são independentes entre si (podem começar em paralelo); STORY-027 espera STORY-026
(consome o lead persistido); STORY-028 fecha o épico. O CTA-B2B da STORY-025 tem o destino preenchido pela
STORY-026.

## Validação final

Critérios em `validation/checklist.md`. Relatório do validador em `validation/report.md`.

**Definição de épico concluído:** todas as estórias `done` + validação `approved` + as duas landings
vivas em homologação, CTA B2C levando ao login e lead B2B capturado e visível no backoffice.

## Histórico

- 2026-07-04 — criado por PO (Fluxo A, WAVE-2026-02). Status `draft` — decompor após EPIC-004.
- 2026-07-05 — **decomposto (Fluxo B)** por PO, após EPIC-004 `done`. 4 estórias criadas: STORY-025
  (landing B2C), STORY-026 (landing B2B + captação de lead), STORY-027 (lista de leads no Backoffice),
  STORY-028 (validação). Épico → `ready`. STORY-025/026 prontas para começar (independentes); 027 espera
  026; 028 é a validação final.
- 2026-07-05 — **EPIC-005 `done`** (PO / Alexandro): épico fechado com base no veredito **APPROVED** da
  STORY-028 (33 pass, 4 pass com ressalva, 0 fail, 1 n/a; 4 ressalvas factuais não-bloqueantes). Todas as
  estórias 025–028 `done`; validação `approved` sobre o sha `da7e2a0` em homologação (as duas landings e a
  lista de leads no Backoffice vivas; loop lead→captura→Backoffice verificado de 1ª mão; suíte 295/295,
  cobertura 95,2%, núcleos a 100%; Dusk 11/11; CI verde). Corrigida a inconsistência da STORY-028
  (frontmatter `draft`→`done`, alinhando ao `index.json`). Commit de fechamento mantido local por decisão
  do PO (evita disparar deploy de homologação por commit só de docs) — sem push.
