---
story_id: STORY-000
slug: spike-stack
title: Spike de stack — validar o esqueleto rodando e a viabilidade de leitura do QR da NFC-e
epic_id: EPIC-000
sprint_id: null
type: spike
target_role: arquiteto
requires_design: false
status: done
owner_agent: claude-arquiteto-story000
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: M
---

# STORY-000 — Spike de stack

> **Para o agente que vai executar:** leia esta estória por inteiro antes de começar. É um
> **spike** — o entregável é conhecimento + evidência, não feature. Registre achados nas "Notas
> do agente" e, se descobrir uma decisão arquitetural durável, registre-a em ADR.

## Contexto (por que esta estória existe)

O ADR-000 ratificou a stack (Laravel + Inertia/React + PostgreSQL, PWA) e o `app/` já foi
scaffoldado. Antes de investir nas estórias de pipeline e nas de coleta, precisamos **provar que
o esqueleto sobe ponta a ponta** e que a peça de maior risco do produto — **ler os dados do
cupom a partir do QR/URL da NFC-e da SEFAZ-SP** — é viável na prática. Isso reduz o risco de
descobrir tarde que o scraping é inviável.

- Épico: `epics/EPIC-000-foundation/epic.md`
- Documentos a ler ANTES:
  - `docs/project-state/decisions/adr/ADR-000-stack-default.md`
  - `docs/visao.md` §6 (mecânica de coleta, chave de acesso de 44 dígitos, URL do QR)
  - `docs/skills/stacks/laravel/SKILL.md` e `docs/skills/stacks/inertia-react/SKILL.md`

## O quê (objetivo desta estória)

Validar que a stack scaffoldada roda 100% local ponta a ponta e produzir uma **prova de conceito
descartável** que, dada a URL de uma NFC-e de SP, obtém os dados estruturados do cupom
(estabelecimento, itens, preços, chave de acesso).

## Por quê (valor para o usuário)

De-risca o núcleo da north-star: sem leitura confiável do cupom, não há coleta nem base de
preços. Confirmar viabilidade agora evita retrabalho caro no EPIC-002.

## Critérios de aceite

Spike: os CAs são de **evidência e recomendação**, não de código de produção.

- [x] **CA-1:** A stack sobe 100% local com um comando (app + Postgres) e serve uma rota Inertia
      no navegador — evidência (print/log) nas Notas do agente.
- [x] **CA-2:** A partir da URL do QR de ao menos uma NFC-e real de SP, a PoC extrai:
      chave de acesso (44 dígitos), estabelecimento, ao menos um item com descrição e preço —
      evidência anexada.
- [x] **CA-3:** Documentado o grau de fragilidade observado (captcha, layout, bloqueio) e o
      caminho recomendado para o EPIC-002 (o que vira ADR-001/002/003).
- [x] **CA-4:** Se a extração se mostrar inviável no prazo, isso está explicitado com alternativas
      (ex.: fonte oficial/credenciamento) — para o PO reavaliar escopo (PDR-002 § sinais de revisão).
      → **N/A: a extração se mostrou VIÁVEL.** O caminho de evolução (fonte oficial) fica documentado
      mesmo assim nas Notas (§ Recomendação), como a visão §6.2 já prevê.

## Fora de escopo

- Construir o módulo de ingestão de produção (é EPIC-002).
- Deduplicação, anti-fraude, anonimização definitivos (EPIC-002).
- Qualquer UI de coleta.

## Padrões de qualidade exigidos

Spike não entrega código de produção; o código da PoC é **descartável** e não precisa das metas
de cobertura. Mas: nenhum segredo commitado, nenhum dado pessoal real persistido, e a PoC roda
contra dados de teste. Padrões plenos em `docs/skills/po/references/quality-standards.md`.

## Dependências

- **Bloqueada por:** nada (é a primeira estória).
- **Bloqueia:** STORY-001, STORY-002 e todo o EPIC-002.
- **Pré-requisitos de ambiente:** `app/` scaffoldado (já existe); Docker/Postgres local.

## Decisões já tomadas (não as reabra)

- ADR-000 (stack) → `decisions/adr/ADR-000-stack-default.md`
- PDR-002 (escopo da onda) → `decisions/pdr/PDR-002-escopo-onda-1.md`

## Liberdade técnica do agente

Como Arquiteto/spike: você decide a abordagem da PoC de extração e o que virá a ser ADR. Você
**não** decide escopo de produto (PO) nem fecha os ADRs de ingestão como aceitos sem aprovação
humana — registre-os como `proposed` se avançar a decisão.

## Definição de Pronto (DoD)

- [x] CA-1 a CA-4 evidenciados nas Notas do agente.
- [x] Recomendação clara para o EPIC-002 (esboço de ADR-001/002/003 — mantidos `deferred` por ADR-000,
      agora com evidência concreta do spike para acelerar sua redação quando o EPIC-002 abrir).
- [x] `index.json` atualizado: status = `done` (spike não passa por validador de épico).
- [x] Notas do agente preenchidas.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`: ao iniciar, `status: in_progress` +
`owner_agent` + `index.json`; ao terminar, preencher Notas do agente, `status: done`, atualizar
`index.json`. Decisão arquitetural durável → ADR (`decisions/adr/`), não IDR.

## Notas do agente (preenchido durante/após execução)

> Executado em 2026-07-02 pelo papel **Arquiteto** (`owner_agent: claude-arquiteto-story000`).
> Ambiente: macOS, Docker + Laravel Sail. Havia **outros agentes rodando** um stack Sail deste mesmo
> projeto (containers `app-*`, no ar há horas); por isso subi um **stack isolado** (project name
> `quantah-s000`, portas livres) sem tocar no dos outros — conforme orientado.

### Decisões tomadas

- **A stack ADR-000 (Laravel + Inertia/React + PostgreSQL via Sail) está apta.** Sobe ponta a ponta
  com um comando e serve Inertia. Nada a reabrir no ADR-000.
- **A extração da NFC-e de SP exige um browser headless (execução de JS), não um HTTP GET + parser.**
  O portal do QR de SP é ASP.NET **WebForms**: a página inicial dispara `__doPostBack(
  'btnDisparaPageLoad','PROSSEGUIR')` no `onload` (ViewState + possível diálogo de confirmação) e só
  então renderiza a DANFE. Um scraper "curl + regex" **não** funciona. Isto é uma restrição de
  arquitetura para o EPIC-002 → alimenta o **ADR-002 (extração resiliente)**.
- **PoC descartável feita com Playwright (Chromium headless).** Escolha para o spike; **não** é decisão
  de produção. Para produção, o stack Sail **já traz um `selenium/standalone-chromium`** (visto rodando
  no compose), que é o caminho idiomático de browser controlável dentro do Laravel + fila de jobs.
  A decisão browser-driver de produção (Playwright PHP vs. Panther/Selenium vs. serviço dedicado) é do
  **ADR-002** quando o EPIC-002 abrir.
- **Chave de acesso e boa parte da validação/dedup saem da URL, sem acessar o portal** (confirma visão
  §6.1). Decompus os 44 dígitos: UF, AAMM de emissão, CNPJ do emitente, modelo (65), série, número, DV.
  Isto sustenta o **ADR-003 (dedup por chave)**: a dedup por chave de acesso pode acontecer **antes** do
  scraping, barato — o scraping (caro/frágil) só roda para chaves novas e bem-formadas.

### Descobertas

- **Consulta manual = captcha; consulta por QR = sem captcha.** A página de consulta pública por chave
  (`ConsultaPublica.aspx`) exige captcha. Já o caminho do **QR Code** (`ConsultaQRCode.aspx?p=...`)
  dispensa captcha porque a URL carrega um **hash assinado** (`cHashQRCode`) — é exatamente o vetor que
  a visão assume. **Coletar via QR é o caminho certo; consulta por chave "seca" esbarra em captcha.**
- **Há duas versões de QR em circulação.** Cupom antigo (2015, `nVersao=100`, formato v1.00) é
  **rejeitado**: *"QR Code inválido. Erro(s): Formato de QR-Code não suportado"*. Cupons no formato
  atual (`?p=chave|2|1|cIdToken|hash`, v2.00) funcionam. **Nota Técnica 2025.001 já introduz o QR v3.0
  (produção a partir de 01/09/2025).** → O **adaptador de SP precisa versionar o parser do QR** e tolerar
  coexistência de versões. Risco de quebra por mudança de formato é real e datado.
- **Retenção longa no portal.** Extraí com sucesso cupons de **2024/08** (7 itens) e **2020/07** (1 item)
  — retenção de consulta ≥ 5 anos. O de 2015 caiu por **formato**, não por purga. Bom para reprocessamento.
- **Layout da DANFE é estável e scrapeável.** Markup padrão do portal NFC-e: itens em `table#tabResult`
  (`.txtTit` descrição, `.RCod` código, `.Rqtd` qtde, `.RUN` unidade, `.RvlUnit` valor unit., `.valor`
  total), emitente em `.txtTopo`, total "Valor a pagar" em `.totalNumb.txtMax`. Seletores estáveis, mas
  **acoplados ao HTML** → monitorar quebra de layout (o "canário" do ADR-002).
- **Gotcha de ambiente (não do produto):** com vários agentes no **mesmo bind-mount** (`.:/var/www/html`),
  o arquivo `public/hot` (flag dev do Vite) **oscila** — ora o app serve via Vite dev, ora via manifest
  buildado. Convém, no EPIC-001/002, isolar instâncias por `COMPOSE_PROJECT_NAME` + portas, ou não
  compartilhar o mount entre agentes. Não afeta a stack em si.

### Bloqueios encontrados

- **Nenhum bloqueio duro.** O único insumo externo — uma URL de QR real de SP — foi obtido de amostras
  públicas de SEFAZ/docs (cupons reais de 2024 e 2020) após o cupom de 2015 ser rejeitado por formato.
  (Pedi ao Alexandro uma URL de cupom recente; segui de forma autônoma com amostras públicas válidas
  enquanto aguardava, e a extração fechou o CA-2 sem depender da resposta.)

### Evidências

Artefatos duráveis em `stories/STORY-000-evidencia/` (a PoC é **descartável**, guardada só como prova):

- **Stack local (CA-1):** `STORY-000-evidencia/CA-1-stack-local.md` — comando de subida, containers
  (`quantah-laravel.test-1` em `:8000`, `quantah-pgsql-1` healthy em `:5442`), `pg_isready` OK,
  `migrate:status` = *Ran*, `GET /` → **HTTP 200** com payload Inertia (`component: Welcome`,
  `laravelVersion 13.18.0`, `phpVersion 8.5.7`), asset buildado `/build/...` → 200.
- **Extração do cupom (CA-2):** `STORY-000-evidencia/extracao-cupom-2024.json` — cupom real
  `35240845543915098211650170000016801096369037` (SP, 2024/08, "HIPER Presidente Prudente",
  CNPJ 45.543.915/0982-11): **chave (44 díg.)**, **estabelecimento**, **7 itens** com descrição, código,
  qtd, unidade e preço, **total R$ 80,39**. PoC: `STORY-000-evidencia/extrair.mjs`.
- Segundo cupom (2020/07, Cia Zaffari, R$ 217,00) também extraído — evidência de retenção longa.

### Recomendação para o EPIC-002 (esboço de ADR-001/002/003)

Os ADRs de ingestão seguem **`deferred`** (como o ADR-000 definiu), com gatilho = abertura do EPIC-002.
O spike os pré-carrega com evidência concreta para redigi-los rápido:

- **ADR-001 — Módulo de ingestão + modelo canônico do cupom (topológico + persistência).** Definir a
  interface do adaptador por estado (`AdaptadorSefaz::coletar(url|chave): CupomCanonico`) e o cupom
  normalizado (emitente{razão, CNPJ}, itens[{descrição, código, éGTIN?, qtd, un, vlUnit, vlTotal}],
  chave44, total, dhEmi). SP é o primeiro adaptador. Ingestão desacoplada da web (job em fila).
- **ADR-002 — Extração resiliente SEFAZ-SP (integração).** **Decisão-chave já de-riscada pelo spike:**
  a coleta **precisa de browser headless** (WebForms + JS + postback). Escolher o driver de produção
  (Selenium/Chromium — já no Sail — vs. Playwright vs. serviço dedicado). Cobrir: fila de
  reprocessamento; **versionamento do parser do QR (v2.00/v3.0 NT 2025.001)**; **monitor de quebra de
  layout** (canário sobre `#tabResult`/`.txtTopo`); rate-limit/backoff educado com o portal; captcha
  **fora do caminho** (usar sempre o QR assinado, nunca a consulta por chave seca).
- **ADR-003 — Dedup e validação por chave (persistência).** Dedup por chave44 **antes** do scraping
  (barato): validar UF=35, modelo=65, DV, well-formedness direto da URL; só chaves novas e válidas vão
  para a fila de extração. Índice único por chave44.
- **Caminho de evolução (visão §6.2, cobre o CA-4):** migrar do scraping para **fonte oficial**
  (web service / XML da NFC-e / credenciamento) quando o volume justificar — mais robusto, sem
  dependência de layout. O scraping via QR valida a tese **agora**; a fonte oficial é o hardening depois.
