---
epic_id: EPIC-003
type: validation-checklist
created_at: 2026-07-04
---

# Checklist de validação — EPIC-003 (Carteira e cashback)

> Para o **validador** (STORY-018): execute cada item em ordem. Para cada um, registre status
> `pass | pass com ressalva | fail | n/a` e evidência (link, log, hash, screenshot) no `report.md`.
> Não invente resultados. Em caso de falha, **não tente consertar** — registre e devolva ao PO.
> Veredito segue `docs/skills/validador/references/verdict-criteria.md`.
>
> Autorado na execução da STORY-018 (CA: "autorar checklist na execução"). Derivado dos CAs das
> STORY-014..017, da "Definição de épico concluído" do `epic.md`, de `docs/visao.md` §5.1 e de
> `docs/skills/po/references/quality-standards.md`. O validador não edita este arquivo após iniciar
> a execução do relatório; só o `report.md`.

## 1. Critérios de aceite das estórias

- [ ] STORY-014 (spike), 015, 016 e 017 estão com `status: done` no `index.json`.
- [ ] Cada CA de cada `story.md` (014–017) foi exercido por ao menos um teste automatizado — a
      **asserção cobre o CA de fato** (não apenas nome parecido).
- [ ] **STORY-015** — crédito de **0,1% do valor** é lançado ao cupom virar válido-único-novo,
      **idempotente por cupom/chave** (nunca credita duas vezes); ledger append-only; arredondamento
      meio-para-cima em centavos definido e testado.
- [ ] **STORY-016** — carteira exibe **saldo em reais + histórico** (cupons válidos + créditos),
      compondo o DS; estados além do feliz (saldo zero / lista vazia); dados vêm do extrato (nada
      hardcoded).
- [ ] **STORY-017** — caminho de resgate (**PIX assistido**, ADR-005) existente; **débito idempotente
      e reconciliável** (sem duplo saque); estados de erro/pendência; máquina de estados
      assumir→aprovar→pagar e rejeitar→estorno; KYC mínimo (CPF = chave PIX).
- [ ] ADR-005 (escopo saque/PIX/KYC) e ADR-009 (RBAC do backoffice) existem, estão `accepted` e
      **indexados** em `decisions.adr`.

## 2. Cobertura de testes

- [ ] Cobertura de linhas do código novo do épico ≥ **80%** (evidência: relatório do CI/comando local).
- [ ] **Núcleo de cálculo de cashback** (`CalculadoraCashback` + `CreditarCashbackService`) ≥ **98%**
      — gate de qualidade do épico (evidência: relatório com números).
- [ ] **Núcleo do dinheiro do saque** (`SolicitarSaqueService`, `SaqueService`, `Cpf`) coberto no
      mesmo padrão (regra financeira = núcleo, `quality-standards.md` §1.1).
- [ ] Testes cobrem: caminho feliz + **crédito duplicado/idempotência** + **sem atribuição (no-op)** +
      **saldo insuficiente / abaixo do mínimo** + **estorno idempotente** + bordas de arredondamento —
      não só o feliz.
- [ ] Frontend web: há E2E em **browser real** (Dusk) cobrindo carteira (STORY-016) e saque
      (STORY-017), incluindo caminho de erro e o barramento por papel (403).

## 3. Automação

- [ ] Setup local automatizado (um comando a partir de clone limpo sobe o app) — declaração/execução
      (`make up`).
- [ ] Pipeline CI **verde** no push que publicou o épico em homologação.
- [ ] Deploy para homologação disparado **automaticamente** pelo pipeline (sem passo manual).
- [ ] **Listener de crédito enfileirado** (evento `CupomValidado` → `CreditarCashbackAoValidar`) roda de
      forma automatizada/idempotente (retry próprio), não como passo manual; exercido em teste.
- [ ] Migrações da base de pagamento (`carteiras`, `carteira_transacoes`, `cupom_atribuicoes`, `saques`,
      `roles`) reversíveis e idempotentes.

## 4. Funcionalidade observável

- [ ] **Carteira acessível em homologação (mobile), HTTP 200** — verificado de forma **independente**
      (requisição/checagem própria, não relato da estória). (STORY-016)
- [ ] Um cupom válido credita **0,1%** e o saldo cresce, visível na carteira em homologação. (STORY-015/016)
- [ ] **Reconciliação saldo × ledger sem divergência** demonstrável (saldo == SUM(ledger)). (STORY-015 CA)
- [ ] Caminho de **saque** (solicitar → backoffice conduz até pago; rejeitar estorna) existente em
      homologação, atrás do papel `operador`. (STORY-017)
- [ ] Logs e métricas básicas de saúde/uso coletados (sem dado pessoal). (quality-standards §3)

## 5. Qualidade transversal (LGPD + dados + segurança)

- [ ] **CPF do saque** (novo dado pessoal, KYC) é tratado com cuidado: **mascarado** na lista do
      backoffice, completo só no detalhe (KYC); validado por VO `Cpf`. Alinhado com ADR-005/ADR-006.
- [ ] **Nenhum vazamento de CPF** em logs, mensagens de erro, fixtures ou telemetria.
- [ ] Base de pagamento (carteiras/transações/atribuição/saques) **segregada** da analítica (ADR-006);
      crédito/extrato **não cruzam** a segregação (referência lógica `cupom_id`, sem FK dura).
- [ ] **Migrações de banco reversíveis** e testadas (base de pagamento nova).
- [ ] Nenhum aviso crítico de segurança aberto introduzido pelo épico; nenhum **segredo/credencial**
      versionado. Se o CI não tem scanner, registrar como **Limitação** e verificar por inspeção direta.
- [ ] **Autorização (ADR-009):** backoffice de saques atrás de `auth` + `can:operar-saques`; não-operador
      recebe 403 e não executa ação.

## 6. Documentação e estado

- [ ] Notas do agente preenchidas em STORY-014..017.
- [ ] ADRs (005/009) e IDRs (008/009) criados durante o épico indexados no `index.json`.
- [ ] Evidência do Designer presente para as estórias `requires_design: true` (016, 017) — specs
      `shipped` no `index.json`.
- [ ] `index.json` coerente: STORY-014..017 `done`; `validation_report` do EPIC-003 será preenchido
      por esta validação (STORY-018).

## 7. Veredito

- [ ] **APROVADO** — todos os itens `pass`, `pass com ressalva` ou `n/a` justificado.
- [ ] **REPROVADO** — ao menos um `fail` bloqueante (ver `verdict-criteria.md`).

Preencha o `report.md` com fatos, evidência e classificação de gravidade dos fails.
**Sem recomendação/planejamento** — isso é do PO.
