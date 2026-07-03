---
story_id: STORY-009
slug: captura-qr-confirmacao
title: Captura do cupom — escanear/compartilhar o QR da NFC-e e confirmar (mobile)
epic_id: EPIC-002
sprint_id: null
type: implementation
target_role: programador
requires_design: true
design_screen_id: null
status: draft
owner_agent: null
created_at: 2026-07-02
updated_at: 2026-07-02
estimated_session_size: L
---

# STORY-009 — Captura do QR e confirmação

> **Para o agente que vai executar:** leia por inteiro. `requires_design: true` — Designer entrega a
> spec de tela em paralelo (PDR-002). Fluxo **mobile-first**, compondo os componentes do DS (EPIC-001).

## Contexto (por que esta estória existe)

A coleta precisa de fricção mínima: o Colaborador escaneia o QR da NFC-e (ou compartilha o link do QR
para o app) e recebe confirmação de que o cupom foi capturado. É a porta de entrada do dado — se for
difícil, não há base.

- Épico: `epics/EPIC-002-coleta-de-cupom/epic.md`
- Documentos a ler ANTES: `docs/visao.md` §6.1–6.2; ADR-001/002/003 (STORY-008); componentes do DS
  (`Components/`) e a vitrine `/ds`; `docs/skills/stacks/inertia-react/SKILL.md`.

## O quê (objetivo desta estória)

Implementar o fluxo de **captura mobile**: (a) escanear o QR pela câmera **e** (b) receber um QR/URL
compartilhado (share target / colar link) → extrair a chave de acesso → mostrar **confirmação**
"cupom capturado" com o componente de feedback do DS.

## Por quê (valor para o usuário)

É o gesto central do Colaborador. Uma captura em segundos, com confirmação clara, é o que faz o dado
existir e o usuário confiar que "contou".

## Critérios de aceite

- [ ] **CA-1:** Em mobile, o usuário escaneia o QR da NFC-e pela câmera e o app extrai a **chave de
      acesso de 44 dígitos** da URL do QR.
- [ ] **CA-2:** O usuário também consegue **compartilhar/colar** o link do QR para o app e chegar ao
      mesmo ponto (chave extraída), sem câmera.
- [ ] **CA-3:** Após capturar, o app mostra **confirmação visível** ("cupom capturado/enviado") usando
      os componentes do DS (snackbar/empty-state/estados), com foco/a11y do EPIC-001.
- [ ] **CA-4:** Entrada inválida (QR que não é NFC-e, chave malformada) é tratada com mensagem de erro
      do DS — sem quebrar o fluxo.
- [ ] **CA-5:** A captura envia a chave para o backend de ingestão (contrato definido na ADR-001);
      **não** duplica regra de validação/dedup (isso é STORY-010) — aqui é captura + handoff.

## Fora de escopo

- Validação SEFAZ / deduplicação / persistência canônica (STORY-010).
- Anonimização de CPF (STORY-011) e instrumentação (STORY-012).
- Crédito de cashback (EPIC-003).

## Padrões de qualidade exigidos

Segue `quality-standards.md`. E2E em **browser real** do fluxo de captura (caminho feliz + erro);
a11y mínima; cobertura ≥80% no código novo; mobile-first sem overflow horizontal.

## Dependências

- **Bloqueada por:** STORY-008 (ADR-001 contrato de ingestão; ADR-003 formato da chave).
- **Bloqueia:** STORY-013 (validação do épico).
- **Pré-requisitos:** componentes do DS (EPIC-001), homologação (EPIC-000).

## Decisões já tomadas (não as reabra)

- ADR-000/007/008; ADR-001/002/003 (STORY-008). Regras do DS (EPIC-001). Escopo só SP.

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-5 passam; **Designer confirmou a spec de tela**.
- [ ] E2E em browser real (captura por câmera simulada/URL + erro) verde; a11y verificada.
- [ ] Pipeline verde; fluxo acessível em homologação (mobile).
- [ ] IDR se houve decisão técnica relevante; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. `requires_design: true` → alinhe cedo com o Designer. Falta de ADR/
decisão de arquitetura → `blocked` + escalar (não decida sozinho).

## Notas do agente (preenchido durante/após execução)

> **Status:** `done`. Spec de tela (Designer) + implementação (Programador) + E2E em browser real,
> tudo verde. Sessão única cobrindo os dois papéis (cowork).

### Decisões tomadas
- **IDR-003 — `@zxing/browser` por import dinâmico** para o decode do QR pela câmera (CA-1), escolhido
  pelo Alexandro por cobrir **iOS Safari + Android** (o `BarcodeDetector` nativo não cobre iOS). O
  chunk fica **fora do bundle inicial** (só baixa ao abrir a câmera — verificado no build: chunk
  `esm-*.js` separado do `app-*.js`). Scanner isolado em `Components/coleta/QrScanner.jsx`.
- **Rota aberta** `/coletar` (sem auth nesta onda): a coleta pura não precisa de login (dedup é por
  chave, não por usuário); a atribuição ao Colaborador/cashback vem com a Carteira (EPIC-003). POST
  protegido por `throttle:30,1`.
- **Handoff sem reimplementar regra (CA-5):** o controller delega a `IngestaoCupomService::capturar()`
  (novo entrypoint) — parse + escopo (SP/NFC-e) + dedup + persiste `pendente`. **Não extrai**: a
  validação SEFAZ/normalização é a STORY-010 (o cupom fica `pendente` até lá; o Job de extração será
  despachado ali). Binding do serviço sem adaptador no `AppServiceProvider` (o `SpSefazAdapter` real
  entra na 010).
- **Erro ancorado no campo (CA-4):** rejeição vira `ValidationException` em `entrada` (não banner
  global); microcopy centralizada no controller, espelhando o spec §5.

### Descobertas
- A câmera (`getUserMedia`) exige **HTTPS** — homologação já é TLS; em local `localhost` é contexto
  seguro. Em headless (Dusk) não há câmera → o scanner **degrada graciosamente** para colar link, o
  que o E2E exercita.
- `ResultadoIngestao` não pode ter método estático e de instância com o mesmo nome (`rejeitado`) —
  PHP dá fatal ("premature end of PHP process"). Predicado renomeado para `foiRejeitado()`.
- Flash do Inertia não vinha compartilhado; adicionei `flash.coleta` (sem PII) no
  `HandleInertiaRequests` para a confirmação pós-`back()`.

### Bloqueios encontrados
- Nenhum. Dependência da STORY-008 (contrato ADR-001, formato da chave ADR-003) já estava aceita.
- **Pendência leve (Designer):** validação humana do protótipo (`prototype_last_validated_at: null`) —
  o protótipo cobre todos os estados; recomendo um olhar do Alexandro, mas não bloqueia o `done`
  (nesta sessão o papel Designer confirmou a spec).

### Links de evidência
- Spec de tela: `design/screens/STORY-009-captura-qr-confirmacao/screen-spec.md` (+ protótipo
  `index.html`), registrada em `index.json › design.screens` (`shipped`).
- Frontend: `resources/js/Pages/Coleta/Captura.jsx`, `resources/js/Components/coleta/QrScanner.jsx`,
  ícone `QrIcon` em `Components/icons.jsx`.
- Backend: `Http/Controllers/ColetaController.php`, rota `routes/web.php` (`coleta.create`/`coleta.store`),
  `IngestaoCupomService::capturar()`, `HandleInertiaRequests` (flash), `AppServiceProvider` (binding).
- Decisão: `decisions/idr/IDR-003-zxing-browser-decode-qr.md` (indexada).
- Testes (verdes): Feature `tests/Feature/Coleta/ColetaControllerTest.php` (contrato do handoff) +
  `CapturaScreenContractTest.php` (microcopy/tokens/testids); **E2E browser real**
  `tests/Browser/ColetaCapturaTest.php` (colar válido → confirmação; inválido → erro; câmera
  indisponível → degrada; alvo de toque ≥48px). Suíte não-Dusk 95/95; Dusk 4/4.
- Comandos: `make test` (unit+feature) e `make e2e` (Dusk em browser real).
