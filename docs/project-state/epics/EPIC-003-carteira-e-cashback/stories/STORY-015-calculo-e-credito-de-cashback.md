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
status: in_review
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

### 2026-07-03 — Implementação concluída (in_review)

**Arquitetura entregue:** base de pagamento **segregada** (ADR-006): `carteiras` (saldo cache
≥0), `carteira_transacoes` (ledger append-only, fonte da verdade), `cupom_atribuicoes` (ponte
cupom→coletor). Crédito de 0,1% **meio-para-cima em centavos** disparado por evento de domínio
`CupomValidado` → listener enfileirado idempotente → `CreditarCashbackService` (crédito + saldo
na mesma transação, sob lock, idempotente por cupom). Coleta agora **autenticada**; cupom novo
atribuído ao Colaborador logado (só o 1º coletor — dedup ADR-003). Decisão de padrão em **IDR-008**.

**Cobertura do núcleo (regra de negócio):** `CalculadoraCashback` **100%**, `CreditarCashbackService`
**100%**, `CreditarCashbackAoValidar`/`CupomValidado` 100% — acima da meta de 98%. Suíte completa:
**179 PHPUnit + 43 Dusk** verdes.

**Mapa CA → teste:**

- **CA — crédito de 0,1% ao validar, idempotente por chave/cupom:**
  - `CalculadoraCashbackTest::test_mil_reais_credita_um_real` (feliz) · `test_arredonda_meio_para_cima`
    · `test_fracao_exatamente_meio_arredonda_para_cima` (borda) · `test_valor_pequeno_arredonda_para_zero`
    · `test_valor_zero_credita_zero` (borda) · `test_valor_negativo_e_invalido` (inválido) —
    `tests/Unit/Cashback/CalculadoraCashbackTest.php`.
  - `CreditarCashbackServiceTest::test_credita_a_carteira_do_coletor` (feliz) ·
    `test_credito_e_idempotente_por_cupom` (borda) · `test_sem_atribuicao_nao_credita` (inválido) ·
    `test_cupom_nao_validado_nao_credita` (inválido) · `test_credito_zero_nao_gera_transacao` (borda) —
    `tests/Feature/Cashback/CreditarCashbackServiceTest.php`.
  - `CashbackNaValidacaoTest::test_cupom_validado_credita_o_coletor` (feliz, ponta-a-ponta) ·
    `test_reprocessamento_nao_duplica_credito` (borda) · `test_cupom_rejeitado_nao_dispara_credito`
    (exceção) · `test_listener_ignora_cupom_inexistente` (exceção) —
    `tests/Feature/Cashback/CashbackNaValidacaoTest.php`.
- **CA — modelo carteira/transações append-only, saldo reconciliável, sem divergência:**
  - `CreditarCashbackServiceTest::test_reconciliacao_saldo_igual_soma_do_ledger` ·
    `test_saldo_negativo_e_barrado_pelo_banco` (invariante ≥0 no banco).
- **CA — arredondamento e moeda (centavos) definidos e testados:** todo o `CalculadoraCashbackTest`
  (incl. `test_converte_reais_para_centavos`, `test_converte_reais_com_um_decimal`,
  `test_reais_negativo_e_invalido`).
- **CA — telemetria: nº de Colaboradores com saldo > 0 mensurável:**
  `CreditarCashbackServiceTest::test_colaboradores_com_saldo_positivo_e_mensuravel` (scope
  `Carteira::comSaldoPositivo`).
- **Atribuição/auth (habilitador do crédito):**
  `ColetaControllerTest::test_coletar_exige_autenticacao` ·
  `test_captura_autenticada_atribui_o_cupom_ao_colaborador` ·
  `test_reenvio_por_outro_colaborador_nao_reatribui` (dedup) —
  `tests/Feature/Coleta/ColetaControllerTest.php`.
- **E2E (Dusk, browser real):** `ColetaCapturaTest::test_captura_por_link_valido_mostra_confirmacao`
  (feliz, autenticado, com atribuição) · `test_link_invalido_mostra_erro_no_campo` (erro) ·
  `test_camera_indisponivel_degrada_para_colar` (alternativo) · `test_anonimo_e_barrado_para_o_login`
  (exceção) — `tests/Browser/ColetaCapturaTest.php`.

**TDD evidenciado:** commits `test(CA-*): ... (vermelho)` precedem os `feat(CA-*): ... (verde)`
em cada ciclo (histórico da main).

**Decisões técnicas locais:** dinheiro em centavos via bcmath (sem float); `cupom_id` como
referência lógica (uuid, sem FK dura) entre bases; listener registrado explicitamente em
`AppServiceProvider` (fora de `app/Listeners`). Ver **IDR-008**.

**Homologação:** push na `main` (2026-07-03) → CI/CD verde (testes + Dusk + build) e **deploy
automático para homologação com smoke test passando** (run 28677419140). DoD de deploy satisfeito.

**Pendências para as próximas estórias (não bloqueiam esta):** a **tela** de carteira (saldo +
histórico) é a STORY-016; o **saque** (débito/estorno + backoffice) é a STORY-017 — o modelo já
prevê os tipos `debito_saque`/`estorno_saque` no CHECK, mas nenhuma lógica de saque foi implementada
aqui (fora de escopo).
