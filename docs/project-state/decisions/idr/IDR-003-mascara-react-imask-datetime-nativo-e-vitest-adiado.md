---
idr_id: IDR-003
slug: mascara-react-imask-datetime-nativo-e-vitest-adiado
title: Máscara via react-imask (valor unmasked), datetime por input nativo (ISO), e Vitest/RTL mantido adiado
status: accepted
decided_at: 2026-07-02
decided_by: programador
owner_agent: claude-programador-story005
related_story: STORY-005
related_adrs: [ADR-000, ADR-008]
related_idrs: [IDR-002]
supersedes: null
superseded_by: null
created_at: 2026-07-02
updated_at: 2026-07-02
---

# IDR-003 — Máscara via react-imask (valor unmasked), datetime por input nativo (ISO), e Vitest/RTL mantido adiado

## Contexto

A STORY-005 abre os componentes de input do DS (EPIC-001): text, masked, datetime, select, checkbox,
radio e switch. Três decisões técnicas dela impactam outras estórias e outros agentes:

1. **Máscara** (`input.masked`) — a spec da stack (`stacks/inertia-react/SKILL.md`) define
   **`react-imask`** como default de campo formatado, com a regra de ouro "guardar o valor **unmasked**
   no `useForm`". Adotar essa lib é uma decisão transversal (vai reaparecer em Coleta/Carteira).
2. **Seletor de data** (`input.datetime`) — a spec permite `react-day-picker` **ou** o input nativo
   como fallback simples; a escolha condiciona dependências e o padrão dos próximos campos de data.
3. **Estratégia de teste** — a **IDR-002** fixou "contrato-em-fonte (Feature) + Dusk" e nomeou
   **explicitamente a STORY-005** como o ponto de reavaliar a adoção de **Vitest + RTL**, porque
   inputs teriam "lógica de cliente rica (máscara/data)". É preciso registrar o resultado dessa
   reavaliação para os próximos agentes.

## Decisão

> **Decidi (1) adotar `react-imask` como a lib de máscara do projeto, guardando sempre o valor
> unmasked; (2) implementar `input.datetime` com o input nativo do browser (valor ISO 8601), deixando
> um calendário estilizado para quando uma tela de produto o exigir; e (3) manter Vitest/RTL adiado —
> a estratégia contrato-em-fonte + Dusk da IDR-002 segue valendo.**

- **Máscara:** `MaskedField` usa `IMaskInput` com `unmask` e expõe `onAccept(unmaskedValue)`; o
  consumidor guarda o valor canônico (só dígitos). Máscara default = **chave de acesso da NFC-e**
  (44 dígitos, 11 grupos de 4). A máscara é **ajuda de UX, não validação** — nenhuma regra de negócio
  no componente; a validação canônica é do servidor (Laravel).
- **Datetime:** `DateTimeField` usa `<input type="date|time|datetime-local">` — seletor real e
  acessível do browser, valor canônico ISO 8601 via `onChange(isoValue)`; fuso e validação no servidor.
- **Teste:** `InputTokenContractTest` (contrato de tokens dos 7 componentes, incl. "zero valor cru" e
  wiring de a11y) + `Tests\Browser\InputTest` (Dusk) que prova, em Chrome real, label/hint/erro com
  `aria-*`, foco visível, alvo ≥48px, contraste, disabled, **valor unmasked** e **valor ISO**.

## Por quê

- **`react-imask`:** é o default documentado da stack e uma peça **testada** que resolve caret,
  paste e delete — que uma máscara caseira erra (footgun clássico). Guardar o unmasked casa com a
  "regra de ouro" da sub-skill e evita mandar máscara para o servidor. Uma única dependência focada,
  justificada por CA-4.
- **Datetime nativo:** KISS. O input nativo já é seletor acessível e entrega ISO 8601 de graça, sem
  arrastar uma lib de calendário (bundle + a11y + estilo) para um componente de DS que ainda não tem
  tela de produto. É o fallback que a própria sub-skill autoriza para o caso simples.
- **Vitest/RTL adiado (reavaliação da IDR-002):** a lógica de cliente "rica" que motivaria o jsdom
  foi **delegada** — a máscara à `react-imask` (já testada) e a data ao controle nativo. O que sobra
  nos componentes é wiring fino (variante→classe, `aria-*`, o valor canônico fluindo para o consumidor)
  — tudo **observável em browser real** e já coberto pelo Dusk (digitar no campo e ler o valor canônico
  num display da vitrine). Introduzir Vitest+jsdom+RTL agora (toolchain novo, risco de compat vs Vite 8,
  passo de CI) não se paga contra o que o Dusk já prova de forma mais fiel. Mantém-se o gatilho: o
  primeiro componente com **lógica pura de cliente sem lib que a cubra** adota Vitest/RTL e então
  sucede a IDR-002/003.

## Alternativas consideradas

- **Máscara caseira (sem lib):** descartada — 44 dígitos agrupados parece simples, mas acertar caret,
  paste e delete é onde a máscara manual quebra; `react-imask` já resolve e é o default da stack.
- **`react-day-picker` para datetime agora:** descartada nesta estória — dep pesada (bundle + estilo +
  a11y própria) sem tela de produto que a exija; o nativo entrega seletor + ISO. Entra por ADR/IDR
  quando uma tela de Coleta pedir calendário estilizado.
- **Adotar Vitest + RTL agora (default da sub-skill, antecipado pela IDR-002):** descartada — a lógica
  que o justificaria está em lib testada/controle nativo; Dusk cobre o resto em browser real. Fica como
  gatilho para o primeiro componente com lógica de cliente própria.

## Consequências

### Para outros agentes
- **Campo formatado = `react-imask` + valor unmasked no `useForm`.** Reusar `MaskedField`; a máscara é
  UX, a validação é do servidor. Não guardar o texto formatado.
- **Campo de data = `DateTimeField` (nativo, ISO 8601).** Calendário estilizado só via ADR/IDR quando
  uma tela pedir.
- **Componente do DS continua = contrato-em-fonte (Feature) + Dusk** (IDR-002). Vitest/RTL só quando um
  componente tiver lógica pura de cliente sem lib que a cubra — aí sucede esta IDR.
- **DS inputs vivem em `resources/js/Components/inputs/`** (namespaced), para não colidir com o
  scaffolding Breeze (`Components/Checkbox.jsx`, `TextInput.jsx`…), que é débito pré-DS fora de escopo.

### Para o projeto
- +1 dependência de runtime: `react-imask` (`^7.6.1`). Zero dep de teste nova; zero passo de CI novo.
- Chunk `Inputs` ~71kB (gzip ~20kB) — inclui a IMask; aceitável para a vitrine.

### Trade-offs aceitos
- Sem camada de unit rápida (jsdom) para os inputs — comportamento coberto por Dusk (mais lento,
  mais fiel). Aceitável enquanto a lógica de cliente estiver delegada a lib/controle nativo.
- Formato de data exibido segue a locale do browser; o valor canônico é sempre ISO — aceitável.

## Como verificar

- Guardas automatizadas: `InputTokenContractTest` (8) + `Tests\Browser\InputTest` (12, Dusk) verdes.
- `sail pint --test` verde; suíte completa `sail artisan test` verde (cobertura ≥80%).
- Gatilho de revisão: primeiro componente com lógica pura de cliente sem lib → reavaliar Vitest/RTL e,
  se adotado, marcar IDR-002 e IDR-003 como `superseded` pela nova.

## Tipo

- [x] **Padrão transversal**: lib de máscara (`react-imask`) e seletor de data (nativo) do projeto.
- [ ] **Workaround**
- [x] **Convenção interna**: DS inputs em `Components/inputs/`; estratégia de teste (IDR-002 mantida).
- [ ] **Otimização**
- [ ] **Refatoração estrutural**

---

## Histórico

- 2026-07-02 — criada como `accepted` por programador (sessão claude-programador-story005) durante
  STORY-005. Reavalia e mantém a IDR-002 (Vitest/RTL adiado).
