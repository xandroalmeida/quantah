---
id: DDR-006
title: Confirmação pós-envio de lead — tela dedicada de agradecimento (PRG), não snackbar
status: accepted   # proposed | accepted | superseded | rejected | deferred
created_at: 2026-07-05
decided_at: 2026-07-05
approved_by: Alexandro
supersedes: ~
superseded_by: ~
related_ddrs: [DDR-005, DDR-004]
related_adrs: []
related_pdrs: [PDR-001, PDR-003]
scope: navegação / confirmação (captação de lead B2B — EPIC-005)
affects_screens: [SCREEN-STORY-026-landing-b2b-quantah-intelligence]
---

# DDR-006 — Tela dedicada de agradecimento do lead (não snackbar)

## Contexto

A landing B2B (STORY-026) captura um lead (nome, e-mail, empresa). O PO decidiu (handoff, decisão #3) que
a confirmação de sucesso é uma **tela dedicada de agradecimento** — **não** apenas um `snackbar` — com
espaço para o próximo passo ("entraremos em contato"). Isso adiciona uma tela/estado ao fluxo; o rabisco
(§8) pediu para formalizar no protótipo se a tela reusa `empty-state`/`content-band` ou pede padrão
próprio. Como é um padrão de confirmação **durável** (vale para esta e para futuras capturas de lead) e
toca a navegação (para onde o fluxo vai após o POST), formaliza-se como DDR antes de codar UI.

Lido: STORY-026 (CA-2 "confirmação em pt-BR"; CA-4 duplicado idempotente sem vazar terceiro),
`design-handoff.md` (decisão #3), **DDR-005** (casca pública `PublicLayout` face `b2b`), DS
(`components.md` → `empty-state`, `badge.positive`, `content-band`; `patterns.md` → `pattern.error`,
`pattern.surface-rhythm`; `voice-and-tone.md` → tom B2B sério/analítico), PDR-003.

## Forças (drivers)

- **Decisão do PO (alto):** tela dedicada, não `snackbar` — a confirmação é um momento do funil de receita,
  merece foco e "próximo passo".
- **Robustez do envio (alto):** o POST cria dado (lead). Precisa de **PRG** (Post/Redirect/Get) para
  refresh e "voltar" não reenviarem o formulário (sem lead duplicado por F5).
- **Idempotência sem vazamento (alto, LGPD/CA-4):** e-mail novo e e-mail duplicado terminam na **mesma**
  confirmação — a tela não pode revelar se o contato já existia.
- **Princípio #7 — estados além do feliz (alto):** sucesso é um estado de primeira classe, com microcopy e
  foco geridos (mover foco para a confirmação; a11y).
- **Princípio #4 — padronização (médio):** reusar componentes do DS; não inventar primitivo.
- **Tom B2B (médio):** confirmação sóbria/analítica, sem festa/emoji.

## Opções consideradas

### Opção A — Tela dedicada via PRG: rota + página própria de agradecimento *(proposta)*

`POST /intelligence/leads` persiste (idempotente) e **redireciona** para `GET /intelligence/obrigado`, uma
página Inertia dedicada (`Intelligence/LeadObrigado`) dentro da casca pública (`PublicLayout` face `b2b`).
Composição do DS: `content-band` + confirmação positiva (marca/`badge.positive` sóbrio) + headline
"Recebemos seu contato." + próximo passo ("Nossa equipe entra em contato em breve.") + `CtaLink` de saída
("Voltar ao Quantah Intelligence" / "Conhecer o app"). Foco movido para o `<h1>` da confirmação.

```
mobile (≥360px) — após enviar
+----------------------------------+
| [nav.bar público]                |
+----------------------------------+
|  content-band                    |
|   ✓ Recebemos seu contato.       |  <- confirmação positiva sóbria (badge/mark positive)
|   Nossa equipe entra em contato  |
|   em breve.                      |
|   [ Voltar ]                     |  <- CtaLink (secundário/neutro)
+----------------------------------+
| [footer público]                 |
+----------------------------------+
```

- **Prós:** é literalmente uma **tela dedicada** (atende a decisão do PO); **PRG** torna refresh/voltar
  seguros (nada de reenvio); mesma tela para novo e duplicado (não vaza terceiro); foco/`aria` geridos;
  compõe só DS (`content-band`, confirmação positiva, `CtaLink`) na casca pública — sem primitivo novo.
- **Contras:** a rota `/intelligence/obrigado` é acessível diretamente (quem digita a URL vê o
  agradecimento sem ter enviado) — inócuo; mitigável, se incomodar, exibindo um estado neutro/CTA de volta.

### Opção B — Mesma rota, estado de agradecimento por flash

`POST` redireciona de volta a `/intelligence` com um flash (`lead=enviado`); a `LandingB2B` troca o
formulário pela confirmação quando o flash está presente. Sem rota nova.

- **Prós:** confirmação **efêmera** (some no refresh — não fica bookmarkável); uma rota só.
- **Contras:** não é uma "tela dedicada" e sim um estado da mesma tela (mais distante da decisão #3);
  exige expor mais um key de flash no `HandleInertiaRequests`; no refresh o usuário volta ao formulário
  vazio (pode reenviar sem querer). Menos robusto que PRG dedicado.

### Opção C — `snackbar` de sucesso (status quo implícito)

Confirmação só por `snackbar` sobre a própria landing.

- **Contras:** **rejeitada pelo PO** (decisão #3 pede tela dedicada, não `snackbar`). Não dá espaço ao
  próximo passo. Rejeitada.

## Avaliação contra os princípios

| Princípio | A (proposta) | B | C |
|---|---|---|---|
| 1. Simplicidade radical | ✅ uma tela, uma mensagem | ✅ | ✅ |
| 2. Mobile-first com paridade | ✅ | ✅ | ✅ |
| 3. Tom profissional B2B | ✅ sóbrio, próximo passo | ✅ | ⚠️ efêmero demais p/ B2B |
| 4. Padronização > criatividade | ✅ compõe DS, sem primitivo | ✅ | ✅ |
| 5. Acessibilidade | ✅ foco movido, `aria` | ⚠️ foco no swap in-place | ⚠️ `aria-live` só |
| 6. Performance percebida | ✅ PRG limpo | ✅ | ✅ |
| 7. Estados além do feliz | ✅ sucesso de 1ª classe + refresh-safe | ⚠️ refresh reabre form | ❌ |

## Decisão

> **Adotada (proposta):** **Opção A** — tela dedicada de agradecimento via PRG.

Atende a decisão do PO (tela dedicada, não `snackbar`), é a única **refresh/voltar-safe** para um POST que
cria dado, e entrega a mesma confirmação para novo e duplicado (LGPD/CA-4). Compõe só componentes do DS na
casca pública (DDR-005), sem débito de primitivo. B é um estado in-place menos robusto; C foi rejeitada
pelo PO.

## Consequências

### Positivas
- Confirmação clara com próximo passo; refresh/voltar não reenviam o lead.
- Idempotência sem vazamento: novo e duplicado terminam na mesma tela.
- Reuso total do DS + casca pública; nova página é composição.

### Negativas / trade-offs assumidos
- `/intelligence/obrigado` é acessível por URL direta (inócuo). Se incomodar, exibir estado neutro.
- Uma rota GET a mais na área pública B2B.

### Impacto no Design System
- **Novo padrão:** `pattern.lead-confirmacao` (confirmação pós-envio: `content-band` + confirmação
  positiva sóbria + próximo passo + CTA de saída, na casca pública; foco movido para o `<h1>`). Documentar
  em `patterns.md` **nesta operação** se aceito. **Não** reusa `empty-state` (que é "sem dados + criar" —
  semântica diferente de "recebido com sucesso").
- Nenhum primitivo/token novo — compõe `content-band`, confirmação positiva (`badge.positive`/marca),
  `CtaLink`.

### Impacto em telas existentes
- `SCREEN-STORY-026-...` (este spec) — desenhado com a tela de agradecimento.
- `Pages/Intelligence/Reservado.jsx` é substituído pela landing B2B na STORY-026 (mesma rota).

## Implementação sugerida (notas para o Programador)

- `POST /intelligence/leads` (ação `CapturarLead`, idempotente) → `redirect('/intelligence/obrigado')`.
- `GET /intelligence/obrigado` → `Intelligence/LeadObrigado` dentro do `PublicLayout` face `b2b`.
- Mover foco para o `<h1>` da confirmação ao montar (`tabindex="-1"` + `focus()`), `data-testid=landing-b2b-sucesso`.
- Microcopy pt-BR, tom B2B sóbrio; sem emoji. Confirmação positiva usa `positive` (não o verde de CTA).

## Critérios para revisitar

- Se a captação de lead ganhar múltiplos formulários/contextos — generalizar o padrão de confirmação.
- Se o B2B ganhar conta/login numa onda futura — o pós-lead pode virar onboarding (revisar).
- Se dados de uso mostrarem abandono na tela de agradecimento — revisar o próximo passo/CTA.

## Aprovação humana

| Campo | Valor |
|---|---|
| Apresentado em | 2026-07-05 |
| Aprovado por | Alexandro |
| Data da aprovação | 2026-07-05 |
| Observações do aprovador | Aprovado ("Aprovado, seguir") sobre o resumo em chat — Opção A (tela dedicada via PRG) confirmada. |

> Aprovado. Padrão de confirmação pós-lead (`pattern.lead-confirmacao`) vigente para o EPIC-005 (STORY-026).
