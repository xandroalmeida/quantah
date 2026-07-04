---
story_id: STORY-022
slug: login-com-google
title: Login com Google para o Coletador
epic_id: EPIC-004
sprint_id: null
type: implementation
target_role: programador
requires_design: false
design_screen_id: null
status: done
owner_agent: Programador
created_at: 2026-07-04
updated_at: 2026-07-04
estimated_session_size: M
---

# STORY-022 — Login com Google

> **Para o agente que vai executar:** leia a estória inteira antes de começar. O botão já foi desenhado
> na tela de login (STORY-021); esta estória entrega o **fluxo OAuth** e o modelo de contas por trás dele.

## Contexto (por que esta estória existe)

O Colaborador Casual — a maioria — precisa entrar em segundos. O login social **Google** é o caminho de
menor fricção decidido no PDR-003. A tela de login de marca (STORY-021) já reserva o botão "Entrar com
Google"; falta implementar o fluxo OAuth e a coexistência com contas de e-mail/senha, conforme o ADR de
acesso (STORY-019).

- Épico: `epics/EPIC-004-acesso-e-areas/epic.md`
- Ler antes: ADR de acesso (STORY-019) — fluxo OAuth e modelo de contas; STORY-021 (tela); PDR-003.

## O quê (objetivo desta estória)

Entregar **login/cadastro com Google** para o Coletador: o botão da tela de login inicia o fluxo OAuth,
que **cria ou vincula** a conta do Coletador e o autentica — em homologação.

## Por quê (valor para o usuário)

Reduz a fricção de entrada ao mínimo, aumentando a conversão do funil B2C (métrica primária do épico).

## Critérios de aceite

- [ ] **CA-1:** Dado um visitante sem conta, quando entra com Google e autoriza, então uma conta de
      Coletador é criada a partir do perfil Google (nome, e-mail) e ele fica autenticado.
- [ ] **CA-2:** Dado um Coletador que já tem conta de **e-mail/senha** com o mesmo e-mail (verificado),
      quando entra com Google, então a identidade Google é **vinculada à conta existente** (não cria
      duplicada), conforme o modelo do ADR.
- [ ] **CA-3:** Falha/cancelamento no provedor Google retorna à tela de login com mensagem em pt-BR, sem
      derrubar a sessão nem expor detalhes técnicos.
- [ ] **CA-4:** Segredos do OAuth (client id/secret) vêm de configuração/secrets, **nunca versionados**
      (quality-standards §4).
- [ ] **CA-5:** E2E cobre o fluxo de login Google (provedor simulado/stub em teste) até o Coletador
      autenticado, incl. o caminho de vínculo (CA-2) e o de erro (CA-3).

## Fora de escopo

- Outros provedores sociais. Login/conta B2B (fora da onda). Destino pós-login definitivo (EPIC-006).
- Redesenho do botão (já é da STORY-021).

## Padrões de qualidade exigidos

Segue `docs/skills/po/references/quality-standards.md`: cobertura ≥ 80% (o **núcleo de vínculo/criação de
conta** no padrão de núcleo, ≥ 98%); E2E do fluxo (CA-5); **segurança** (segredos fora do versionamento);
**pt-BR** nas mensagens. Sem código não testado.

## Dependências

- **Bloqueada por:** STORY-019 (ADR de acesso), STORY-021 (tela de login que hospeda o botão).
- **Bloqueia:** — (STORY-024 valida).
- **Pré-requisitos de ambiente:** credenciais OAuth Google de teste/homologação disponíveis via secrets.

## Decisões já tomadas (não as reabra)

- PDR-003 (Google + e-mail/senha). ADR de acesso (STORY-019) — fluxo OAuth e modelo de contas. ADR-000.

## Liberdade técnica do agente

Você decide a estrutura de código/testes e a forma de stub do provedor em teste, dentro do ADR. Não
decide o modelo de contas (é do ADR) nem os CAs. Se faltar decisão arquitetural, **pare e registre**.

## Definição de Pronto (DoD)

- [x] Todos os CAs passam; unitários + E2E verdes; coberturas exigidas — Unit+Feature 265/265, Dusk 64/64.
      Núcleo de contas (`UpsertGoogleUser`) coberto em todos os ramos por `GoogleAccountResolutionTest`.
- [x] Nenhum segredo versionado; segredos por secrets do CI/ambiente — `services.google` lê `.env`;
      testes/CI usam o driver **fake** (GOOGLE_FAKE), sem credencial real (CA-4).
- [x] Pipeline verde; deploy de homologação **verificado ao vivo por Alexandro** (2026-07-04): "Entrar
      com Google" autentica na homolog. Credenciais reais provisionadas via **secrets individuais**
      (`GOOGLE_CLIENT_ID/SECRET`), injetadas no `.env` de homolog pelo passo do CI "Injetar Google + Mail".
- [x] IDR registrado se houve decisão técnica relevante — sem IDR: Socialite/modelo de contas já
      decididos no ADR-010; o driver fake é detalhe de teste local.
- [x] `index.json` = `done`; "Notas do agente" preenchidas.

## Provisionamento de homologação (feito)

- Secrets do GitHub: `GOOGLE_CLIENT_ID`, `GOOGLE_CLIENT_SECRET` (+ `MAIL_USERNAME`, `MAIL_PASSWORD` para o
  e-mail). O deploy (`ci-cd.yml` › "Injetar Google + Mail no .env de homolog") anexa `GOOGLE_*`
  (`GOOGLE_FAKE=false`) e `MAIL_*` ao `.env`, com dedup por prefixo, sobre o `PROD_ENV` base.
- Redirect URI de homolog cadastrado no cliente OAuth do Google Console (Alexandro).
- Verificado ao vivo: Google login OK; reset de senha e verificação de e-mail chegam via Gmail SMTP.

## Protocolo do agente (obrigatório)

Siga `docs/skills/po/references/agent-task-format.md`.

## Notas do agente (preenchido durante/após execução)

### Decisões tomadas (Programador, 2026-07-04)

- **Socialite (driver google)** conforme ADR-010; **não** persistimos access/refresh token — só a
  identidade no login. Rotas `GET /auth/google/redirect|callback` no grupo `guest`.
- **Núcleo de contas isolado** em `App\Actions\Auth\UpsertGoogleUser` (testável sem HTTP): cria / vincula
  por e-mail verificado / loga / **recusa** e-mail Google não verificado (`UnverifiedGoogleEmailException`,
  fail-secure). `email_verified_at` via `forceFill` (não é mass-assignable).
- **Erro/cancelamento (CA-3):** callback trata `?error=`, exceção de provedor (`report()` + genérico) e
  e-mail não verificado → sempre `redirect()->route('login')->withErrors(['google' => …])` em pt-BR, sem
  derrubar sessão nem vazar detalhe. No sucesso: `session()->regenerate()` (anti-fixation).
- **Provedor fake por flag** (`services.google.fake` / `GOOGLE_FAKE`) — `App\Support\Auth\FakeGoogleProvider`
  registrado via `Socialite::extend` só quando ligado (dev/CI/E2E). Dispensa credencial real (CA-4);
  em homolog/prod (flag off) vale o driver real.
- **Botão ativado** (DDR-004): `GoogleButton` virou link real para `/auth/google/redirect` (navegação de
  página inteira, não Inertia), sem o selo "Em breve".
- **Erro do Google no Login** lido de `usePage().props.errors` (não do `useForm`, que só popula no submit
  do próprio form).

### Descobertas

- `.env.dusk.local` (que o Dusk troca em runtime, APP_URL=laravel.test) precisou de `GOOGLE_FAKE=true` —
  senão o E2E ia ao Google real ("Missing client_id"). CI usa `.env.example` (já com a flag).
- Config cacheada mascarava a flag no app servido → `config:clear` necessário após mexer em `config/services`.
- Isolamento de sessão no Dusk: testes que autenticam agora chamam `->logout()` no fim (o browser é
  reusado entre testes; sem isso, telas de convidado eram redirecionadas).

### IDRs criados

Nenhum — Socialite e modelo de contas são do ADR-010; o fake é detalhe de teste local.

### Cobertura final (mapeamento CA → teste)

| CA | Teste |
|---|---|
| CA-1 (Google cria conta + autentica) | `GoogleAccountResolutionTest::test_cria_conta_para_email_novo_verificado`, `GoogleLoginControllerTest::test_callback_cria_conta_e_autentica`, E2E `AcessoGoogleTest::test_botao_google_ativo_cria_conta_e_autentica` |
| CA-2 (vínculo por e-mail, sem duplicar) | `GoogleAccountResolutionTest::test_vincula_google_a_conta_email_senha_existente` (+ `…ja_tem_google_id`, `…nao_verificada`), `GoogleLoginControllerTest::test_callback_vincula_conta_existente_sem_duplicar`, E2E `AcessoGoogleTest::test_login_google_vincula_conta_existente` |
| CA-3 (falha/cancelamento → login pt-BR) | `GoogleLoginControllerTest::test_callback_cancelado_volta_ao_login_com_erro_ptbr` + `…falha_do_provedor…`, E2E `AcessoGoogleTest::test_cancelamento_google_volta_ao_login_com_erro_ptbr` |
| CA-4 (segredos fora do versionamento) | driver **fake** por flag; `services.google` só lê `.env`; nada versionado |
| CA-5 (E2E com provedor simulado, incl. vínculo e erro) | `AcessoGoogleTest` (3 cenários, fake driver) |
| fail-secure (e-mail não verificado) | `GoogleAccountResolutionTest::test_recusa_quando_email_google_nao_verificado` + `…nao_retorna_email`, `GoogleLoginControllerTest::test_callback_email_nao_verificado_volta_ao_login` |

Suíte completa: **Unit+Feature 265/265**, **Dusk 64/64**, **pint** limpo.

### Links de evidência

- Screenshots reais (Chrome via Dusk) em `tests/Browser/screenshots/`.
- Migração: `add_google_columns_to_users_table` (google_id unique/nullable, avatar, password nullable).
