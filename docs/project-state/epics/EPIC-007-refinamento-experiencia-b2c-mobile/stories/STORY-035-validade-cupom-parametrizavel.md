---
story_id: STORY-035
slug: validade-cupom-parametrizavel
title: Validade parametrizável na coleta — rejeitar cupom com mais de N dias (default 7)
epic_id: EPIC-007
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: done
owner_agent: claude-programador
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: S
---

# STORY-035 — Validade parametrizável do cupom

> **Para o agente que vai executar:** leia por inteiro. Regra de negócio na **ingestão** (não é tela nova).
> O limite deve ser **parametrizável** (config), não um número mágico no código.

## Contexto (por que esta estória existe)

A coleta hoje aceita cupons independentemente de quão antigos são. O negócio quer priorizar dado **fresco**
(a north-star é sobre cupons novos) e evitar coleta de notas muito antigas. Precisamos **rejeitar cupons com
mais de N dias** desde a emissão — com **N configurável** (default **7**), para ajustar sem deploy de código.
O cupom já guarda `data_emissao` (ADR-001) e a ingestão já tem ponto de rejeição com motivo
(`ResultadoIngestao`/`IngestaoCupomService`, ver STORY-009/010).

- Épico: `epics/EPIC-007-refinamento-experiencia-b2c-mobile/epic.md`
- Documentos a ler ANTES: ADR-001 (modelo/`data_emissao`), STORY-010 (validação SEFAZ/dedup/persistência e
  motivos de rejeição), `docs/skills/stacks/laravel/SKILL.md`.
- Arquivos-âncora: `app/Domain/Coleta/IngestaoCupomService.php`, `app/Domain/Coleta/ResultadoIngestao.php`,
  `app/Http/Controllers/ColetaController.php`, `config/` (novo `config/coleta.php`), `.env`/`config('...')`.

## O quê (objetivo desta estória)

1. Introduzir um **parâmetro de janela de validade** — ex.: `config('coleta.janela_dias')`, default `7`,
   sobreponível por env — em vez de valor fixo.
2. Na **ingestão/validação**, comparar `data_emissao` com "agora" (America/Sao_Paulo): se exceder a janela,
   **rejeitar** o cupom com um **motivo específico** (ex.: `cupom_expirado`/"Cupom fora do prazo de N dias").
3. Superfície o motivo na confirmação da coleta (microcopy pt-BR do DS), sem quebrar o fluxo.

## Por quê (valor para o usuário)

Deixa claro o "prazo para valer" (expectativa honesta) e mantém a base **fresca** — qualidade do dado que
sustenta a north-star.

## Critérios de aceite

- [ ] **CA-1:** O limite de dias vem de **configuração** (`config('coleta.janela_dias')`), com **default 7**
      e sobreponível por env; **nenhum número mágico** no código de regra.
- [ ] **CA-2:** Cupom com `data_emissao` **mais antigo** que a janela é **rejeitado** na ingestão, com
      **motivo** próprio persistido/retornado (não confundível com dedup ou chave inválida).
- [ ] **CA-3:** Cupom **dentro** da janela segue o fluxo normal (validação/persistência/cashback como hoje).
- [ ] **CA-4:** A borda de coleta mostra **mensagem pt-BR** clara ao rejeitar por prazo (microcopy do DS),
      ancorada como as demais rejeições (STORY-009), sem banner global quebrado.
- [ ] **CA-5:** Fronteira testada (limite exatamente em N dias) e comportamento definido (documentar se N
      dias é inclusivo/exclusivo); fuso America/Sao_Paulo respeitado.

## Fora de escopo

- Tela de configuração/admin do parâmetro (é config/env nesta fase).
- Mudar dedup, validação de chave ou regras de cashback.
- Reprocessar cupons já coletados sob a regra nova.

## Padrões de qualidade exigidos

Segue `quality-standards.md`. Testes de unidade/feature cobrindo dentro/fora da janela e a fronteira; motivo
de rejeição distinto; parâmetro lido de config. Cobertura ≥80% no código novo.

## Dependências

- **Bloqueada por:** EPIC-002 (ingestão/validação) — já entregue.
- **Bloqueia:** STORY-037 (validação do épico).
- **Decisões:** possível **IDR** curto se a semântica da janela (inclusiva/exclusiva, base horária) ou o
  motivo de rejeição merecer registro. Sem ADR novo.

## Decisões já tomadas (não as reabra)

- ADR-001 (`data_emissao` no modelo) e o pipeline de ingestão/rejeição (STORY-010) valem. Fuso do projeto:
  America/Sao_Paulo.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; testes de fronteira verdes.
- [ ] Parâmetro em `config/coleta.php` (default 7) documentado; motivo de rejeição pt-BR.
- [ ] Pipeline verde; IDR se houve decisão relevante; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Sem tela nova. Decisão de semântica da regra → IDR.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas (ver IDR-013)

- Janela aferida em `IngestaoCupomService::processarExtracao()` — único ponto onde `data_emissao`
  existe (extração assíncrona, ADR-002). Semântica **inclusiva** (≤ N dias vale; estritamente mais
  velho expira), comparada em **America/Sao_Paulo**. Motivo próprio `cupom_expirado` (status
  `rejeitado`, sem cashback, sem itens persistidos).
- Limite em `config/coleta.php` › `janela_dias` (default 7, env `COLETA_JANELA_DIAS`) — sem número
  mágico.
- Microcopy pt-BR centralizada em `ColetaController::microcopyRejeicao()`, com o N da config, pelo
  **mesmo** mecanismo de rejeição ancorada no campo das demais (não banner global).

### Descobertas

- O payload padrão do fake e um teste de LGPD usavam data fixa `2026-01-15` — sob a janela de 7 dias
  em 2026-07-05 isso expiraria e quebraria o caminho feliz. Troquei por data recente dinâmica
  (`now(SP)->subDay()`), mais robusta que hardcoded.
- **Surfacing assíncrono:** a confirmação de captura mostra "capturado" e a expiração é determinada
  depois (fila). A microcopy está pronta/ancorada, mas exibir o desfecho assíncrono na tela (status)
  não estava no escopo S → **ressalva/wishlist** para a STORY-037.

### Mapeamento CA → teste (todos verdes)

`tests/Feature/Coleta/ValidadeCupomTest.php`:
- **CA-1** → `test_janela_default_e_sete_dias_via_config`, `test_janela_configuravel_muda_o_desfecho`.
- **CA-2** → `test_cupom_fora_da_janela_e_rejeitado_com_motivo_proprio`, `test_motivo_expirado_e_distinto_de_outras_rejeicoes`.
- **CA-3** → `test_cupom_dentro_da_janela_e_validado`.
- **CA-4** → `ColetaControllerTest::test_microcopy_de_cupom_expirado_e_ptbr_com_o_prazo_da_config`.
- **CA-5** → `test_limite_exatamente_na_janela_ainda_e_valido`, `test_um_segundo_alem_da_janela_e_rejeitado`,
  `test_fuso_sao_paulo_respeitado_na_fronteira`, `test_sem_data_emissao_nao_rejeita_por_prazo`.

### Bloqueios encontrados

Nenhum.

### Links de evidência

- Testes novos: `ValidadeCupomTest` (9) + `ColetaControllerTest` (+1 microcopy) — verdes.
- Suíte completa sem regressão: **Pest 313/313**; **Dusk 87/87**.
- Decisão: `decisions/idr/IDR-013-janela-validade-cupom-semantica.md`.
- Arquivos: `config/coleta.php` (novo), `app/Domain/Coleta/IngestaoCupomService.php`,
  `app/Http/Controllers/ColetaController.php`, fixtures de teste ajustadas.
