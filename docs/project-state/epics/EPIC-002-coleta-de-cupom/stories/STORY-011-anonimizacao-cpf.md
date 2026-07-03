---
story_id: STORY-011
slug: anonimizacao-cpf
title: Anonimização de CPF na coleta (LGPD)
epic_id: EPIC-002
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: done
owner_agent: claude-programador-story011
created_at: 2026-07-02
updated_at: 2026-07-03
estimated_session_size: M
---

# STORY-011 — Anonimização de CPF na coleta (LGPD)

> **Para o agente que vai executar:** leia por inteiro. Estória de **conformidade LGPD** — o objetivo
> é garantir que nenhum CPF em claro entre/permaneça na base. Siga a ADR-006 (STORY-008).

## Contexto (por que esta estória existe)

A NFC-e pode conter o **CPF do consumidor** ("CPF na nota"). O Quantah não precisa do CPF para a base
de preços e não pode retê-lo em claro sem base legal e risco. A coleta precisa **anonimizar/descartar**
o CPF já na ingestão, com segregação de bases (LGPD, visao §7).

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- Documentos a ler ANTES: `docs/visao.md` §7 (Privacidade e LGPD); ADR-006 (anonimização/segregação);
  ADR-001 (modelo canônico — onde o CPF entraria).

## O quê (objetivo desta estória)

Garantir que, quando o CPF estiver presente na nota, ele seja **anonimizado ou descartado na coleta**
(nunca persistido em claro no modelo canônico), conforme a decisão da ADR-006.

## Por quê (valor para o usuário)

Protege o consumidor e o Quantah: minimização de dado pessoal reduz risco jurídico e de vazamento, e
sustenta a confiança necessária para a coleta colaborativa.

## Critérios de aceite

- [x] **CA-1:** Quando a nota traz CPF, o valor **não é persistido em claro** em nenhuma tabela do
      modelo canônico (verificado por teste que inspeciona o que foi gravado).
- [x] **CA-2:** O tratamento segue a ADR-006 (anonimização — ex.: hash/pseudonimização com segregação —
      **ou** descarte, conforme a decisão); o teste prova o comportamento decidido.
- [x] **CA-3:** A dedução/dedup do cupom (STORY-010) **não depende** do CPF — funciona sem ele.
- [x] **CA-4:** Logs/telemetria **não vazam** CPF (nem em texto de log, nem em mensagens de erro).
- [x] **CA-5:** Há teste de regressão que falha se, no futuro, algum caminho passar a gravar CPF em claro.

## Fora de escopo

- Política de retenção/portal de titular (direitos LGPD além da minimização na coleta) — evolução.
- Outros dados pessoais além do CPF na nota (não há previsão de coleta na onda).

## Padrões de qualidade exigidos

Segue `quality-standards.md` + seção LGPD/Segurança. Cobertura alta no caminho de tratamento de dado
pessoal; nenhum CPF em claro em base/log/fixtures versionadas.

## Dependências

- **Bloqueada por:** STORY-008 (ADR-006) e STORY-010 (persistência sobre a qual o tratamento atua).
- **Bloqueia:** STORY-013 (validação — item LGPD do checklist).

## Decisões já tomadas (não as reabra)

- ADR-006 (anonimização/segregação). Minimização de dado é princípio (visao §7). Só SP.

## Definição de Pronto (DoD)

- [x] CA-1 a CA-5 passam; teste de regressão anti-CPF-em-claro presente e verde.
- [x] Nenhum CPF em claro em base/log/fixtures; pipeline verde.
- [x] IDR se houve decisão técnica; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Dúvida sobre base legal/decisão LGPD que não esteja na ADR-006 →
`blocked` + escalar ao PO (é decisão de produto/jurídica, não do agente).

## Notas do agente (preenchido durante/após execução)

### Documentos lidos
- ADR-006 (decisão: Opção A — **descartar** o CPF na coleta), ADR-001 (modelo canônico sem coluna
  de CPF; ponto de normalização), visão §7 (minimização/LGPD por design). Código da STORY-008/010:
  `AnonimizadorCpf`, `SpSefazAdapter`, `IngestaoCupomService`, `HttpSefazSpFetcher`, `ExtrairCupomJob`,
  modelos `Cupom`/`CupomItem`, migrações.

### Entendimento consolidado
- O mecanismo já nascera no spike (STORY-008): `AnonimizadorCpf::limpar()` (descarta chaves de CPF e
  escova valores com cara de CPF) já roda no `SpSefazAdapter` antes da normalização; a migração não
  tem coluna de CPF. STORY-011 é a **estória de conformidade**: formalizar a suíte de regressão
  CA-1..CA-5 e **fechar brechas reais** — não reimplementar o anonimizador.

### Decisões tomadas
- **Confirmada a Opção A da ADR-006 (descartar).** Não introduzi hash/cifra — fora de escopo e
  contra a minimização.
- **IDR-005** — estendi a anonimização do ponto de normalização (adaptador) para a **fronteira de
  ingestão**: o `qr_conteudo` (conteúdo do QR colado, coluna canônica gravada em `persistirPendente`)
  passa por `AnonimizadorCpf::limparTexto()` antes de gravar. Método estático (função pura) para não
  mexer no construtor/DI do serviço. A chave assinada é preservada; só o padrão de CPF sai.

### Descobertas
- **Brecha real fechada (CA-1):** `cupons.qr_conteudo` era persistido cru — um CPF colado como lixo
  (`&cpf=...`) vazaria para tabela canônica. Era o único ponto do pipeline fora do caminho do
  adaptador. Fechado por TDD (RED → GREEN) + regressão de banco inteiro.
- **CA-4:** o pipeline não loga payload; o único ponto de log é `ExtrairCupomJob::failed()`
  (cupom_id + mensagem de exceção com string fixa). O Job serializa só o `cupomId` (UUID), não o
  payload — nada de CPF chega a `failed_jobs`. Travado por teste que captura `MessageLogged`.
- **Sem falso positivo** do padrão de CPF (11 dígitos com fronteira) sobre chave de 44 dígitos, CNPJ
  de 14, GTIN de 13 e o `token`/`hash` do QR real — verificado em teste.

### Mapa CA → teste
- **CA-1:** `AnonimizacaoCpfLgpdTest::test_cpf_do_retorno_sefaz_nunca_e_persistido`,
  `::test_cpf_no_conteudo_do_qr_nao_e_persistido`.
- **CA-2:** `::test_normalizacao_descarta_cpf_preservando_o_resto` + `AnonimizadorCpfTest` (unidade).
- **CA-3:** `::test_dedup_por_chave_independe_da_presenca_de_cpf`.
- **CA-4:** `::test_logs_do_pipeline_nao_vazam_cpf`.
- **CA-5:** `::test_regressao_banco_inteiro_sem_cpf_apos_pipeline` (varre todas as colunas de
  `cupons`/`cupom_itens`; quebra se o anonimizador sair do caminho, pois há CPF plantado em
  `descricao`, coluna persistida).

### Bloqueios encontrados
- Nenhum. A ADR-006 cobriu todas as decisões de LGPD; nada foi escalado ao PO.

### Links de evidência
- Suíte completa verde: **135 testes / 592 asserções** (`sail test`); E2E de captura verde
  (`ColetaCapturaTest`, 4 cenários); lint (Pint) limpo.
- Commits (TDD): `test(CA-1)` RED → `fix(CA-1)` GREEN (qr_conteudo) → `test(CA-2)` bordas.
- IDR: `decisions/idr/IDR-005-anonimizacao-cpf-no-conteudo-do-qr.md`.
