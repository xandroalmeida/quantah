# Visão de Produto — Quantah

> **Fonte:** consolidado a partir de `docs/visao.md` (v1.0). Este arquivo é a visão de
> produto operacional do PO; a `docs/visao.md` permanece como documento fundador de
> produto/negócio/marca.

## O problema

Todo dia milhões de NFC-e são emitidas no Brasil, cada uma com dados estruturados de
preço no ponto de venda (estabelecimento, item, código, preço, data). Esse dado é de
domínio público, mas fica **preso cupom a cupom** — sem consolidação, ninguém consegue
enxergar o preço praticado do varejo em escala e em tempo quase real. Quem precisa desse
dado (indústria/CPG, varejo, institutos) não tem uma fonte granular, fresca e nacional; e
o consumidor que gera o dado não recebe nada de volta.

## O valor central

O Quantah transforma os cupons que o consumidor **já recebe** em uma **base de preços do
varejo em tempo quase real**, com granularidade de item, loja, região e data. O consumidor
envia o QR Code da NFC-e (fricção mínima) e é recompensado; a plataforma extrai e consolida;
o dado agregado vira inteligência de mercado vendida ao B2B (Quantah Intelligence).

O ativo é o dado. O app de consumidor é o mecanismo de coleta; o painel B2B é onde está a
receita. A sequência do negócio é: **provar coleta e volume → estruturar produto B2B →
monetizar.**

## Para quem

- **Lado da coleta (oferta de dados):** consumidores brasileiros que recebem NFC-e —
  colaboradores casuais e engajados. Cupons de terceiros são bem-vindos; a única salvaguarda
  é a deduplicação por chave de acesso.
- **Lado do consumo (demanda, clientes pagantes):** indústria/CPG, varejo e terceiros de
  dados (consultorias, institutos).

## Escopo do MVP

- PWA, cobertura inicial **apenas Estado de São Paulo** (1 adaptador de extração).
- Coleta via QR Code (scan no app ou compartilhamento da URL do leitor nativo).
- Extração por scraping do portal público da SEFAZ-SP (migração para fonte oficial depois).
- Incentivo que **não depende de autorização regulatória**: **cashback** (R$ 1 a cada
  R$ 1.000 em cupons válidos, 0,1%) + **gamificação** sem prêmio material.
- **Sorteio** implementado desde já, porém **desligado por um gate de ativação** até sair a
  autorização SPA/SCPC — que corre em paralelo, fora do caminho crítico.
- Gancho de valor imediato ao consumidor: "você pagou mais caro/barato que a média".

**Fora do escopo do MVP** (evolução): comparador geolocalizado, controle de gastos, alertas
de preço; e os produtos B2B (painel de preço praticado, share of shelf, market basket,
índices de inflação, ruptura, benchmark) — que só serão modelados após validar o volume de
coleta.

## Princípios de produto (não-negociáveis)

1. **Fricção mínima na coleta** — enviar um cupom é escanear e confirmar. Cada passo a mais
   derruba a contribuição.
2. **O dado é o produto** — decisões priorizam volume, qualidade e frescor da base.
3. **Privacidade por design** — o objetivo nunca é identificar o consumidor; CPF é
   anonimizado desde a coleta.
4. **Confiança em dois lados** — o consumidor confia que será pago e respeitado; o cliente
   B2B confia que o dado é real, representativo e legalmente obtido.

## Hipótese central do MVP

Se dermos fricção mínima + recompensa (cashback/gamificação) + valor imediato de volta,
consumidores em SP enviarão cupons em **volume e frequência** suficientes para formar uma
base de preços densa o bastante para sustentar o produto B2B. **Validar essa hipótese de
coleta é o objetivo do MVP** — por isso a north-star é de volume de coleta (ver
`north-star.md`).
