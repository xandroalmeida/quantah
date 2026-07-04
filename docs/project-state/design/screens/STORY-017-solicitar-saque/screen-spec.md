---
id: SCREEN-STORY-017-solicitar-saque
story: STORY-017-resgate-saque
epic: EPIC-003-carteira-e-cashback
status: ready
created_at: 2026-07-03
updated_at: 2026-07-03
owner_designer: claude-story017
related_ddrs: []
ds_components_used: [card.content, card.feature-dark, input.text, input.money, button.primary, button.tertiary, snackbar, badge.info]
exceptions_to_ds: []
viewports: [mobile, desktop]
prototype_path: STORY-017-solicitar-saque/index.html
prototype_last_validated_at: 2026-07-04
---

# Spec de tela — Solicitar saque (Colaborador)

> Referência: `STORY-017-resgate-saque` + **ADR-005** (PIX assistido, KYC mínimo). Regras de valor/CPF
> vêm do domínio (`SolicitarSaqueService`): mínimo **R$ 5,00**, saldo suficiente, **chave PIX = CPF** do titular.

## 1. Objetivo da tela

O Colaborador **solicita o resgate** do saldo via PIX. Uma tarefa: informar quanto sacar e para qual CPF
(que é a chave PIX). O pagamento é assistido — cai depois que o operador confere e paga (não é instantâneo).

## 2. Fluxo

### Entrada
- Da **/carteira**, botão **"Sacar"** (aparece quando há saldo). Rota `/carteira/saque` (ou modal), sob `auth`.

### Ações
- **Primária:** preencher valor + CPF → **"Solicitar saque"**. Reserva o valor (debita o saldo) e cria o
  saque em `solicitado`.
- **Secundária:** "Cancelar" → volta à carteira.
- **Saída (sucesso):** confirmação "Saque solicitado" + volta à carteira com o saldo já reduzido.

## 3. Layout

### Mobile (≥360px) — primário
```
| ← Sacar                                  |
| +--------------------------------------+ |
| | Saldo disponível                     | |  card.feature-dark (contexto)
| | R$ 12,47                             | |
| +--------------------------------------+ |
| Quanto você quer sacar?                  |
| [ R$ 0,00 ]                              |  input.money  (min R$5, max saldo)
| CPF (sua chave PIX)                       |
| [ 000.000.000-00 ]                       |  input.text (máscara CPF; inputMode numeric)
| Você recebe via PIX na chave do tipo CPF.|  badge.info / nota
|                                          |
| [ Solicitar saque ]                      |  button.primary (≥48px)
| [ Cancelar ]                             |  button.tertiary
+------------------------------------------+
```
- No desktop: mesma coluna centralizada `max-w-md` (form curto não estica). Nav = top-nav (como /carteira).

## 4. Estados

### 4.1. Feliz (form preenchido válido) → submit → **sucesso**
Confirmação: "Saque solicitado" + "Você recebe no PIX em até X dias úteis. Avisaremos por aqui." + saldo
atualizado. (Prazo é microcopy de expectativa; operação é manual.)

### 4.2. Erros de validação (por campo)
- **Valor abaixo do mínimo:** "O valor mínimo de saque é R$ 5,00."
- **Valor acima do saldo:** "Você tem R$ 12,47 disponíveis."
- **CPF inválido:** "CPF inválido."
- Erros ancorados no campo (não banner global), `role="alert"`.

### 4.3. Saldo insuficiente / abaixo do mínimo (bloqueio de entrada)
Se o saldo é menor que R$ 5,00, o botão "Sacar" na carteira leva a um aviso: "Você precisa de pelo menos
R$ 5,00 para sacar." (ou a tela mostra o form desabilitado com essa nota). Sem travar o app.

### 4.4. Loading (submit)
Botão em estado `loading` (spinner + `aria-busy`), desabilitado durante o POST. Sem tela vazia.

### 4.5. Erro inesperado
`snackbar` danger: "Não foi possível solicitar o saque. Tente de novo." Sem stack trace.

### 4.6. Sem permissão
Guest → `/login` (rota `auth`).

## 5. Microcopy

| Lugar | Texto |
|---|---|
| Título | `Sacar` |
| Card contexto | `Saldo disponível` · `R$ 12,47` |
| Label valor | `Quanto você quer sacar?` |
| Placeholder valor | `R$ 0,00` |
| Label CPF | `CPF (sua chave PIX)` |
| Placeholder CPF | `000.000.000-00` |
| Nota chave PIX | `Você recebe via PIX na chave do tipo CPF.` |
| CTA primário | `Solicitar saque` |
| CTA secundário | `Cancelar` |
| Erro valor < mín | `O valor mínimo de saque é R$ 5,00.` |
| Erro valor > saldo | `Você tem R$ 12,47 disponíveis.` |
| Erro CPF | `CPF inválido.` |
| Sucesso (título) | `Saque solicitado` |
| Sucesso (texto) | `Você recebe no PIX em até 3 dias úteis. A gente avisa por aqui.` |
| Bloqueio mínimo | `Você precisa de pelo menos R$ 5,00 para sacar.` |
| Erro inesperado | `Não foi possível solicitar o saque. Tente de novo.` |

Dinheiro `R$ 0,00`; CPF com máscara só na UI (persistência canônica sem máscara — server).

## 6. Acessibilidade
- `input.money` e CPF com `<label>` associado; `inputMode="numeric"` no CPF; máscara é UX (validação canônica no servidor).
- Erros por campo com `aria-describedby` + `role="alert"`.
- Botão de submit ≥48px; `loading` com `aria-busy`.
- Foco inicial no campo de valor.

## 7. Identificadores estáveis

| Elemento | Id lógico |
|---|---|
| Tela | `screen-saque` |
| Saldo disponível | `screen-saque-saldo` |
| Campo valor | `screen-saque-valor` |
| Campo CPF | `screen-saque-cpf` |
| Erro valor | `screen-saque-erro-valor` |
| Erro CPF | `screen-saque-erro-cpf` |
| CTA solicitar | `screen-saque-submit` |
| Sucesso | `screen-saque-sucesso` |

## 8. Exceções ao DS
Nenhuma — composta de componentes/tokens existentes (`input.money` é o `input.text` com máscara de dinheiro).

## 9. Protótipo HTML fiel
`STORY-017-solicitar-saque/index.html` — estados `form`, `erro`, `sucesso`, `sem-saldo` por `?state=`;
mobile/desktop; tokens/microcopy/ids reais. Apresentado ao humano para validação.

## 10. Dependências
- Domínio: `SolicitarSaqueService` (mínimo R$5, saldo sob lock, CPF=chave). Rota `auth`.
- Contrato: POST `valor_centavos` (ou reais convertidos), `cpf` (= chave). Saldo vem como prop.

## 11. Histórico
| Data | Mudança | Quem |
|---|---|---|
| 2026-07-03 | criação spec + protótipo | claude-story017 |
| 2026-07-04 | validação humana — aprovado ("continue") | Alexandro |
