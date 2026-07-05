---
story_id: STORY-027
slug: lista-de-leads-no-backoffice
title: Lista de leads B2B no Backoffice, sob o papel operacional
epic_id: EPIC-005
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: done
owner_agent: claude-story027
created_at: 2026-07-05
updated_at: 2026-07-05
estimated_session_size: S
---

# STORY-027 — Lista de leads B2B no Backoffice

> **Para o agente que vai executar:** leia a estória inteira antes de começar. Sem tela de marca nova
> (`requires_design: false`): reuse o padrão visual do Backoffice existente (EPIC-004/STORY-023) e os
> tokens do DS. Há dado pessoal (lead): atenção à LGPD (§4). Se algo estiver ambíguo, registre em "Notas
> do agente" e pause.

## Contexto (por que esta estória existe)

Os leads B2B capturados na landing (STORY-026) precisam ser **visíveis para a operação** para virarem
demanda acionável. Esta estória entrega a **lista de leads no Backoffice**, atrás do papel operacional
(RBAC do ADR-009) — a mesma barreira que já protege o Backoffice de saques (EPIC-004). Fecha o ciclo do
épico: lead capturado → persistido → **visível a quem opera**.

- Épico: `epics/EPIC-005-portas-de-entrada/epic.md`
- Ler antes: STORY-026 (modelo/persistência do lead); ADR-009 (RBAC / papel operacional); a estória de
  segmentação de áreas e guardas do EPIC-004 (STORY-023 — padrão de área do Backoffice); `design/system/`
  (tokens/componentes de tabela/lista); PDR-003.

## O quê (objetivo desta estória)

Expor no **Backoffice**, sob o **papel operacional**, uma **lista simples dos leads B2B** (nome, e-mail,
empresa, data de captação) — viva em homologação, protegida por guarda de acesso.

## Por quê (valor para o usuário)

Sem visibilidade, o lead capturado é um dado morto. Esta lista torna a demanda B2B **observável pela
operação**, condição para dar seguimento comercial e para a métrica do épico ("lead que aparece no
Backoffice").

## Critérios de aceite

Cada item é uma asserção testável. O agente DEVE escrever testes que cubram cada um.

- [ ] **CA-1:** Existe uma rota no **Backoffice** que lista os leads B2B com, no mínimo, **nome, e-mail,
      empresa e data de captação**, em pt-BR e usando o padrão visual do Backoffice / DS.
- [ ] **CA-2:** Dado um usuário **com o papel operacional**, quando acessa a rota, então vê a lista de
      leads persistidos (incluindo os capturados via STORY-026) — em homologação.
- [ ] **CA-3:** Dado um usuário **sem o papel operacional** (Coletador comum ou visitante anônimo), quando
      tenta acessar a rota, então é **barrado** (403/redirect ao login), consistente com a barreira do
      Backoffice do EPIC-004.
- [ ] **CA-4:** A lista tem **estado vazio** tratado (mensagem em pt-BR quando não há leads) e **ordenação
      determinística** (ex.: mais recentes primeiro), verificável por teste.
- [ ] **CA-5:** E2E em browser real percorre: (a) usuário operacional vê um lead recém-capturado na lista;
      (b) usuário sem papel é barrado — com asserções de texto em pt-BR.

## Fora de escopo

- Edição, exclusão, exportação, busca/filtragem avançada dos leads — só listagem simples nesta estória.
- Captação do lead e a landing B2B — são da STORY-026.
- Qualquer ação comercial sobre o lead (contato, status de funil) — fora do épico.

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`: cobertura ≥ 80% no código novo e **≥ 98% na guarda
de acesso** (núcleo de autorização — reuso do RBAC do ADR-009); **E2E em browser real** (CA-5); **a11y AA**;
**pt-BR** (§5.1); **LGPD** (§4 — exibição de PII restrita ao papel operacional, sem PII em log). Sem código
não testado.

## Dependências

- **Bloqueada por:** STORY-026 (precisa do modelo/persistência do lead).
- **Bloqueia:** STORY-028 (validação).
- **Pré-requisitos de ambiente:** homologação operante; usuário com papel operacional semeado; ao menos um
  lead de teste.

## Decisões já tomadas (não as reabra)

- ADR-009 (RBAC — papel operacional guarda o Backoffice). Padrão de área/guarda do EPIC-004 (STORY-023).
- PDR-003 (escopo B2B da onda). PDR-001 (DS). ADR-011/IDR-010 (i18n).

## Liberdade técnica do agente

Você decide a estrutura de código/testes, o componente de lista/tabela (reusando o DS) e refatorações
locais. Você **não** decide stack/framework, critérios de aceite, nem o modelo de RBAC (é do ADR-009). Se
faltar decisão arquitetural sem ADR cobrindo, **pare e registre** em "Notas do agente".

## Definição de Pronto (DoD)

- [x] Todos os critérios de aceite passam.
- [x] Testes escritos e passando; **guarda/controller 100%** (≥98%); total 95,2% (≥80%).
- [x] Teste E2E (CA-5) escrito e passando; lista viva em homologação (operador vê o lead; sem papel barrado).
- [x] Pipeline de CI verde; deploy de homologação realizado e verificado (1ª mão).
- [x] IDR registrado se houve descoberta técnica relevante — **não** (reuso da guarda ADR-009 e do padrão
      visual do Backoffice; nenhuma decisão técnica durável nova).
- [x] `index.json` atualizado: status = `done`.
- [x] "Notas do agente" preenchidas.

### Mapeamento CA → teste

| CA | Teste(s) |
|---|---|
| CA-1 (lista com nome/e-mail/empresa/data, DS, pt-BR) | `Feature/Backoffice/LeadsTest::test_operador_ve_lista_de_leads_com_campos` |
| CA-2 (operador vê os leads persistidos) | `Feature/Backoffice/LeadsTest::test_operador_ve_lista...`; `Browser/BackofficeLeadsTest::test_operador_ve_lead_na_lista` |
| CA-3 (sem papel = barrado) | `Feature/Backoffice/LeadsTest` (coletador → 403; anônimo → redirect /login); `Browser/BackofficeLeadsTest::test_sem_papel_e_barrado` (403 branded pt-BR) |
| CA-4 (estado vazio + ordenação determinística) | `Feature/Backoffice/LeadsTest::test_estado_vazio`, `::test_ordenacao_mais_recentes_primeiro` |
| CA-5 (E2E: operador vê lead recém-capturado; sem papel barrado) | `Browser/BackofficeLeadsTest` (2 cenários) |

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- 2026-07-05 — Rota `GET /backoffice/leads` dentro do grupo `middleware(['auth','can:operar-saques'])`
  existente — reusa a guarda do Backoffice (ADR-009), sem novo gate. Lista só-leitura; ordenação
  determinística `created_at desc, id desc`. Data em pt-BR via `Formato::dataHora` (America/Sao_Paulo).
- 2026-07-05 — Página reusa o padrão visual do Backoffice de saques (tabela desktop + cards mobile,
  `EmptyState`), sem tela de marca nova (`requires_design: false`).

### Descobertas
- 2026-07-05 — Fechou o loop do épico ao vivo: o lead capturado na landing B2B (STORY-026) aparece na
  lista do Backoffice sob o papel operacional, verificado de 1ª mão em homologação.

### Bloqueios encontrados
- <nenhum>

### IDRs criados
- <nenhum — reuso de padrões existentes>

### Cobertura final
- Guarda/controller (`Backoffice/LeadsController`): **100%** (≥98%). Total do projeto: **95,2%** (gate 80%).
- E2E (Dusk): operador vê o lead recém-capturado; usuário sem papel é barrado (403 pt-BR). Suíte: 295
  Feature/Unit + 73 Dusk verdes.

### Links de evidência
- Commits (main): `<red>` (vermelhos STORY-027), `cd1641c` (feat lista de leads).
- CI/CD (run 28737093347): Testes+build, E2E (Dusk) e Deploy homologação — **verde**.
- Homologação verificada (1ª mão): anônimo em `/backoffice/leads` → 302 `/login` (guarda); operador
  (`test@example.com`) autenticado → 200 `Backoffice/Leads/Index` com o lead `verificacao-story026@quantah.test`
  (capturado na STORY-026) visível na lista.
