# Testes para quem não programa — a rede de segurança

> O Idealizador não escreve testes. Mas **exige** testes e **lê** o resultado. Esta reference explica, para uma pessoa não-técnica, o que "teste mínimo + fluxo alternativo" significa e **por que importa** — sem ensinar a escrever um único teste. O agente escreve; a pessoa entende o suficiente para cobrar e confiar.

## A ideia em uma frase

> **O teste é a rede que deixa você mudar de ideia sem quebrar o que já funcionava.**

Num POC, você vai mudar de ideia o tempo todo — esse é o objetivo. A cada mudança existe o risco de quebrar algo que já funcionava, em algum canto que você nem está olhando. O teste é um vigia automático: depois de cada mudança, o agente roda os testes e eles avisam, em segundos, se algo que funcionava parou de funcionar. Sem essa rede, cada ajuste vira um salto no escuro.

Por isso o teste mínimo é **parte do piso** do POC (`viability-bar.md`, item 3) — não burocracia, mas o que torna a iteração rápida **segura**.

## Você não precisa escrever. Precisa de duas coisas.

### 1. Saber EXIGIR

Ao terminar um fluxo, você pede ao agente, em linguagem simples:

- "Cria a rede pra esse fluxo." → o agente escreve um **teste de feature** que faz o fluxo do começo ao fim e confirma que **gravou**.
- "Inclui o caso de dar errado." → o agente escreve o teste do **caminho alternativo** (dado em branco, lista vazia, item que não existe).
- "Confirma que o app sobe." → o **teste de fumaça**: o app liga e a tela principal aparece.

Isso é tudo o que você precisa pedir. O **como** (Pest, PHPUnit, `assertDatabaseHas`) é do agente (`stacks/laravel/SKILL.md` › Testes) — você nunca toca nisso.

### 2. Saber LER o resultado

Quando o agente roda os testes, o resultado é **verde ou vermelho**:

- 🟢 **Verde (passou)** — a rede está de pé. O fluxo faz o que devia, inclusive o caso de erro. Pode seguir tranquilo.
- 🔴 **Vermelho (falhou)** — alguma coisa que devia funcionar não está funcionando. **Não é hora de avançar.** O agente conserta antes de seguir.

Você não precisa entender *por que* ficou vermelho — só precisa saber que **vermelho significa "ainda não".** O agente lê os detalhes e resolve. A sua régua é simples: **só consideramos um fluxo pronto quando os testes dele estão verdes.**

## O que "fluxo alternativo mínimo" quer dizer (e por que é metade do trabalho)

A maioria das pessoas descreve só o caminho onde tudo dá certo — o "caminho feliz". Mas software de verdade encontra dado errado, campo vazio, clique em algo que já sumiu. Se o app só foi testado no caminho feliz, você **não sabe** o que acontece quando algo sai do trilho — e é aí que a ideia costuma falhar (ou revelar uma regra que você nem sabia que tinha).

Por isso, para **cada** fluxo, além do caminho feliz, você lista com o agente os "e se der errado?" mais importantes. Em linguagem de não-técnico, os três que quase sempre importam:

| Tipo de erro | Pergunta a se fazer | Exemplo |
|---|---|---|
| **Dado inválido** | "E se preencher errado ou deixar em branco?" | salvar sem o nome obrigatório → mostra aviso, não grava |
| **Vazio** | "E se não tiver nada ainda?" | abrir a lista sem nenhum cadastro → mostra "nada por aqui ainda" |
| **Não encontrado** | "E se a pessoa acessar algo que não existe?" | abrir um item que foi apagado → mensagem amigável, não tela quebrada |

Você **não** precisa cobrir todos os erros possíveis — isso é trabalho do time de MVP (que cobre toda borda e todo caso inválido). No POC, **o erro mais importante de cada fluxo** já basta, desde que esteja **desenhado e testado**. Listar esses casos em linguagem simples é a sua parte; transformá-los em teste é a do agente.

## Como listar os caminhos para o agente

Use o template `templates/flow-spec.md`. Na seção de caminhos alternativos, escreva como você falaria com uma pessoa:

```
Caminho feliz:
1. O cliente preenche nome e telefone e clica em Salvar.
2. O cadastro aparece na lista.

E se der errado:
- Deixou o nome em branco → mostra aviso "nome é obrigatório", não salva.
- A lista ainda está vazia → mostra "nenhum cadastro ainda".
- Tentou abrir um cadastro que foi apagado → mostra "não encontrado", não quebra.
```

O agente lê isso e cria: um teste do caminho feliz (que confirma a gravação) e um teste de cada "e se der errado". Pronto — a rede do fluxo está completa pelo piso de POC.

## O que NÃO é da sua conta (é do agente / do MVP)

Para você não se afogar em rigor que não é seu:

- **Como o teste é escrito** — sintaxe, ferramenta, asserções. Do agente.
- **Cobertura de 80%/98%, todas as bordas, todos os casos inválidos** — do **time de MVP** (`viability-bar.md` › fora do piso). No POC isso seria excesso.
- **E2E em browser real (Dusk), testes de carga, testes de segurança** — do **time de MVP**.
- **Por que um teste específico ficou vermelho** — o agente diagnostica e conserta.

Sua régua começa e termina em: **exigir a rede para cada fluxo (feliz + erro principal) e só seguir quando estiver verde.** O resto é do agente agora, e do time depois.

## Por que vale a pena, mesmo "sendo só um POC"

Sem a rede, a terceira ou quarta mudança de ideia quebra silenciosamente algo da primeira — e você só descobre na pior hora, mostrando para um possível sócio ou investidor. Um punhado de testes mínimos custa pouco e compra muito: **liberdade de iterar rápido sem medo.** É o que torna o POC ágil de verdade, em vez de uma casa de cartas que desmonta a cada ajuste.
