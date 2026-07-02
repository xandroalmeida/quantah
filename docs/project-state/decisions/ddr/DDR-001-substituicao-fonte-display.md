---
id: DDR-001
title: Substituição da fonte proprietária Wise Sans por Inter 900 / Manrope
status: accepted
created_at: 2026-07-02
decided_at: 2026-07-02
approved_by: Alexandro
supersedes: ~
superseded_by: ~
related_ddrs: [DDR-002]
related_adrs: [ADR-000]
related_pdrs: [PDR-001]
scope: transversal
affects_screens: []
---

# DDR-001 — Substituição da fonte proprietária Wise Sans por Inter 900 / Manrope

## Contexto

O PDR-001 adotou o design system Wise-derived (`docs/DESIGN-wise.md`) como base do UX/UI. Esse
DS usa **Wise Sans** (fonte proprietária da Wise) para todos os displays em peso 900. Wise Sans
não é licenciável para uso fora da Wise — precisamos de um substituto open-source antes de
qualquer tela. O próprio DESIGN-wise.md ("Note on Font Substitutes") já indica os candidatos.

Documentos lidos: PDR-001, ADR-000 (stack Inertia+React/Tailwind), `DESIGN-wise.md` (typography).

## Forças (drivers)

- **Legalidade/licença** (alto): Wise Sans não pode ser usada; bloqueia produção.
- **Fidelidade ao DS** (alto): manter a "pegada" geométrica pesada de peso 900 do hero.
- **Disponibilidade na stack** (médio): Inter já vem no starter Laravel+Inertia+React.
- **Performance de carregamento** (médio): peso da fonte no bundle/rede (mobile do Colaborador).
- **Custo** (baixo): ambas as opções são gratuitas (SIL OFL).

## Opções consideradas

### Opção A — Inter (900 no display, 400/600 no resto)
Uma única família para tudo. Inter 900 cobre o display; Inter 400/600 o corpo/UI.
- **Prós:** uma só família (menos peso, menos gestão); já é a "segunda face" real da marca;
  presente no ecossistema; excelente legibilidade em mobile.
- **Contras:** Inter 900 é um pouco menos "chunky" que Wise Sans — momento de marca levemente
  mais sóbrio.

### Opção B — Manrope 800/900 no display + Inter no corpo
Manrope no display (mais geométrica/arredondada, próxima do espírito Wise Sans) + Inter no resto.
- **Prós:** display mais próximo da heaviness da Wise Sans; contraste display×corpo mais nítido.
- **Contras:** duas famílias (mais peso e gestão); Manrope precisa ser adicionada.

### Status quo — manter Wise Sans
- **Contras:** inviável (licença) — não é opção real.

## Avaliação contra os princípios

| Princípio | A | B | Status quo |
|---|---|---|---|
| 1. Simplicidade radical | ✅ uma família | ⚠️ duas famílias | ❌ |
| 2. Mobile-first / paridade | ✅ | ✅ | ❌ |
| 3. Tom profissional Quantah | ✅ sóbrio | ✅ mais marcante | ❌ |
| 4. Padronização > criatividade | ✅ | ✅ | ❌ |
| 5. Acessibilidade | ✅ Inter ótima | ✅ | ❌ |
| 6. Performance percebida | ✅ menos peso | ⚠️ +1 família | ❌ |
| 7. Estados além do feliz | n/a | n/a | n/a |

## Decisão

> **Adotada:** Opção A — **Inter** como família única (900 no display/hero, 600 em títulos e
> labels, 400 no corpo).

Vence pela simplicidade (uma família), performance no mobile do Colaborador e por Inter já ser
a segunda face real do DS. Manrope (Opção B) fica documentada como caminho se, no teste de
marca (DDR-002), o hero pedir mais "peso" — troca localizada só do display, sem afetar o corpo.

## Consequências

### Positivas
- Zero risco de licença; fonte SIL OFL gratuita.
- Um único arquivo de família → bundle menor, menos gestão de fonte.

### Negativas / trade-offs assumidos
- Hero levemente menos "chunky" que a referência Wise — aceitável e alinhado ao tom sóbrio.

### Impacto no Design System
- `tokens.md` › Tipografia: família display = **Inter 900** (Manrope como alternativa
  documentada). Atualizado nesta operação.

### Impacto em telas existentes
- Nenhuma tela existe ainda. `affects_screens: []`.

## Implementação sugerida (notas para o Programador)
- Carregar Inter com os pesos 400/600/900 (subset latin) via o mecanismo de fontes do starter;
  mapear no `tailwind.config.js` (`fontFamily.display` e `fontFamily.sans` = Inter).
- `font-feature-settings: "calt"` como no DS de origem.

## Critérios para revisitar
- Se o teste de marca (DDR-002) concluir que o hero precisa de mais peso → migrar display para
  Manrope 800/900 (sem tocar no corpo).

## Aprovação humana

| Campo | Valor |
|---|---|
| Apresentado em | 2026-07-02 |
| Aprovado por | Alexandro |
| Data da aprovação | 2026-07-02 |
| Observações do aprovador | Aprovado em sessão de Cowork (papel Designer). |

> Sem este bloco preenchido, o DDR não pode ir para `accepted`.
