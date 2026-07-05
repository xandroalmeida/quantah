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
status: ready
owner_agent: null
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

- [ ] Todos os critérios de aceite passam.
- [ ] Testes unitários escritos e passando, atingindo as coberturas exigidas (incl. ≥ 98% na guarda).
- [ ] Teste E2E (CA-5) escrito e passando em homologação.
- [ ] Pipeline de CI verde; deploy de homologação realizado e verificado.
- [ ] IDR registrado se houve descoberta técnica relevante.
- [ ] `index.json` atualizado: status = `done`.
- [ ] "Notas do agente" preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas
- <data> — <decisão local>

### Descobertas
- <data> — <gotcha / item para o PO saberem>

### Bloqueios encontrados
- <data> — <bloqueio> — <resolução ou aberto>

### IDRs criados
- <nenhum até aqui>

### Cobertura final
- Unitários: <%>
- E2E: <cenários, evidência>

### Links de evidência
- PR / Pipeline / Deploy de homologação: <urls>
