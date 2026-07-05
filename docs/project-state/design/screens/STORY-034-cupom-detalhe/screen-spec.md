---
id: SCREEN-STORY-034-cupom-detalhe
story: STORY-034-cupom-listagem-e-detalhe
epic: EPIC-007-refinamento-experiencia-b2c-mobile
status: shipped
created_at: 2026-07-05
updated_at: 2026-07-05
owner_designer: claude-programador
related_ddrs: [DDR-007]
ds_components_used: [card.content, badge.positive, badge.info, empty-state, nav.bottom, nav.bar]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-034-cupom-detalhe/index.html
prototype_last_validated_at: 2026-07-05
---

# Spec de tela — Detalhe do cupom

> Referência: estória `STORY-034-cupom-listagem-e-detalhe` e brief `design-handoff.md` (CAs e contexto
> vêm de lá — **não duplico**). Modelo paralelo (PDR-002): spec + protótipo validados com o Alexandro
> (aprovado em 2026-07-05). Dados do read-model `DetalheCupom` (nada hardcoded); sem PII (ADR-006).

## 1. Objetivo da tela

Ao tocar um cupom no histórico da Carteira, o Colaborador vê **que compra foi essa e o que tinha nela**:
estabelecimento, quando/quanto, e a lista de itens. É a prova de que "a nota contou". Uma tarefa:
*reconhecer e conferir o cupom*. Só leitura.

## 2. Fluxo

- **Entrada:** toque num item do histórico da Carteira (`/carteira`) → `/carteira/cupom/{id}`, guarda
  `auth` + **posse** (via `CupomAtribuicao`, ADR-006). Cupom de outro Coletador → 404. Guest → `/login`.
- **Ações:** nenhuma escrita. Retorno à listagem pelo link "← Carteira" e pela `nav.bottom` (seção
  Carteira ativa).
- **Saída:** permanece na leitura; "← Carteira" volta ao histórico.

## 3. Layout (mobile-first ≥360px, sobre o surface-rhythm do DS)

Página **sage** (`bg-canvas-soft`) → cards brancos (`card.content`). Verde `primary` **só** como accent
(badge `Validado`). Retorno consistente (casca DDR-007).

```
← Carteira                                  (link, alvo ≥48px, foco visível)
┌───────────────────────────────────────┐  card.content — CABEÇALHO
│ Supermercados Cavicchiolli Ltda   [✓ Validado] │  estabelecimento (display-xs) + badge status
│ CNPJ 43.259.548/0028-83                │  contexto secundário (mute)
│ 01/07/2026                             │  data pt-BR (mute)
│ ───────────────────────────────────── │
│ Total                       R$ 235,43  │  total em destaque (display-sm, tabular)
└───────────────────────────────────────┘
Itens                                       (h2 body-md-strong)
┌───────────────────────────────────────┐  card.content — ITEM (repete)
│ SALSICHA HOT DOG SADIA 500G            │  descrição (quebra elegante)
│ 1 UN × R$ 14,85              R$ 14,85  │  qtd+unidade × unitário   |   total à direita
└───────────────────────────────────────┘
[ nav.bottom fixo: Início · Escanear · Carteira(•) · Perfil ]
```

## 4. Estados

- **Validado com itens (cheio):** cabeçalho completo + lista de itens.
- **Sem nome do emitente (fallback):** título "Estabelecimento não identificado"; o **CNPJ** vira o
  identificador. Não quebra.
- **Pendente/sem itens:** badge `info` "Processando" + `empty-state` acolhedor ("Estamos processando este
  cupom / Os itens aparecem assim que a validação na SEFAZ terminar"). A extração é assíncrona.

## 5. Microcopy (pt-BR)

- Retorno: "← Carteira". Total: "Total". Seção: "Itens".
- Status (badge): `validado`→"Validado" (positive), `pendente`/`extraindo`→"Processando" (info),
  `falha`→"Não validado" (negative), `rejeitado`→"Recusado" (negative).
- Vazio: título "Estamos processando este cupom"; instrução "Os itens aparecem assim que a validação na
  SEFAZ terminar."

## 6. A11y / formato

Alvos ≥48px (link de retorno, itens da nav); foco visível (anel `ink`); descrições longas quebram sem
overflow horizontal; datas `dd/mm/aaaa` e moeda `R$ 1.234,56` (America/Sao_Paulo). Feedback de status
nunca só cor — badge tem ícone + texto.

## 7. Ajuste na listagem (SCREEN-STORY-016, sem tela nova)

Cada item do histórico passa a mostrar **estabelecimento** (com fallback) + **data · valor** (além do
crédito) e vira **clicável** (`<Link>` → detalhe), com foco visível e área de toque do card inteiro.
