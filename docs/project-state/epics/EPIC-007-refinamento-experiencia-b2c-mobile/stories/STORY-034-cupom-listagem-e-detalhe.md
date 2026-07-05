---
story_id: STORY-034
slug: cupom-listagem-e-detalhe
title: Cupom com contexto — estabelecimento e data na listagem + tela de detalhe com itens
epic_id: EPIC-007
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: SCREEN-STORY-034-cupom-detalhe
status: done
owner_agent: claude-programador
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: L
---

# STORY-034 — Cupom com contexto (listagem + detalhe)

> **Para o agente que vai executar:** leia por inteiro. `requires_design: true` — Designer entrega a spec da
> **tela de detalhe do cupom** em paralelo (PDR-002); brief em `design-handoff.md`. Atenção ao dado novo:
> o **nome do estabelecimento não existe hoje** no modelo — só `cnpj_emitente`.

## Contexto (por que esta estória existe)

O histórico da Carteira mostra cada cupom como **"Cupom de R$ X" + data** — sem **onde** a compra foi feita e
sem como **abrir o cupom** para ver seus itens. O Coletador não reconhece a compra (qual mercado?) e não
consegue conferir o que foi lido. O modelo canônico (ADR-001) já guarda `data_emissao`, `valor_total`,
`cnpj_emitente` e os **itens** (`cupom_itens` / `CupomItem`), mas **não guarda o nome do estabelecimento** e
**não há tela de detalhe**.

- Épico: `epics/EPIC-007-refinamento-experiencia-b2c-mobile/epic.md`
- Documentos a ler ANTES: ADR-001 (modelo canônico), ADR-002 (extração SEFAZ SP), ADR-006 (sem PII no
  cupom — o **nome do emitente é dado do estabelecimento, não PII do consumidor**), `screen-spec` da
  Carteira (STORY-016), `docs/skills/stacks/inertia-react/SKILL.md`.
- Arquivos-âncora: `app/Domain/Cashback/ExtratoCarteira.php`, `resources/js/Pages/Carteira/Index.jsx`,
  `app/Models/Cupom.php` / `CupomItem.php`, `app/Domain/Coleta/Sefaz/HttpSefazSpFetcher.php` +
  `SpSefazAdapter.php` + `CupomExtraido.php` (extração), `app/Domain/Coleta/IngestaoCupomService.php`,
  migrations em `app/database/migrations/`, `routes/web.php`.

## O quê (objetivo desta estória)

1. **Capturar e persistir o nome do estabelecimento** (razão social/nome fantasia do emitente) na extração
   SEFAZ: adicionar coluna `nome_emitente` (nullable) em `cupons` (migration), preencher no
   `CupomExtraido`/adapter/ingestão. Vale para cupons **novos** (sem backfill).
2. **Listagem com contexto:** exibir **nome do estabelecimento + data de emissão** em cada item do histórico
   da Carteira (read-model `ExtratoCarteira`), no lugar/junto do "Cupom de R$ X".
3. **Tela de detalhe do cupom:** nova rota + tela (mobile-first, DS) exibindo cabeçalho (estabelecimento,
   data, valor total, status) e a **lista de itens** (`descricao`, `quantidade`/`unidade`, `valor_unitario`,
   `valor_total`). Cada item da listagem vira **clicável** e leva ao detalhe.

## Por quê (valor para o usuário)

Reconhecer a compra ("Supermercado X, 03/07") e conferir os itens dá **prova de que a nota contou** e
confiança no cashback — reforço direto da recorrência.

## Critérios de aceite

- [ ] **CA-1:** Cupons **novos** validados passam a ter `nome_emitente` preenchido a partir da extração
      SEFAZ; quando a fonte não trouxer o nome, o campo fica nulo e a UI degrada (mostra fallback, ex.:
      CNPJ/"Estabelecimento não identificado") sem quebrar.
- [ ] **CA-2:** Na listagem/histórico da Carteira, cada item exibe **nome do estabelecimento** e **data de
      emissão** (formato pt-BR), além do valor/crédito já existentes.
- [ ] **CA-3:** Tocar em um item da listagem **abre a tela de detalhe** do cupom correspondente (rota
      própria, guarda `auth`; o cupom pertence à jornada do Coletador logado).
- [ ] **CA-4:** A tela de detalhe mostra o **cabeçalho** (estabelecimento, data, valor total, status) e a
      **lista de itens** do cupom; estado vazio/pendente tratado (cupom ainda `pendente`/sem itens).
- [ ] **CA-5:** Sem vazar PII (ADR-006): a tela usa dados do cupom/itens; **não** expõe CPF. Datas/moeda em
      pt-BR; a11y AA; mobile-first sem overflow.

## Fora de escopo

- Backfill do `nome_emitente` em cupons antigos (dívida, se necessário).
- Matching/normalização de produtos entre lojas (ADR-004, onda futura).
- Edição/exclusão de cupom pelo usuário; filtros/busca no histórico.

## Padrões de qualidade exigidos

Segue `quality-standards.md`. Teste de ingestão cobrindo a captura do `nome_emitente`; teste do read-model
(listagem com estabelecimento/data); E2E em browser real (mobile) do fluxo listagem → detalhe → itens;
cobertura ≥80% no código novo; **Designer confirma a spec** da tela de detalhe.

## Dependências

- **Bloqueada por:** EPIC-006 `done`; reusa EPIC-002 (extração/ingestão) e EPIC-003 (histórico/carteira).
- **Bloqueia:** STORY-037 (validação do épico).
- **Decisões:** pode exigir **IDR** para a captura do nome na extração SEFAZ + coluna `nome_emitente`
  (onde extrair no DANFE, fallback quando ausente). Sem ADR novo previsto (extensão do modelo ADR-001).

## Decisões já tomadas (não as reabra)

- ADR-001 (modelo canônico) e ADR-002 (extração SP) valem; aqui é **estender** o modelo com um campo do
  emitente, não redesenhar a ingestão. ADR-006: nome do estabelecimento **não é** PII do consumidor.
  Escopo só SP.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; **Designer confirmou a spec** da tela de detalhe.
- [ ] Migration aplicada; extração preenche `nome_emitente`; E2E mobile (listagem → detalhe) verde.
- [ ] Pipeline verde; acessível em homologação (mobile); IDR registrado se houve decisão técnica.
- [ ] `index.json` = `done` (e `design.screens` indexado); "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. `requires_design: true` → alinhe cedo com o Designer (brief em
`design-handoff.md`). Decisão de extração/coluna → IDR. Falta de decisão de arquitetura → `blocked` + escalar.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas (ver IDR-015)

- **`nome_emitente` (string nullable)** em `cupons` via migration; parse do topo do DANFE
  (`<div class="txtTopo">`), propagado por `CupomExtraido.nomeEmitente` (opcional) → adapter → ingestão.
  Sem backfill; ausência degrada com fallback "Estabelecimento não identificado" + CNPJ.
- **Detalhe:** rota `GET /carteira/cupom/{cupom}` (guarda `auth` + posse via `CupomAtribuicao`;
  não-dono → 404). Read-model `DetalheCupom` formata cabeçalho (estabelecimento, CNPJ, data pt-BR,
  total, status→label+variante) + itens (qtd sem zeros à direita, moeda pt-BR). `Formato::cnpj()` novo.
- **Listagem:** `ExtratoCarteira` expõe `estabelecimento` (com fallback) + `cupom_id`; o item vira
  `<Link>` clicável para o detalhe, mostrando estabelecimento + data · valor.
- **Design (modelo paralelo, PDR-002):** spec + protótipo HTML (`SCREEN-STORY-034`) **validados com o
  Alexandro em 2026-07-05** ("Aprovar como está"). Sem novo padrão/exceção de DS → sem DDR.

### Descobertas

- O payload padrão do fake e o de LGPD ganharam `nome_emitente`; dois E2E de carteira (que afirmavam o
  antigo "Cupom de R$ X") foram atualizados para o novo item (estabelecimento + data · valor).
- `nomeEmitente` fica **fora** da checagem estrutural do adapter (default null) — fontes sem o campo não
  viram falha estrutural.

### Mapeamento CA → teste (todos verdes)

- **CA-1** → `Coleta/NomeEmitenteTest` (persiste / ausência→null); `Coleta/HttpSefazSpFetcherTest` (parse);
  `Carteira/ExtratoCarteiraTest::test_extrato_usa_fallback...`; `Carteira/CupomDetalheTest::test_fallback...`.
- **CA-2** → `Carteira/ExtratoCarteiraTest::test_extrato_expoe_estabelecimento_e_cupom_id`;
  `Browser/CupomDetalheE2eTest` (estabelecimento + data na listagem).
- **CA-3** → `Browser/CupomDetalheE2eTest` (1 toque → detalhe → retorno); `CupomDetalheTest`
  (auth + `test_nao_dono_recebe_404`).
- **CA-4** → `CupomDetalheTest::test_dono_ve_cabecalho_e_itens` e `test_cupom_pendente_sem_itens...`.
- **CA-5** → `CupomDetalheTest::test_detalhe_nao_expoe_cpf`; `CupomDetalheE2eTest` (sem overflow horizontal).

### Bloqueios encontrados

Nenhum.

### Links de evidência

- Testes novos: `NomeEmitenteTest`, `CupomDetalheTest` (6), `CupomDetalheE2eTest`, `ExtratoCarteiraTest`
  (+2), `HttpSefazSpFetcherTest` (+1). Suíte completa: **Pest 323/323**; **Dusk 90/90**.
- Design: `design/screens/STORY-034-cupom-detalhe/` (`screen-spec.md` + `index.html`), aprovado 2026-07-05.
- Decisão: `decisions/idr/IDR-015-captura-nome-emitente-sefaz.md`.
- Migration: `2026_07_05_000002_add_nome_emitente_to_cupons_table.php`. Arquivos: `HttpSefazSpFetcher`,
  `CupomExtraido`, `SpSefazAdapter`, `IngestaoCupomService`, `Cupom`, `ExtratoCarteira`, `DetalheCupom`
  (novo), `CarteiraController`, `Formato`, `routes/web.php`, `Pages/Carteira/{Index,CupomDetalhe}.jsx`.
