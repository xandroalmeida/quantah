# Onda em execução — WAVE-2026-03: Remuneração por pontos gamificados

- **Status:** active
- **Início:** 2026-07-06
- **Escopo decidido em:** PDR-005 (modelo de pontos em PDR-004)
- **North-star:** cupons NFC-e válidos, únicos e novos por semana (`../product/north-star.md`)
- **Onda anterior:** WAVE-2026-02 (closed) — fechamento em
  `../reports/status-2026-07-06-wave-2026-02-close-wave-2026-03-open.md`

## Objetivo de negócio

Substituir o rate fixo de cashback (R$ 1/R$ 1.000) pelo **modelo de pontos gamificado** ponta a ponta:
cada cupom válido gera pontos por um motor de regras configurável (CNAE do emitente, itens únicos,
valor, itens com bônus); pontos convertem em R$ por resgate manual com mínimo, pela taxa vigente; o
saldo legado é migrado para pontos; e todos os parâmetros são administráveis no Backoffice. Tudo em
homologação, pronto para o piloto nascer com o incentivo definitivo.

## Hipótese que estamos validando

> Um incentivo paramétrico por pontos — que remunera mais o dado que vale mais (categoria, diversidade,
> produtos estratégicos) e transforma o acúmulo em mecânica de jogo — sustenta a recorrência do
> Colaborador com custo de aquisição do dado controlável, sem quebrar a tese de remuneração por serviço
> (PDR-004, premissa regulatória).

## Épicos da onda (em ordem)

| # | Épico | Status | Outcome | Critério de pronto (observável) |
|---|---|---|---|---|
| 1 | **EPIC-009 Enriquecimento cadastral do emitente** | ready | CNPJ do emitente consultado na RFB (API pública, fila, cache ≥30d parametrizável) | No Backoffice, um cupom processado exibe razão social e CNAE do emitente; consulta repetida no prazo do cache não bate na API — em homologação. |
| 2 | **EPIC-010 Motor de pontos** | draft | Cupom válido gera pontos em fila, por regras compostas | Colaborador envia cupom e vê pontos creditados no extrato, com memória de cálculo por regra — em homologação. |
| 3 | **EPIC-011 Conversão, resgate e migração** | draft | N pontos = X R$; resgate manual com mínimo; saldo legado migrado | Colaborador resgata pontos acima do mínimo e vê saldo em R$ na carteira; saldo antigo aparece convertido no extrato — em homologação. |
| 4 | **EPIC-012 Configuração do mecanismo (Backoffice)** | draft | Parâmetros dos dois mecanismos administráveis, com auditoria | Backoffice altera regra/taxa/mínimo/TTL e a mudança vale só dali pra frente, com histórico de quem mudou — em homologação. |

## Justificativa da sequência

**EPIC-009 primeiro** porque o CNAE é insumo do motor de pontos e é a peça de maior risco externo (API
de terceiros) — o spike do Arquiteto abre a onda. **EPIC-010 em seguida**, consumindo o CNAE; enquanto
não há tela de configuração, os parâmetros nascem versionados como configuração semeada (a tela do
EPIC-012 os expõe depois). **EPIC-011 depois do motor**, porque resgate e migração pressupõem pontos
existindo. **EPIC-012 por último**: administra parâmetros que os três anteriores estabeleceram —
antecipá-lo seria configurar o que ainda não existe.

## O que esta onda NÃO inclui

- Piloto/baseline da north-star, endurecimento transversal (WISH-004/005/006), B2B autenticado e
  promoção a produção — candidatos em `next-wave.md`.
- Badges, níveis e ranking (gamificação sem valor material) — onda futura.
- Sorteio (gate regulatório da visão §8.3) — segue desligado.

## Pendências herdadas da Onda 2

- **EPIC-008 gate manual de device** (install Android/iOS, SW ativo, Lighthouse) — pendente com o
  Alexandro; não bloqueia esta onda (IDR-016).
