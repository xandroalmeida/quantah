---
story_id: STORY-016
slug: tela-carteira-saldo-historico
title: Tela de carteira — saldo em reais + histórico de cupons/créditos (mobile)
epic_id: EPIC-003
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: SCREEN-STORY-016-carteira-saldo-historico
status: done
owner_agent: claude-story016
status_note: aprovada por Alexandro (2026-07-03); deploy homolog verde (run 28690992037)
created_at: 2026-07-03
updated_at: 2026-07-03
estimated_session_size: M
---

# STORY-016 — Tela de carteira

> **Para o agente que vai executar:** tela do **Colaborador** (superfície de marca) → exige
> **artefato de Designer** antes da implementação (diferente do painel interno da STORY-012).

## Contexto (por que esta estória existe)

O Colaborador só percebe o incentivo se **vê o saldo crescer**. Esta tela mostra o saldo em reais e
o histórico de cupons enviados e créditos recebidos — é o que fecha o loop percebido pelo usuário.

## Critérios de aceite (alto nível — refinar na execução)

- Carteira mobile em homologação exibindo **saldo em reais** e **histórico** (cupons válidos +
  créditos correspondentes), compondo componentes do DS (EPIC-001).
- Estados além do feliz: saldo zero, carregando, erro, lista vazia.
- Dados vêm da carteira/extrato da STORY-015 (nada de número hardcoded).
- Acessibilidade e microcopy Quantah (duas faces) conforme DS.
- Feature/E2E verdes (mesmo padrão da STORY-009).

## Design (requires_design)

Exige spec de tela do Designer em `design/screens/STORY-016-*` **antes** da implementação. (Não se
aplica a exceção DDR-003 — aquela vale só para telas internas de leitura.)

## Fora de escopo

- Ação de saque/resgate (STORY-017).
- Gamificação/ranking (Onda 2).

## Dependências

- **Bloqueado por:** STORY-015 (fonte do saldo/histórico); EPIC-001 (componentes).
- **Bloqueia:** STORY-018.

## Definição de pronto

Tela em homologação com estados; artefato de Designer presente e validado; testes verdes;
`index.json` = `done`; Notas do agente preenchidas.

## Notas do agente

### 2026-07-03 — Designer → Programador (claude-story016)

**Fluxo:** como `requires_design: true` e não havia artefato, atuei primeiro como **Designer**
(spec + protótipo fiel), o **Alexandro validou o protótipo** no navegador (aprovado com o fix do
loading), e só então implementei como **Programador** (TDD + E2E).

**Design (validado):** `design/screens/STORY-016-carteira-saldo-historico/` — saldo como
`card.feature-dark` (momento de marca), histórico em lista de cards, `badge.positive` no crédito,
estados no protótipo (preenchido/vazio/loading/erro), mobile-first com paridade desktop. Reusa 100%
do DS (zero exceção). `prototype_last_validated_at: 2026-07-03`.

**Decisões do dono:** incluir a **navegação** (bottom-nav mobile / top-nav desktop) nesta estória,
como início do shell do app.

**Decisões técnicas (Programador):**
- Read-model `App\Domain\Cashback\ExtratoCarteira` (leitura pura): saldo do cache + histórico juntando
  cada `credito_cashback` ao seu `Cupom` por `cupom_id` (referência lógica, **sem cruzar a segregação
  ADR-006**) em uma 2ª query (`whereIn`, sem N+1). Formatação de reais/data na borda de apresentação.
- `CarteiraController` fino → `Inertia::render('Carteira/Index', ...)`; rota `/carteira` sob `auth`.
- Página React compõe o DS (Card/Badge/EmptyState/NavBottom/NavBar/icons); microcopy = spec §5;
  `data-testid` = spec §7.
- **Desvio consciente (registrado no spec §4.2):** a página é **server-rendered**, logo os estados
  **loading** e **erro** do protótipo não têm gatilho client-side — são a barra de progresso e a página
  de erro nativas do Inertia. Implementei os 2 estados reais (**preenchido** e **vazio**); não fabriquei
  skeleton/snackbar para dado que sempre chega com a página. Se o extrato virar dado pesado, o skeleton
  do spec é o fallback (deferred prop).

**Cobertura:** núcleo de leitura `ExtratoCarteira` **100%**; `CarteiraController` 100%.
Suíte completa: **188 PHPUnit + 46 Dusk** verdes.

**Mapa CA → teste:**

- **CA — carteira exibe saldo em reais + histórico (cupons válidos + créditos), compondo o DS:**
  `ExtratoCarteiraTest::test_saldo_formatado_em_reais` · `test_saldo_grande_usa_separador_de_milhar`
  (borda) · `test_extrato_junta_credito_ao_cupom_e_formata` (feliz) ·
  `test_extrato_ordena_do_mais_recente_para_o_mais_antigo` · `test_extrato_nao_inclui_transacoes_de_outra_carteira`
  (isolamento) · `test_credito_sem_cupom_correspondente_usa_fallback` (borda) —
  `tests/Feature/Carteira/ExtratoCarteiraTest.php`.
  `CarteiraControllerTest::test_renderiza_saldo_e_extrato_do_colaborador` (contrato Inertia) —
  `tests/Feature/Carteira/CarteiraControllerTest.php`.
  **E2E:** `CarteiraTest::test_preenchido_mostra_saldo_e_historico` (browser real).
- **CA — estados além do feliz (saldo zero / lista vazia):**
  `CarteiraControllerTest::test_colaborador_sem_creditos_ve_saldo_zero_e_extrato_vazio` ·
  `ExtratoCarteiraTest::test_usuario_sem_carteira_tem_saldo_zero_e_extrato_vazio`.
  **E2E:** `CarteiraTest::test_vazio_mostra_cta_que_leva_a_coleta` (vazio → CTA → /coletar).
  *(loading/erro: tratados pelo Inertia — ver desvio acima; sem E2E próprio por não haver estado
  client-side a exercitar.)*
- **CA — dados vêm da carteira/extrato (STORY-015), nada hardcoded:** todo o `ExtratoCarteiraTest`
  lê dos models reais; `CarteiraController` injeta o read-model.
- **CA — auth / sem permissão:** `CarteiraControllerTest::test_carteira_exige_autenticacao` +
  `CarteiraTest::test_anonimo_e_barrado_para_o_login` (E2E, guest → /login).
- **CA — acessibilidade + Feature/E2E verdes:** componentes semânticos do DS (`<nav aria-label>`,
  `aria-current`, headings, botão real); E2E em browser real cobrindo os 3 desfechos alcançáveis.

**TDD evidenciado:** commits `test(STORY-016): ... (vermelho)` precedem `feat(STORY-016): ... (verde)`.

**Pendências (fora de escopo):** saque/resgate é a STORY-017; unificar a nav nas telas Captura/Métricas
é follow-up (aqui a nav entra pela Carteira). Validação final do épico é a STORY-018.
