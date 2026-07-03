---
idr_id: IDR-003
slug: zxing-browser-decode-qr
title: Decode do QR da NFC-e no cliente com @zxing/browser, por import dinâmico
status: accepted
decided_at: 2026-07-02
decided_by: programador
owner_agent: claude-programador
related_story: STORY-009
related_adrs: [ADR-000, ADR-001]
related_idrs: []
supersedes: null
superseded_by: null
created_at: 2026-07-02
updated_at: 2026-07-02
---

# IDR-003 — Decode do QR da NFC-e no cliente com @zxing/browser

## Contexto

A STORY-009 (captura) precisa **ler o QR Code da NFC-e pela câmera** (CA-1), além de aceitar o link
colado/compartilhado (CA-2). A escolha da stack de FE já está fixada (ADR-000: Inertia + React); a
biblioteca concreta de decode de QR tem **liberdade dentro do ADR** — logo é decisão do Programador
(IDR), não do Arquiteto. O requisito de produto é forte: o público é consumidor de massa no Brasil,
com fatia grande de **iOS Safari** — a câmera precisa funcionar lá, não só no Chrome/Android.

Opções consideradas: (a) API nativa `BarcodeDetector` — zero dependência, mas **sem suporte no iOS
Safari**, o que deixaria metade do público sem scan; (b) uma lib JS de decode (`@zxing/browser`,
`html5-qrcode`, `jsQR`); (c) só colar/compartilhar, adiando a câmera (não cumpre o CA-1). O usuário
(Alexandro) optou explicitamente pela lib JS para garantir cobertura iOS + Android.

## Decisão

> **Decidi usar `@zxing/browser` para decodificar o QR pela câmera, carregado por _import
> dinâmico_ (`await import('@zxing/browser')`) — fica fora do bundle inicial e só baixa quando o
> usuário abre a câmera. O caminho de colar/compartilhar link (CA-2) não depende da lib. Falha de
> permissão/câmera degrada graciosamente para o campo de colar.**

Encapsulei o scanner em `resources/js/Components/coleta/QrScanner.jsx` (câmera + decode + limpeza da
stream ao desmontar); a página `Pages/Coleta/Captura.jsx` só consome `onDetected`/`onError`. A
validação/normalização da chave é do backend (`IngestaoCupomService`, ADR-001/003) — a lib só
extrai o texto do QR.

## Justificativa

- **Cobertura real do público (o driver que decidiu):** `@zxing/browser` (porte do ZXing) funciona
  em iOS Safari e Android via `getUserMedia`; o `BarcodeDetector` nativo não cobre iOS — inaceitável
  para o gesto central da coleta.
- **Custo controlado (princípio #1/#11):** a lib entra por **import dinâmico** — o chunk (~115 kB
  gzip) não pesa no carregamento inicial da tela; só baixa ao abrir a câmera. Verificado no build:
  vira um chunk separado (`esm-*.js`), não entra no `app-*.js`.
- **Maturidade:** ZXing é a referência de decode de código de barras/QR; `@zxing/browser` é o wrapper
  de browser mantido, sem dependências nativas.
- **Reversibilidade (#7):** o scanner está isolado num componente com contrato `onDetected/onError`;
  trocar a lib (ou adotar `BarcodeDetector` como _fast path_ onde existir) é mudança local, sem tocar
  a página nem o backend.

## Consequências

- **+1 dependência de FE** (`@zxing/browser`) — aceita, isolada e lazy.
- **HTTPS obrigatório** para a câmera (`getUserMedia`) — homologação já é TLS; em local, `localhost`
  é contexto seguro.
- **Testabilidade:** o decode por câmera não roda em headless sem câmera; o E2E (Dusk) cobre o
  caminho de colar link (feliz + erro) e a **degradação graciosa** ao clicar em escanear. O decode em
  si é responsabilidade da lib (não reescrevemos ZXing).
- **Se a fatia iOS/uso justificar**, avaliar um _fast path_ com `BarcodeDetector` nativo antes do
  import da lib — evolução local, não reabre este IDR.

## Verificação

- Build mostra o chunk do zxing separado do bundle inicial (import dinâmico funcionando).
- `Tests\Feature\Coleta\CapturaScreenContractTest::test_scanner_usa_import_dinamico_do_zxing` trava o
  import dinâmico no fonte.
- `Tests\Browser\ColetaCapturaTest` exercita o botão de escanear (degradação) + os caminhos de colar.
