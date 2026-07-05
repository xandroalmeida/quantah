---
epic_id: EPIC-007
type: validation-report
validated_at: 2026-07-05
validated_by: validador (sessão claude-validador)
verdict: approved
checklist_source: epics/EPIC-007-refinamento-experiencia-b2c-mobile/validation/checklist.md
---

# Relatório de Validação — EPIC-007 (Refinamento da experiência B2C mobile)

## TL;DR

> **Veredito**: **APPROVED**.
> **Contagem**: 19 passes, 3 passes com ressalva, 0 fails, 2 n/a justificados.
> **Bloqueantes**: nenhum.

---

## Resumo executivo

O EPIC-007 refina a experiência B2C mobile em quatro frentes (casca app-like, cupom com contexto,
validade parametrizável, coerência de jornada) + validação. As quatro estórias de implementação
(033–036) estão `done` no `index.json`. A suíte completa passa (**Pest 323/323, 1488 asserções**;
**Dusk 90/90, 410 asserções**), com cobertura total **95,2%** e todo o código novo da onda ≥ 87,5%
(bem acima do piso de 80%). Os fluxos de FE tocados têm E2E em browser real (Dusk, ADR-002/008) no
viewport mobile 360–390px. As três ressalvas são não-bloqueantes: (a) a rejeição por prazo é aferida
na extração assíncrona, então a confirmação da coleta não mostra o motivo de forma síncrona em
produção (microcopy pronta e ancorada no campo; IDR-013); (b) verificação em homologação no aparelho
depende de push, que o PO decidiu manter local (repo sem remote); (c) os formulários do Perfil ainda
usam o foco índigo do Breeze (dívida R5 do EPIC-006, fora de escopo). Nenhuma bloqueia a entrega.

---

## Checklist preenchido

### Bloco 1 — Critérios de aceite

| Item | Status | Evidência |
|---|---|---|
| 1.1 — Estórias 033–036 `done` no índice | ✅ | `index.json`: STORY-033/034/035/036 = `done`. |
| 1.2 — Casca (STORY-033) | ✅ | `tests/Browser/CascaMobileTest.php` 5/5: nav no viewport nas 5 rotas logadas (360×640), sem overflow-x a 360/390, "Entrar" `right ≤ innerWidth` + header sem scroll, manifest/metatags via DOM, alvos ≥48px. `public/manifest.json` `display: standalone`. |
| 1.3 — Cupom (STORY-034) | ✅ | `NomeEmitenteTest` (persiste/ausência→null), `HttpSefazSpFetcherTest` (parse do DANFE), `ExtratoCarteiraTest` (estabelecimento+cupom_id+fallback), `CupomDetalheTest` 6/6 (cabeçalho, itens, posse→404, pendente, sem-CPF), `Browser/CupomDetalheE2eTest` (listagem→detalhe em 1 toque). |
| 1.4 — Validade (STORY-035) | ✅ | `ValidadeCupomTest` 9/9: config default 7 + sobreponível, rejeição `cupom_expirado` (distinta), dentro segue, fronteira inclusiva + 1s além, fuso SP, sem-data. |
| 1.5 — Coerência (STORY-036) | ✅ | `ProfileTest` (DELETE /profile → 405; `profile.destroy` ausente), `Browser/CoerenciaJornadaTest` (Perfil sem "Excluir Conta"; menu "Escanear", destino `/coletar` + `aria-current` intactos). |

### Bloco 2 — Cobertura de testes

| Item | Status | Evidência |
|---|---|---|
| 2.1 — Cobertura ≥ 80% no código novo | ✅ | `sail artisan test --coverage`: DetalheCupom 100%, ExtratoCarteira 100%, Cupom 100%, CarteiraController 100%, CupomExtraido 100%, IngestaoCupomService 99,1%, SpSefazAdapter 97%, ColetaController 96%, HttpSefazSpFetcher 95,7%, Formato 87,5%. Total 95,2%. |
| 2.2 — TDD evidenciado | ✅ | Histórico de commits: cada estória tem commit `test(...)` (vermelho) antes do `feat(...)` (verde) — ex.: `27bc295` test → `d919286` feat (034); `337b377` test → `53bdcec` feat (035). |
| 2.3 — E2E em browser real | ✅ | Laravel Dusk (Selenium, Chrome) — `CascaMobileTest`, `CupomDetalheE2eTest`, `CoerenciaJornadaTest`, + jornada existente; caminho feliz + erro (posse 404, sem overflow). |
| 2.4 — Feliz + inválidos + exceções + bordas | ✅ | Ex.: ValidadeCupomTest cobre feliz (dentro), inválido (fora/motivo), borda (exato N dias, 1s além, sem-data), exceção-adjacente (fuso). CupomDetalheTest cobre posse (404) e estado pendente. |
| 2.5 — Suíte completa verde | ✅ | Pest 323/323 (1488 asserções); Dusk 90/90 (410 asserções). |

### Bloco 3 — Transversais da WAVE-2026-02

| Item | Status | Evidência |
|---|---|---|
| 3.1 — 100% pt-BR (R$, dd/mm/aaaa, SP) | ✅ | `Formato` (R$ 1.234,56 / dd/mm/aaaa / America/Sao_Paulo); `DetalheCupom`/`ExtratoCarteira` formatam na borda; i18n `Scan`→"Escanear"; E2E afirmam texto pt-BR (Início/Carteira/Perfil, Validado, Escanear). |
| 3.2 — a11y AA | ✅ | Alvos ≥48px verificados por `rect.height` (CascaMobileTest, CoerenciaJornadaTest); foco visível (anel `ink`) nos links de nav/item/retorno; `aria-current=page` preservado; badge nunca só cor (ícone+texto). |
| 3.3 — Mobile-first sem overflow | ✅ | `document.body.scrollWidth ≤ innerWidth` a 360 e 390px nas telas logadas, na landing e no detalhe (asserções nos 3 E2E). |
| 3.4 — DS, verde único accent | ✅ | Telas compõem `card.content`/`badge`/`empty-state`/`nav.*` (sem primitivo novo — screen-spec `exceptions_to_ds: []`); primary só em badge validado/saldo. |

### Bloco 4 — Funcionalidade observável

| Item | Status | Evidência |
|---|---|---|
| 4.1 — Entregáveis exercitáveis (browser real, mobile) | ✅ | Cada entregável do `epic.md` tem cenário Dusk no viewport mobile; screenshots do app real capturados na execução (casca, detalhe cheio/sem-nome/pendente, listagem). |
| 4.2 — Sem regressão do EPIC-006 | ✅ | `JornadaContinuaTest` (loop coletar→saldo→extrato→saque) verde após ajuste da listagem; suíte completa sem quebra. |
| 4.3 — Homologação no aparelho (deploy) | 🚫 n/a | O PO decidiu manter commits **locais** (repo sem remote do template; sem push nesta sessão). O deploy de homolog é tag/push-gated (quality-standards §2), fora do alcance da sessão. Funcionalidade observável validada em **browser real** local (Dusk) + viewport mobile. |

### Bloco 5 — Qualidade transversal

| Item | Status | Evidência |
|---|---|---|
| 5.1 — Migração reversível | ✅ | `2026_07_05_000002_add_nome_emitente_to_cupons_table.php` tem `up`/`down` (dropColumn); nullable, sem backfill (IDR-015). |
| 5.2 — Sem PII do consumidor (ADR-006) | ✅ | `CupomDetalheTest::test_detalhe_nao_expoe_cpf` (conteúdo sem "cpf"); `nome_emitente` é dado público do estabelecimento; AnonimizadorCpf segue no pipeline. |
| 5.3 — Sem segredos; lint limpo | ✅ | Varredura no diff da onda sem segredos; Pint `passed` nos arquivos PHP tocados; build Vite verde (JS). |

### Bloco 6 — Estado e decisões

| Item | Status | Evidência |
|---|---|---|
| 6.1 — `index.json` atualizado | ✅ | Estórias 033–036 `done`; `SCREEN-STORY-034` em `design.screens` (shipped); decisões indexadas. |
| 6.2 — IDRs/screen indexados | ✅ | IDR-012 (casca/PWA), IDR-013 (janela), IDR-014 (exclusão de conta), IDR-015 (nome_emitente) em `decisions.idr`; screen-spec + index.html em `design/screens/STORY-034-cupom-detalhe/`. |
| 6.3 — "Notas do agente" preenchidas | ✅ | STORY-033/034/035/036 com decisões, descobertas, mapeamento CA→teste e evidências. |

---

## Ressalvas (não-bloqueantes, fatos)

- **R1 — Rejeição por prazo é assíncrona.** `data_emissao` só existe pós-extração (fila), então a
  confirmação de captura mostra "capturado" e a expiração é determinada depois; a microcopy pt-BR
  existe e é ancorada no campo pelo mesmo mecanismo das demais rejeições (IDR-013). Verificado em
  `ValidadeCupomTest` (regra) e `ColetaControllerTest` (microcopy).
- **R2 — Homologação no aparelho não exercitada nesta sessão** por decisão do PO de não fazer push
  (repo sem remote). Observabilidade validada em browser real local + viewport mobile.
- **R3 — Foco índigo do Breeze nos formulários do Perfil** (dados/senha) permanece — dívida R5 do
  EPIC-006, explicitamente fora do escopo desta onda.

## Limitações da validação

- Cobertura medida via `--coverage` do runner (pcov/xdebug no container Sail); não há gate de cobertura
  em CI remoto nesta sessão (sem remote).
- O comportamento dinâmico do `100dvh` no Safari iOS (raiz do CA-1 da STORY-033) não é reproduzível em
  Chrome headless; os E2E são guardas de regressão (nav no viewport, sem overflow) — a correção é a
  troca para `dvh` + safe-area, verificada por inspeção de código e screenshots mobile.

## Veredito

**APPROVED** — todas as estórias 033–036 `done`, todos os itens `pass` ou `n/a` justificado, zero
fails, zero bloqueantes. As três ressalvas são não-bloqueantes.
