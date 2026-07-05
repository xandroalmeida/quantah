---
story_id: STORY-036
slug: coerencia-perfil-e-navegacao
title: Coerência de jornada — remover excluir conta e renomear o menu "Cupons" para escanear
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

# STORY-036 — Coerência de perfil e navegação

> **Para o agente que vai executar:** leia por inteiro. Dois ajustes pequenos e independentes de coerência
> da jornada. Sem tela nova; toca navegação (i18n) e Perfil.

## Contexto (por que esta estória existe)

Dois pontos de incoerência apareceram no teste:

1. **Excluir conta** está exposto no Perfil (`DeleteUserForm`, herança do Breeze) sem necessidade nesta
   fase — risco de auto-exclusão acidental e superfície a mais para suportar no piloto.
2. O menu **"Cupons"** (`nav.bottom`/`nav.bar`) leva ao **escaneamento** (`/coletar`), mas o rótulo sugere
   uma **lista de cupons** — expectativa errada. Deve dizer o que faz (ex.: "Escanear").

- Épico: `epics/EPIC-007-refinamento-experiencia-b2c-mobile/epic.md`
- Documentos a ler ANTES: IDR-010 (i18n — chave = string-fonte em inglês, resolvida em `lang/pt_BR.json`),
  DDR-007 (casca logada). 
- Arquivos-âncora: `resources/js/Layouts/AppLayout.jsx` (`SECOES`), `resources/js/i18n.js` +
  `resources/js/lang/pt_BR.json` (ou `app/lang/pt_BR.json`), `resources/js/Pages/Profile/Edit.jsx`,
  `resources/js/Pages/Profile/Partials/DeleteUserForm.jsx`, `app/Http/Controllers/ProfileController.php`
  (`destroy`), `routes/web.php` (`profile.destroy`).

## O quê (objetivo desta estória)

1. **Remover a opção de excluir conta** da UI do Perfil (retirar o `DeleteUserForm` da tela). Avaliar
   remover/neutralizar a rota `DELETE /profile` e `ProfileController@destroy` para não deixar a ação
   acessível por baixo dos panos (decidir e registrar).
2. **Renomear o menu "Cupons"** para um rótulo que sugira escanear (ex.: **"Escanear"**), via i18n
   (IDR-010) — atualizando a string-fonte e o `pt_BR.json`. O destino (`/coletar`) e a seção ativa não mudam.

## Por quê (valor para o usuário)

Menos risco de erro (sem exclusão acidental) e um menu que **diz o que faz** — navegação mais previsível.

## Critérios de aceite

- [ ] **CA-1:** A tela de Perfil **não exibe** a opção de excluir conta.
- [ ] **CA-2:** A ação de exclusão não fica acessível por rota direta (rota/controller removidos ou
      bloqueados) — decisão registrada nas notas; sem quebrar as demais ações do Perfil (dados/senha).
- [ ] **CA-3:** O item de menu antes "Cupons"/"Coupons" agora exibe um rótulo de **escanear** (pt-BR), em
      `nav.bottom` (mobile) e `nav.bar` (desktop), com a seção ativa e o destino `/coletar` intactos.
- [ ] **CA-4:** Texto novo segue o mecanismo de i18n (IDR-010); 100% pt-BR; a11y (`aria-current`,
      alvo ≥48px) preservada.

## Fora de escopo

- Fluxo alternativo de exclusão de conta (LGPD/atendimento) — se necessário, vira estória própria.
- Reestilização dos formulários do Perfil (dívida R5 do EPIC-006).
- Renomear outras seções do menu.

## Padrões de qualidade exigidos

Segue `quality-standards.md`. Teste/contrato do menu (rótulo/i18n/testid) e do Perfil sem o form de
exclusão; se a rota for removida, teste que `DELETE /profile` não executa. pt-BR; a11y.

## Dependências

- **Bloqueada por:** EPIC-006 `done` (casca/Perfil na área logada).
- **Bloqueia:** STORY-037 (validação do épico).
- **Decisões:** decisão sobre remover vs bloquear a rota `destroy` → registrar (nota/IDR curto se durável).

## Decisões já tomadas (não as reabra)

- IDR-010 (mecanismo de i18n) e DDR-007 (fonte única da navegação) valem. Verde como único accent (PDR-001).

## Definição de Pronto (DoD)

- [ ] CA-1 a CA-4 passam.
- [ ] Testes verdes (menu renomeado; Perfil sem exclusão; rota tratada).
- [ ] Pipeline verde; decisão da rota registrada; `index.json` = `done`; "Notas do agente" preenchida.

## Protocolo do agente (obrigatório)

Siga `agent-task-format.md`. Sem tela nova. Registre a decisão sobre a rota de exclusão.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas

- **Excluir conta: removida (não bloqueada)** — apaguei `DeleteUserForm`, a rota `profile.destroy` e
  `ProfileController@destroy`. `DELETE /profile` → 405. Registrado em **IDR-014** (fluxo LGPD vira
  estória futura). Import `Auth` órfão removido do controller.
- **Rótulo do menu via i18n (IDR-010):** chave-fonte `Scan` → `"Scan": "Escanear"` no `lang/pt_BR.json`.
  A seção continua `cupons` (testid `app-nav-cupons`, `aria-current`) e o destino `/coletar` intactos —
  só o texto muda.

### Descobertas

- `getText()` do Selenium aplica `text-transform`, então o botão "Salvar" (uppercase no DS) vem como
  "SALVAR" — no E2E asserto títulos estáveis dos formulários que permanecem ("Informações do Perfil",
  "Atualizar Senha") em vez do texto do botão.
- Nenhum teste dependia do rótulo "Cupons" do nav (a ref em `CapturaScreenContractTest` é "Escanear QR
  Code", outra string). `lang/pt_BR.json` é alfabético — inseri `Scan` na posição correta.

### Mapeamento CA → teste (todos verdes)

- **CA-1** → `CoerenciaJornadaTest::test_perfil_nao_exibe_excluir_conta` (E2E: sem "Excluir Conta";
  forms de dados/senha presentes).
- **CA-2** → `ProfileTest::test_account_deletion_route_is_removed` (405 + conta persiste) e
  `test_profile_destroy_route_name_does_not_exist`.
- **CA-3** → `CoerenciaJornadaTest::test_menu_escanear_renomeado_mantem_destino_e_secao` (rótulo
  "Escanear"; click → `/coletar`; `aria-current=page`).
- **CA-4** → mesmo E2E (i18n `Scan`→Escanear, pt-BR, alvo ≥48px verificado por rect.height).

### Bloqueios encontrados

Nenhum.

### Links de evidência

- Testes: `ProfileTest` (rota removida) + `CoerenciaJornadaTest` (E2E) — verdes.
- Suíte completa: **Pest 313/313**; **Dusk 89/89**.
- Decisão: `decisions/idr/IDR-014-exclusao-de-conta-removida-nesta-fase.md`.
- Arquivos: `AppLayout.jsx`, `lang/pt_BR.json`, `Pages/Profile/Edit.jsx`, `ProfileController.php`,
  `routes/web.php`; removido `Pages/Profile/Partials/DeleteUserForm.jsx`.
