---
id: SCREEN-STORY-009-captura-qr-confirmacao
story: STORY-009-captura-qr-confirmacao
epic: EPIC-002-coleta-de-cupom
status: ready                # draft | ready | in_implementation | shipped | superseded
created_at: 2026-07-02
updated_at: 2026-07-02
owner_designer: Designer (Alexandro / Claude)
related_ddrs: [DDR-001, DDR-002]
ds_components_used: [button, card, snackbar, badge, input.text, icon]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-009-captura-qr-confirmacao/index.html
prototype_last_validated_at: 2026-07-02
---

# Spec de tela — Capturar cupom

> Referência: estória `STORY-009-captura-qr-confirmacao` (CAs e contexto vêm de lá — **não duplique**).
> Princípios que dirigiram decisões: **#1 fricção mínima** (a coleta é o gesto central; cada passo a
> mais derruba contribuição) e **#7 todos os estados** (câmera negada, inválido, duplicado, sucesso).

## 1. Objetivo da tela

Capturar um cupom da NFC-e em segundos: **escanear o QR pela câmera** ou **colar/compartilhar o link**,
e receber **confirmação clara** de que a nota foi recebida. Uma única tarefa — sem cadastro de dados,
sem escolha de categoria; a validação e a contagem acontecem depois (STORY-010/012).

## 2. Fluxo

### Entrada

- O Colaborador chega pela navegação principal (ação "Capturar") **ou** por um **link compartilhado**
  do leitor de QR nativo do celular, que abre o app em `/coletar?p=<link>` com a entrada pré-preenchida.
- Nada precisa ser verdade antes (tela aberta nesta onda; a atribuição ao Colaborador/cashback é EPIC-003).

### Ações possíveis na tela

- **Primária:** escanear o QR (câmera) → detecta → envia automaticamente.
- **Alternativa:** colar o link/chave no campo → "Enviar cupom".
- **Secundária:** "Cancelar" (fecha a câmera e volta à escolha).
- **Saída após sucesso:** "Enviar outro cupom" (recomeça limpo).

### Saída

- **Sucesso (capturado):** confirmação "Cupom capturado!" + selo "Cupom recebido".
- **Duplicado:** confirmação suave "Você já tinha enviado esse cupom" (não é erro — é informação).
- **Erro recuperável (inválido / fora de SP / não-NFC-e):** mensagem **no campo**, sem sair da tela.
- **Câmera negada/indisponível:** aviso + degrada para o caminho de colar link (não trava).

## 3. Layout

### Mobile (≥360px) — viewport primário

```
+--------------------------------+
|            [ QR ]              |  ← ícone + título
|        Capturar cupom          |
|  Escaneie o QR ou cole o link  |
+--------------------------------+
| [  Escanear QR Code   ]  (CTA) |  ← button.primary, min 48px
|                                |
|  Link ou chave da nota  [____] |  ← input.text (float label)
|  [     Enviar cupom     ]      |  ← button (primary quando há entrada)
+--------------------------------+
```

Em modo câmera, a área principal vira o vídeo quadrado (`aspect-square`) com moldura de mira verde,
o texto-guia embaixo e o botão "Cancelar" (secondary).

- Componentes do DS: `card` (content, `shadow-elev-2`), `button` (primary/secondary), `input.text`,
  `snackbar` (warning/danger), `badge` (positive/info), `icon` (QR, receipt).
- Card único centralizado, `max-w-md`, respiração `gap-xl`; página em `canvas-soft` (mood da marca),
  card em `canvas` (elevação por contraste — pattern surface-rhythm). Alvo de toque ≥48px (`min-h-3xl`).

### Desktop (≥1024px)

Mesma composição, card centralizado `max-w-md` (não estica — a tarefa é curta e focada; largura extra
não agrega e prejudicaria a leitura). O gesto é idêntico; muda só o enquadramento na tela maior.

## 4. Estados

### 4.1. Caminho feliz (captura)

Escolha → (câmera detecta **ou** colar + enviar) → **confirmação**. Microcopy na seção 5.

### 4.2. Loading

Sem "primeiro fetch": a tela abre pronta. O único loading é o **envio** — o botão "Enviar cupom" entra
em `loading` (spinner sobre o rótulo, `aria-busy`), bloqueando reenvio. Câmera abrindo mostra o vídeo
já visível com a moldura (sem spinner em tela vazia).

### 4.3. Vazio

Não se aplica no sentido clássico (sem lista). O estado inicial **já é** o convite à ação (CTA de
escanear + campo de colar) — nunca uma tela morta.

### 4.4. Erro

- **Dado inválido (chave malformada / QR que não é NFC-e):** mensagem **no campo** (`input.text` em
  estado de erro, texto associado por `aria-describedby`). Não é banner global.
- **Fora de escopo (nota não-SP):** mesma ancoragem no campo — explica o limite da onda.
- **Não é NFC-e (modelo ≠ 65):** idem.
- **Câmera negada/indisponível:** `snackbar` warning no topo do card + a tela **cai para o modo colar**
  (o usuário segue pelo campo). Não é beco sem saída.
- **Erro inesperado do servidor:** o padrão do Inertia (página de erro) — fora do escopo de microcopy
  desta tela; a tela não inventa estado.

### 4.5. Sem permissão

Não há gate de autorização nesta onda (tela aberta). Quando a Carteira entrar (EPIC-003) e exigir login,
o redirect de auth é responsabilidade daquela estória — não desta tela.

### 4.6. Parcial / degradado

Sem câmera (browser sem suporte / permissão negada) = **degradação graciosa** para colar link — o
caminho de valor continua inteiro. É o cenário 4.4 "câmera".

### 4.7. Primeira vez vs recorrente

Sem onboarding dedicado nesta tela — a fricção mínima exige que a primeira captura seja igual à décima.

## 5. Microcopy completo

| Lugar | Texto |
|---|---|
| Título da tela | Capturar cupom |
| Subtítulo | Escaneie o QR Code da sua nota ou cole o link. |
| CTA escanear | Escanear QR Code |
| Guia (câmera ativa) | Aponte a câmera para o QR Code da NFC-e. |
| CTA cancelar câmera | Cancelar |
| Label do campo | Link ou chave da nota |
| CTA enviar | Enviar cupom |
| Erro do campo (chave malformada) | Esse código não parece um QR de NFC-e. Confira e tente de novo. |
| Erro do campo (não-SP) | Por enquanto o Quantah só coleta notas de São Paulo. |
| Erro do campo (não-NFC-e) | Esse documento não é uma NFC-e. |
| Aviso (câmera negada) | Não conseguimos acessar a câmera. Cole o link da nota. |
| Aviso (sem câmera) | Nenhuma câmera encontrada. Cole o link da nota. |
| Aviso (sem suporte) | Seu navegador não abriu a câmera aqui. Cole o link da nota. |
| Sucesso (título) | Cupom capturado! |
| Sucesso (selo) | Cupom recebido |
| Sucesso (corpo) | Recebemos sua nota. Vamos validar e confirmar em instantes. |
| Duplicado (título) | Você já tinha enviado esse cupom |
| Duplicado (selo) | Cada nota conta uma vez |
| Duplicado (corpo) | Esse cupom já está na sua contribuição. Cada nota conta uma vez só. |
| CTA após confirmação | Enviar outro cupom |

Vocabulário conforme `docs/skills/po/references/glossary.md` (Cupom, Colaborador, NFC-e, chave de
acesso). Tom: direto, acolhedor, sem jargão — alinhado a "Cada nota conta" (visao §11.2).

## 6. Acessibilidade (notas específicas)

- **Foco inicial:** navegação normal (a tela abre por full visit); o `h1` inicia a ordem de leitura.
- **Ordem de foco:** título → CTA escanear → campo → enviar. Em modo câmera: vídeo → guia → cancelar.
- **Ícone-ação:** o vídeo tem `aria-label` ("Câmera para escanear o QR Code da nota"); o CTA de scan
  tem texto (ícone + rótulo), não ícone solto.
- **Erro no campo:** mensagem textual vinculada (`aria-describedby` + `role="alert"` — via componente
  `Field` do DS), não só borda vermelha.
- **Live region:** o aviso de câmera é `snackbar` (`role="status" aria-live="polite"`); a confirmação
  de sucesso é anunciada (`role="status"`).
- **Contraste (WCAG AA):** todos os tokens usados são do DS (ink/body/positive/warning sobre
  canvas/canvas-soft) — ✅ herda a validação de contraste do EPIC-001.
- **Alvo de toque ≥48px:** ✅ todos os botões usam `min-h-3xl`.

## 7. Identificadores estáveis sugeridos

| Elemento | Identificador lógico |
|---|---|
| Raiz da tela | `screen-captura` |
| Título | `screen-captura-title` |
| CTA escanear | `screen-captura-scan-btn` |
| Vídeo da câmera | `screen-captura-video` |
| Cancelar câmera | `screen-captura-cancel-btn` |
| Campo colar | `screen-captura-paste-field` |
| Erro do campo | `screen-captura-error-entrada` |
| Enviar | `screen-captura-submit-btn` |
| Aviso de câmera | `screen-captura-camera-error` |
| Confirmação (sucesso/duplicado) | `screen-captura-success` |
| Selo | `screen-captura-badge` |
| Enviar outro | `screen-captura-another-btn` |

## 8. Exceções ao Design System

Nenhuma. A tela é 100% composta com componentes e tokens existentes do DS (EPIC-001). O único elemento
novo é o **ícone de QR** (`QrIcon`), acrescido ao set de ícones do DS seguindo o mesmo wrapper `Svg`
(currentColor, sem valor cru) — extensão do set, não exceção de token.

## 9. Protótipo HTML fiel (validação humana)

- **Localização:** `STORY-009-captura-qr-confirmacao/index.html` (sibling deste spec).
- **Cobertura:** estados alcançáveis por `?state=` e chips no topo — `idle`, `scan`, `camera-error`,
  `field-error`, `success`, `duplicate`; viewports mobile e desktop (o card não muda de forma).
- **Fidelidade:** tokens reais do DS (cores/tipografia/raio), microcopy = seção 5, ids da seção 7.
- **Restrições:** HTML/CSS vanilla, sem rede; a câmera é simulada por um placeholder (protótipo de
  validação, não código de produção).
- **Validação humana:** ✅ aprovada por Alexandro em 2026-07-02 (testada em homologação, no celular,
  por HTTPS — `https://quantah-homolog.34.39.229.117.sslip.io/coletar`).

## 10. Dependências e premissas

- **Contrato de ingestão:** `ADR-001` (a captura chama `IngestaoCupomService::capturar()`; formato da
  chave em `ADR-003`). A tela **não** valida/deduplica — só captura e faz handoff (CA-5).
- **Decode do QR:** biblioteca client-side (`@zxing/browser`, import dinâmico) — decisão do Programador,
  registrada em `IDR-003`. Câmera exige HTTPS (homologação já é TLS).
- **Premissa do back:** o handoff retorna `capturado | duplicado | rejeitado(motivo)`; a validação
  SEFAZ e a persistência canônica são STORY-010 (o cupom fica `pendente` até lá).

## 11. Histórico de mudanças

| Data | Mudança | Quem | Motivo |
|---|---|---|---|
| 2026-07-02 | criação (spec + estados + microcopy) | Designer | decomposição do EPIC-002; sync com o contrato ADR-001 |
| 2026-07-02 | protótipo v1 (mobile/desktop, estados por `?state=`) | Designer | cobrir seção 4; pendente validação humana |
| 2026-07-02 | validação humana | Alexandro | tela testada em homologação (celular, HTTPS) — **aprovada** |
