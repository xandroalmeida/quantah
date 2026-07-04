---
id: SCREEN-STORY-017-backoffice-saques
story: STORY-017-resgate-saque
epic: EPIC-003-carteira-e-cashback
status: draft
created_at: 2026-07-03
updated_at: 2026-07-03
owner_designer: claude-story017
related_ddrs: []
ds_components_used: [card.content, badge.positive, badge.warning, badge.info, badge.negative, button.primary, button.secondary, button.danger, input.text, empty-state]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-017-backoffice-saques/index.html
prototype_last_validated_at: null
---

# Spec de tela — Backoffice de saques (operador)

> Referência: `STORY-017` + **ADR-005** (PIX assistido) + **ADR-009** (acesso por papel `operador`).
> Superfície **interna/administrativa** (não é superfície de marca do Colaborador); atrás do Gate
> `operar-saques`. Aqui o operador conduz a máquina de estados e paga o PIX por fora.

## 1. Objetivo da tela

O operador **vê os saques pendentes** e os **conduz** pela máquina de estados: assume o KYC, aprova/rejeita
(confere CPF × chave PIX), executa o PIX manualmente e marca **pago** com o comprovante. Uma tarefa central:
*resolver a fila de saques*.

## 2. Fluxo

### Entrada
- Rota `/backoffice/saques`, atrás de `auth` + `can:operar-saques` (ADR-009). Não-operador → 403.

### Ações (máquina de estados — ADR-005)
- **Assumir** (`solicitado → em_analise`): operador passa a cuidar do saque.
- **Aprovar** (`em_analise → aprovado`): CPF confere com a chave PIX.
- **Rejeitar** (`em_analise → rejeitado`): KYC falha → **estorna** o saldo ao Colaborador.
- **Marcar pago** (`aprovado → pago`): exige **comprovante** (e2e/id do PIX).
- **Saída:** a lista atualiza o status do saque operado.

## 3. Layout

### Desktop (≥1024px) — primário (uso do operador é majoritariamente desktop)
```
| Quantah · Backoffice            Saques |
| Filtro: [Todos][Solicitados][Em análise][Aprovados][Pagos][Rejeitados]
| +------------------------------------------------------------------+
| | Valor    CPF            Status         Solicitado   Ação         |
| | R$ 8,00  111.***.**-35  [solicitado]   03/07 14:20  [Assumir]    |
| | R$ 5,00  390.***.**-05  [em análise]   03/07 13:10  [Abrir]      |
| +------------------------------------------------------------------+
```
Clicar numa linha (ou "Abrir") mostra o **detalhe** com as ações do estado atual + campo de comprovante.

### Mobile (≥360px)
Tabela vira **lista de cards** (`pattern.listing`, sem scroll horizontal): cada card mostra valor, CPF
mascarado, status (badge) e as ações disponíveis do estado. Detalhe empilha.

## 4. Estados
- **Lista com itens:** por status; badge por estado (solicitado=info, em_analise=warning, aprovado=positive,
  pago=positive forte, rejeitado=negative).
- **Vazio:** `empty-state` "Nenhum saque neste filtro." (sem CTA — é fila operacional).
- **Detalhe:** dados do saque + ações do estado atual. Em `aprovado`, campo **comprovante** obrigatório para pagar.
- **Erro de transição:** se a ação não cabe no estado (ex.: outro operador já mudou), `snackbar`: "Esse saque
  mudou de estado. Atualize a lista."
- **Sem permissão:** não-operador nunca renderiza — 403 antes.
- **Loading:** skeleton de linhas.

## 5. Microcopy

| Lugar | Texto |
|---|---|
| Título | `Saques` |
| Filtros | `Todos` · `Solicitados` · `Em análise` · `Aprovados` · `Pagos` · `Rejeitados` |
| Colunas | `Valor` · `CPF` · `Status` · `Solicitado` · `Ação` |
| Ação assumir | `Assumir` |
| Ação aprovar | `Aprovar` |
| Ação rejeitar | `Rejeitar` |
| Ação pagar | `Marcar pago` |
| Label comprovante | `Comprovante (e2e / id do PIX)` |
| Vazio | `Nenhum saque neste filtro.` |
| Erro transição | `Esse saque mudou de estado. Atualize a lista.` |
| Confirmar rejeição | `Rejeitar este saque? O valor volta para o saldo do Colaborador.` |

CPF **mascarado** na exibição (`111.***.***-35`) — minimização de PII na tela (ADR-006); o valor canônico
completo fica só onde a operação exige.

## 6. Acessibilidade
- Tabela semântica (`<table>`, `<th scope>`, `<caption class=sr-only>`) no desktop; lista de cards com
  headings no mobile.
- Ações são `<button>` reais; ação destrutiva (rejeitar) com confirmação e cor `danger` + texto (não só cor).
- Status por **badge com ícone + texto** (não só cor). Foco visível; alvos ≥48px.

## 7. Identificadores estáveis

| Elemento | Id lógico |
|---|---|
| Tela | `backoffice-saques` |
| Filtro <status> | `backoffice-filtro-<status>` |
| Linha do saque | `backoffice-saque-row` |
| Badge de status | `backoffice-saque-status` |
| Botão assumir | `backoffice-acao-assumir` |
| Botão aprovar | `backoffice-acao-aprovar` |
| Botão rejeitar | `backoffice-acao-rejeitar` |
| Campo comprovante | `backoffice-comprovante` |
| Botão pagar | `backoffice-acao-pagar` |
| Vazio | `backoffice-vazio` |

## 8. Exceções ao DS
Nenhuma. Superfície interna reusa Card/Badge/Button/EmptyState (análogo ao painel de métricas, STORY-012).

## 9. Protótipo HTML fiel
`STORY-017-backoffice-saques/index.html` — estados `lista`, `detalhe`, `vazio` e os status via `?state=`;
mobile/desktop; ações da máquina de estados navegáveis. Apresentado ao humano.

## 10. Dependências
- Domínio: `SaqueService` (assumir/aprovar/pagar/rejeitar). Acesso: Gate `operar-saques` (ADR-009).
- CPF exibido mascarado; valor em centavos → reais na borda.

## 11. Histórico
| Data | Mudança | Quem |
|---|---|---|
| 2026-07-03 | criação spec + protótipo | claude-story017 |
