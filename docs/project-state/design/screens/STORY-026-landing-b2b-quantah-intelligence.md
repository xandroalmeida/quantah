---
id: SCREEN-STORY-026-landing-b2b-quantah-intelligence
story: STORY-026-landing-b2b-captacao-de-lead
epic: EPIC-005-portas-de-entrada
status: ready
created_at: 2026-07-05
updated_at: 2026-07-05
owner_designer: designer
related_ddrs: [DDR-004, DDR-005, DDR-006]   # DDR-004: marca; DDR-005: casca pública; DDR-006: tela de agradecimento do lead (pattern.lead-confirmacao)
ds_components_used: [hero-band-dark, nav.bar, nav.link, pattern.form, input.text, button.primary, snackbar, empty-state, footer]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-026-landing-b2b-quantah-intelligence/index.html
prototype_last_validated_at: 2026-07-05   # validado pelo PO (Alexandro) — "Aprovado, pode implementar"
---

# Spec de tela — Landing B2B / Quantah Intelligence ("Do cupom ao insight.")

> Referência: estória `STORY-026-landing-b2b-captacao-de-lead` (CAs e contexto vêm de lá — **não duplico**).
> Brief do PO: `epics/EPIC-005-portas-de-entrada/design-handoff.md` (decisões do PO já aplicadas).
> **Estágio: spec detalhado (Passo 3).** Sync nas Notas da estória. Casca DDR-005 (face `b2b`); tela de
> agradecimento DDR-006 (`pattern.lead-confirmacao`, accepted). Texto LGPD validado pelo PO. Protótipo em
> `STORY-026-landing-b2b-quantah-intelligence/index.html`. `draft → ready` só após validação humana.

## 1. Objetivo da tela

Fazer um interessado B2B (indústria/CPG, varejo, terceiros de dados) **entender a oferta do Quantah
Intelligence e deixar o contato**. Uma tarefa principal: capturar um lead válido (nome, e-mail, empresa).
Tom **sério/analítico** (a face B2B da marca).

## 2. Fluxo

### Entrada
- Rota pública (sem sessão). Chega por divulgação B2B ou pelo CTA "Para empresas" da landing B2C.

### Ações possíveis
- **Primária:** preencher e enviar o formulário de lead (nome, e-mail, empresa).
- **Secundária:** voltar para a landing B2C (nav compartilhada).

### Saída
- Após envio bem-sucedido → **tela dedicada de agradecimento** (decisão do PO), com o próximo passo
  ("entraremos em contato").
- Após erro de validação → permanece no form, erro por campo (`pattern.form`), sem persistir.
- Após duplicado → mensagem neutra (idempotente), sem vazar dado de terceiros.

## 3. Layout

### Mobile (≥360px)

```
+----------------------------------+
| [nav.bar]  Quantah Intelligence  |  <- nav compartilhada; wordmark "Intelligence"
+----------------------------------+
|  hero-band-dark (ink)            |
|  "Do cupom ao insight."          |  <- headline verde (primary) sobre ink
|  subtítulo analítico body-lg     |
+----------------------------------+
|  content: o que é / para quem    |  <- 2-3 blocos sóbrios, sem festa
+----------------------------------+
|  Formulário (pattern.form)       |
|   [ Nome            ]            |  <- input.text
|   [ E-mail          ]            |
|   [ Empresa         ]            |
|   fine print LGPD + link         |  <- caption/mute: aviso curto + link política (sem checkbox)
|   [ Quero saber mais ]           |  <- button.primary (único verde)
+----------------------------------+
|  [footer] (compartilhado)        |
+----------------------------------+
```

### Desktop (≥1024px)

```
+----------------------------------------------------------+
| [nav.bar] Quantah Intelligence            Para coletores |
+----------------------------------------------------------+
|  hero-band-dark: "Do cupom ao insight." (headline verde) |
+----------------------------------------------------------+
|  2 colunas: ESQ conteúdo (o que é / valor)               |
|             DIR card do formulário (pattern.form)        |
+----------------------------------------------------------+
|  [footer]                                                |
+----------------------------------------------------------+
```

- `hero-band-dark` casa com o tom B2B (headline verde sobre `ink`). No desktop, form em card à direita
  (`canvas` sobre fundo escuro) — elevação por contraste de superfície (tokens).

## 4. Estados (o formulário exige todos — detalhar no Passo 3)

- **Padrão / vazio** — form pronto para preenchimento (mobile + desktop).
- **Preenchendo** — foco visível, validação inline leve.
- **Enviando (loading)** — estado do `button.primary` (desabilitado + spinner no botão); **sem** spinner em
  tela branca.
- **Sucesso** — **tela dedicada de agradecimento** (não só `snackbar`), com próximo passo.
- **Erro de validação por campo** — obrigatório ausente / e-mail inválido, associado ao campo (`pattern.form`),
  mensagem textual (não só borda), sem persistir.
- **Duplicado** — e-mail já cadastrado: mensagem neutra idempotente (não confirma nem nega existência de terceiro).
- **Erro de rede/inesperado** — mensagem honesta + retry (`snackbar` danger), sem stack trace.

## 5. Microcopy (completo — pt-BR, voz B2B: séria/analítica)

| Local | Texto |
|---|---|
| Header — wordmark | **Quantah** / **Intelligence** · link **Voltar ao app** |
| Hero — headline | **Do cupom ao insight.** |
| Hero — subtítulo | **Preço praticado, share of shelf e índices de inflação por região, a partir de milhões de cupons fiscais. Para indústria, varejo e times de dados.** |
| Conteúdo — bloco 1 | **Preço real, item a item** — O que foi de fato pago no varejo, por região e período — direto da nota fiscal. |
| Conteúdo — bloco 2 | **Sortimento e share of shelf** — Que produtos aparecem, onde e com que frequência na cesta do consumidor. |
| Conteúdo — bloco 3 | **Para quem decide com dados** — Indústria/CPG, varejo e times de dados que precisam de preço e sortimento reais. |
| Form — título | **Quero saber mais** (intro desktop: "Fale com o time do Intelligence.") |
| Form — apoio | Nome, e-mail e empresa — nossa equipe entra em contato. |
| Form — labels | **Nome** · **E-mail** · **Empresa** |
| Form — CTA | **Quero saber mais** (único verde; `loading` no envio) |
| Erro — e-mail inválido | **Use um e-mail válido, com @ e domínio.** |
| Erro — obrigatório | **Informe o [campo].** (ex.: "Informe o nome.") |
| **LGPD (validado pelo PO)** | **Ao enviar, você concorda que a Quantah use seu nome, e-mail e empresa para entrar em contato sobre o Quantah Intelligence. Saiba como tratamos seus dados na [Política de Privacidade](/privacidade).** (aviso curto, **sem checkbox**) |
| Erro de rede | **Não foi possível enviar agora.** Verifique a conexão e tente de novo. (`snackbar` danger + "Tentar de novo") |
| **Agradecimento** (DDR-006) | **Recebemos seu contato.** / Nossa equipe do Quantah Intelligence entra em contato em breve pelo e-mail informado. / CTA **Voltar ao Quantah Intelligence** |
| Duplicado | **mesma** tela de agradecimento (idempotente) — sem confirmar nem negar existência de terceiro |

> Tagline fixa **"Do cupom ao insight."**; tom sério/analítico, sem emoji, sem festa (`voice-and-tone.md`).

### Página `/privacidade` (conteúdo base LGPD — validação do PO)

Página pública de texto (fora do fluxo de lead), destino do link do aviso. Conteúdo mínimo proposto:
**Controlador** (Quantah); **Dados coletados** (nome, e-mail, empresa no lead B2B; + dados de coleta do
app conforme o caso); **Finalidade** (contato comercial sobre o Quantah Intelligence; operação do app);
**Base legal** (consentimento no envio do formulário); **Compartilhamento** (não vende dados a terceiros);
**Direitos do titular** (acesso, correção, exclusão) e **canal de contato** (e-mail do encarregado);
**Retenção**. Texto legal **validado pelo PO** antes de produção.

## 6. Acessibilidade (AA)

- **Contraste:** headline `primary` sobre `ink` (grande, ≥3:1); corpo `body`/`ink` sobre `canvas`;
  `on-primary` sobre `primary` no CTA; erro `negative-darkest` (texto) + borda `negative` (≥3:1 UI).
- **Formulário:** cada campo com `<label>` associado; erro vinculado por `aria-describedby` e
  `role="alert"` (feedback nunca só por cor — mensagem textual). Alvos ≥48px.
- **Envio:** `button.primary` com `aria-busy` no loading (spinner no botão, sem tela branca).
- **Sucesso:** foco movido para o `<h1>` da tela de agradecimento (`tabindex="-1"` + `focus()`).
- **Erro assíncrono (rede):** `snackbar` com `role="alert"`/`aria-live`.
- **Semântica:** `<nav>` / `<main>` / `<section aria-labelledby>` / `<footer>`; um `<h1>` por tela.

## 7. Identificadores estáveis (âncoras de E2E — CA-6)

| Elemento | `data-testid` |
|---|---|
| Campo nome | `landing-b2b-field-nome` |
| Campo e-mail | `landing-b2b-field-email` |
| Campo empresa | `landing-b2b-field-empresa` |
| CTA enviar | `landing-b2b-cta-enviar` |
| Erro do campo e-mail | `landing-b2b-error-email` |
| Snackbar erro de rede | `landing-b2b-erro-rede` |
| Confirmação (tela agradecimento) | `landing-b2b-sucesso` |
| CTA de saída da confirmação | `landing-b2b-sucesso-voltar` |

## 8. Exceções ao Design System

Nenhuma. Casca compartilhada = **DDR-005** (`pattern.public-shell`, face `b2b`). Tela de agradecimento =
**DDR-006** (`pattern.lead-confirmacao`) — **não** reusa `empty-state` (semântica "sem dados", diferente
de "recebido com sucesso"); compõe `content-band` + confirmação positiva (`positive`, não o verde de CTA)
+ `CtaLink`. Formulário = `pattern.form` com `input.text` (`Field`/`TextField`).

## 9. Protótipo HTML fiel

`STORY-026-landing-b2b-quantah-intelligence/index.html` — vanilla, zero build. Tokens reais do DS. Cobre
mobile (`?vp=mobile`) e desktop (`?vp=desktop`) e **todos** os estados via `?state=`: padrão, preenchendo,
enviando, erro-campo, duplicado, erro-rede, sucesso. Duplicado renderiza a **mesma** tela de agradecimento.
Sem rede/backend. Apresentado ao PO para validação antes de `ready`.

## 10. Dependências e premissas

- Persistência do lead (modelo nome/e-mail/empresa + deduplicação idempotente por e-mail) — Programador
  (STORY-026). Sucesso via **PRG** → rota dedicada `/intelligence/obrigado` (DDR-006).
- `/privacidade` — página pública de texto criada nesta estória (destino do link LGPD); conteúdo validado
  pelo PO.
- Casca compartilhada com a STORY-025 via `PublicLayout` (DDR-005).

## 11. Histórico de mudanças

| Data | Mudança | Quem | Motivo |
|---|---|---|---|
| 2026-07-05 | criação (rabisco) | designer | rabisco inicial pós-decisões do PO; aguarda sync com Programador |
| 2026-07-05 | spec detalhado + protótipo (Passo 3) | designer | pós-sync; DDR-006 accepted + texto LGPD validado; microcopy/estados/a11y/ids completos e protótipo HTML fiel |
