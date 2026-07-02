---
pdr_id: PDR-001
slug: design-system-wise
title: Adotar o design system Wise-derived como base do UX/UI do Quantah
status: accepted  # proposed | accepted | superseded | rejected
decided_at: 2026-07-02
decided_by: PO (Alexandro / Claude)
supersedes: null
superseded_by: null
related_epics: []
related_adrs: [ADR-000]
---

# PDR-001 — Adotar o design system Wise-derived como base do UX/UI do Quantah

## Contexto

O Quantah tem duas faces de interface (app de coleta para o Colaborador e, futuramente,
Quantah Intelligence para o Analista B2B) e ainda não tem linguagem visual definida. A
`docs/visao.md` §11.4 dá uma direção de marca (preciso, confiável, moderno, brasileiro sem
ser caricato; conceito de *medição + dado*; **evitar** cara de "app de cupom de desconto" e
de "banco/fintech genérico"), mas não um sistema aplicável.

Existe no repositório um design system pronto e completo — `docs/DESIGN-wise.md` — derivado da
linguagem do Wise: verde-limão como único accent de CTA, canvas sage, tipografia display
pesada (Wise Sans 900) + Inter para o resto, cards e botões com raio 24px, paleta semântica
completa, e um conjunto de componentes/tokens já especificado. O usuário (aprovador do
produto) determinou que o UX/UI da plataforma **siga este design system**.

## Opções consideradas

### Opção 1 — Adotar o DESIGN-wise.md como base obrigatória do DS
- Descrição: tornar o `DESIGN-wise.md` a fonte dos tokens e componentes; o Designer o
  consolida no DS canônico do projeto e o mapeia para o tema da stack de FE (Inertia+React).
- Prós: sistema completo e coerente já pronto (cores, tipografia, spacing, componentes,
  do's/don'ts); acelera muito o discovery de UI; verde+precisão+grid conversa com o conceito
  de marca da visão; paleta semântica pronta para estados de produto.
- Contras: é a estética de uma **fintech** (Wise) — tensão direta com o "evitar banco/fintech
  genérico" da visão; usa fonte proprietária (Wise Sans) que exige substituto open-source;
  risco de o app "parecer um clone do Wise" em vez de parecer Quantah.

### Opção 2 — Criar um design system do zero a partir da §11.4 da visão
- Descrição: o Designer parte só do briefing de marca e constrói tudo.
- Prós: identidade 100% própria desde o começo.
- Contras: caro e lento no MVP (a north-star é volume de coleta, não polimento visual);
  reinventa o que o DESIGN-wise.md já entrega; risco de inconsistência.

### Opção 3 — Status quo (nada mudar)
- Consequência: cada tela nasce com decisão visual ad hoc, sem sistema — dívida de design e
  retrabalho garantidos assim que houver mais de duas telas.

## Decisão

> **Optamos pela Opção 1.**

O UX/UI do Quantah adota o design system de `docs/DESIGN-wise.md` como **base obrigatória**
(tokens de cor, tipografia, spacing, raios, elevação e o catálogo de componentes). O Designer
consolida esse material no DS canônico do projeto e o mapeia para o tema da stack de FE ativa;
a partir daí, toda tela do produto é construída sobre esse sistema.

## Justificativa

Entrega um sistema visual completo e coerente sem custo de discovery, o que é o certo para uma
fase cujo norte é **volume de coleta** e não refino estético — o DS deve viabilizar telas
rápidas, usáveis e consistentes para pessoas não-técnicas. O accent verde, o grid e a
sensação de precisão conversam com o conceito de marca da visão (medição + dado). A paleta
semântica pronta cobre os estados de produto (aceite/recusa de cupom, saldo, alertas) que o
fluxo de coleta exige.

## Consequências

### Positivas
- Base de tokens/componentes pronta → Designer foca em fluxo e telas, não em fundar o sistema.
- Consistência visual desde a 1ª tela; menos dívida de design.
- Paleta semântica pronta para os estados do fluxo de coleta.

### Negativas / trade-offs aceitos
- **Tensão de marca:** o DS é fintech (Wise). Aceitamos usá-lo como *base de sistema*, não
  como identidade final — o Designer deve **diferenciar** para o app parecer Quantah (logo,
  ilustração, microcopy, uso do dark/green para "momentos de marca"), não um clone do Wise.
- **Fonte proprietária:** Wise Sans não é usável; adota-se substituto open-source (Inter 900
  ou Manrope 800/900 no display; Inter no corpo), conforme o próprio DESIGN-wise.md indica.

### Para o time técnico
- ADRs que esta decisão pode demandar: o ADR-000 (stack Inertia+React) precisa contemplar como
  os tokens do DS mapeiam para o tema/CSS da stack (Tailwind config, variáveis CSS).
- Impacto em épicos: cria uma trilha de Designer no início da 1ª onda (ver
  `roadmap/next-wave.md`) — "Design System foundation" antes das telas de fluxo.

## Padrões transversais que esta decisão institui (qualidade de produto)

Estes são requisitos de produto — valem para toda estória de UI, independentemente do épico:

1. **Toda estória com UI referencia o DS canônico** e usa seus tokens/componentes — não
   introduz cor, fonte, raio ou espaçamento fora do sistema.
2. **Sem segundo accent de marca** — o verde é o único accent de CTA; estados usam a paleta
   semântica (nunca o verde-CTA como "sucesso").
3. **Acessibilidade mínima** — contraste e alvos de toque conforme o DS (~48px de botão).
4. **Mobile-first com paridade responsiva** — breakpoints do DS (mobile <768 / tablet /
   desktop), sem "mobile esticado" nem "desktop encolhido".
5. **Estórias de UI são `requires_design: true`** — Designer e Programador alinham cedo na
   mesma estória.

## Divisão de papéis a partir daqui

- **Designer (não-PO):** consolida `DESIGN-wise.md` → DS canônico em
  `docs/especificacao/design-system.md` + tokens em `docs/project-state/design/system/`,
  registra DDRs (inclusive a substituição de fonte e a diferenciação de marca vs. Wise), e
  mapeia os tokens para o tema Inertia+React.
- **Arquiteto (não-PO):** ao ratificar o ADR-000, contempla o mapeamento tokens→tema da stack.
- **PO (eu):** mantenho o padrão transversal acima nos critérios de aceite das estórias.

## Sinais de revisão

- Se, em teste com usuários do lado de coleta, o app for percebido como "app de banco/fintech"
  ou "não confio pra colocar meus dados", reavaliar a diferenciação de marca sobre o DS.
- Se o mapeamento dos tokens para a stack de FE se mostrar inviável ou custoso, o Arquiteto
  escala e reavaliamos a base.
