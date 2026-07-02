# Flow-spec — <nome do fluxo>

> **O que é.** A descrição de **um** fluxo, em linguagem simples, **antes** de construir. O Idealizador (pessoa não-técnica) preenche; o agente lê e constrói. Um arquivo por fluxo, em `docs/prototipo/flows/`. Sem jargão técnico — descreva comportamento e produto.
>
> Regra de ouro: **não pule os "caminhos alternativos / e se der errado".** Eles são metade do trabalho e o que separa um POC honesto de uma demo enganosa (`references/viability-bar.md`, item 4). Se você não sabe quais são, o agente te ajuda a listar.

---

## Nome do fluxo
<um nome curto e claro — ex.: "Cadastrar um cliente", "Confirmar um pedido">

## Quem faz
<Colaborador / Analista B2B / um usuário qualquer> — <em que situação essa pessoa faz isso>

## Objetivo
<o que essa pessoa quer conseguir, em uma frase — ex.: "registrar um novo cliente para poder fazer pedidos depois">

## Passos do caminho feliz (quando tudo dá certo)
> Numere do começo (onde a pessoa começa) até o fim (o resultado visível).

1. <ex.: a pessoa abre a tela de clientes e clica em "Novo">
2. <ex.: preenche nome e telefone>
3. <ex.: clica em "Salvar">
4. <ex.: o cliente aparece na lista>

## Caminhos alternativos / e se der errado
> Os jeitos de sair do trilho. No POC, cubra pelo menos **o erro mais importante** de cada tipo. O agente vai desenhar E testar cada um.

- **Dado inválido / em branco:** <ex.: deixou o nome vazio → mostra aviso "nome é obrigatório", não salva>
- **Vazio / nada ainda:** <ex.: a lista de clientes está vazia → mostra "nenhum cliente cadastrado ainda">
- **Não encontrado:** <ex.: tentou abrir um cliente que foi apagado → mostra "não encontrado", sem quebrar a tela>
- **Outros que importam:** <liste se houver — ex.: "telefone repetido → avisa que já existe">

## O que precisa gravar (ficar guardado no banco)
> O que tem que sobreviver quando a pessoa fecha e reabre o app.

- <ex.: nome do cliente>
- <ex.: telefone>
- <ex.: data em que foi cadastrado>

## Como saber que deu certo
> O resultado visível que confirma sucesso — e o que confirma que **gravou** (fecha e reabre, continua lá).

- **Sucesso:** <ex.: "o cliente aparece na lista com o nome certo">
- **Gravou de verdade:** <ex.: "recarreguei a página / reabri o app e o cliente continua lá">
- **Erro tratado:** <ex.: "ao deixar o nome em branco, vejo o aviso e nada é salvo">

---

*Preenchido pelo Idealizador antes de construir. O agente usa isto para construir o fluxo (feliz + alternativos) e escrever os testes mínimos. Ver `references/building-with-the-agent.md` e `references/testing-for-non-coders.md`.*
