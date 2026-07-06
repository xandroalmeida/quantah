# Próxima onda (rascunho) — depois da WAVE-2026-03

> **Status: rascunho.** Esboço da onda seguinte à Onda 3 (remuneração por pontos). Épicos aqui são
> **candidatos com outcome**, não detalhados em estórias. O detalhamento só acontece quando a onda for
> aberta em `current-wave.md` (Fluxo A), após fechar a WAVE-2026-03.

## Tese provável da onda

Com o incentivo definitivo (pontos) vivo, o foco volta ao plano adiado pelo PDR-005: **rodar o piloto,
medir a north-star com o modelo novo e endurecer o que ficou como dívida**.

## Épicos candidatos (ordem a definir com o aprendizado da Onda 3)

1. **Piloto e baseline da north-star**
   - *Outcome:* piloto fechado (equipe + grupo convidado) rodando **sobre o modelo de pontos**; primeira
     leitura de cupons válidos, únicos e novos/semana, com guarda-corpos (frescor, cobertura,
     custo/cupom — agora derivado dos parâmetros de pontuação).

2. **Gamificação sem valor material (camada de jogo)**
   - *Outcome:* badges, níveis e/ou ranking sobre os pontos (visão §8.2 — livre de autorização),
     mantida a separação conceitual pontos-remuneração × jogo (PDR-004, premissa regulatória).

3. **Endurecimento transversal (dívidas das Ondas 1–2)**
   - *Outcome:* scanner de segredos/dependências no CI (WISH-004), observabilidade (WISH-006), chaves
     i18n (WISH-005) — fechando ressalvas das validações.

4. **B2B autenticado (quando houver demanda quente)**
   - *Outcome:* área Quantah Intelligence logada com a primeira entrega de valor de dado — agora com o
     CNAE do enriquecimento (EPIC-009) como dimensão de segmentação.

5. **Caminho de produção com gate humano**
   - *Outcome:* promoção homologação → produção com aprovação de 1 clique.

## Dependências / sequência

- Depende do **fechamento da WAVE-2026-03** — em particular da calibragem inicial dos parâmetros de
  pontuação (custo/cupom simulado antes do piloto).
- Gate manual de device do EPIC-008 (PWA) idealmente resolvido antes do piloto.
- Ao abrir a onda: fixar metas numéricas da north-star, escrever `current-wave.md`, registrar PDR de
  escopo e gerar status report de abertura.
