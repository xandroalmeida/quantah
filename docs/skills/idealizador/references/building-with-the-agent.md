# Construindo com o agente — como uma pessoa não-técnica conduz

> O Idealizador não programa. Ele **dirige** o agente. Esta reference é o método de condução: como descrever, pedir, validar e iterar — e como reconhecer quando o agente está complicando demais. Pensada para você (agente) guiar a pessoa não-técnica, e para a pessoa entender o ritmo.

O acordo: **a pessoa decide produto e valida; o agente escreve o código.** Isso não significa "manda fazer e torce". Significa um loop curto e repetido onde a pessoa descreve em linguagem simples, o agente constrói o mínimo, e a pessoa confirma no navegador. Pequenos passos, sempre rodando.

## O loop, em uma imagem

```
descrever um fluxo  →  agente faz scaffold/constrói  →  pessoa abre no navegador
       ↑                                                        ↓
   ajustar a ideia  ←——————  "não era bem isso"  ←——  valida: roda? grava? faz sentido?
```

Um fluxo de cada vez. Nunca "constrói o app todo e depois a gente vê".

## Passo 1 — Descrever o fluxo em linguagem simples

Antes de construir, a pessoa descreve **um** fluxo usando o template `templates/flow-spec.md`. Não precisa de jargão — precisa de clareza sobre o comportamento. O agente faz as perguntas que faltam:

- **Quem faz isso?** (Colaborador ou Analista B2B, ou um usuário só)
- **O que essa pessoa quer conseguir?** (o objetivo, em uma frase)
- **Quais os passos quando dá tudo certo?** (caminho feliz, numerado)
- **O que pode dar errado?** (caminhos alternativos — ver abaixo, é onde a maioria esquece)
- **O que precisa ficar guardado?** (o que grava no banco)
- **Como a gente sabe que deu certo?** (o resultado visível)

> Se a pessoa pular os "e se der errado?", **você puxa**. "E se o cliente deixar o nome em branco? E se a lista estiver vazia? E se ele clicar em algo que já foi apagado?" Cada resposta vira um caminho alternativo — e é exatamente o que o item 4 da barra de viabilidade exige (`viability-bar.md`).

## Passo 2 — Pedir o scaffold (uma vez, no começo)

No primeiro fluxo, peça ao agente o esqueleto: app Laravel + SQLite, a primeira tela em Livewire, a primeira tabela. O agente se apoia nos **generators** (`make:model -m`, `make:livewire`) — não inventa estrutura (`stacks/laravel/SKILL.md`). Ao fim do scaffold, a pessoa já deve conseguir **abrir o app no navegador** mesmo que vazio (item 1 da barra).

## Passo 3 — Construir um fluxo de cada vez

O agente constrói **o fluxo descrito** — caminho feliz **e** o erro principal — já **apresentável** (componentes do kit Tailwind/Livewire, não HTML cru), e nada além. Não adianta construir cinco telas de uma vez: quebra o loop de validação e acumula código não conferido. Ao terminar o fluxo, o agente também escreve o teste mínimo (Passo 5) antes de seguir para o próximo. Aparência crível faz parte do "construído", não de um passo de "embelezar depois" (item 5 da barra — `references/viability-bar.md`).

## Passo 4 — Validar no navegador (roda e grava)

A validação é da pessoa, e é concreta:

1. **Roda?** Abre o endereço, a tela aparece.
2. **Grava?** Faz a ação, depois **fecha e reabre** o app — o dado continua lá. (Não basta "apareceu na tela"; tem que sobreviver ao reinício — item 2 da barra.)
3. **Faz sentido?** O comportamento bate com a ideia. Se não bate, é aqui que se descobre.
4. **Parece um produto?** A tela tem cara de demo que você mostraria a um investidor — não de protótipo cru (item 5 da barra). Se não, o agente aplica o kit/tema antes de seguir.

O agente entrega um **roteiro curto** para isso: "abra `http://localhost:8000`, clique em Novo, preencha X, salve; depois recarregue a página — deve continuar lá".

## Passo 5 — Pedir o teste

Depois que o fluxo funciona, a pessoa **exige** o teste — não escreve, exige. "Cria a rede pra esse fluxo, incluindo o caso de dar errado." O agente escreve o teste de feature (feliz + erro principal) e mostra o resultado **verde**. A pessoa aprende a ler só isso: verde = a rede está de pé; vermelho = algo quebrou (ver `testing-for-non-coders.md`).

## Passo 6 — Iterar sem medo

Mudar de ideia é o **propósito** de um POC, não um acidente. Quando a pessoa diz "na verdade, queria que fosse assim", o agente ajusta a flow-spec, ajusta o código, ajusta o teste — e roda os testes de novo para confirmar que **o que já funcionava continua funcionando**. Essa é a rede em ação: sem ela, cada mudança seria um risco cego.

## Como reconhecer que o agente está complicando demais

A pessoa não-técnica não consegue avaliar código — mas **consegue** perceber sintomas. Ensine-a (e a si mesmo) a perguntar: **"isso parece muito complexo para um POC?"** Sinais de alerta:

| Sintoma | O que provavelmente é | O que pedir |
|---|---|---|
| "Vou primeiro montar a estrutura de autenticação/permissões" | Auth robusta — trabalho de MVP | "Pro POC, login simples ou nenhum. Adia o resto." |
| "Preciso configurar Docker/infra/servidor de banco" | Infra de produção | "SQLite roda sozinho. Sobe com um comando só." |
| "Vou criar uma camada genérica pra suportar vários casos" | Abstração prematura | "Faz só o caso que a gente tem agora." |
| "Antes preciso migrar pra Postgres" | Migração de produção | "SQLite é o trampolim. Postgres é do time de MVP." |
| "Vou adicionar a biblioteca X, Y e Z" | Dependências demais | "O Laravel já faz isso? Usa o que o framework dá." |
| Muitos passos manuais pra rodar | Saindo do 'um comando' | "Quero abrir com um comando só." |
| Demora muito num fluxo e nada roda | Perdeu o loop | "Para. Faz a versão mais simples que roda e grava." |

> Quando a pessoa levanta essa bandeira, **você (agente) reconhece e simplifica** — ou explica em linguagem simples por que aquele pedaço é mesmo necessário **agora** (raro). A regra: no POC, o caminho mais simples que roda e grava quase sempre vence. Tudo que parece "fazer direito" e não ajuda a provar a ideia é trabalho de MVP adiado (`poc-mindset.md`, `viability-bar.md`).

## Como a pessoa fala com o agente (sem jargão)

A pessoa descreve **comportamento e produto**, não tecnologia. Bons pedidos:

- ✅ "Quando o cliente salva o pedido, ele tem que aparecer na lista do outro lado."
- ✅ "Se faltar o nome, mostra um aviso e não deixa salvar."
- ✅ "Quero abrir o app e ver os cadastros que fiz ontem."
- ✅ "Isso parece complicado demais — dá pra fazer mais simples só pra testar a ideia?"

Pedidos que **não** são da pessoa (são do agente decidir, ou do MVP):

- ❌ "Usa o padrão repository com injeção de dependência." (decisão técnica — do agente)
- ❌ "Configura índice composto nessa tabela." (otimização — do MVP)
- ❌ "Coloca Sanctum com refresh token." (auth de produção — do MVP)

Se a pessoa não souber como pedir algo, ela descreve **o que o usuário deve ver/conseguir** e o agente traduz para a implementação. O contrato é esse: **produto e comportamento são dela; tradução para código é sua.**

## Encerramento de cada passo

Ao terminar um fluxo, o agente sempre devolve, em chat e em linguagem simples:

- **O que foi feito** — uma frase.
- **Como testar à mão** — endereço, o que clicar, o que preencher, e "feche e reabra pra confirmar que gravou".
- **O que esperar ver** — o resultado de sucesso e o de erro.
- **O que ficou de fora / é stub** — se algo foi encenado, diz aqui (e registra no handoff).

Isso fecha o loop e prepara a próxima volta.
