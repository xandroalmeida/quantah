# Onda em execução — WAVE-2026-02: De POC a produto (porta de entrada, identidade e jornada B2C)

- **Status:** active
- **Início:** 2026-07-04
- **Escopo decidido em:** PDR-003
- **North-star:** cupons NFC-e válidos, únicos e novos por semana (`../product/north-star.md`)
- **Onda anterior:** WAVE-2026-01 (closed) — retrospectiva em
  `../reports/status-2026-07-04-wave-2026-01-close.md`

## Objetivo de negócio

Transformar o **mecanismo provado na Onda 1** (loop cupom → saldo) em um **produto apresentável**: uma
porta de entrada pública (landing pages), identidade e acesso próprios do Coletador, segmentação dos três
públicos e uma **jornada B2C completa pós-login** — tudo em pt-BR. É a camada que viabiliza um **piloto
real** com usuários e, com ele, o **primeiro baseline** da north-star.

## Hipótese que estamos validando

> Com uma porta de entrada clara (landing), cadastro/login de baixa fricção (Google **e** e-mail/senha) e
> uma jornada mobile coesa pós-login, convertemos visitantes em **Coletadores ativos** e sustentamos a
> coleta — condição para a north-star sair de zero. O lado B2B, nesta fase, só precisa de vitrine e
> **lista de interessados**.

## Épicos da onda (em ordem)

| # | Épico | Status | Outcome | Critério de pronto (observável) |
|---|---|---|---|---|
| 1 | **EPIC-004 Acesso e áreas** | ✅ done | Identidade de acesso do Coletador + 3 áreas segmentadas | Coletador entra por login de marca (Google ou e-mail/senha), em pt-BR, sem logo do Laravel; áreas B2C/B2B/Backoffice separadas por guardas — em homologação. Validado 2026-07-05 (`approved_with_pending`; F-NB-1 → WISH-004). |
| 2 | **EPIC-005 Portas de entrada** | ✅ done | Landing B2C e landing B2B (Quantah Intelligence) | Visitante entende a proposta; CTA B2C leva ao login; lead B2B capturado e visível no backoffice — em homologação. Validado 2026-07-05 (`approved`; 4 ressalvas não-bloqueantes → WISH-005/006 e reforço a WISH-001). |
| 3 | **EPIC-006 Jornada do Coletador** | ✅ done | Home-hub e fluxo completo pós-login (mobile) | Coletador logado chega à home-hub e percorre coletar → saldo → extrato → saque, sem página genérica — em homologação. Validado 2026-07-05 (`approved`; 6 ressalvas não-bloqueantes R1–R6). |
| 4 | **EPIC-007 Refinamento B2C mobile** | 🔜 ready | Casca app-like + cupom detalhado + ajustes de jornada | No celular: `nav.bottom` fixo em todas as telas, detalhe do cupom com itens em 1 toque, cupom fora da janela rejeitado, sem excluir conta, menu de escanear renomeado — em homologação. Decomposto 2026-07-05 (STORY-033/034/035/036 + validação 037). |

## Justificativa da sequência

**EPIC-004 primeiro** porque tudo se liga a ele: as landings (EPIC-005) precisam de um login real para o
CTA, e a jornada (EPIC-006) exige o Coletador autenticado e as áreas segmentadas. Estabelecer aqui também
o **mecanismo de i18n** e varrer as superfícies existentes para pt-BR paga a dívida de idioma cedo. Depois
que o EPIC-004 fecha, **EPIC-005 e EPIC-006 podem correr em paralelo** (uma é a face pública, a outra a
experiência logada), reusando coleta/carteira/saque da Onda 1.

## Requisito transversal desta onda

- **Localização pt-BR** — todo texto visível em português do Brasil, sem resíduo de scaffolding em inglês
  nem logo do Laravel; formatos brasileiros (R$, dd/mm/aaaa, America/Sao_Paulo). Registrado em
  `../../skills/po/references/quality-standards.md` §5.1 e cobrado no DoD de cada épico.
- Padrões de PDR-001 seguem valendo: telas sobre o DS (só tokens do sistema), verde como único accent de
  CTA, mobile-first, a11y AA.

## Decisões de produto que acompanham o escopo (PDR-003)

- **Login B2C:** Google **e** e-mail/senha.
- **B2B nesta onda:** captação de lead/waitlist (sem login/conta B2B).
- **Jornada B2C:** home-hub pós-login (mobile-first).
- **Marca:** Quantah (app B2C) + Quantah Intelligence (B2B), conforme visão §11.3.

## Decisões arquiteturais que a onda vai demandar (via spike)

Um **spike no início do EPIC-004** (`target_role: arquiteto`) produz o(s) ADR(s) para: **login social
Google (OAuth)** + modelo de contas/verificação, **segmentação de áreas e guardas** das 3 faces, e
**mecanismo de i18n (pt-BR)**. EPIC-005 e EPIC-006 não preveem novos ADRs (reusam o que existe).

## Metas / medição

Esta onda é sobre **habilitar o piloto**, não sobre bater número de north-star ainda. Marcos:

- Funil B2C ponta a ponta vivo em homologação (visitante → cadastro → home-hub → coleta → saldo → saque).
- Lista de leads B2B começando a existir.
- Ao fim da onda, condições dadas para rodar o **piloto** e coletar o **primeiro baseline** da north-star.

## Riscos da onda (ver status report de abertura)

Escopo largo (auth + marca + várias telas); dependência externa do OAuth Google (mitigação: spike cedo,
e-mail/senha como caminho paralelo); dívida transversal carregada da Onda 1 (scanner de segredos/deps no
CI, observabilidade RED) que pode ou não entrar nesta onda.

## Próximo passo

**EPIC-004, EPIC-005 e EPIC-006 fechados** (`done`, validados 2026-07-05). **EPIC-007 adicionado à onda e
decomposto** (Fluxos A+B, 2026-07-05) e em `ready`: refinamento da experiência B2C mobile a partir de 9
ajustes do teste no celular (Alexandro), **consolidados em 4 frentes** + validação — STORY-033 (casca mobile
app-like) · STORY-034 (cupom: estabelecimento/data na listagem + tela de detalhe com itens) · STORY-035
(validade parametrizável na coleta) · STORY-036 (remover excluir conta + renomear menu de escanear) →
STORY-037 (validação). É o **último épico da onda** — fechá-lo completa a WAVE-2026-02 e habilita o piloto
com menos atrito. STORY-034 é `requires_design` (brief em `EPIC-007/design-handoff.md`): Designer e
Programador pegam juntas pelo modelo paralelo. Ordem sugerida: 033 e 035 primeiro (baixo risco, isoladas),
034 em paralelo (maior, tem dado novo + tela), 036 rápida; 037 fecha. Possíveis IDRs em 034 (captura do
nome do emitente na extração SEFAZ + coluna `nome_emitente`) e 035 (semântica da janela de validade).
Housekeeping das ressalvas do EPIC-005 registrado na wishlist (WISH-005 chaves i18n no payload, WISH-006
dashboard de observabilidade; R3 reforça WISH-001; WISH-004 segue pendente). Commits de docs mantidos locais
por decisão do PO (sem push).
