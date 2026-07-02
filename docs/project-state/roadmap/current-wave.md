# Onda em execução — WAVE-2026-01: Provar a coleta em SP (com incentivo)

- **Status:** active
- **Início:** 2026-07-02
- **Escopo decidido em:** PDR-002
- **North-star:** cupons NFC-e válidos, únicos e novos por semana (`../product/north-star.md`)

## Objetivo de negócio

Entregar, em homologação, o loop completo **Colaborador escaneia/compartilha o QR da NFC-e →
cupom validado, deduplicado e persistido → saldo em cashback creditado** — sobre a stack
ratificada (ADR-000) e o design system adotado (PDR-001).

## Hipótese que estamos validando

> Com fricção mínima na coleta **e** recompensa (cashback), consumidores em SP enviam cupons em
> volume suficiente para formar uma base de preços densa. A Onda 1 monta o loop e o **instrumento
> de medição**, e fecha um piloto para gerar o **primeiro baseline** da north-star.

## Épicos da onda (em ordem)

| # | Épico | Outcome | Critério de pronto (observável) |
|---|---|---|---|
| 1 | **EPIC-000 Foundation** | Ambiente + pipeline + tokens do DS vivos | Hello world em homologação com paleta/tipografia do DS; CI/CD verde; ambiente 1-comando. |
| 2 | **EPIC-001 Design System em código** | Biblioteca de componentes React do DS | Vitrine em homologação com os componentes mínimos e seus estados. |
| 3 | **EPIC-002 Coleta de cupom** | Cupom vira dado válido, único e novo | Fluxo de coleta em homologação; cupom SP validado+deduplicado+persistido; north-star instrumentada. |
| 4 | **EPIC-003 Carteira e cashback** | Enviar cupom vira saldo | Saldo credita 0,1% sobre cupons válidos, visível em homologação; caminho de resgate existe. |

## Justificativa da sequência

EPIC-000 destrava tudo (nada sobe sem ambiente). EPIC-001 destrava as telas (Coleta e Carteira
compõem componentes prontos, sem reinventar). EPIC-002 é o **núcleo de valor** — sem coleta não
há base nem north-star. EPIC-003 adiciona o **incentivo** que a hipótese exige para o volume ser
real. Coleta antes de cashback porque o crédito incide sobre o cupom já validado.

## Metas da north-star (Onda 1 — provisórias, para virar baseline)

Não há usuários reais ainda; a Onda 1 é sobre **existir e medir**. Metas como marcos, não
compromissos rígidos:

- **Instrumento de medição** da north-star existindo e visível ao fim do EPIC-002.
- **Piloto fechado** (equipe + pequeno grupo convidado) gerando um **baseline** de cupons
  válidos-únicos-novos/semana ao fim da onda — o número absoluto vira a linha de base para as
  metas da Onda 2.
- **Guarda-corpos** monitorados desde já: frescor (dias emissão→coleta), cobertura (nº de
  estabelecimentos distintos), custo por cupom.

> As metas numéricas de crescimento serão fixadas na abertura da Onda 2, com o baseline real.

## Decisões arquiteturais que a onda vai demandar (via spike)

ADR-001 (ingestão/modelo canônico), ADR-002 (extração SEFAZ-SP), ADR-003 (deduplicação),
ADR-005 (pagamento/PIX), ADR-006 (LGPD/CPF). Cada um abre como estória de spike
(`target_role: arquiteto`) no início do épico correspondente.

## Riscos da onda (ver status report de abertura)

Fragilidade do scraping SEFAZ-SP (ADR-002); custo da camada de pagamento (mitigação: resgate
simplificado); baixa recorrência no piloto sem gamificação (aceito para a Onda 1).

## Próximo passo

Fluxo B: decompor **EPIC-000** em estórias (começando pelo STORY-000 spike de stack). Não
detalhar estórias dos épicos seguintes ainda.
