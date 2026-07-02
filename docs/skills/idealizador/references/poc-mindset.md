# Mentalidade de POC — simples por dentro, crível por fora

> Leia isto antes de construir qualquer coisa. É a régua interna que te guia quando a pessoa não-técnica não souber pedir — e quando você se pegar querendo "fazer direito" algo que o POC não precisa (ou **deixando de lado a aparência**, que o POC precisa muito).

O POC tem **um único trabalho**: descobrir se a ideia merece virar produto. Tudo que serve a isso entra; tudo que não serve, espera o MVP. Esta reference é a filosofia; a régua concreta de "pronto" está em `viability-bar.md`.

**Um aviso que vale para tudo abaixo:** este POC é mostrado a **investidores e clientes** — é um **artefato de venda**, não um rascunho interno. Por isso "simples" se aplica à **engenharia** (por dentro), não à **aparência** (por fora). Por dentro, o mais simples que funciona. Por fora, precisa **parecer um produto de verdade**. Não confunda os dois.

## A pergunta que governa tudo

A cada decisão — uma feature, um refino, uma biblioteca, uma tela mais bonita — pergunte:

> **Isso ajuda a provar (ou refutar) que a ideia tem mercado?**

- **Sim** → faça, no jeito mais simples que funciona.
- **Não** → não faça. Anote como lacuna conhecida no pacote de handoff e siga.

Cuidado para não aplicar essa pergunta à coisa errada. O **capricho de engenharia** que parece "profissional" (abstração, infra, cobertura, auth robusta) quase sempre responde **não** — adie. Já a **aparência** costuma responder **sim**: o POC é mostrado a investidores e clientes, e parecer crível é parte de provar que a ideia vende. A régua não é "função em vez de aparência" — é **função como base, aparência como o que converte "funciona" em "acredito nisso"**. Um POC que roda mas parece quebrado mina o próprio pitch; um POC que roda, grava **e parece um produto de verdade** prova o que precisa.

## Princípios

### 1. Dois "polimentos" — adie um, invista no outro
A palavra "polimento" esconde duas coisas opostas:

- **Capricho de engenharia** — abstração genérica, auth robusta, infra, cobertura completa, arquitetura "para o futuro". **Adie para o MVP.** Não prova a ideia e afoga a pessoa não-técnica.
- **Aparência e experiência da demo** — visual consistente e crível, telas que parecem um produto de verdade. **Invista.** O POC é mostrado a investidores e clientes; é por aí que a ideia é vendida.

O Idealizador **se importa muito** com a aparência — e está certo. Uma demo amadora enfraquece uma ideia boa; uma demo que parece um produto real faz a ideia ser levada a sério. A boa notícia: aparência crível no POC é **barata** — não se desenha do zero, usa-se o **Tailwind e um kit de componentes pronto** da stack (`stacks/livewire/SKILL.md`). Gaste tempo no **fluxo que prova a ideia** e numa **aparência crível** — não em gold-plating de engenharia.

### 2. Simplicidade ao extremo
A solução mais simples que demonstra o comportamento. Sem abstração "para o futuro" — no POC o futuro é incerto por definição (é justamente o que estamos testando). Apoie-se nos defaults e generators do framework (`stacks/laravel/SKILL.md`); não invente arquitetura. Se você está construindo uma camada genérica para um caso que ainda nem se provou real, pare.

> Isto é o mesmo espírito do **"Simples é o belo"** que o Arquiteto carrega (`_project.md` › Decisões de método; `arquiteto/references/architecture-principles.md`) — só que no POC ele vale **com ainda menos cerimônia**. O Arquiteto está dormente nesta fase; o princípio dele, não. Quando o projeto graduar, é ele quem decide a estrutura real — você não precisa antecipar isso.

### 3. Primeiro funciona, depois encanta — mas os dois entram
"Feito e rodando" é a **fundação**: um POC que não sobe e não grava não prova nada, por mais bonito que seja. Então a **ordem** é rodar e persistir primeiro, deixar crível e apresentável depois. Atenção: é ordem, não dispensa. Num POC que vai para a frente de investidor, a aparência **vem depois** da função — nunca **no lugar** dela, e nunca fica de fora. Estado vazio/erro precisa existir (faz parte do fluxo alternativo) e precisa ser **honesto, funcional E apresentável** — um empty-state cuidado é parte do que faz a demo parecer um produto.

### 4. Honestidade radical sobre o que é stub
POC vive de atalhos — e atalhos são legítimos. O que **não** é legítimo é esconder que são atalhos. Se você fingiu um pagamento, encenou um envio de e-mail, chumbou um valor que viria de uma integração, ou pulou uma validação — **registre no pacote de handoff** (`templates/handoff-packet.md`, seção "O que está stub/faked") e diga ao Idealizador, em palavras simples, o que é de verdade e o que é encenação.

Stub honesto é uma ferramenta de POC. Stub escondido é uma armadilha que explode na cara do time de MVP, que vai construir achando que algo já funciona.

### 5. Descartável-consciente, mas amigável a handoff
O código do POC **não vai para produção** — o time de MVP vai endurecer ou reescrever partes. Isso te liberta de perseguir perfeição interna. **Mas** o POC não é jogado fora: ele vira o ponto de partida do MVP, e o **pacote de handoff** (`docs/prototipo/`) é o que o PO lê para nascer a especificação (`po/SKILL.md`).

Então a régua é: **código pode ser descartável; o entendimento não pode.** Você pode tomar atalhos no código, mas tudo o que você **descobriu** sobre o produto — fluxos, regras, telas, o que o usuário esperava — precisa estar capturado em linguagem simples no handoff. O que se perde quando o POC é jogado fora deve ser só o código, nunca o aprendizado.

### 6. O usuário não-técnico decide; você protege
A pessoa decide produto e muda de ideia — isso é o POC funcionando, não um problema. Seu papel é deixar a mudança **barata e segura**: testes mínimos como rede (`testing-for-non-coders.md`), pequenos passos, sempre rodando. E é proteger contra a complexidade que ela não tem como ver — quando uma ideia simples de descrever esconde trabalho grande, você levanta a bandeira em linguagem simples (`building-with-the-agent.md`), não constrói calado um monstro.

## A tensão que você precisa equilibrar

| Pouco guardrail (lixo) | Equilíbrio (POC bom) | Excesso de rigor (afoga a pessoa) |
|---|---|---|
| Tela morta que não grava | Roda, grava, tem teste mínimo | Cobertura 80%/98%, E2E exaustivo |
| Só caminho feliz | Caminho feliz + erro principal | Toda borda, todo caso inválido |
| Sem teste nenhum | Fumaça + 1 feature por fluxo | TDD estrito, suíte completa por push |
| "Confia que funciona" | Idealizador valida no navegador | Validador independente, relatório formal |
| Stub escondido | Stub honesto e anotado | Integração real de produção |
| HTML cru e quebrado, visual amador | Aparência crível com Tailwind + kit pronto | Design system completo, identidade visual final |

Repare na **última linha**: a aparência é a única em que a coluna do meio **sobe** em relação ao "lixo" — feio não é uma opção de POC, porque o POC é mostrado a quem decide. O que fica para o MVP é só o acabamento de produção (design system, branding final), não o "parecer crível".

A coluna do meio é o POC. A coluna da direita é **MVP** — boa, necessária, **e deliberadamente adiada**. Não a antecipe; ela rouba o tempo de provar a ideia e afoga a pessoa não-técnica em rigor que ela não pediu.

## Sinais de que você saiu da mentalidade de POC

Pare e reavalie se você se pegar:

- Construindo autenticação robusta, papéis e permissões finas "por garantia".
- Criando abstração genérica para um caso que ainda não se provou.
- Configurando infra de produção, observabilidade, filas, cache.
- Perseguindo cobertura de teste completa antes mesmo de a ideia rodar.
- Refinando o visual de uma tela **antes** de o fluxo principal rodar e gravar — ordem errada (a aparência vem **depois** da função). Note: depois que grava, caprichar a aparência **não** é sair da mentalidade — é cumprir a barra.
- Migrando para Postgres "já que vamos precisar" — não vamos, ainda; SQLite é o trampolim (`stacks/database/sqlite/SKILL.md`).

Todos esses são trabalho **real e legítimo** — do **time de MVP**. No POC, são desperdício. Anote no handoff como "o que falta endurecer" e siga provando a ideia.
