---
id: SCREEN-STORY-026-landing-b2b-quantah-intelligence
story: STORY-026-landing-b2b-captacao-de-lead
epic: EPIC-005-portas-de-entrada
status: draft
created_at: 2026-07-05
updated_at: 2026-07-05
owner_designer: designer
related_ddrs: [DDR-004, DDR-005]   # DDR-004: sistema de marca; DDR-005: casca pública compartilhada (header/rodapé). Tela de agradecimento do lead = candidato a novo DDR (a formalizar na 026)
ds_components_used: [hero-band-dark, nav.bar, nav.link, pattern.form, input.text, button.primary, snackbar, empty-state, footer]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-026-landing-b2b-quantah-intelligence/index.html   # produzido no Passo 3; ainda não existe no rabisco
prototype_last_validated_at: null
---

# Spec de tela — Landing B2B / Quantah Intelligence (rabisco inicial)

> Referência: estória `STORY-026-landing-b2b-captacao-de-lead` (CAs e contexto vêm de lá — **não duplico**).
> Brief do PO: `epics/EPIC-005-portas-de-entrada/design-handoff.md` (decisões do PO já aplicadas).
> **Estágio: rabisco (Passo 1).** Direção para alinhar com o Programador. Microcopy completo, LGPD,
> a11y detalhada e protótipo HTML fiel vêm no Passo 3, após o sync. Não sai de `draft` sem protótipo validado.

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

## 5. Microcopy

> Rascunho — consolidar no Passo 3 com `voice-and-tone.md` (tom B2B sério/analítico) e glossário do PO.
> Tagline fixa: **"Do cupom ao insight."** **Texto do aviso LGPD**: proposta do Designer no Passo 3,
> **validada pelo PO** antes de produção (fronteira de microcopy legal). Labels/erros/CTA a redigir no spec.

## 6. Acessibilidade (a detalhar no Passo 3)

Piso AA: contraste do headline verde (`primary`) sobre `ink`; rótulos associados a cada campo; erro
vinculado ao campo e anunciado (leitor de tela); `aria-live` para snackbar/erro assíncrono; foco movido
para a tela de agradecimento após sucesso; alvo ≥48px.

## 7. Identificadores estáveis sugeridos

| Elemento | Identificador lógico |
|---|---|
| Campo nome | `landing-b2b-field-nome` |
| Campo e-mail | `landing-b2b-field-email` |
| Campo empresa | `landing-b2b-field-empresa` |
| CTA enviar | `landing-b2b-cta-enviar` |
| Erro do campo e-mail | `landing-b2b-error-email` |
| Confirmação (tela agradecimento) | `landing-b2b-sucesso` |

## 8. Exceções ao Design System

Nenhuma prevista no rabisco. Header/rodapé público compartilhado com a B2C → **candidato a DDR** (mesma
proposta da STORY-025). Confirmar no sync se a **tela de agradecimento** reusa `empty-state`/`content-band`
ou pede um padrão próprio (avaliar no protótipo).

## 9. Protótipo HTML fiel

Pendente (Passo 3). Cobrirá mobile+desktop e **todos** os estados do formulário (incl. sucesso, erro por
campo, duplicado); apresentado ao PO via `present_files` antes de `ready`.

## 10. Dependências e premissas

- Contrato de persistência do lead (modelo nome/e-mail/empresa + deduplicação) — definido pelo Programador
  na STORY-026 (não duplicar aqui).
- Texto/base legal do aviso LGPD — validação do PO.
- Header/rodapé compartilhado com a STORY-025 — coordenar (candidato a DDR/componente).

## 11. Histórico de mudanças

| Data | Mudança | Quem | Motivo |
|---|---|---|---|
| 2026-07-05 | criação (rabisco) | designer | rabisco inicial pós-decisões do PO; aguarda sync com Programador |
