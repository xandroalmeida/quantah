---
id: DDR-004
title: Padrão de acesso — marca Quantah no lugar do Laravel, split-hero no desktop, hierarquia Google vs. e-mail/senha
status: accepted
created_at: 2026-07-04
decided_at: 2026-07-04
approved_by: Alexandro
supersedes: ~
superseded_by: ~
related_ddrs: [DDR-001, DDR-002]
related_adrs: [ADR-010]
related_pdrs: [PDR-001, PDR-003]
scope: acesso (telas de autenticação — transversal ao EPIC-004)
affects_screens: [SCREEN-STORY-021-login-cadastro-coletador]
---

# DDR-004 — Padrão de acesso: marca Quantah, split-hero e hierarquia Google vs. e-mail/senha

## Contexto

A porta de entrada do Coletador é hoje o scaffolding do **Laravel Breeze**: card cinza genérico com o
**logo do Laravel** (`ApplicationLogo`) e textos em inglês. A STORY-021 substitui essa apresentação por
telas no padrão visual do DS, em pt-BR, mantendo o fluxo de e-mail/senha e **reservando o lugar do botão
"Entrar com Google"** (STORY-022). Antes de o Programador cristalizar as telas, três decisões são
**duráveis** (valem para todas as telas de acesso e para as STORY-022/023) e caras de reverter:

1. **Identidade de marca no acesso** — o que entra no lugar do logo do Laravel.
2. **Layout de acesso no desktop** — como o espaço extra é usado sem virar "mobile esticado".
3. **Hierarquia entre Google e e-mail/senha** — quem é o CTA verde, dado que o DS permite **um só**
   acento primário por contexto e o Google exige seu próprio branding.

Lido: STORY-021 (CAs, fora de escopo), EPIC-004 (outcome/métrica), **ADR-010** (OAuth Google + contas +
áreas; "Google é o principal redutor de fricção"), **DDR-002** (marca é base de sistema, não clone do
Wise), **DDR-001** (Inter 900), DS (`tokens.md`, `components.md`, `patterns.md`, `voice-and-tone.md`),
PDR-001 (DS) e PDR-003 (escopo da onda).

## Forças (drivers)

- **Primeira impressão de produto = métrica primária do épico** (alto): a tela precisa **parecer o
  Quantah** — o logo do Laravel é o sintoma que a estória existe para curar.
- **Fricção mínima para o Colaborador Casual** (alto, ADR-010 F1): o Google é o caminho de menor atrito;
  a hierarquia visual precisa deixá-lo evidente **quando existir** — sem, ainda assim, roubar o único
  acento verde do DS.
- **Princípio #4 — padronização > criatividade** (alto): regra de ouro do DS = **um** CTA `primary`
  verde por contexto; o Google tem branding próprio (não pode ser verde).
- **Princípio #3 — tom profissional** (alto): marca sóbria, sem mascote/gradiente festivo; wordmark
  tipográfico Inter 900 (DDR-001), acento verde parcimonioso.
- **Princípio #2 — mobile-first com paridade** (alto): o Coletador é quase só mobile; o desktop é
  continuidade, não a estrela.
- **Restrição externa** (médio): Google Sign-In exige o logo multicolor oficial — exceção inevitável ao
  DS de acento.
- **Extensibilidade** (médio, ADR-010 F5): o padrão precisa acomodar um 2º provedor social e a área B2B
  sem retrabalho.

## Opções consideradas

### Opção A — Lockup tipográfico + split-hero escuro no desktop; e-mail/senha é o CTA verde, Google acima como botão neutro *(proposta)*

Marca = **lockup**: um *tile* verde (raio `xl`) com glifo de **nota fiscal** + wordmark "Quantah" (Inter
900). No mobile, lockup sobre o sage + card branco. No desktop, **split 50/50**: painel `ink` à esquerda
("momento de marca" `card.feature-dark` — headline verde "Cada nota conta.") e card do formulário à
direita. Ordem vertical do card: **Google (neutro, branco/borda ink) → divisor "ou" → e-mail/senha →
`button.primary` verde "Entrar"**. Enquanto o Google não existe, ele aparece **desabilitado** com selo
"Em breve" (ou oculto por flag, colapsando o divisor).

```
mobile (≥360px)                     desktop (≥1024px)
+--------------------------+        +------------------+-------------------+
| [◧] Quantah              |        | [◧] Quantah      |  Entrar           |
| Cada nota conta.         |        |                  |  [G  Google   ⌛] |
| +----------------------+ |        |  Cada nota       |  ----- ou -----   |
| | Entrar               | |        |  conta.          |  E-mail [_______] |
| | [G Google        ⌛] | |        |  (verde 900)     |  Senha  [_______] |
| | ------- ou -------   | |        |  Transforme os…  |  [    Entrar    ] |
| | E-mail [__________]  | |        |  Do cupom ao     |  Criar conta…     |
| | Senha  [__________]  | |        |  insight.        |                   |
| | [     Entrar      ]  | |        +------------------+-------------------+
| +----------------------+ |
+--------------------------+
```

- **Prós:** marca forte na primeira impressão (métrica do épico); respeita "um CTA verde" (Google é
  neutro, não compete); Google fica **acima** = evidente quando existir (fricção mínima, ADR-010);
  desktop usa o espaço com propósito (não estica o mobile); reusa `card.feature-dark`, `card.content`,
  `button.primary/tertiary` sem componente novo; wordmark tipográfico casa com DDR-001/002.
- **Contras:** painel escuro grande é o uso mais "marqueteiro" do DS — precisa de disciplina para não
  virar decoração; exige a exceção do logo Google (inevitável em qualquer opção com Google).

### Opção B — Card único centrado, marca só no topo, sem painel de hero (mesmo no desktop)

Mantém o card centralizado do Breeze em todos os viewports; troca só o logo pelo lockup e o texto para
pt-BR. Sem split no desktop.

```
desktop (≥1024px)
+---------------------------------+
|            [◧] Quantah          |
|         +------------------+    |
|         |  Entrar          |    |
|         |  [G Google   ⌛] |    |
|         |  E-mail [______] |    |
|         |  Senha  [______] |    |
|         |  [   Entrar    ] |    |
|         +------------------+    |
+---------------------------------+
```

- **Prós:** mais simples de implementar; menor risco de "excesso de marca"; paridade trivial.
- **Contras:** desktop fica com **80% de vazio** (sinal de alerta do princípio #2); primeira impressão
  fraca — não vende o produto na tela que a métrica do épico mede; desperdiça a chance de marca.

### Opção C — Google como botão verde primário (acento no social)

Inverte a hierarquia: o **Google** vira o `button.primary` verde; e-mail/senha fica secundário/colapsado.

- **Prós:** empurra o caminho de menor fricção com força máxima.
- **Contras:** **viola a regra de ouro do DS** (Google não pode ser verde — tem branding próprio; e verde
  não é "sucesso/social"); confunde a marca de terceiro com o acento Quantah; ilegível/estranho com o
  logo multicolor dentro de um botão verde. Rejeitada.

### Status quo — manter o Breeze cinza com logo do Laravel

- **Contras:** é exatamente o problema que a estória existe para resolver. Rejeitada.

## Avaliação contra os princípios

| Princípio | A (proposta) | B | C | Status quo |
|---|---|---|---|---|
| 1. Simplicidade radical | ✅ uma ação/tela | ✅ | ⚠️ hierarquia confusa | ✅ |
| 2. Mobile-first com paridade | ✅ desktop com propósito | ❌ desktop vazio | ✅ | ⚠️ |
| 3. Tom profissional | ✅ sóbrio, marca disciplinada | ✅ | ❌ verde de terceiro | ✅ |
| 4. Padronização > criatividade | ✅ um CTA verde; reusa DS | ✅ | ❌ quebra regra de ouro | ✅ |
| 5. Acessibilidade | ✅ (ver spec §6) | ✅ | ⚠️ contraste logo/verde | ⚠️ inglês |
| 6. Performance percebida | ✅ sem fetch; loading no submit | ✅ | ✅ | ✅ |
| 7. Estados além do feliz | ✅ erro/loading/placeholder/reenvio | ✅ | ✅ | ❌ |

## Decisão

> **Adotada (proposta):** **Opção A**.

É a única que atende as duas forças de maior peso ao mesmo tempo — **marca forte na primeira impressão**
(métrica do épico) e **Google evidente sem quebrar a regra de ouro do DS** (um acento verde). B fracassa
no desktop vazio e na primeira impressão; C viola a padronização do DS. A é 100% construída com
componentes/tokens existentes (`card.feature-dark`, `card.content`, `button.primary/tertiary`,
`input.text`), sem débito de componente novo.

## Consequências

### Positivas
- Porta de entrada que **parece o Quantah** em mobile e desktop, em pt-BR.
- Hierarquia pronta para a STORY-022: o Google só "acende" no lugar já reservado.
- Sem componente novo no DS — só composição e um token de marca (o lockup).

### Negativas / trade-offs assumidos
- O painel `ink` grande é o uso mais chamativo do DS: assumimos o risco de "marca demais" e mitigamos com
  disciplina (headline curta, sem ilustração, sem 2º acento).
- Assumimos a **exceção do logo Google** (multicolor) — inevitável com Google Sign-In.

### Impacto no Design System
- **Novo token de marca:** `brand.lockup` (tile verde raio `xl` + glifo de nota + wordmark Inter 900) e
  `brand.mark` (só o tile+glifo, p/ favicon/avatar). A registrar em `design/system/` (componentes/patterns)
  **nesta operação** se o DDR for aceito.
- **Novo padrão:** `pattern.auth` (split-hero escuro no desktop, card único no mobile, ordem
  Google→ou→e-mail/senha→CTA). Documentar em `patterns.md` ao aceitar.
- Nenhum componente de input/botão novo — só reuso.

### Impacto em telas existentes
- `SCREEN-STORY-021-login-cadastro-coletador` (este spec) — já desenhado sobre a Opção A.
- Futuro: STORY-022 (Google real) e STORY-023 (áreas) herdam o padrão; sem revisão retroativa de telas já
  entregues (carteira/backoffice não usam o padrão de acesso).

## Implementação sugerida (notas para o Programador)

- Substituir `ApplicationLogo` (logo Laravel) pelo lockup Quantah; refatorar `GuestLayout` para o
  split-hero responsivo (Tailwind `lg:grid-cols-2`; painel `bg-ink` só em `lg:`).
- Placeholder Google: `button.tertiary` desabilitado com selo; `aria-disabled`, `tabindex="-1"`; flag de
  rollout controla desabilitado × oculto (ambos previstos no protótipo).
- Identificadores lógicos no spec §7 → `data-testid`. Ancoram o E2E do CA-6.
- Microcopy via i18n (STORY-020), chaves em `lang/pt_BR`.

## Critérios para revisitar

- Quando a STORY-022 entregar o Google real — reavaliar se a ordem/estilo do botão se confirma no uso.
- Se um 2º provedor social entrar — o bloco "Google → ou" precisa generalizar para N provedores.
- Se dados de uso mostrarem o painel de hero prejudicando conversão no desktop — reduzir o peso do escuro.
- Se a marca ganhar um logotipo definitivo (asset de brand) — o lockup tipográfico pode ser substituído.

## Aprovação humana

| Campo | Valor |
|---|---|
| Apresentado em | 2026-07-04 |
| Aprovado por | Alexandro |
| Data da aprovação | 2026-07-04 |
| Observações do aprovador | Aprovado ("aprovado") sobre o protótipo `SCREEN-STORY-021` — Opção A confirmada. |

> Aprovado. Padrão de acesso vigente para EPIC-004 (STORY-021/022/023).
