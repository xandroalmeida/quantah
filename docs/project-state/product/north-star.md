# North-Star Metric — Quantah

> A north-star do MVP mede **volume de coleta** — a hipótese central do MVP é que
> conseguimos formar uma base de preços densa via crowdsourcing (ver `vision.md`). Enquanto
> não provarmos coleta, produto B2B e receita são prematuros.

## North-star

**Cupons NFC-e válidos, únicos e novos coletados por semana** — em SP durante o MVP.

- **Válido:** autenticado contra a SEFAZ-SP (cupom existe e é legítimo).
- **Único:** deduplicado pela chave de acesso de 44 dígitos (sem contagem em dobro).
- **Novo:** primeira vez que aquela chave entra na base.

Por que essa e não "cupons enviados" ou "usuários": o valor do produto é **dado real,
consolidável e não-duplicado**. Envios brutos inflam sem gerar base; usuários cadastrados não
garantem dado. Cupons válidos-únicos-novos é o que efetivamente **cresce o ativo**.

> Cadência semanal (não mensal) porque no MVP precisamos de loop de aprendizado curto para
> ajustar fricção e incentivo.

## Árvore de métricas de apoio

A north-star (cupons válidos-únicos-novos/semana) é produto de três alavancas. Mexer em
qualquer uma move o topo:

1. **Colaboradores ativos na semana** — quantas pessoas enviaram ≥1 cupom válido.
   *Alavanca de aquisição/retenção.* Sub-sinais: novos colaboradores/semana, retenção W1→W2.

2. **Cupons válidos por colaborador ativo** — volume médio por pessoa que envia.
   *Alavanca de engajamento/gamificação* (puxada pelo Colaborador Engajado). Sub-sinal:
   distribuição casual vs. caçador.

3. **Taxa de sucesso de envio** — % de cupons enviados que viram cupom válido-único-novo
   (não rejeitado por scraping quebrado, captcha, duplicata ou cupom inválido).
   *Alavanca de qualidade técnica da coleta e de fricção.* Sub-sinais: % falha de extração,
   % duplicata, % inválido.

Relação: **north-star ≈ (colaboradores ativos) × (cupons válidos por colaborador) × (taxa de
sucesso de envio)**.

## Guarda-corpos (não sacrificar em nome do volume)

- **Frescor:** mediana de dias entre a data de emissão do cupom e a coleta (dado velho vale
  menos para o B2B).
- **Cobertura:** nº de estabelecimentos/redes distintos cobertos por semana (densidade não
  pode vir de poucas lojas).
- **Custo por cupom:** cashback + infra por cupom válido coletado (o incentivo tem que ser
  sustentável).

## Metas

Baselines e metas numéricas serão fixados na abertura da 1ª onda (Fluxo A), após o Arquiteto
ratificar a stack e termos ambiente de homologação de pé — não faz sentido cravar número sem
um baseline real de coleta.
