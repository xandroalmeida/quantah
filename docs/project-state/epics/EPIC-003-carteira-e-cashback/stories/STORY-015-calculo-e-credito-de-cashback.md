---
story_id: STORY-015
slug: calculo-e-credito-de-cashback
title: Cálculo e crédito automático de cashback (0,1%) sobre cupom válido
epic_id: EPIC-003
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: in_progress
owner_agent: claude-programador-story015
created_at: 2026-07-03
updated_at: 2026-07-03
estimated_session_size: L
---

# STORY-015 — Cálculo e crédito de cashback

> **Para o agente que vai executar (programador):** o foco é a **regra de negócio correta e
> auditável**, com cobertura alta (a `quality-standards.md` pede 98% no núcleo de cálculo).

## Contexto (por que esta estória existe)

É a **fundação do saldo**: quando um cupom entra na base como válido-único-novo (EPIC-002), o
Colaborador deve receber **R$ 1 a cada R$ 1.000** (0,1% do valor do cupom) creditado na carteira.
Sem este crédito, a carteira (STORY-016) e o saque (STORY-017) não têm o que exibir/movimentar.

## Critérios de aceite (alto nível — refinar na execução)

- Ao um cupom ser marcado válido-único-novo, um crédito de **0,1% do valor** é lançado na carteira
  do Colaborador, **idempotente por chave de acesso** (nunca credita o mesmo cupom duas vezes).
- Modelo de carteira/transações append-only (saldo derivado do extrato, reconciliável).
- Arredondamento e moeda definidos e testados (centavos; sem perda/dobro por arredondamento).
- **Reconciliação saldo × cupons sem divergência** (métrica de qualidade do épico).
- Cobertura ≥ 98% no núcleo da regra de cálculo/crédito.
- Telemetria: nº de Colaboradores com saldo > 0 (métrica de apoio) mensurável.

## Fora de escopo

- Tela da carteira (STORY-016) e saque (STORY-017).
- Estorno/ajuste manual de crédito (Onda 2, salvo decisão em contrário).

## Dependências

- **Bloqueado por:** EPIC-002 (crédito incide sobre cupom válido) — done.
- **Bloqueia:** STORY-016 (exibe saldo/histórico), STORY-017 (movimenta saldo), STORY-018.

## Definição de pronto

Regra implementada e coberta (≥98%); reconciliação sem divergência; `index.json` = `done`; IDR se
houve decisão técnica; Notas do agente preenchidas.

## Notas do agente

### 2026-07-03 — Leitura inicial completa (claude-programador-story015)

- **Documentos lidos:** STORY-015 (inteira), EPIC-003 (epic.md), **ADR-005** (modelo da carteira/ledger,
  invariante, idempotência, base segregada), ADR-006 (segregação de bases, referenciada), ADR-003 (dedup
  por chave), `_project.md`, disciplinas do programador (reading/testing/database), sub-skills
  laravel/postgres/database-method. Mapeei o fluxo de coleta do EPIC-002 (`IngestaoCupomService`,
  `ExtrairCupomJob`, `Cupom`).

- **Entendimento consolidado:** quando um cupom vira **válido-único-novo** (status `validado`, setado só
  em `IngestaoCupomService::normalizarEpersistir`), o Colaborador que o coletou recebe **0,1% do valor** em
  centavos, na sua **carteira**, via **ledger append-only** (fonte da verdade do saldo). Crédito
  **idempotente por cupom**; saldo é cache reconciliável (`saldo == SUM(ledger)`). A base de pagamento
  (carteiras/transações/atribuição) é **segregada** da analítica (ADR-006).

- **Lacuna encontrada e decisão do dono (Alexandro, 2026-07-03):**
  1. Não existia vínculo Colaborador↔cupom (base analítica sem PII; `/coletar` era **anônimo**). Decisão:
     **login obrigatório na coleta** — `/coletar` passa a exigir auth e cada cupom **novo** é atribuído ao
     Colaborador logado (`cupom_atribuicoes`, na base de pagamento). Dedup (ADR-003): só o **1º** coletor
     de uma chave é atribuído/creditado; reenvio de terceiro não reatribui.
  2. Arredondamento do 0,1%: **meio-para-cima** ao centavo (`intdiv(valorCentavos + 500, 1000)`).

- **Decisões técnicas (dentro do meu papel):**
  - **Dinheiro em centavos inteiros** (ADR-005). `cupons.valor_total` é `decimal(12,2)` reais → converto
    para centavos com **bcmath** (sem float).
  - Crédito disparado por **evento de domínio `CupomValidado`** (produzido pela fronteira de Coleta,
    pós-commit) consumido por um **listener enfileirado** `CreditarCashbackAoValidar` (retry/idempotência
    próprios) → `CreditarCashbackService`. Decouple Coleta↔Cashback + confiabilidade (o crédito não
    depende do sucesso da extração e reprocessa sozinho). Registrar em **IDR** (decisão de padrão).
  - Idempotência garantida por **índice único parcial** `carteira_transacoes(cupom_id) WHERE
    tipo='credito_cashback'` + guarda no serviço, sob **lock** da carteira (`FOR UPDATE`).

- **Dúvidas:** nenhuma (as duas ambiguidades foram decididas pelo dono).

- **Plano (5 bullets):**
  1. Migrations da base de pagamento (`carteiras`, `carteira_transacoes`, `cupom_atribuicoes`).
  2. Núcleo puro `CalculadoraCashback` (TDD, ≥98%).
  3. Models + gate auth em `/coletar` + atribuição na `capturar`.
  4. Evento `CupomValidado` + listener + `CreditarCashbackService` (crédito idempotente, reconciliável).
  5. Suíte completa verde + Dusk do fluxo autenticado + cobertura + IDR + roteiro.

- **Testes que pretendo escrever (mapa CA→teste, preenchido ao final):**
  - **CalculadoraCashback** (unit): feliz (R$1000→100c, R$87,90→9c), bordas (exato .5 arredonda p/ cima,
    valor que arredonda p/ 0, R$0), inválido (valor negativo → exceção).
  - **CreditarCashbackService** (feature/integração): credita 0,1% na carteira do coletor; **idempotente**
    (2ª chamada não duplica); **sem atribuição → no-op**; **reconciliação** saldo == SUM(ledger);
    invariante saldo ≥ 0; crédito zero não gera transação.
  - **Atribuição/auth** (feature): `/coletar` exige login (guest → redirect); cupom novo gera atribuição
    ao user; reenvio por 2º user não reatribui (dedup).
  - **Fluxo ponta-a-ponta** (feature): user autenticado envia cupom → job valida → carteira creditada;
    cupom sem atribuição (CLI) valida sem crédito; cupom rejeitado/falha não credita.
  - **E2E (Dusk):** coleta autenticada (caminho feliz) segue funcionando; guest é levado ao login.
