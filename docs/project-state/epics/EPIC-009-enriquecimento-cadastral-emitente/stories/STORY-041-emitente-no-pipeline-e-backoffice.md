---
story_id: STORY-041
slug: emitente-no-pipeline-e-backoffice
title: Emitente enriquecido no pipeline do cupom e visível no Backoffice
epic_id: EPIC-009
sprint_id: null
type: implementation
target_role: programador
requires_design: false
status: in_review
owner_agent: claude-programador-story041
created_at: 2026-07-06
updated_at: 2026-07-06
estimated_session_size: M
---

# STORY-041 — Emitente enriquecido no pipeline do cupom e visível no Backoffice

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. Se algo estiver
> ambíguo, registre em "Notas do agente" e pause em vez de adivinhar.

## Contexto (por que esta estória existe)

A STORY-040 entregou o serviço de enriquecimento CNPJ. Agora ele precisa entrar no fluxo real: todo
cupom validado/deduplicado dispara o enriquecimento do seu emitente, e o resultado fica visível no
Backoffice — a evidência observável do épico.

- Épico: `epics/EPIC-009-enriquecimento-cadastral-emitente/epic.md`
- Documentos canônicos a ler ANTES de codificar:
  - ADRs da STORY-039; `decisions/adr/ADR-001` (pipeline canônico do cupom), `ADR-003` (dedup)
  - `decisions/adr/ADR-009` (RBAC — Backoffice)
  - `docs/especificacao/design-system.md` (padrão visual das telas)

## O quê (objetivo desta estória)

Integrar o enriquecimento ao pipeline do cupom (disparo assíncrono pós-validação) e exibir os dados do
emitente no detalhe do cupom no Backoffice, incluindo o estado do enriquecimento (pendente / enriquecido
/ indisponível).

## Por quê (valor para o usuário)

Fecha o outcome do épico: o operador enxerga a categoria de cada estabelecimento, e o EPIC-010 passa a
ter o insumo fluindo automaticamente para todo cupom novo.

## Critérios de aceite

- [ ] **CA-1:** Dado um cupom que completa validação/deduplicação, então o enriquecimento do emitente é
      disparado automaticamente via fila, sem alterar a latência percebida do envio pelo Colaborador.
- [ ] **CA-2:** Dado um cupom cujo emitente já está no cache, então o vínculo é imediato, sem chamada
      externa.
- [ ] **CA-3:** No Backoffice, o detalhe do cupom exibe razão social, CNAE (código + descrição),
      município/UF e situação cadastral do emitente — em pt-BR, no padrão visual do DS.
- [ ] **CA-4:** Cupom com enriquecimento pendente ou indisponível exibe o estado correspondente de
      forma clara (sem erro, sem campo vazio mudo).
- [ ] **CA-5:** Cupons de um mesmo CNPJ compartilham o mesmo registro de emitente (sem duplicação).
- [ ] **CA-6:** E2E: enviar cupom → cupom listado no Backoffice com emitente enriquecido (ou estado
      pendente que resolve após processamento da fila).

## Fora de escopo

- Pontuação (EPIC-010); telas do Colaborador; tela de configuração do TTL (EPIC-012).
- Reprocessamento em massa de cupons antigos (se necessário, decisão do PO na abertura do EPIC-011).

## Padrões de qualidade exigidos

`docs/skills/po/references/quality-standards.md`: cobertura ≥ 80% (novo) / ≥ 98% (regra de negócio);
E2E do fluxo (CA-6) obrigatório; sem passos manuais.

## Dependências

- **Bloqueada por:** STORY-040.
- **Bloqueia:** STORY-042 (validação) e a decomposição do EPIC-010.
- **Pré-requisitos de ambiente:** homologação com fila operante.

## Decisões já tomadas (não as reabra)

- PDR-004 (regra 2); ADRs da STORY-039; ADR-001/003/009.

## Liberdade técnica do agente

Como na STORY-040: estrutura de código, testes e refatorações locais são suas; fonte/fila/cache e
critérios de aceite não. Sem ADR para algo necessário → pare e registre.

## Definição de Pronto (DoD)

- [ ] Todos os CAs passam, com testes (incluindo E2E CA-6).
- [ ] Coberturas atingidas; CI verde; deploy em homologação verificado.
- [ ] IDR registrado se aplicável; `index.json` e esta estória atualizados.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`.

## Notas do agente (preenchido durante/após execução)

> Sessão `claude-programador-story041` — skill `programador` + sub-skill `stacks/laravel`.

### Decisões tomadas (locais, dentro dos ADRs)
- **Pipeline (CA-1/CA-2):** listener enfileirado `EnriquecerEmitenteAoValidar` reage a `CupomValidado`
  (padrão IDR-008) e delega a `EnriquecimentoService::solicitar($cupom->cnpj_emitente)` — cache-first, roda
  fora do request. Registrado no `AppServiceProvider` ao lado do listener de cashback.
- **Vínculo cupom↔emitente por CNPJ (ADR-014):** `Cupom::emitente()` = `belongsTo(Emitente, 'cnpj_emitente',
  'cnpj')` — relação **lógica**, sem FK nem `emitente_id`. Cupons do mesmo CNPJ compartilham o registro (CA-5).
- **Read-model `ApresentacaoEmitente`** (presenter puro): traduz o registro (ou a ausência dele) num
  view-model com **estado + rótulo + variante de badge** — `pendente` (sem registro), `enriquecido`,
  `sem_cnae`, `nao_encontrado`, `indisponivel`. Garante o CA-4 (nunca campo vazio mudo). CNAE formatado
  `XXXX-X/XX — descrição`.
- **Backoffice:** rotas `backoffice.cupons.index/show` dentro do grupo `auth + can:operar-saques` (RBAC
  ADR-009, guard por grupo). Páginas Inertia com a casca hand-rolled do Backoffice (sem layout dedicado,
  como Saques) e componentes do DS (`Badge`, `Card`, helper `Linha`).
- **Sem reprocessamento de cupons antigos** (fora de escopo): `emitentes` popula na demanda.

### Descobertas
- A **lista** do Backoffice mostra todos os cupons `validado` (inclusive dados de dev). No E2E Dusk, isso
  torna "clicar no primeiro item" não-determinístico; provei "listado com o estado" na lista e abri o
  detalhe **por id** (determinístico). Nos testes de feature (RefreshDatabase) a lista é isolada.
- **Fila no Dusk não é `sync`** (`.env.dusk.local` usa `database`), então o E2E semeia o **estado final**
  do emitente diretamente (convenção Dusk do projeto) em vez de esperar a fila.
- Testes de feature Inertia usam `withoutVite()` (idioma do projeto) — evita depender do manifest.
- O `nome_emitente` do cupom (snapshot do DANFE, IDR-015) e a `razao_social` do emitente (RFB) convivem: o
  detalhe mostra os dois (a nota e o cadastro oficial), que podem diferir (fantasia × razão social).

### Bloqueios encontrados
- Nenhum. Todos os CAs cobertos pelos ADRs da STORY-039/040.

### IDRs criados
- Nenhum. Decisões locais dentro dos ADR-012/013/014; nenhuma introduz padrão transversal novo.

### Cobertura final
- **100%** nos arquivos novos/tocados: `ApresentacaoEmitente`, `Listeners/EnriquecerEmitenteAoValidar`,
  `Backoffice/CuponsController`, `Models/Cupom`. Suíte completa: **390 verdes**; total do projeto **96,2%**
  (gate `--min=80` ✅). Dusk (E2E): **3 verdes**. Pint limpo.

### Mapa CA → teste (final)
- **CA-1** (disparo automático via fila): `EnriquecerEmitenteAoValidarTest::test_handle_solicita_enriquecimento_do_cnpj_do_cupom`,
  `..._test_pipeline_real_enriquece_o_emitente_ao_validar` (capturar → validado → listener → job → persiste).
- **CA-2** (cache, sem chamada externa): `EnriquecerEmitenteAoValidarTest::test_emitente_ja_em_cache_nao_redispara`.
- **CA-3** (detalhe mostra razão/CNAE/município/UF/situação): `CuponsBackofficeTest::test_detalhe_mostra_emitente_enriquecido`,
  `ApresentacaoEmitenteTest::test_enriquecido_mostra_todos_os_campos`.
- **CA-4** (estados pendente/indisponível claros): `ApresentacaoEmitenteTest` (todos os estados),
  `CuponsBackofficeTest::test_detalhe_sem_emitente_mostra_pendente`.
- **CA-5** (mesmo CNPJ = um emitente): `CuponsBackofficeTest::test_cupons_do_mesmo_cnpj_compartilham_emitente`,
  `EnriquecerEmitenteAoValidarTest::test_dois_cupons_do_mesmo_cnpj_compartilham_um_emitente`.
- **CA-6** (E2E enviar→listado→detalhe): `BackofficeCuponsTest::test_operador_ve_cupom_e_emitente_enriquecido`,
  `..._test_cupom_sem_enriquecimento_mostra_pendente`, `..._test_nao_operador_e_barrado`.

### Links de evidência
- Commits: `test(STORY-041)` (vermelho) → `feat(STORY-041)` (verde) na `main` (listener + Backoffice + E2E).
- Dusk: `BackofficeCuponsTest` (3 cenários) rodou em Chrome real (container `quantah-selenium`).

### Pendências de DoD (fora da minha alçada nesta sessão)
- **CI verde + deploy homolog verificado:** commits na `main` **local**; aguarda o **push** (dispara CI/CD +
  deploy homolog) para fechar como `done`.
