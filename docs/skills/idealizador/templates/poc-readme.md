# Quantah — POC (prova de conceito)

> **O que é.** O `README` do app do POC, na raiz do projeto. Curto e prático: o que é, como rodar com **um comando**, como usar, o que é real vs. encenado, e o status dos fluxos. Pensado para qualquer pessoa abrir e usar — inclusive não-técnica. O detalhe de produto vive no pacote de handoff (`docs/prototipo/handoff.md`).
>
> **Atenção:** isto é um **POC** — prova de viabilidade, **não** vai para produção. Roda em Laravel + Livewire + SQLite. A migração para Postgres e o endurecimento são trabalho do time de MVP (ver `references/graduation.md`).

---

## O que é

<uma ou duas frases: o que o produto faz e para quem. Base: `plataforma de inteligência de preços via NFC-e que coleta cupons fiscais de forma colaborativa (crowdsourcing) para construir uma base de preços do varejo em tempo quase real, monetizada como inteligência de mercado (B2B)`.>

## Como rodar (um comando)

> Requisito: <ex.: PHP + Composer instalados, ou Docker para o Sail>.

```bash
# 1. instalar dependências (primeira vez)
composer install

# 2. preparar o banco SQLite (primeira vez)
php artisan migrate

# 3. subir o app
php artisan serve
#   — ou, com Docker:  ./vendor/bin/sail up
```

Depois, abra no navegador: **<http://localhost:8000>**

> O banco é um arquivo SQLite (`database/database.sqlite`) — sem servidor, sem configuração. Os dados ficam gravados ali; fechar e reabrir o app não apaga nada. **Rodar os testes também não apaga seus dados**: a rede de testes usa um banco separado (`database/testing.sqlite`), não o seu.

## Como usar

> Roteiro rápido para ver a ideia funcionando.

1. <ex.: abra a tela inicial>
2. <ex.: clique em "Novo" e cadastre um item>
3. <ex.: recarregue a página — o item continua lá (gravou de verdade)>
4. <ex.: percorra o fluxo principal de ponta a ponta>

**Login (se houver):** <usuário/senha de teste — ou "não tem login neste POC">

## O que é real vs. o que é encenado (stub)

> Honestidade de POC: o que funciona de verdade e o que foi fingido para a prova.

| Funcionalidade | Real? | Observação |
|---|---|---|
| <ex.: cadastro e listagem> | ✅ real | grava em SQLite |
| <ex.: envio de e-mail> | ⚠️ stub | só mostra mensagem na tela; não envia |
| <ex.: pagamento> | ⚠️ stub | sempre "aprovado"; sem gateway |

Detalhe completo em `docs/prototipo/handoff.md` › "O que está stub / faked".

## Status dos fluxos

| Fluxo | Status | Testes |
|---|---|---|
| <ex.: Cadastrar cliente> | ✅ funcionando | 🟢 feliz + erro |
| <ex.: Confirmar pedido> | 🚧 parcial | 🟢 feliz / 🔴 erro pendente |
| <ex.: Relatório> | 📋 planejado | — |

## Rodar os testes

```bash
php artisan test
#   — ou:  ./vendor/bin/pest
```

Verde = a rede está de pé (`references/testing-for-non-coders.md`). O POC mira o piso: fumaça + um teste por fluxo principal + o erro mais importante de cada fluxo.

---

*POC de Quantah. Fonte de verdade de produto: `docs/prototipo/handoff.md`. Fase atual em `_project.md` (`phase`).*
