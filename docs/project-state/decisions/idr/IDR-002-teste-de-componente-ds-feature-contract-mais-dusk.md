---
idr_id: IDR-002
slug: teste-de-componente-ds-feature-contract-mais-dusk
title: Teste de componente do DS via contrato-em-fonte (Feature) + Dusk; Vitest/RTL adiado
status: accepted
decided_at: 2026-07-02
decided_by: programador
owner_agent: claude-programador-story004
related_story: STORY-004
related_adrs: [ADR-000, ADR-008]
related_idrs: [IDR-001]
supersedes: null
superseded_by: null
created_at: 2026-07-02
updated_at: 2026-07-02
---

# IDR-002 — Teste de componente do DS via contrato-em-fonte (Feature) + Dusk; Vitest/RTL adiado

## Contexto

A STORY-004 abre a biblioteca de componentes React do DS (EPIC-001) — o `Button` é o primeiro e
"serve de molde para os demais". A sub-skill `stacks/inertia-react` lista **Vitest + React Testing
Library** como default de teste de componente, mas ele **não está instalado** no projeto
(sem `vitest`/RTL no `package.json`; Vite está em `^8.0.0`, muito novo). O padrão de teste de UI
**já estabelecido** (STORY-002) é: teste **Feature (PHP)** varrendo o fonte do JSX para o contrato
de tokens (`NoRawColorInHelloTest`, `TailwindThemeTokensTest`) + **Dusk** (ADR-008) para
comportamento e a11y em browser real (cor computada, contraste, foco por teclado). Como todo o
EPIC-001 vai testar componentes, a estratégia precisa ser explícita para os próximos agentes.

## Decisão

> **Decidi testar os componentes do DS com a dupla já estabelecida — contrato-em-fonte (teste
> Feature PHP que varre o `.jsx`) + Dusk (browser real) — e adiar a adoção de Vitest/RTL até um
> componente cuja lógica de cliente (ex.: máscara/seletor de data dos inputs, STORY-005)
> realmente justifique uma camada de unit rápida em jsdom.**

Para o `Button`: `Tests\Feature\DesignSystem\ButtonTokenContractTest` garante as 5 variantes, o
mapa token-a-token e "zero valor cru"; `Tests\Browser\ButtonTest` (Dusk) prova, em Chrome real,
os tokens computados, contraste AA por variante, alvo ≥48px, foco por teclado e o bloqueio de
clique (disabled/loading + `aria-busy` + spinner). A vitrine `/ds/buttons` é o host do E2E.

**Convenção de lint associada:** o projeto usa `test_snake_case` (60 métodos; convenção
Laravel/Breeze). Foi adicionado `app/pint.json` fixando `php_unit_method_casing=snake_case` e um
passo **Lint (Pint)** no CI, para o gate de formatação valer sem brigar com a convenção.

## Por quê

- **KISS + seguir o que já existe.** Reusar o padrão da STORY-002 mantém consistência e evita
  introduzir um toolchain (Vitest + jsdom + RTL) no meio do épico, com risco de compatibilidade
  contra o Vite 8. Menos uma engrenagem para os próximos agentes manterem agora.
- **Dusk cobre o que importa num botão em browser de verdade.** A lógica do `Button` é fina
  (variante→classe, disabled/loading→bloquear clique + `aria-busy`). Verificar isso em Chrome real
  (CSS de verdade, foco por teclado, contraste computado) é **mais fiel** que jsdom, que
  reconhecidamente não roda CSS nem foco real (gotcha da própria sub-skill).
- **Adoção sob demanda.** A STORY-005 (inputs com máscara `react-imask` e seletor de data) tem
  lógica de cliente rica (guardar valor unmasked/ISO no `useForm`) que **sim** pede unit rápido —
  esse é o gatilho natural para introduzir Vitest/RTL, quando o custo se paga.

## Alternativas consideradas

- **Introduzir Vitest + RTL agora (default da sub-skill):** descartada nesta estória — toolchain
  novo (deps + config jsdom + passo de CI) com risco de compat vs Vite 8, para testar um
  componente cuja lógica o Dusk já cobre em browser real. Fica como gatilho da STORY-005.
- **Só Dusk, sem o teste Feature de contrato:** descartada — o contrato-em-fonte ("zero valor
  cru", variantes→tokens) é barato, rápido e é a guarda que já existe no projeto contra regressão
  de token; complementa o Dusk sem custo de browser.

## Consequências

### Para outros agentes
- **Componente do DS = contrato-em-fonte (Feature) + Dusk por variante/estado.** Cada componente
  novo do EPIC-001 segue esse molde até que Vitest/RTL seja adotado.
- **A vitrine (`/ds/buttons`, depois a kitchen sink da STORY-006) é o host de E2E** dos componentes.
- **Testes em `test_snake_case`**; `pint.json` já reflete isso. Rodar `sail pint --test` antes do push.
- **STORY-005 é o ponto de decidir Vitest/RTL** (lógica de máscara/data). Se adotado, atualizar/
  suceder esta IDR e adicionar o passo de teste JS ao CI.

### Para o projeto
- Zero dependência nova nesta estória. +1 passo de CI (Pint) e +1 arquivo `pint.json`.
- `minHeight`/`minWidth` do Tailwind passam a herdar a escala de spacing (para `min-h-3xl`=48px).

### Trade-offs aceitos
- Sem camada de unit rápida (jsdom) para componentes por ora — o comportamento é coberto por Dusk,
  que é mais lento mas mais fiel. Aceitável para componentes presentacionais como o `Button`.

## Como verificar

- Guardas automatizadas: `ButtonTokenContractTest` (contrato de tokens) + `ButtonTest` (Dusk).
  Um componente novo do DS sem esse par é sinal de desvio.
- `sail pint --test` verde no CI (passo "Lint (Pint)").
- Gatilho de revisão: ao chegar a STORY-005 (inputs), reavaliar a adoção de Vitest/RTL e, se
  adotado, marcar esta IDR como `superseded` pela nova.

## Tipo

- [x] **Padrão transversal**: estratégia de teste dos componentes do DS no EPIC-001.
- [ ] **Workaround**
- [x] **Convenção interna**: `test_snake_case` fixado no Pint + gate de lint no CI.
- [ ] **Otimização**
- [ ] **Refatoração estrutural**

---

## Histórico

- 2026-07-02 — criada como `accepted` por programador (sessão claude-programador-story004)
  durante STORY-004.
