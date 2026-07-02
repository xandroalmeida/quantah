---
adr_id: ADR-008
slug: ferramenta-e2e-dusk
title: Ferramenta de E2E em browser real — Laravel Dusk
status: accepted  # proposed | accepted | superseded | rejected | deferred
decided_at: 2026-07-02  # YYYY-MM-DD quando virar accepted
decided_by: arquiteto
approved_by: Alexandro  # ex: "Alexandro" — preenchido na aprovação humana
supersedes: null
superseded_by: null
related_adrs: [ADR-000, ADR-002, ADR-007]
related_pdrs: []
related_epics: [EPIC-000, EPIC-002]
created_at: 2026-07-02
updated_at: 2026-07-02
---

# ADR-008 — Ferramenta de E2E em browser real (Laravel Dusk)

> **Nota de decisão:** o Arquiteto **recomendou Playwright** (Opção A) por consistência com o
> browser-driver do EPIC-002. O **dono (Alexandro) optou por Laravel Dusk** (Opção B) no aceite de
> 2026-07-02 — decisão registrada abaixo. Este ADR preserva a análise original íntegra (a recomendação
> não muda a honestidade do trade-off) e fixa **Dusk** como a ferramenta de E2E do projeto.

> **Gatilho:** STORY-001 exige um teste E2E em browser real (CA-5) e declara que **a ferramenta de
> E2E é decisão de ADR do Arquiteto** ("Dusk default do ecossistema Laravel vs Playwright. Se não
> houver ADR, pare e escale — não escolha sozinho"). Este ADR fecha essa decisão.

## Contexto

O padrão de qualidade do projeto (`po/references/quality-standards.md`, e a skill do Programador)
torna **E2E em browser real inegociável** para todo fluxo de FE web: cada caminho mapeado (feliz,
alternativos, exceção) vira um cenário E2E rodando em **Chromium/navegador de verdade**, não em
simulação. A STORY-001 precisa disso já no hello-world (CA-5) e todas as estórias de produto seguintes
herdarão a ferramenta escolhida aqui.

Dois candidatos idiomáticos existem para uma app **Laravel + Inertia/React**:

- **Laravel Dusk** — o E2E oficial do ecossistema Laravel. Dirige Chrome via ChromeDriver, roda no
  contexto da app (acesso a factories, `DatabaseMigrations`, helpers de login), sintaxe PHP.
- **Playwright** — framework de automação de browser da Microsoft (Node). Dirige Chromium/Firefox/WebKit
  reais, auto-waiting robusto, trace viewer, ótimo em CI.

Fato relevante do histórico do projeto: o **spike STORY-000 já usou Playwright** neste repositório para
provar a extração da NFC-e (o portal da SEFAZ-SP é ASP.NET WebForms e exige JS/postback — um browser
headless controlável). O spike registrou que o **EPIC-002 vai precisar de um browser headless** para o
scraping de produção (ADR-002). Ou seja: o projeto **já terá** uma ferramenta de automação de browser
por necessidade de produto, independentemente do E2E.

## Forças (drivers) da decisão

- **F1 — Fidelidade ao usuário real:** o E2E precisa exercitar o app como o usuário (JS, hidratação do
  Inertia/React, navegação real), não um DOM simulado. **Peso: alto.**
- **F2 — Consistência de ferramental (uma só ferramenta de browser):** o EPIC-002 exige browser
  headless para o scraping; ter **uma** ferramenta de automação de browser no projeto reduz superfície
  cognitiva e de manutenção. **Peso: alto.**
- **F3 — Robustez em CI (baixa flakiness):** E2E frágil que "pisca" vermelho envenena a pipeline e o
  gate de qualidade. **Peso: alto.**
- **F4 — Integração com o back Laravel (dados/estado de teste):** poder semear dados/logar usuário para
  o cenário. **Peso: médio.**
- **F5 — Custo de aprendizado/manutenção:** DX, debugging, documentação. **Peso: médio.**

## Opções consideradas

### Opção A — Playwright — *escolhida*
- **Resumo:** E2E em Node dirigindo Chromium real; roda contra a app servida (local no CI e/ou contra a
  URL de homologação). Preparo de estado via endpoints de teste/seeders ou via a própria UI.
- **Como atende aos princípios:** ✅ Fidelidade (browser real, auto-wait para apps JS como Inertia);
  ✅ Consistência (mesma família de ferramenta do scraping do EPIC-002); ✅ Robustez (auto-waiting +
  trace viewer reduzem flakiness); ⚠️ Integração com Laravel exige um caminho explícito de seed (rota de
  teste/artisan), não vem "de graça" como no Dusk.
- **Prós concretos:** já validado neste projeto (spike STORY-000); melhor tooling de debugging (trace,
  vídeo, screenshots); auto-waiting maduro para SPA/Inertia; roda os 3 engines; excelente em GitHub
  Actions; reaproveitável no EPIC-002 (um só browser-driver no projeto).
- **Contras concretos:** stack de teste em Node separada do PHPUnit/Pest; preparo de dados de teste
  precisa de um mecanismo explícito (rota/comando de seed no ambiente de teste).

### Opção B — Laravel Dusk
- **Resumo:** E2E em PHP, no contexto da app, com ChromeDriver.
- **Como atende aos princípios:** ✅ Integração nativa (factories, `DatabaseMigrations`, login helper);
  ✅ Fidelidade (Chrome real); ⚠️ Robustez (auto-waiting mais manual, mais propenso a `waitFor` na
  mão); ❌ Consistência (seria uma **segunda** ferramenta de browser além do Playwright que o EPIC-002
  vai usar de qualquer jeito).
- **Prós:** tudo em PHP; setup/teardown de banco integrado; idiomático Laravel; menos context-switch para
  quem vive no back.
- **Contras:** duplica o ferramental de browser no projeto (fere F2); auto-waiting menos robusto tende a
  mais flakiness com apps Inertia/React; tooling de trace/debug inferior ao Playwright.

### Opção C — Status quo / não decidir agora
- **Consequência:** STORY-001 não fecha o CA-5; o gate de E2E de todas as estórias seguintes fica indefinido.
- **Custo de adiar:** alto — E2E é gate duro do projeto; sem ferramenta, nenhuma estória de FE fecha.

## Matriz comparativa

| Critério (força) | Peso | A — Playwright | B — Dusk |
|---|---|---|---|
| F1 — Fidelidade ao usuário real | alto | ✅ browser real + auto-wait SPA | ✅ browser real |
| F2 — Consistência (1 ferramenta de browser) | alto | ✅ mesma do scraping EPIC-002 | ❌ segunda ferramenta |
| F3 — Robustez em CI | alto | ✅ auto-waiting + trace | ⚠️ mais waits manuais |
| F4 — Integração com Laravel | médio | ⚠️ seed via rota/artisan explícito | ✅ nativo |
| F5 — Custo de aprendizado/manutenção | médio | ⚠️ stack Node à parte, mas já presente | ✅ tudo PHP |

## Decisão (aceita)

> **Recomendação do Arquiteto: Opção A (Playwright).** **Decisão do dono no aceite: Opção B — Laravel Dusk.**

Adotamos **Laravel Dusk** como a ferramenta padrão de E2E em browser real do Quantah. Alexandro optou
por Dusk priorizando a **integração nativa com o Laravel** (F4) e o **menor context-switch** (F5): a
suíte E2E fica toda em PHP, no mesmo ecossistema do backend, com factories, `DatabaseMigrations` e helper
de login sem cola adicional — o que reduz o atrito de preparo de estado de teste que o Playwright exigiria
(rota/comando de seed explícito). Dusk dirige um **Chrome real** via ChromeDriver, satisfazendo o gate de
"browser de verdade" (F1) e o CA-5 da STORY-001.

O Arquiteto registra que a recomendação técnica era Playwright, pela **consistência de ferramental** (F2)
com o browser-driver que o EPIC-002 vai exigir para o scraping. Essa força **não desaparece**: fica
explícito que o projeto conviverá com **duas ferramentas de automação de browser** — Dusk (E2E) e o
driver de scraping do EPIC-002 (a definir no ADR-002, provavelmente ainda um browser headless controlável).
O dono aceitou esse trade-off conscientemente em favor da coesão com o Laravel na camada de teste.

## Consequências

### Positivas (o que ganhamos)
- **Suíte E2E 100% em PHP/Laravel** — factories, migrations e login helper nativos; preparo de estado de
  teste sem mecanismo extra.
- **Menor context-switch** para quem vive no backend; um só ecossistema de teste (PHPUnit/Pest + Dusk).
- **Chrome real** via ChromeDriver — satisfaz o gate de browser real e o CA-5.

### Negativas / trade-offs aceitos
- **Duas ferramentas de browser no projeto** (Dusk para E2E; o driver de scraping do EPIC-002) — fere a
  força F2 (consistência). Aceito pelo dono em favor da integração com o Laravel.
- **Auto-waiting menos robusto** que o Playwright para apps Inertia/React — exige disciplina de
  `waitFor*` explícito nos cenários para evitar flakiness; mitigado por bons seletores e esperas por
  elemento/estado (nunca `sleep` fixo).
- **Chrome/ChromeDriver no CI** — a versão do driver precisa acompanhar a do Chrome (o `dusk:chrome-driver
  --detect` resolve; fixar no pipeline).

### Neutras
- Onde o E2E aponta (app servida localmente no CI vs URL de homologação) é detalhe de implementação da
  STORY-001 — o CA-5 aceita "homologação **ou** ambiente equivalente". Recomenda-se rodar no CI contra a
  app buildada e, adicionalmente, um smoke contra a URL de homolog pós-deploy.

### Para o time
- **Impacto em estórias existentes:** define o E2E da **STORY-001** (CA-5) e o gate de E2E de todas as
  estórias de FE seguintes — em **Dusk**.
- **ADRs/PDRs relacionados:** o **ADR-002** (driver de scraping do EPIC-002) fica **independente** desta
  escolha — não reusa Dusk; decidirá seu próprio browser-driver de produção. Roda contra o ambiente do
  **ADR-007**.
- **Necessidade de spike:** não — Dusk é o E2E oficial e maduro do Laravel.

## Plano de verificação

- **Como verificar conformidade:** o repositório contém a suíte Dusk (`tests/Browser/`); o CI executa o
  E2E em Chrome real e **falha o merge** se algum cenário quebrar; toda estória de FE web anexa evidência
  (screenshot/console do runner) dos cenários E2E.
- **Sinais de revisão (quando reabrir):** se a flakiness do auto-waiting do Dusk com Inertia/React virar
  dor recorrente que o Playwright resolveria; se fizer sentido unificar E2E e scraping numa só ferramenta
  quando o ADR-002 escolher o driver de produção.

---

## Aprovação humana

- **Status final:** ✅ aceita
- **Aprovado por:** Alexandro
- **Data:** 2026-07-02
- **Forma do aceite:** aprovação explícita em chat (sessão de 2026-07-02)
- **Condicionantes do aceite:** **decisão do dono por Laravel Dusk**, sobrepondo a recomendação do
  Arquiteto (Playwright). Trade-off aceito: duas ferramentas de browser no projeto (Dusk para E2E; driver
  de scraping do EPIC-002 à parte).

---

## Histórico

- 2026-07-02 — criada como `proposed` por Arquiteto, destravando o CA-5 da STORY-001. Recomendação:
  Playwright, por consistência com o browser-driver que o EPIC-002 já exige.
- 2026-07-02 — **aceita** por Alexandro com **Opção B (Laravel Dusk)**, sobrepondo a recomendação;
  título/slug ajustados para refletir a decisão → `accepted`.
