---
adr_id: ADR-011
slug: i18n-mecanismo-localizacao-ptbr
title: i18n — mecanismo de localização pt-BR (strings e formatos brasileiros)
status: proposed  # proposed | accepted | superseded | rejected | deferred
decided_at: null  # YYYY-MM-DD quando virar accepted
decided_by: arquiteto
approved_by: null  # ex: "Alexandro" — preenchido na aprovação humana
supersedes: null
superseded_by: null
related_adrs: [ADR-000, ADR-010]
related_pdrs: [PDR-003]
related_epics: [EPIC-004]
created_at: 2026-07-04
updated_at: 2026-07-04
---

# ADR-011 — i18n: mecanismo de localização pt-BR

## Contexto

A WAVE-2026-02 (PDR-003) fixa **pt-BR como requisito transversal**: todo texto visível ao usuário final em
português do Brasil, **sem strings de scaffolding em inglês** (o Breeze trouxe "Log in", "Remember me",
"Whoops!", "This password does not match our records") e **sem texto de interface hardcoded fora de um
mecanismo de localização** — é o que exige `docs/skills/po/references/quality-standards.md` §5.1. Além do
idioma, o padrão pede **formatos brasileiros**: moeda `R$ 1.234,56`, datas `dd/mm/aaaa`, fuso
`America/Sao_Paulo`.

A stack (ADR-000) é **Laravel + Inertia/React + PostgreSQL**. O texto ao usuário nasce em **dois lugares**:
(a) no **backend** — mensagens de validação, e-mails transacionais, notificações, strings do framework/Breeze;
(b) no **frontend React** — rótulos, botões, estados vazios, microcópia das telas. Um mecanismo de i18n para
esta stack precisa cobrir os dois sem criar duas fontes de verdade divergentes.

O produto é, hoje e no horizonte previsível, **monolíngue (pt-BR)** — não há requisito de troca de idioma em
runtime nem público em outra língua (PDR-003 é sobre público brasileiro). A decisão, portanto, é escolher o
mecanismo **mais simples que cumpra §5.1** (princípio #1) sem montar um framework multi-locale que ninguém vai
usar — mas deixando a porta aberta para um 2º locale como mudança aditiva (princípio #7), caso um dia surja.

Restrição herdada (importante, não reabrir): a persistência de data/hora é **UTC**
(`stacks/database/database-method.md`, `stacks/laravel/SKILL.md`), com valor canônico **ISO 8601**; a
localização é de **exibição**, não de armazenamento. Este ADR não pode contradizer isso.

## Forças (drivers) da decisão

- **F1 — Cumprir §5.1 sem resíduo:** zero inglês na superfície; nenhuma string de UI hardcoded fora do
  mecanismo; formatos brasileiros (moeda/data/fuso).
- **F2 — Uma fonte de verdade para as strings:** backend e frontend não podem divergir (ex.: mesma mensagem de
  erro escrita duas vezes). Consistência > conveniência local.
- **F3 — Simplicidade (princípios #1, #4):** monolíngue hoje — não introduzir runtime de i18n pesado nem
  dependência transversal grande sem demanda. Usar o que Laravel já entrega.
- **F4 — Compatibilidade com a persistência UTC/ISO (herança):** localização é camada de exibição; não desloca
  o dado canônico.
- **F5 — Reversibilidade / extensibilidade (#7):** se um 2º locale surgir, o mecanismo suporta com mudança
  aditiva (mais um arquivo de traduções), sem reescrever telas.
- **F6 — Fronteira com o Designer:** o **mecanismo** é decisão do Arquiteto; o **conteúdo/voz** das strings é do
  Designer (`design/system/voice-and-tone.md`). O ADR decide onde as strings vivem e como chegam à tela — não a
  redação.

## Opções consideradas

### Opção A — Localização nativa do Laravel (`lang/`) como fonte única, exposta ao React via prop compartilhada do Inertia + helper `t()` fino

- **Resumo:** `app.locale = pt_BR`, `app.faker_locale = pt_BR`. As strings ao usuário vivem em **arquivos de
  tradução do Laravel** (`lang/pt_BR/*.php` + `lang/pt_BR.json`), incluindo validação, auth e e-mails (usando o
  pacote community **`laravel-lang/lang`** para já ter validação/auth/passwords traduzidos, em vez de traduzir à
  mão). O frontend recebe as chaves que a UI usa via **prop compartilhada do Inertia** (`HandleInertiaRequests`)
  e um **helper `t()` leve** no React (thin, sem framework de i18n pesado — opcionalmente `laravel-react-i18n`
  para colar as duas pontas). Uma fonte de verdade (`lang/`), FE e BE consistentes.
- **Como atende aos princípios:**
  - ✅ Simplicidade (#1): usa o mecanismo nativo do framework opinativo (#4); helper `t()` é trivial.
  - ✅ Fonte única (F2): string escrita uma vez em `lang/`, consumida nos dois lados.
  - ✅ Extensibilidade (F5): 2º locale = mais uma pasta `lang/<locale>` + `setLocale`.
- **Prós concretos:** validação/auth/e-mails já cobertos por `laravel-lang`; nada de literais soltos; troca de
  locale trivial se um dia precisar.
- **Contras concretos:** expõe um bundle de strings ao cliente (poucos KB — aceitável); exige disciplina de
  colocar chave em `lang/` em vez de escrever no JSX.

### Opção B — Strings pt-BR direto no React, de um módulo centralizado no FE (sem passar pelo Laravel)

- **Resumo:** um módulo de strings no frontend (`resources/js/strings.ts`), telas consomem dele; backend
  traduz o seu lado à parte.
- **Prós:** não trafega bundle de tradução; simples no FE.
- **Contras:** **duas fontes de verdade** (strings de FE no módulo, strings de BE no `lang/`) que divergem com o
  tempo — fere F2; e mensagens que nascem no backend (validação/e-mail) não têm como ser reaproveitadas na tela.
  Descartada como principal por F2.

### Opção C — Framework de i18n de runtime no frontend (ex.: i18next / react-intl) com múltiplos catálogos

- **Resumo:** runtime completo de i18n no React, com detecção/troca de locale, pluralização ICU, etc.
- **Prós:** poderoso para multi-locale real, pluralização rica.
- **Contras:** **over-engineering** para um produto monolíngue (#1); dependência transversal grande; ainda
  precisaria integrar com o `lang/` do backend para não duplicar. Reavaliável **se** surgir requisito
  multi-locale real — aí vira ADR própria. Descartada agora.

### Opção D — Status quo (Breeze em inglês, strings hardcoded)

- **Consequência:** viola §5.1 e o outcome do EPIC-004 (Coletador veria inglês/scaffolding). Bloqueia a onda.

## Matriz comparativa

| Critério (força) | Peso | **A — lang/ nativo + prop Inertia** | B — módulo FE isolado | C — i18n runtime FE | D — status quo |
|---|---|---|---|---|---|
| F1 — cumpre §5.1 | alto | ✅ cobre FE+BE+e-mail | ⚠️ FE sim, BE à parte | ✅ FE; BE à parte | ❌ |
| F2 — fonte única | alto | ✅ `lang/` | ❌ duas fontes | ⚠️ precisa integrar | ❌ |
| F3 — simplicidade/#4 | alto | ✅ nativo + helper fino | ✅ FE simples | ❌ runtime pesado | ✅ (mas inválido) |
| F4 — UTC/ISO preservado | alto | ✅ só exibição | ✅ | ✅ | n/a |
| F5 — extensível a 2º locale | médio | ✅ aditivo | ⚠️ FE só | ✅ | ❌ |
| #1/#11 custo | alto | ✅ | ✅ | ❌ | — |

## Decisão proposta

> **Optamos pela Opção A.**

**Mecanismo de strings:** a **localização nativa do Laravel** (`lang/`) é a **fonte única** de todo texto ao
usuário. Configuramos `app.locale = pt_BR` e `app.faker_locale = pt_BR`, e adotamos **`laravel-lang/lang`** para
já ter validação, autenticação, passwords e paginação em pt-BR (elimina o inglês do Breeze). As strings de tela
vivem em `lang/pt_BR/*.php` / `lang/pt_BR.json`; o frontend as recebe por uma **prop compartilhada do Inertia**
(via `HandleInertiaRequests::share`) e as consome por um **helper `t()` fino** no React. Nenhuma string de UI é
escrita como literal solto no JSX — a chave vive no `lang/`. (A biblioteca de cola `laravel-react-i18n` é
**opção do Programador** dentro deste ADR — IDR —, desde que a fonte única continue sendo o `lang/`.)

**Formatos brasileiros:**
- **Moeda:** `R$ 1.234,56`. Canônico = inteiro em centavos (já é a convenção da carteira/cashback). Formatação
  de exibição no **servidor** quando o valor já vem do backend (`Number::currency($v/100, 'BRL', 'pt_BR')` ou
  cast/acessor); no cliente, quando dinâmico, `new Intl.NumberFormat('pt-BR', { style:'currency', currency:'BRL' })`.
- **Datas:** `dd/mm/aaaa`. Persistência **UTC / ISO 8601** (herança — **não muda**); **exibição** convertida para
  `America/Sao_Paulo` na borda (Carbon `->locale('pt_BR')` / `->timezone('America/Sao_Paulo')->translatedFormat`
  no BE; `Intl.DateTimeFormat('pt-BR', { timeZone:'America/Sao_Paulo' })` no FE). O valor canônico continua ISO
  8601 em UTC no `useForm`/API (`stacks/inertia-react`, `stacks/laravel`).
- **Fuso:** `app.timezone` permanece **`UTC`** (persistência); `America/Sao_Paulo` é fuso de **exibição**, não de
  armazenamento. (Explicitar isso evita o erro de trocar `app.timezone` para SP e quebrar a convenção UTC.)

**Locale único:** sem seletor de idioma nesta onda. `pt_BR` é fixo. Um 2º locale, se um dia exigido, é ADR
própria (reabre a Opção C) — mudança aditiva sobre este mecanismo.

## Justificativa

A Opção A é a única que satisfaz **§5.1 inteira com fonte única** (F1+F2): cobre as strings que nascem no
backend (validação, e-mail, auth — onde está boa parte do inglês do Breeze) **e** as de tela, sem duplicar.
Faz isso pelo **mecanismo nativo** do framework opinativo (#4), com um helper trivial no React — sem arrastar um
runtime de i18n que um produto monolíngue não justifica (#1, contra a Opção C). O `laravel-lang/lang` remove o
inglês do scaffolding "de graça", em vez de tradução manual. Preserva a herança **UTC/ISO** (F4): localização é
exibição, o dado canônico não se move. E permanece **reversível/extensível** (F5): um 2º locale é aditivo. O
trade-off — trafegar um pequeno bundle de strings ao cliente e a disciplina de "chave no `lang/`, não literal no
JSX" — é barato e verificável. A fronteira com o Designer fica limpa (F6): decidimos o **mecanismo**; a **voz**
das strings segue `voice-and-tone.md`.

## Consequências

### Positivas (o que ganhamos)
- Uma fonte de verdade (`lang/`) para FE e BE; zero inglês de scaffolding (via `laravel-lang`).
- Formatos brasileiros consistentes (moeda/data/fuso) sem quebrar a persistência UTC/ISO.
- Extensível a multi-locale como mudança aditiva; sem dependência de runtime pesado.

### Negativas / trade-offs aceitos
- Bundle de strings trafega ao cliente (poucos KB) — aceitável; pode-se enviar só o namespace da tela se crescer.
- Exige **disciplina**: nenhuma string de UI hardcoded no JSX — a chave vai para `lang/`. Verificável em review/E2E.

### Neutras
- Introduz a dependência `laravel-lang/lang` (dev-friendly, amplamente usada) — trade-off melhor que traduzir os
  arquivos do framework à mão.

### Para o time
- **Impacto em estórias:** **STORY-020** materializa este mecanismo (configura `pt_BR`, instala `laravel-lang`,
  cria `HandleInertiaRequests::share` + helper `t()`, e faz a **varredura pt-BR** das superfícies existentes —
  login, `/ds`, carteira, backoffice de saques). STORY-021/022/023 já nascem consumindo o mecanismo.
- **ADRs/PDRs relacionados:** cumpre **PDR-003** (pt-BR transversal) e §5.1; alinhado a **ADR-000** (stack) e
  **ADR-010** (as telas de acesso nascem em pt-BR). Respeita a convenção UTC/ISO das sub-skills de stack.
- **Necessidade de spike de validação:** não.

## Plano de verificação

- **Conformidade:** cada estória com UI cobre, em E2E (Dusk, ADR-008), ≥1 asserção de texto **pt-BR** na
  superfície entregue e declara ter varrido strings de scaffolding remanescentes (§5.1 › Verificação). Inspeção/
  review: nenhum literal de UI no JSX (chaves no `lang/`); `app.timezone` continua `UTC`.
- **Sinais de revisão (reabrir):** surgir requisito de **2º idioma** / troca de locale em runtime → ADR própria
  (Opção C); o bundle de strings crescer a ponto de pesar no carregamento → dividir por namespace de tela.

---

## Aprovação humana

- **Status final:** ⬜ pendente | ✅ aceita | ❌ rejeitada | 🔄 superseded
- **Aprovado por:** <Alexandro>
- **Data:** YYYY-MM-DD
- **Forma do aceite:** <chat / PR>
- **Condicionantes do aceite:** <se houver>

---

## Histórico

- 2026-07-04 — criada como `proposed` por Arquiteto (STORY-019, spike de arquitetura de acesso do EPIC-004).
