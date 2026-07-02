---
id: DDR-002
title: Diferenciação de marca — usar o DS Wise-derived como base, não clonar o Wise
status: accepted
created_at: 2026-07-02
decided_at: 2026-07-02
approved_by: Alexandro
supersedes: ~
superseded_by: ~
related_ddrs: [DDR-001]
related_adrs: []
related_pdrs: [PDR-001]
scope: transversal
affects_screens: []
---

# DDR-002 — Diferenciação de marca: base de sistema, não clone do Wise

## Contexto

O PDR-001 adotou um DS derivado da linguagem do **Wise** (fintech global). A `docs/visao.md`
§11.4 pede identidade "precisa, confiável, moderna, brasileira sem ser caricata" e **evitar
explicitamente** "cara de app de cupom de desconto" e "cara de banco/fintech genérico". Há
tensão direta: adotar a estética de uma fintech e ao mesmo tempo não parecer fintech genérica.
O PDR-001 resolveu a tensão delegando ao Designer: usar o DS como **base de sistema** e
diferenciar para o app parecer Quantah. Este DDR fixa **como** diferenciar.

Documentos lidos: PDR-001, `docs/visao.md` §11, `DESIGN-wise.md`, `vision.md`/`personas.md` do
estado de produto.

## Forças (drivers)

- **Risco de confusão de marca** (alto): parecer "um Wise" ou "só mais um app de cupom" corrói
  confiança do Colaborador ("vou colocar meus dados aqui?") e credibilidade do B2B.
- **Conceito de marca da visão** (alto): "medição + dado", pontos que formam padrões, grid.
- **Custo/velocidade no MVP** (médio): north-star é volume de coleta, não refino visual — a
  diferenciação precisa ser barata e localizada, não um rebrand completo.
- **Coerência do DS** (médio): diferenciar sem quebrar as regras de ouro dos tokens.

## Opções consideradas

### Opção A — Diferenciar por camada de marca sobre o sistema Wise (recomendada)
Manter tokens/componentes do DS Wise-derived; diferenciar em: **logo/wordmark Quantah próprio**,
**linguagem de ilustração autoral** (pontos/grid = "dado que se multiplica", conceito da visão),
**microcopy Quantah** (duas faces: app amigável / B2B analítico), e uso pontual dos **momentos
dark+green** (`card.feature-dark`, `hero-band-dark`) como assinatura. Sem tocar na paleta base.
- **Prós:** barato, rápido, preserva o sistema pronto; foco no que carrega marca (logo, ilustração,
  voz); reversível/incremental.
- **Contras:** paleta continua próxima da do Wers; diferenciação depende de execução de logo/ilustração.

### Opção B — Reskin de paleta (trocar o verde-limão por um verde Quantah próprio)
Manter estrutura, mas mudar o hue do accent para descolar visualmente do Wise.
- **Prós:** distância visual imediata do Wise.
- **Contras:** mexe na fundação (novo DDR de paleta, re-derivar semânticas/contraste); custo alto
  no MVP; risca a "regra de ouro" sem ganho de coleta. Prematuro.

### Status quo — adotar o Wise sem diferenciar
- **Contras:** viola a intenção da visão (§11.4); risco de parecer clone/fintech genérica.

## Avaliação contra os princípios

| Princípio | A | B | Status quo |
|---|---|---|---|
| 1. Simplicidade radical | ✅ camada fina | ⚠️ re-derivação | ✅ |
| 2. Mobile-first / paridade | ✅ | ✅ | ✅ |
| 3. Tom profissional Quantah | ✅ resolve a tensão | ✅ | ❌ parece Wise |
| 4. Padronização > criatividade | ✅ preserva DS | ⚠️ | ✅ |
| 5. Acessibilidade | ✅ | ⚠️ revalidar contraste | ✅ |
| 6. Performance percebida | ✅ | ✅ | ✅ |
| 7. Estados além do feliz | n/a | n/a | n/a |

## Decisão

> **Adotada:** Opção A — diferenciação por **camada de marca** sobre o sistema Wise-derived,
> sem alterar a paleta base no MVP.

A diferenciação vive em logo/wordmark próprio, ilustração autoral (conceito "medição + dado":
pontos/grid), microcopy de duas faces e uso deliberado dos momentos dark+green como assinatura.
A troca de paleta (Opção B) fica como caminho futuro, disparado só por evidência (ver critérios).

## Consequências

### Positivas
- Resolve a tensão da visão sem custar um rebrand; mantém o sistema pronto e a velocidade do MVP.
- Concentra o esforço de marca no que de fato diferencia (logo, ilustração, voz).

### Negativas / trade-offs assumidos
- A paleta segue próxima da do Wise no MVP — risco residual de associação, mitigado por logo,
  ilustração e voz. Monitorado (ver critérios para revisitar).

### Impacto no Design System
- `voice-and-tone.md`: já reflete as duas faces e o "sem cara de app de cupom".
- `patterns.md`: `pattern.surface-rhythm` marca o uso pontual de dark+green como assinatura.
- Diretriz de ilustração (pontos/grid) a detalhar quando abrir a 1ª tela com ilustração.

### Impacto em telas existentes
- Nenhuma tela existe ainda. `affects_screens: []`.

## Implementação sugerida (notas para o Programador)
- Nada a implementar agora além de reservar slot de logo/wordmark no `nav.bar`/`app.bar`.

## Critérios para revisitar
- Se, em teste com usuários do lado de coleta, o app for percebido como "app de banco/fintech"
  ou "parece o Wise" → reavaliar, possivelmente escalando para a Opção B (reskin de paleta) via
  novo DDR (que supersede este na parte de paleta).

## Aprovação humana

| Campo | Valor |
|---|---|
| Apresentado em | 2026-07-02 |
| Aprovado por | Alexandro |
| Data da aprovação | 2026-07-02 |
| Observações do aprovador | Aprovado em sessão de Cowork (papel Designer). |

> Sem este bloco preenchido, o DDR não pode ir para `accepted`.
