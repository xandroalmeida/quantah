# Checklist de validação — EPIC-007

> Materializado dos critérios de aceite da STORY-037 (validação) + requisitos transversais do `epic.md`.
> É o **o quê** a validação confirma. O veredito e as evidências vivem em `report.md`.

## Bloco 1 — Critérios de aceite (por estória)

- 1.1 — Todas as estórias 033–036 com `status: done` no `index.json`.
- 1.2 — **STORY-033 (casca):** `nav.bottom` fixo/visível sem rolar em todas as telas logadas (Início,
  Escanear, Carteira, Perfil) no mobile, sem overflow; "Entrar" inteiro na landing; PWA standalone
  (manifest + metatags iOS).
- 1.3 — **STORY-034 (cupom):** listagem mostra estabelecimento + data; toque abre detalhe com itens;
  `nome_emitente` capturado em cupons novos, com fallback quando ausente.
- 1.4 — **STORY-035 (validade):** cupom fora da janela rejeitado com motivo próprio; limite de config
  (default 7); dentro da janela segue normal; fronteira testada; fuso America/Sao_Paulo.
- 1.5 — **STORY-036 (coerência):** sem opção de excluir conta (UI e rota); menu de escanear renomeado.

## Bloco 2 — Cobertura de testes

- 2.1 — Cobertura ≥ 80% no código novo da onda.
- 2.2 — TDD evidenciado (teste vermelho antes do código no histórico de commits).
- 2.3 — E2E em browser real (Dusk, ADR-008) nos fluxos de FE tocados (casca, listagem→detalhe,
  coerência), caminho feliz + erro.
- 2.4 — Testes cobrem caminho feliz + inválidos + exceções + bordas.
- 2.5 — Suíte completa verde (unit/feature + E2E).

## Bloco 3 — Transversais da WAVE-2026-02

- 3.1 — 100% pt-BR (R$ 1.234,56, dd/mm/aaaa, America/Sao_Paulo); sem string de scaffolding em inglês
  nas telas tocadas.
- 3.2 — a11y AA: alvos ≥48px, foco visível, `aria-current`/aria preservados.
- 3.3 — Mobile-first sem overflow horizontal em 360–390px.
- 3.4 — Design System: verde como único accent; sem primitivo novo fora do DS.

## Bloco 4 — Funcionalidade observável

- 4.1 — Entregáveis do `epic.md` exercitáveis (casca, detalhe do cupom, rejeição por prazo, coerência)
  em browser real no viewport mobile.
- 4.2 — Sem regressão da jornada do EPIC-006 (loop coletar → saldo → extrato → saque).
- 4.3 — Homologação no celular (deploy) — sujeito à decisão do PO sobre push (repo sem remote).

## Bloco 5 — Qualidade transversal

- 5.1 — Migração da onda reversível (`down`) e idempotente.
- 5.2 — Sem PII do consumidor exposta (ADR-006); `nome_emitente` é dado do estabelecimento.
- 5.3 — Sem segredos introduzidos no código; lint/format limpos.

## Bloco 6 — Estado e decisões

- 6.1 — `index.json` atualizado (estórias `done`, telas e decisões indexadas).
- 6.2 — IDRs/screen do épico registrados e indexados.
- 6.3 — "Notas do agente" de cada estória preenchidas.
