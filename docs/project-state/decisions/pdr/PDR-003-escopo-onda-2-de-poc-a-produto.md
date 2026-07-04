---
pdr_id: PDR-003
slug: escopo-onda-2-de-poc-a-produto
title: Escopo da Onda 2 — de POC a produto (porta de entrada, identidade e jornada B2C)
status: accepted
decided_at: 2026-07-04
decided_by: PO (Alexandro / Claude)
supersedes: null
superseded_by: null
related_epics: [EPIC-004, EPIC-005, EPIC-006]
related_adrs: []
---

# PDR-003 — Escopo da Onda 2 (de POC a produto)

## Contexto

A WAVE-2026-01 provou o **mecanismo**: o loop cupom → saldo funciona em homologação (coleta,
validação/dedup, cashback, carteira, saque). Mas a plataforma ainda tem **cara de POC** — não há porta
de entrada pública, o login exibe o logo do Laravel, os três públicos (Coletador B2C, cliente B2B,
operação interna) não estão segmentados, e o Coletador logado cai numa página genérica em vez de uma
jornada. Sem essa camada de produto, não dá para rodar um piloto real com usuários — e sem piloto não
há baseline da north-star.

## Opções consideradas

### Opção 1 — Uma onda focada em B2C, "de POC a produto"
- Descrição: uma onda com três épicos — acesso/identidade + segmentação de áreas (com login Google e
  e-mail/senha no padrão visual), portas de entrada (landing B2C e landing B2B com captação de lead), e
  a jornada B2C completa pós-login (home-hub mobile-first). pt-BR como requisito transversal.
- Prós: entrega uma "cara de produto" coerente e um funil ponta a ponta (visitante → cadastro →
  jornada), habilitando o piloto; mantém foco no lado que gera o dado (B2C).
- Contras: toca autenticação, marca e várias telas — escopo largo para uma onda.

### Opção 2 — Duas ondas (B2C agora, B2B depois)
- Descrição: adiar a landing B2B e a captação de lead para uma onda seguinte.
- Prós: onda menor. Contras: a face B2B é barata (uma landing + um formulário) e é o que abre canal de
  demanda; adiá-la não acelera o B2C de forma relevante.

### Opção 3 — Status quo (nada mudar)
- Consequência: a plataforma continua com cara de POC; não é apresentável a coletadores nem a clientes;
  o piloto e o baseline da north-star ficam bloqueados.

## Decisão

> **Optamos pela Opção 1.** Uma onda (WAVE-2026-02) — "de POC a produto" — com foco no B2C, entregando
> porta de entrada pública, identidade/acesso do Coletador e a jornada completa pós-login, mais uma
> landing B2B com captação de lead. Localização pt-BR é requisito transversal da onda.

Decisões de produto que acompanham este escopo (definidas com o PO em 2026-07-04):

- **Login B2C:** Google **e** e-mail/senha (cobertura de público acima da fricção mínima).
- **B2B nesta onda:** apenas **captação de lead/waitlist** (formulário da landing → backoffice). **Sem
  login/conta B2B** ainda — a área B2B fica reservada na arquitetura de segmentação, sem features.
- **Jornada B2C:** **home-hub** pós-login (mobile-first) que costura coleta, saldo/carteira, histórico e
  prêmios/saque — substituindo a página genérica atual.
- **Três áreas:** Coletador (B2C, autenticado), Quantah Intelligence (B2B, público + lead por ora) e
  Backoffice Operacional (autenticado + RBAC, **sem CTA público**). B2C e B2B têm CTA nas landings;
  Backoffice não é anunciado.
- **Marca:** seguir a arquitetura da visão §11.3 — **Quantah** (app B2C, "Cada nota conta.") e **Quantah
  Intelligence** (B2B, "Do cupom ao insight.").

## Justificativa

O mecanismo já existe; o gargalo agora é **produto e distribuição**. Uma porta de entrada clara, um
cadastro de baixa fricção e uma jornada coesa são a condição para converter visitantes em Coletadores
ativos e sustentar a coleta — exatamente o que a north-star ("cupons válidos, únicos e novos por
semana") precisa para sair de zero. Manter o foco no B2C respeita a assimetria do modelo: o dado nasce
do lado da coleta; o B2B é canal de demanda que, nesta fase, só precisa de uma vitrine e uma lista de
interessados.

## Consequências

### Positivas
- Plataforma apresentável e um funil B2C ponta a ponta em homologação — destrava o piloto e o primeiro
  baseline da north-star.
- Canal de demanda B2B aberto (lista de leads) a custo baixo.
- Dívida de idioma (strings de scaffolding em inglês) eliminada com o requisito pt-BR.

### Negativas / trade-offs aceitos
- Onda de escopo largo (auth + marca + várias telas); mitigada pela sequência (acesso/áreas primeiro) e
  por reusar coleta/carteira/saque da Onda 1.
- B2B autenticado adiado — aceito: não há features B2B para justificar a área logada agora.

### Para o time técnico
- ADRs que esta decisão pode demandar: **login social Google + modelo de contas e verificação**,
  **segmentação de áreas/guards das 3 faces**, **mecanismo de i18n (pt-BR)** — a abrir via **spike**
  (`target_role: arquiteto`) no início do EPIC-004.
- Impacto em épicos: EPIC-004 (acesso e áreas), EPIC-005 (portas de entrada), EPIC-006 (jornada B2C).

## Sinais de revisão

- Se o piloto mostrar que a fricção de cadastro não é o gargalo (e sim descoberta/aquisição),
  repriorizar a próxima onda para growth/aquisição em vez de mais jornada.
- Se aparecer demanda B2B concreta (lead quente pedindo acesso), antecipar a área B2B autenticada.
