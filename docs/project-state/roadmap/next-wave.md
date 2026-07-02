# Próxima onda (rascunho) — Onda 1: Provar a coleta em SP

> **Status: rascunho.** Este arquivo é o esboço da 1ª onda (Fluxo A ainda não fechado). Épicos
> aqui são candidatos com *outcome*, não detalhados em estórias. O detalhamento só acontece
> quando a onda for aberta em `current-wave.md`, após o Arquiteto ratificar o ADR-000.

## Objetivo de negócio da onda

Validar a **hipótese central do MVP**: consumidores em SP enviam cupons NFC-e em volume e
frequência suficientes para formar uma base de preços densa. North-star: **cupons válidos,
únicos e novos por semana** (ver `../product/north-star.md`).

## Épicos candidatos (ordem provável)

1. **EPIC-000 — Foundation** (deployável desde o dia 1)
   - *Outcome:* pipeline em verde + "hello world" em homologação, ambiente automatizado.
   - Inclui **estória de spike para o Arquiteto** (ratificar/ajustar ADR-000) **antes** das de
     implementação.
   - Inclui a **fundação técnica do Design System**: mapear os tokens do DS (PDR-001) para o
     tema da stack de FE (Inertia+React / Tailwind), para que as telas seguintes já nasçam sobre
     o sistema.

2. **EPIC-00X — Design System foundation** (trilha de Designer, cedo)
   - *Outcome:* DS canônico do Quantah publicado e utilizável pelo Programador.
   - Consolidar `docs/DESIGN-wise.md` → `docs/especificacao/design-system.md` + tokens em
     `design/system/` (cor, tipografia, spacing, raios, elevação, componentes).
   - Registrar DDRs: **substituição da fonte proprietária** (Wise Sans → Inter 900 / Manrope) e
     **diferenciação de marca vs. Wise** (o app deve parecer Quantah, não um clone).
   - *Dono:* Designer (`target_role: designer`). PO só define o critério de aceite.
   - Pode rodar em paralelo à parte técnica do EPIC-000; **bloqueia** as telas de fluxo abaixo.

3. **EPIC-00X — Coleta de cupom (o coração da coleta)**
   - *Outcome:* Colaborador escaneia/compartilha o QR da NFC-e, vê confirmação e recebe feedback
     "cupom aceito" — em homologação. Extração via adaptador SEFAZ-SP + deduplicação por chave.
   - Todas as telas sobre o DS (PDR-001).

4. **EPIC-00X — Carteira e cashback**
   - *Outcome:* saldo em reais (R$1/R$1.000), histórico de cupons e créditos, base do resgate.

5. **EPIC-00X — Gamificação básica + gancho de valor**
   - *Outcome:* pontos/níveis/ranking simples e o "você pagou mais caro/barato que a média",
     para puxar recorrência (alavanca da north-star).

> Sorteio fica **fora desta onda** (implementado com gate desligado; burocracia SPA em paralelo).

## Padrões transversais desta onda (de PDR-001)

- Toda estória com UI é `requires_design: true` e **referencia o DS canônico**; usa só tokens
  do sistema (sem cor/fonte/raio/spacing fora dele).
- Verde é o único accent de CTA; estados usam a paleta semântica.
- Mobile-first com paridade responsiva; a11y mínima (contraste + alvo ~48px).

## Dependências / sequência

- **Arquiteto ratifica ADR-000** → destrava EPIC-000.
- **Design System foundation** destrava as telas de fluxo (Coleta, Carteira, Gamificação).
- Ao abrir a onda: fixar metas numéricas da north-star, escrever `current-wave.md`, registrar
  PDR de escopo da onda e gerar o status report de abertura.
