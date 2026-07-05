---
idr_id: IDR-013
slug: janela-validade-cupom-semantica
title: Semântica da janela de validade do cupom — checada na extração, inclusiva em N dias, fuso SP
status: accepted  # proposed | accepted | superseded
decided_at: 2026-07-05
decided_by: programador
owner_agent: claude-programador
related_story: STORY-035
related_adrs: [ADR-001, ADR-002]
related_idrs: []
related_ddrs: []
supersedes: null
superseded_by: null
created_at: 2026-07-05
updated_at: 2026-07-05
---

# IDR-013 — Semântica da janela de validade do cupom

> Implementation Decision Record. Registra **onde** e **com que semântica** a janela de validade
> (STORY-035) é aplicada, para que a validação (STORY-037) e futuras estórias de coleta não a
> reabram nem a dupliquem em outro ponto do pipeline.

## Contexto

O negócio quer rejeitar cupons com mais de N dias desde a emissão, com N configurável (default 7).
O modelo (ADR-001) guarda `data_emissao`, mas essa data **só é conhecida após a extração SEFAZ**
(ADR-002 — extração assíncrona): na captura (`capturar()`), o cupom é persistido `pendente` e a
extração é enfileirada; a chave de acesso só carrega o ano-mês (`anoMes`), não o dia. Precisei
decidir onde aferir o prazo, a semântica de "N dias" e como surfaçar o motivo.

## Decisão

> **Decidi: aferir a janela em `IngestaoCupomService::processarExtracao()` (após extração bem
> sucedida, onde `data_emissao` existe); um cupom é válido se sua idade for ≤ N dias em
> America/Sao_Paulo (limite inclusivo — exatamente N dias ainda vale), e estritamente mais velho é
> rejeitado com o motivo próprio `cupom_expirado` (status `rejeitado`, sem cashback, sem persistir
> itens). N vem de `config('coleta.janela_dias')` (default 7, env `COLETA_JANELA_DIAS`). Sem
> `data_emissao` não se rejeita por prazo. A microcopy pt-BR (`ColetaController::microcopyRejeicao`)
> reusa o mesmo mecanismo de rejeição ancorada no campo das demais (não banner global).**

## Por quê

- **Na extração, não na captura:** é o único ponto com a data precisa (dia/hora). Fazer na captura
  exigiria adivinhar pela `anoMes` (impreciso para janelas < 1 mês) ou duplicar a regra.
- **Inclusiva em N dias, fuso SP:** "mais de N dias" ⇒ expira a partir de N dias + epsilon;
  exatamente N dias permanece válido (borda previsível e testável). O fuso do projeto é
  America/Sao_Paulo — comparar em UTC erraria a fronteira perto da virada do dia.
- **Motivo próprio `cupom_expirado`:** não confundível com `chave_malformada`/`fora_de_escopo_uf`/
  dedup/negócio — telemetria e UX conseguem distinguir "fora do prazo" de "chave inválida".

## Alternativas consideradas

- **Checar na captura com pré-gate por `anoMes`:** descartada — impreciso dentro do mês e duplicaria
  a regra em dois pontos; a extração já é o dono da data.
- **Semântica exclusiva (exatamente N dias já expira):** descartada — borda menos intuitiva ("7 dias
  para valer" naturalmente inclui o 7º dia).
- **Rejeitar antes de descartar itens vs depois:** rejeita-se **antes** de `normalizarEpersistir` —
  cupom expirado não persiste itens nem dispara `CupomValidado` (sem cashback).

## Consequências

### Para outros agentes
- A regra de prazo vive **só** em `processarExtracao()`. Não reimplementar na captura, no controller
  ou no job — o job chama `processarExtracao()` e herda a regra.
- Motivo `cupom_expirado` é o canônico para "fora do prazo"; microcopy centralizada em
  `ColetaController::microcopyRejeicao()`.
- Cupom expirado fica `status = rejeitado`, `motivo_falha = cupom_expirado` — não conta para cashback
  nem aparece no extrato de crédito.

### Para o projeto
- +`config/coleta.php` (`janela_dias`, env `COLETA_JANELA_DIAS`). Sem dependência nova.
- **Surfacing assíncrono:** como a extração é assíncrona (fila), a confirmação de captura mostra
  "capturado" e a expiração é determinada depois. A microcopy está pronta e ancorada no campo pelo
  mesmo mecanismo das demais rejeições; exibir o desfecho assíncrono na tela (status/polling) fica
  como **wishlist** para a validação (não estava no escopo S desta estória).

### Trade-offs aceitos
- Fixtures de teste que ingeriam com data fixa antiga (`2026-01-15`) passaram a usar data recente
  dinâmica (`now(SP)->subDay()`), pois sob a regra nova aquela data expira. Reflete a realidade
  (coleta de nota fresca) e é mais robusto que data hardcoded.

## Como verificar

- `tests/Feature/Coleta/ValidadeCupomTest.php`: dentro/fora da janela, fronteira exata (inclusiva),
  1s além, fuso SP, sem-data, e config sobreponível.
- `tests/Feature/Coleta/ColetaControllerTest::test_microcopy_de_cupom_expirado...`: microcopy pt-BR
  com o N da config, pelo mecanismo de campo.
- Se o surfacing assíncrono virar requisito, abrir estória nova — não reabrir esta regra.

## Tipo

- [x] **Convenção interna**: ponto único e semântica da janela de validade.

---

## Histórico

- 2026-07-05 — criada como `accepted` por programador (sessão claude-programador) durante STORY-035.
