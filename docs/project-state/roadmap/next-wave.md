# Próxima onda (rascunho) — depois da WAVE-2026-02

> **Status: rascunho.** Esboço da onda seguinte à Onda 2 (de POC a produto). Épicos aqui são
> **candidatos com outcome**, não detalhados em estórias. O detalhamento só acontece quando a onda for
> aberta em `current-wave.md` (Fluxo A), após fechar a WAVE-2026-02 e ler o aprendizado do piloto.

## Tese provável da onda

Com a plataforma apresentável e o funil B2C vivo (Onda 2), o foco vira **rodar o piloto, medir a
north-star e sustentar recorrência** — e endurecer o que ficou como dívida consciente.

## Épicos candidatos (ordem a definir com o baseline em mãos)

1. **Piloto e baseline da north-star**
   - *Outcome:* piloto fechado (equipe + grupo convidado) rodando; primeira leitura de **cupons válidos,
     únicos e novos/semana**, com guarda-corpos (frescor, cobertura, custo/cupom).

2. **Recorrência / engajamento B2C (gancho de valor)**
   - *Outcome:* gamificação básica (pontos/níveis/ranking simples) e/ou "você pagou mais caro/barato que
     a média" — as alavancas de recorrência da visão §8.2 e §5.

3. **Endurecimento transversal (dívida da Onda 1)**
   - *Outcome:* scanner de segredos/dependências no CI (gitleaks/audit) e **observabilidade RED**
     (latência p50/p95/p99, taxa de erro) verificável — fechando as ressalvas das validações.

4. **B2B autenticado (quando houver demanda quente)**
   - *Outcome:* área Quantah Intelligence logada com a primeira entrega de valor de dado (a definir),
     partindo dos leads capturados na Onda 2.

5. **Caminho de produção com gate humano**
   - *Outcome:* promoção homologação → produção com aprovação de 1 clique (cruza com dívida do CI/CD).

## Dependências / sequência

- Depende do **fechamento da WAVE-2026-02** e do **aprendizado do piloto** para priorizar entre
  recorrência, endurecimento e B2B.
- Ao abrir a onda: fixar metas numéricas da north-star com o baseline real, escrever `current-wave.md`,
  registrar o PDR de escopo e gerar o status report de abertura.
