# Pacote de Handoff — Quantah

> **O que é este documento.** O pacote de handoff do POC, em **linguagem simples** (sem jargão técnico). É mantido **vivo** durante todo o POC — a cada fluxo novo ou regra descoberta, atualize aqui. Quando o projeto graduar para MVP, este é o pacote que o Idealizador entrega ao time, e é a **fonte de verdade que o PO lê** para nascer a especificação (`po/SKILL.md`). Vive em `docs/prototipo/`.
>
> Preencha tudo. Onde algo foi encenado (stub), **diga**. Onde falta algo, **diga**. Honestidade aqui economiza meses depois.

---

## 1. Ideia & valor

- **O que é:** <uma frase: o que o produto faz, para quem, qual o diferencial — base para `plataforma de inteligência de preços via NFC-e que coleta cupons fiscais de forma colaborativa (crowdsourcing) para construir uma base de preços do varejo em tempo quase real, monetizada como inteligência de mercado (B2B)`>
- **Que problema resolve:** <a dor concreta do usuário>
- **Por que importa / qual o valor:** <por que alguém usaria/pagaria>
- **O que o POC provou (ou não):** <o sinal de viabilidade que buscamos e o que aprendemos>

## 2. Personas / usuários

> Quem usa o produto. Não "o usuário" genérico — pessoas com contexto e objetivo.

- **Colaborador** — <quem é, o que quer conseguir, em que situação usa>
- **Analista B2B** — <quem é, o que quer conseguir, em que situação usa>
- **Público-alvo geral (consumidores brasileiros que recebem NFC-e nas compras (lado da coleta) e clientes B2B — indústria/CPG, varejo e terceiros de dados (lado da demanda)):** <descrição concreta>

> Se o produto tem só um tipo de usuário, descreva-o e ignore a dualidade.

## 3. Fluxos

> Cada fluxo é uma coisa que um usuário consegue fazer de ponta a ponta. Para **cada** fluxo, o caminho feliz **e** os caminhos alternativos/erro (o item 4 da barra de viabilidade). Use a flow-spec (`templates/flow-spec.md`) como fonte; resuma aqui.

### Fluxo: <nome do fluxo>
- **Quem faz:** <Colaborador / Analista B2B / usuário>
- **Objetivo:** <o que essa pessoa quer conseguir>
- **Caminho feliz (passo a passo):**
  1. <passo>
  2. <passo>
- **Caminhos alternativos / erro:**
  - <ex.: deixou campo obrigatório em branco → mostra aviso, não grava>
  - <ex.: lista vazia → mostra estado "nada por aqui ainda">
  - <ex.: acessou item inexistente → mensagem amigável>
- **Status:** <funcionando / parcial / planejado>

*(repita o bloco para cada fluxo)*

## 4. Telas

> Lista de telas e o propósito de cada uma. Este documento não precisa ser bonito — precisa de clareza sobre o que cada tela serve. (O **POC em si** é apresentável; aqui é só o registro.)

| Tela | Propósito | Quem usa |
|---|---|---|
| <nome da tela> | <para que serve> | <persona> |
| | | |

**Direção visual usada no POC** (para o Designer herdar o ponto de partida, não recomeçar do zero):
kit/tema de componentes usado (ex.: Tailwind UI / Flux / daisyUI / starter), paleta/cores principais,
e o que funcionou ou não na frente de clientes/investidores. _<preencha>_

## 5. Entidades de dados (o que se grava)

> O que o app guarda no banco, em linguagem simples. Cada "coisa que se cadastra" é uma entidade. Liste os campos principais.

### <Entidade — ex.: "Cadastro de cliente">
- **O que é:** <descrição em uma frase>
- **Guarda:** <campos principais — ex.: nome, telefone, data de criação>
- **Relaciona com:** <outras entidades — ex.: "cada pedido pertence a um cliente">

*(repita para cada entidade)*

## 6. Regras de negócio descobertas

> Regras que a ideia exige e que apareceram durante a construção — muitas vezes ao desenhar os caminhos de erro. São ouro para o PO. Escreva como regra clara.

- <ex.: "Um cliente não pode ter dois cadastros com o mesmo telefone.">
- <ex.: "Um pedido só pode ser confirmado se tiver pelo menos um item.">
- <ex.: "Quando o estoque chega a zero, o item some da lista de oferta.">

## 7. O que está stub / faked (encenado, não real)

> **Seção crítica de honestidade.** Tudo que parece funcionar mas é encenação. O time de MVP vai construir achando que algo é real se você não avisar aqui.

| O que parece | Como está de verdade no POC | O que o MVP precisa fazer |
|---|---|---|
| <ex.: "envia e-mail de confirmação"> | <ex.: "não envia nada — só mostra mensagem na tela"> | <ex.: "integrar provedor de e-mail real"> |
| <ex.: "processa pagamento"> | <ex.: "sempre aprova; nenhum gateway conectado"> | <ex.: "integrar gateway de pagamento"> |
| | | |

## 8. Lacunas conhecidas / o que falta endurecer

> O que ficou deliberadamente de fora do POC porque é trabalho de MVP. Não é falha — é escopo adiado (`viability-bar.md` › fora do piso).

- **Banco:** roda em SQLite (trampolim). Migrar para Postgres é trabalho do Arquiteto (ADR — `stacks/database/sqlite/SKILL.md`).
- **Segurança:** <o que tem hoje — ex.: "login simples / nenhum"; o que falta — auth/autorização robusta>.
- **Cobertura de testes:** mínima (fumaça + 1 por fluxo + erro principal). Falta cobertura completa, todas as bordas, E2E em browser real.
- **Escala / performance / observabilidade / infra:** não tratadas — do time de MVP.
- **Outras lacunas:** <liste o que você sabe que falta>

## 9. Como rodar o POC

> O "um comando" e como usar. Espelha o `README` do POC (`templates/poc-readme.md`).

- **Subir:** <ex.: `./vendor/bin/sail up` ou `php artisan serve`>
- **Abrir:** <endereço — ex.: `http://localhost:8000`>
- **Login (se houver):** <usuário/senha de teste, ou "não tem login">
- **Roteiro rápido para ver a ideia funcionando:**
  1. <passo>
  2. <passo>
- **Onde estão as flow-specs detalhadas:** `docs/prototipo/flows/`

---

*Documento vivo. Última atualização: <data>. Mantido pelo Idealizador durante a fase POC; entregue ao time na graduação para MVP.*
