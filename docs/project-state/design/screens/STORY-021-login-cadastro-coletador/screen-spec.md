---
id: SCREEN-STORY-021-login-cadastro-coletador
story: STORY-021-login-cadastro-coletador-padrao-visual
epic: EPIC-004-acesso-e-areas
status: in_implementation
created_at: 2026-07-04
updated_at: 2026-07-04
owner_designer: Designer (agente)
related_ddrs: [DDR-004]  # accepted 2026-07-04
ds_components_used:
  - button.primary
  - button.tertiary
  - input.text
  - input.masked
  - card.content
  - card.feature-dark
  - snackbar
  - badge.positive
viewports: [mobile, desktop]
prototype_path: STORY-021-login-cadastro-coletador/index.html
prototype_last_validated_at: 2026-07-04
---

# Spec de tela — Acesso do Coletador (entrar / criar conta / recuperar senha / confirmar e-mail)

> Referência: estória `STORY-021` (CAs e contexto vêm de lá — **não duplico**).
> Depende do **DDR-004** (padrão de acesso: marca Quantah no lugar do logo Laravel, split-hero no
> desktop, hierarquia Google vs. e-mail/senha). Enquanto o DDR estiver `proposed`, este spec fica `draft`.

## 1. Objetivo da tela

Dar ao Coletador uma **porta de entrada que parece o Quantah** (não scaffolding do Laravel), em pt-BR,
com o mínimo de fricção: entrar ou criar conta por **e-mail/senha**, recuperar senha e confirmar e-mail.
A tela **reserva o lugar do botão "Entrar com Google"** (implementado na STORY-022).

Uma tarefa por tela: **entrar** / **criar conta** / **pedir link de redefinição** / **definir nova
senha** / **confirmar e-mail**. Nenhuma acumula duas ações primárias.

## 2. Fluxo

### Entrada
- **Entrar** (`/login`) — visitante não autenticado; destino padrão do Breeze e de qualquer redirect por
  `auth`. Foco inicial no campo **E-mail**.
- **Criar conta** (`/register`) — a partir do link "Criar conta" na tela de entrar, ou de landing/CTA.
- **Redefinir senha** (`/forgot-password`) — a partir de "Esqueci minha senha".
- **Criar nova senha** (`/reset-password/{token}`) — a partir do link no e-mail de redefinição.
- **Confirmar e-mail** (`/verify-email`) — após criar conta por e-mail/senha (usuário autenticado, ainda
  não verificado).

### Ações possíveis e saídas
| Tela | Ação primária | Saída sucesso | Ações secundárias |
|---|---|---|---|
| Entrar | Entrar (e-mail/senha) | destino pós-login atual (até a home-hub do EPIC-006 existir) | Esqueci minha senha → Redefinir; Criar conta → Criar conta; Google (desabilitado) |
| Criar conta | Criar conta | Confirmar e-mail | Entrar → Entrar; Google (desabilitado) |
| Redefinir senha | Enviar link | mesma tela com callout "enviado" | Voltar para entrar |
| Criar nova senha | Redefinir senha | Entrar (autenticado ou com aviso) | — |
| Confirmar e-mail | Reenviar e-mail | mesma tela com callout "reenviado" | Sair |

### Após erro recuperável
- Credencial inválida (entrar) → callout de erro **acima do formulário**, genérico, **sem dizer qual
  campo falhou** (CA-3). Foco vai para o callout (leitor de tela anuncia via `role="alert"`).
- Erro de campo (criar conta / nova senha) → mensagem **associada ao campo** (`aria-describedby`), nunca
  só cor.

## 3. Layout

O ritmo de superfície da marca (`pattern.surface-rhythm`): página **sage** (`canvas-soft`) → **card
branco** (`canvas`). O acento verde só no **CTA primário**. O logo do Laravel some; entra o **lockup
Quantah** (marca + wordmark). Detalhes de marca e hierarquia no **DDR-004**.

### Mobile (≥360px)

Wordmark + tagline no topo (sobre o sage), formulário num card branco único. CTA primário full-width.

```
+----------------------------------+
|  [◧] Quantah                     |   ← lockup (marca verde + wordmark 900)
|  Cada nota conta.                |   ← tagline body-md
|                                  |
|  +----------------------------+  |   ← card.content (branco, raio xl)
|  |  Entrar                    |  |   h1 display (900)
|  |  Que bom te ver de novo.   |  |   subtítulo body-sm
|  |  [G  Entrar com Google  ⌛]|  |   placeholder desabilitado ("Em breve")
|  |  ---------- ou ----------  |  |
|  |  E-mail   [____________]   |  |
|  |  Senha    [____________]   |  |
|  |  ☐ Manter conectado  Esq.  |  |
|  |  [       Entrar        ]   |  |   button.primary (verde), full-width, ≥52px
|  |  Ainda não tem conta? Criar|  |
|  +----------------------------+  |
+----------------------------------+
```

- Componentes do DS: `card.content`, `input.text`, `button.primary`, `button.tertiary` (variante do
  placeholder Google), `snackbar`/callout inline, `badge.positive` (pill "Em breve").
- Alvo de toque ≥48px (inputs/botões a 52px). Card com padding `xl`.

### Desktop (≥1024px)

Split 50/50: **painel de marca escuro** à esquerda (`card.feature-dark` mood — ink + verde), formulário
à direita num card branco centralizado (largura máx ~380px, sem esticar).

```
+---------------------------+----------------------------+
|  [◧] Quantah              |                            |
|                           |   Entrar                   |
|                           |   Que bom te ver de novo.  |
|   Cada nota               |   [G Entrar com Google ⌛] |
|   conta.                  |   -------- ou --------      |
|   (headline verde 900)    |   E-mail  [____________]   |
|   Transforme os cupons…   |   Senha   [____________]   |
|                           |   ☐ Manter conectado  Esq. |
|   Do cupom ao insight.    |   [       Entrar        ]  |
|   (ink, texto verde)      |   Ainda não tem conta?…    |
+---------------------------+----------------------------+
```

- A esquerda é continuidade, não decoração: reforça marca e proposta de valor na primeira impressão
  (métrica primária do épico). Headline `display-xl` verde sobre `ink`; sub `body-md` em cinza claro.
- A direita reusa **o mesmo card e os mesmos campos** do mobile — paridade real, não "mobile esticado".

### Tablet (768px)
Sem comportamento próprio: abaixo de `lg` usa o layout mobile (card centralizado sobre sage, sem o painel
lateral). Omitido de propósito.

## 4. Estados

### 4.1. Caminho feliz (preenchido)
Coberto pelos sketches. Microcopy na §5. CTA primário verde único por tela.

### 4.2. Loading
Sem fetch inicial de dados — as telas de acesso abrem prontas (nenhum skeleton necessário). O **loading
é do submit**: ao enviar, o `button.primary` troca o label por **spinner inline + "Entrar…"**, fica
`aria-busy` e bloqueia novo toque (evita duplo submit). Estado `carregando` na tela **Entrar** do
protótipo.

### 4.3. Vazio
Não se aplica (formulário, não listagem). O "vazio" conceitual da conta nova é a tela **Confirmar
e-mail**, que já instrui o próximo passo (abrir o link no e-mail) — nunca "sem dados".

### 4.4. Erro
- **Credencial inválida (entrar)** — callout `err` acima do form, `role="alert"`:
  "E-mail ou senha incorretos." **Sem vazar qual campo** (CA-3). Estado `erro` da tela Entrar.
- **E-mail já cadastrado (criar conta)** — erro no campo E-mail: "Este e-mail já tem conta. Tente
  entrar." Estado `erro` da tela Criar conta.
- **Senhas diferentes (nova senha)** — erro no campo Confirmar: "As senhas não são iguais." Estado
  `erro` da tela Nova senha.
- **Senha fraca (criar conta / nova senha)** — helper vira erro no campo Senha: "Use pelo menos 8
  caracteres." (validação canônica é do servidor).
- **Erro de rede / inesperado no submit** — `snackbar` de erro com ação "Tentar de novo" (padrão
  `pattern.error`); não trava a tela. Conteúdo técnico nunca aparece ao usuário.

### 4.5. Sem permissão
Não se aplica — estas são as telas públicas de acesso. A segmentação de áreas/guardas é da STORY-023.

### 4.6. Parcial / degradado
Não há carga parcial. Se o provedor de e-mail de reset falhar, o back responde igual ("se houver conta…")
para não vazar existência de conta — mensagem idêntica ao sucesso (§5).

### 4.7. Primeira vez vs recorrente
- **Placeholder do Google (CA-5)**: botão **desabilitado** com selo "Em breve" (`badge.positive` em tom
  neutro) até a STORY-022. Reserva o espaço, não quebra layout. Há também o estado **`sem-google`** (flag
  desliga o botão e o divisor "ou") para o caso de a flag ocultar em vez de desabilitar — decisão de
  rollout do Programador; ambos previstos.
- **Confirmar e-mail — reenviado**: após reenviar, callout `ok` confirma o novo envio.

## 5. Microcopy completo (pt-BR)

Vocabulário: `docs/skills/po/references/glossary.md`. Tom: `voice-and-tone.md` (direto, sem emoji, sem
culpar o usuário). Deve entrar via i18n (STORY-020) — chaves em `lang/pt_BR`.

### Comuns
| Lugar | Texto |
|---|---|
| Wordmark | Quantah |
| Tagline (mobile) | Cada nota conta. |
| Headline hero (desktop) | Cada nota conta. |
| Sub hero (desktop) | Transforme os cupons das suas compras em dinheiro na carteira — e ajude a construir a maior base de preços do varejo brasileiro. |
| Rodapé hero (desktop) | Do cupom ao insight. |
| Botão Google (entrar) | Entrar com Google |
| Botão Google (criar conta) | Criar conta com Google |
| Selo do placeholder Google | Em breve |
| Divisor | ou |

### Entrar
| Lugar | Texto |
|---|---|
| Título | Entrar |
| Subtítulo | Que bom te ver de novo. |
| Label E-mail | E-mail |
| Placeholder E-mail | voce@exemplo.com |
| Label Senha | Senha |
| Placeholder Senha | •••••••• |
| Checkbox | Manter conectado |
| Link | Esqueci minha senha |
| CTA primário | Entrar |
| CTA loading | Entrar… |
| Erro credencial (global) | E-mail ou senha incorretos. |
| Rodapé | Ainda não tem conta? **Criar conta** |

### Criar conta
| Lugar | Texto |
|---|---|
| Título | Criar conta |
| Subtítulo | Leva menos de um minuto. Cada nota vira dinheiro na sua carteira. |
| Label Nome | Nome |
| Placeholder Nome | Como quer ser chamado |
| Label E-mail | E-mail |
| Placeholder E-mail | voce@exemplo.com |
| Label Senha | Senha |
| Placeholder Senha | Crie uma senha |
| Helper Senha | Use pelo menos 8 caracteres. |
| Label Confirmar senha | Confirmar senha |
| Placeholder Confirmar senha | Repita a senha |
| CTA primário | Criar conta |
| Erro E-mail (em uso) | Este e-mail já tem conta. Tente entrar. |
| Erro Senha (fraca) | Use pelo menos 8 caracteres. |
| Rodapé | Já tem conta? **Entrar** |

### Redefinir senha (esqueci)
| Lugar | Texto |
|---|---|
| Título | Redefinir senha |
| Subtítulo | Informe seu e-mail. Enviamos um link para você criar uma nova senha. |
| Label E-mail | E-mail |
| CTA primário | Enviar link |
| Sucesso (callout) | Se houver uma conta com esse e-mail, enviamos um link para redefinir a senha. |
| Link | Voltar para entrar |

### Criar nova senha (reset)
| Lugar | Texto |
|---|---|
| Título | Criar nova senha |
| Subtítulo | Escolha uma nova senha para a sua conta. |
| Label E-mail (readonly) | E-mail |
| Label Nova senha | Nova senha |
| Helper Nova senha | Use pelo menos 8 caracteres. |
| Label Confirmar | Confirmar nova senha |
| CTA primário | Redefinir senha |
| Erro Confirmar (diferente) | As senhas não são iguais. |

### Confirmar e-mail (verify)
| Lugar | Texto |
|---|---|
| Título | Confirme seu e-mail |
| Corpo | Enviamos um link de confirmação para **{e-mail}**. Abra-o para ativar sua conta e começar a enviar notas. |
| CTA primário | Reenviar e-mail |
| Sucesso (callout) | Enviamos um novo link de confirmação para o seu e-mail. |
| Link | Sair |

> **Nota de conteúdo (PO valida):** as mensagens de recuperação/confirmação afetam privacidade (não
> vazar existência de conta). Proponho o texto; o PO valida antes de produção (fronteira fuzzy da skill).

## 6. Acessibilidade (notas desta tela)

- **Ordem de foco:** lockup (link "/") → [Google, se presente] → campos na ordem visual → checkbox →
  "Esqueci minha senha" → CTA primário → rodapé. No desktop, o painel de marca (esquerda) é decorativo
  informativo — vem **depois** do form na ordem de leitura assistiva (form primeiro).
- **Foco inicial:** primeiro campo editável (E-mail; Nome em criar conta; Nova senha em reset).
- **Placeholder Google desabilitado:** `aria-disabled="true"`, `tabindex="-1"` (fora da ordem de foco
  enquanto inativo) — não confunde o leitor de tela com um botão que não faz nada.
- **Erro global (credencial):** container `role="alert"`, foco movido para ele ao aparecer — anunciado.
- **Erro de campo:** `aria-invalid` + `aria-describedby` apontando para a mensagem textual; ícone + texto,
  nunca só borda vermelha.
- **Contraste (WCAG AA):** texto `ink`/`body` sobre `canvas`/`canvas-soft` ✅; `on-primary` sobre
  `primary` no CTA ✅ (nunca branco sobre verde); headline `primary` sobre `ink` no hero ✅; erro
  `negative-darkest` sobre `canvas` ✅. Selo "Em breve": `warning-content` sobre creme ✅.
- **Alvos de toque ≥48px:** inputs e botões a 52px ✅.
- **Foco visível:** outline `primary` 3px nos inputs; outline `ink` no CTA verde (o verde some sobre o
  verde) — substitui, não remove, o indicador padrão.
- **Logo do Google:** logo multicolor oficial é **exceção justificada** ao DS (marca de terceiro, não
  acento Quantah) — ver §8.

## 7. Identificadores estáveis sugeridos

| Elemento | Identificador lógico |
|---|---|
| Botão Google (placeholder) | `acesso-google-btn` |
| Campo (genérico) | `acesso-campo-<id>` (`email`, `senha`, `nome`, `senha_conf`) |
| Erro de campo | `acesso-erro-<id>` |
| Erro global de credencial | `acesso-erro-credencial` |
| CTA Entrar | `acesso-entrar-submit` |
| CTA Criar conta | `acesso-criar-submit` |
| CTA Enviar link (reset) | `acesso-reset-submit` |
| Callout reset enviado | `acesso-reset-enviado` |
| CTA Redefinir senha | `acesso-nova-senha-submit` |
| CTA Reenviar e-mail | `acesso-verif-reenviar` |
| Callout verificação reenviada | `acesso-verif-reenviado` |
| Link Criar conta / Entrar | `acesso-ir-criar-conta` / `acesso-ir-entrar` |

> O Programador materializa como `data-testid` da stack (Inertia+React). Ancoram o E2E do CA-6.

## 8. Exceções ao Design System

| O que diverge | Por quê | Vira DDR? |
|---|---|---|
| Botão Google usa o **logo multicolor oficial do Google** (vermelho/azul/amarelo/verde) | Exigência de marca do provedor (Google Sign-In branding). Não é acento Quantah. | Não — restrição externa; documentada aqui e no DDR-004 |
| Painel de marca **escuro** (`ink`) ocupando metade da tela no desktop | É o "momento de marca" (`card.feature-dark`) na primeira impressão — uso pontual e proposital, não decoração | Sim — faz parte do **DDR-004** (padrão de acesso) |
| Lockup/wordmark Quantah (marca nova) no lugar do `ApplicationLogo` do Laravel | Não existia identidade de acesso; o logo do Laravel era scaffolding | Sim — **DDR-004** propõe o tratamento de marca |

## 9. Protótipo HTML fiel

- **Localização:** `STORY-021-login-cadastro-coletador/index.html` (sibling deste spec).
- **Cobertura:** 5 telas (entrar, criar-conta, esqueci-senha, nova-senha, confirmar-email); estados
  `padrão / erro / carregando / sem-google / enviado / reenviado` conforme a tela; viewports mobile e
  desktop. Seletores de tela/estado/viewport no topo (query string).
- **Fidelidade:** tokens reais do DS; microcopy = §5 palavra por palavra; identificadores da §7 como
  `data-testid`. HTML/CSS/JS vanilla, sem rede, abre clicando no arquivo.

### Checklist antes de `ready`
- [x] `index.html` abre sem erro em navegador moderno.
- [x] Todos os estados da §4 acessíveis pelo protótipo.
- [x] Mobile e desktop navegáveis.
- [x] Microcopy do protótipo = §5.
- [x] Identificadores da §7 presentes no HTML.
- [x] Caminho feliz percorrível (criar conta → confirmar e-mail; entrar → loading; esqueci → enviado).
- [x] Tokens reais do DS.
- [x] **Protótipo apresentado ao humano (Alexandro) e validado em 2026-07-04** ("aprovado").
- [x] **DDR-004 `accepted`** (2026-07-04, Alexandro).

## 10. Dependências e premissas

- **Back:** rotas do Breeze já existem (login, register, forgot/reset, verify-email); STORY-020 entregou
  i18n. Sem contrato novo de API — só troca de apresentação + chaves pt-BR.
- **Google:** apenas o **lugar** do botão aqui; OAuth é da STORY-022. O placeholder não dispara nada.
- **DDR-004** (padrão de acesso + marca) precisa ser `accepted` para este spec sair de `draft`.
- **Destino pós-login:** o atual do Breeze até a home-hub (EPIC-006). Fora de escopo aqui.

## 11. Histórico de mudanças

| Data | Mudança | Quem | Motivo |
|---|---|---|---|
| 2026-07-04 | criação: spec + protótipo (5 telas, todos os estados, mobile/desktop) | Designer | STORY-021 (`requires_design`) |
| 2026-07-04 | DDR-004 proposto (padrão de acesso + marca) | Designer | tratamento de marca e hierarquia Google/e-mail são decisão durável |
| 2026-07-04 | validação humana + `ready` | Alexandro | protótipo aprovado ("aprovado"); DDR-004 accepted; spec liberado para implementação |
