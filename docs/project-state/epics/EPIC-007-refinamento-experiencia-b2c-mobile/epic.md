---
epic_id: EPIC-007
slug: refinamento-experiencia-b2c-mobile
title: Refinamento da experiência B2C mobile — casca app-like, cupom detalhado e ajustes de jornada
wave: WAVE-2026-02
status: ready
owner_role: po
created_at: 2026-07-05
updated_at: 2026-07-05
target_completion: 2026-08-15
---

# EPIC-007 — Refinamento da experiência B2C mobile

## Por que existimos (problema do usuário)

A jornada B2C ficou de pé (EPIC-006), mas o **teste real no celular** expôs atritos que atrapalham a
sensação de "produto" e a navegação do Coletador. A casca não se comporta como app: o menu inferior fica
atrás da barra do navegador (some em Carteira/Perfil, e nas demais só aparece com scroll), o botão
"Entrar" da landing corta na borda, e não há modo standalone. Além disso, o dado do cupom aparece pobre —
o histórico mostra só "Cupom de R$ X", sem **onde** e **quando** a compra foi feita, e não há como abrir o
cupom para ver seus itens. Há ainda dois ajustes de coerência: a opção de **excluir conta** exposta sem
necessidade nesta fase, e o menu **"Cupons"** que sugere uma lista quando na verdade abre o **escaneamento**.
Esses pontos, somados, minam a confiança de que "a nota contou" e a fluidez da navegação — justamente às
vésperas do piloto.

## Resultado esperado (outcome)

Ao fim deste épico, o Coletador tem uma **experiência mobile coesa e app-like**: menu inferior sempre
visível e fixo em todas as telas, sem cortes de layout, com opção de uso em modo standalone (sem a barra do
navegador). O **cupom deixa de ser um número solto**: a listagem mostra estabelecimento e data, e um toque
abre o **detalhe com os itens** da compra. A coleta passa a **rejeitar cupons fora da janela de validade**
(parametrizável), e a navegação/perfil ficam coerentes com a fase (sem excluir conta; o menu de escanear
diz o que faz).

## Métrica de sucesso (como saberemos que funcionou)

- Métrica primária: em homologação, no celular, o Coletador enxerga o menu inferior **fixo em 100% das
  telas logadas** (Início, Escanear, Carteira, Perfil) sem rolar, sem overflow horizontal, e abre o
  **detalhe de um cupom com seus itens** em 1 toque a partir da listagem.
- Métrica de qualidade: cupom fora da janela de validade é **rejeitado com motivo** (limite lido de
  configuração, não hardcoded); estabelecimento + data de emissão visíveis na listagem; a opção de excluir
  conta ausente; menu renomeado. Tudo coberto por E2E/teste em browser real (mobile), 100% pt-BR, a11y AA.

## Entregável visível no fim do épico

- [ ] **Casca mobile app-like** — `nav.bottom` fixo e visível em todas as telas logadas (Carteira e Perfil
      inclusive), sem cortes; header público sem cortar o "Entrar"; modo standalone/PWA (sem barra do
      navegador ao abrir).
- [ ] **Cupom com contexto** — listagem exibindo nome do estabelecimento + data de emissão, e **tela de
      detalhe do cupom** (cabeçalho + itens) acessível por 1 toque.
- [ ] **Validade parametrizável na coleta** — cupom com mais de N dias (default 7, configurável) é
      rejeitado com motivo claro.
- [ ] **Coerência de jornada** — sem opção de excluir conta; menu "Cupons" renomeado para algo que sugira
      escanear.

## Fora de escopo (explicitamente)

- Notificações push / app nativo empacotado em loja (o "app-like" é PWA, não build nativo).
- Reestilização completa dos formulários do Perfil (dívida R5 do EPIC-006, segue fora).
- Novas regras de cashback, dedup ou de saque (reusa as da Onda 1).
- Matching de produtos entre lojas / enriquecimento analítico dos itens (ADR-004, onda futura).
- Reprocessar ou re-extrair cupons antigos para preencher o nome do estabelecimento retroativamente
  (a captura do nome vale para cupons novos; backfill é dívida, se necessário).

## Referências da especificação

- `docs/visao.md` §3.1 (princípios de produto), §5.1 (MVP coleta e incentivo).
- ADR-001 (modelo canônico do cupom), ADR-002 (extração SEFAZ SP), ADR-003 (chave/dedup),
  ADR-006 (segregação de bases / sem PII no cupom).
- DDR-007 (casca de navegação da área logada — `pattern.app-shell`), DDR-005 (casca pública compartilhada).
- Telas base: `design/screens/STORY-016-carteira-saldo-historico`, `STORY-030-navegacao-b2c`,
  `STORY-025-landing-b2c`, `STORY-009-captura-qr-confirmacao`.
- `docs/skills/po/references/quality-standards.md` §1 (testes/E2E), §5 (a11y), §5.1 (pt-BR).

## Dependências

- **Bloqueia:** nada (refinamento sobre a jornada já entregue; habilita o piloto com menos atrito).
- **Bloqueado por:** EPIC-006 (jornada B2C) `done`. Reusa EPIC-002 (coleta/ingestão) e EPIC-003
  (carteira/histórico).
- **Decisões arquiteturais necessárias:** possivelmente **um IDR** para a captura do nome do emitente na
  extração SEFAZ + coluna `nome_emitente` (STORY-034) e outro para o parâmetro de janela de validade
  (STORY-035), se a regra de rejeição tiver nuance. UI nova (detalhe do cupom) entra pelo modelo paralelo
  (`requires_design`, PDR-002) com brief em `design-handoff.md`.

## Estórias

Decompostas no Fluxo B (2026-07-05) a partir de 9 ajustes levantados no teste mobile pelo Alexandro,
**consolidados em 4 frentes** verticais + a validação (evitando uma estória por micro-ajuste):

- [ ] **STORY-033** — Casca mobile app-like: `nav.bottom` fixo/visível em todas as telas (corrige
      `h-screen`/100vh → `dvh` + safe-area), header público sem cortar "Entrar", e modo standalone/PWA.
      Cobre os ajustes de menu inferior (Carteira/Perfil e "só com scroll"), botão "Entrar" cortado e
      experiência sem barra do navegador. Bloqueia 037.
- [ ] **STORY-034** — Cupom com contexto: exibir **estabelecimento + data de emissão** na listagem e criar
      a **tela de detalhe do cupom** (cabeçalho + itens), com item da lista clicável. Inclui capturar e
      persistir o **nome do estabelecimento** na extração (`requires_design`). Bloqueia 037.
- [ ] **STORY-035** — Validade parametrizável na coleta: rejeitar cupom com mais de N dias, com N em
      configuração (default 7) e motivo de rejeição claro. Bloqueia 037.
- [ ] **STORY-036** — Coerência de jornada: remover a opção de **excluir conta** e **renomear** o menu
      "Cupons" para um rótulo de escanear. Bloqueia 037.
- [ ] **STORY-037** (validação) — validação final do épico (`target_role: validador`). Bloqueada por
      033–036. `draft` até as estórias de implementação avançarem.

### Ordem sugerida e paralelismo

STORY-033 primeiro (impacto visível amplo, baixo risco, destrava a sensação de app). STORY-035 é isolada
(regra de ingestão) e pode correr em paralelo. STORY-034 é a maior (tem dado novo + tela nova) —
Designer e Programador pegam juntas pelo modelo paralelo. STORY-036 é rápida e independente. STORY-037
fecha o épico.

## Validação final

Critérios em `validation/checklist.md`. Relatório do validador em `validation/report.md`.

**Definição de épico concluído:** todas as estórias `done` + validação `approved` + no celular, em
homologação: menu inferior fixo em todas as telas logadas, detalhe do cupom com itens acessível por 1
toque, cupom fora da janela rejeitado, sem excluir conta, menu de escanear renomeado.

## Histórico

- 2026-07-05 — criado e **decomposto (Fluxos A+B)** por PO, a partir de 9 ajustes do teste mobile do
  Alexandro. Consolidado em 4 estórias de implementação (casca mobile; cupom detalhado; validade
  parametrizável; coerência de jornada) + validação. Adicionado à WAVE-2026-02 como refinamento
  pré-piloto. STORY-034 é `requires_design` (brief em `design-handoff.md`). Possíveis IDRs em 034/035.
