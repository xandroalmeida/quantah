---
idr_id: IDR-010
slug: i18n-chaves-por-string-fonte-e-t-no-inertia
title: i18n — chaves por string-fonte (JSON translations), helper t() via prop do Inertia e Formato
status: accepted  # proposed | accepted | superseded
decided_at: 2026-07-04
decided_by: programador
owner_agent: claude-code (sessão STORY-020)
related_story: STORY-020
related_adrs: [ADR-011]
related_idrs: [IDR-003]
supersedes: null
superseded_by: null
created_at: 2026-07-04
updated_at: 2026-07-04
---

# IDR-010 — i18n: chaves por string-fonte, helper t() via prop do Inertia e Formato

> Implementation Decision Record. Registra o **como** da fundação de i18n (ADR-011) para que as
> estórias seguintes (STORY-021/022/023) e outros agentes sigam o mesmo padrão em vez de inventar outro.

## Contexto

A STORY-020 materializou o ADR-011 (localização pt-BR, fonte única no `lang/` do Laravel, exposta ao
React por prop compartilhada + helper `t()` fino, sem framework de i18n de runtime). O ADR fixou a
direção; restavam as decisões de implementação que viram **padrão do projeto**: qual o formato das
chaves, como o `t()` obtém o dicionário no cliente, de onde vêm as traduções de framework, e onde vive a
formatação de moeda/data. Este IDR fixa esse padrão.

## Decisão

> **Decidi:** (1) as chaves de tradução são a **própria string-fonte em inglês** (padrão *JSON
> translations* do Laravel) — `__('Log in')` no back-end, `t('Log in')` no React; (2) o dicionário do
> locale ativo (`lang/<locale>.json`) é compartilhado como prop `translations` do Inertia e registrado
> **uma vez no boot** (`app.jsx` → `setTranslations`), de onde o helper `t()` (`resources/js/i18n.js`) o
> lê — utilizável em qualquer lugar, inclusive fora de componentes; (3) as traduções de framework
> (validation/auth/passwords/pagination) vêm do pacote **`laravel-lang/common`** (dev), não escritas à
> mão; (4) a formatação brasileira de moeda/data/fuso vive num único helper **`App\Support\Formato`**.

## Por quê

- **Chave = string-fonte:** é o padrão idiomático do Laravel para `lang/<locale>.json`, elimina um mapa
  paralelo de chaves simbólicas, faz `fallback_locale=en` degradar para um texto legível (a própria
  string) em vez de uma chave crua, e reaproveita direto o dicionário do `laravel-lang` (que já vem
  chaveado por inglês). KISS (coding-principles #1) e "siga o framework opinativo" (#3).
- **Registrar no boot (monolíngue):** como o produto é pt-BR único (ADR-011), o dicionário é **idêntico
  entre páginas** — não precisa reagir a navegação nem de contexto React. Registrar uma vez no `setup()`
  do Inertia deixa `t()` como função pura de módulo (sem hook), simples de usar em qualquer arquivo.
- **`laravel-lang` para o framework:** traduzir `validation.php` à mão é caro e propenso a erro; o pacote
  é a fonte battle-tested — foi o nomeado no ADR-011.
- **`Formato` único:** moeda/data/fuso estavam duplicados (`number_format` em 3 lugares); centralizar
  resolve a regra de 3 (DRY) e dá o ponto único para o fuso de **exibição** `America/Sao_Paulo` sem tocar
  a persistência (UTC/ISO).

## Alternativas consideradas

- **Chaves simbólicas** (`auth.login.submit`): mais "arrumado", mas exige manter um mapa próprio e não
  reaproveita o dicionário do `laravel-lang`; degradação de fallback vira chave crua. Descartada por
  YAGNI/simplicidade num produto monolíngue.
- **Lib de i18n de runtime no FE** (i18next/react-intl) ou `laravel-react-i18n` com context provider:
  over-engineering para locale único (ADR-011 já descartou); o helper de módulo cobre o caso sem
  dependência transversal.
- **Módulo de strings só no FE:** criaria duas fontes de verdade (FE × `lang/`), divergentes com o tempo.

## Consequências

### Para outros agentes
- **Toda string de UI nova nasce como `t('<texto em inglês>')`** (ou `__()` no back-end), com a chave
  adicionada em `lang/pt_BR.json` (ou nos `.php` do framework). **Nunca** literal de UI hardcoded no JSX.
- **Moeda/data/fuso** passam por `App\Support\Formato` — não reintroduzir `number_format`/`->format` solto
  nas telas. Persistência continua **UTC/ISO**; SP é só exibição.
- `laravel-lang/common` é a base de traduções de framework — não trazer outro pacote equivalente.
- O tom/voz das strings é do Designer (`voice-and-tone.md`); o default do `laravel-lang` pode ser
  ajustado por chave quando a marca pedir (ex.: a STORY-021 pode preferir "Entrar" a "Iniciar sessão").

### Para o projeto
- +1 dependência de dev (`laravel-lang/common`) e um bundle de ~13KB de strings compartilhado ao cliente
  (aceito no ADR-011; dividir por namespace de tela se crescer).
- Sem runner de teste JS: o `t()` é coberto por **E2E Dusk** (Vitest segue adiado — ver [[IDR-003]]).

### Trade-offs aceitos
- Disciplina manual: "chave no `lang/`, não literal no JSX" não é hoje verificado por linter (não há
  ESLint no projeto) — é pego em review e pela ausência de inglês nos E2E.
- Botões do DS usam `text-transform: uppercase`: E2E que asserta texto de botão deve considerar a caixa
  alta renderizada (ver `tests/Browser/I18nPtBrTest.php`).

## Como verificar

- E2E de cada estória com UI asserta ≥1 texto pt-BR e a ausência de scaffolding em inglês (§5.1).
- `app.timezone` deve permanecer `UTC`; se alguém trocar para SP, quebra a convenção de persistência.
- Se surgir requisito de 2º idioma → reabrir via ADR (Opção C do ADR-011), não estender este padrão ad-hoc.

## Tipo

- [x] **Padrão transversal**: mecanismo de i18n e formatação que vira default no projeto.
- [x] **Convenção interna**: chave = string-fonte; strings de UI sempre via `t()`/`__()`.
- [x] **Refatoração estrutural**: unificação de moeda/data em `App\Support\Formato`.

---

## Histórico

- 2026-07-04 — criada como `accepted` por programador durante STORY-020 (fundação de i18n do EPIC-004).
