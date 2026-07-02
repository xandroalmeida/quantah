# Wishlist — pré-backlog do Quantah

Estoque durável de desejos e dívidas ainda **não** comprometidos em sprint. Não é backlog
(compromisso ordenado de entrega) nem fila de bugs (defeitos). É inventário rastreável.

Opere sobre esta pasta segundo `docs/skills/po/references/wishlist.md`.

## Arquivos

- `wishlist.json` — estado atual de todos os itens (fonte de verdade da lista) + `next_id`.
- `items/WISH-XXX-<slug>.md` — um arquivo por item, a partir de `docs/skills/po/templates/wish.md`.

## Estados

`new` → `triaged` → `accepted` → `specced` → `done` (ou `rejected`).

- **new**: recém-capturado, sem triagem.
- **triaged**: revisado pelo PO, contexto entendido, ainda sem compromisso.
- **accepted**: PO decidiu que vamos fazer — candidato a virar estória/spec.
- **specced**: já promovido a estória/spec/PDR (`spec_link` preenchido).
- **done** / **rejected**: encerrado.

## Como promover

Transformar um WISH em estória/spec **não** acontece aqui — segue a Operação 3 de
`wishlist.md` (lê o item inteiro + referências, cria a estória no épico certo e preenche
`spec_link`).
