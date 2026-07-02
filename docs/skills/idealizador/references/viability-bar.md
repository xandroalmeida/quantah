# A barra de viabilidade — quando o POC está "pronto"

> Esta é a reference central de **guardrails** do Idealizador. É a "definition of done" do POC: o piso mínimo inegociável que separa "prova de conceito de verdade" de "demo que engana". Cinco itens. Todos obrigatórios.

O POC tem um piso, não a barra profissional. O piso existe para você (agente) não entregar lixo a uma pessoa que não tem como avaliar o código. Mas ele é deliberadamente **baixo o suficiente** para não afogar essa pessoa em rigor de engenharia. Cumprir os cinco itens não é "fazer bem feito como o time faria" — é "fazer o mínimo honesto para a prova valer" (incluindo parecer crível, porque a prova é mostrada a quem decide).

## Os cinco itens do piso

### 1. Roda em algum lugar

**O que conta:**
- O app sobe com **um comando**: `./vendor/bin/sail up` (Laravel Sail) ou `php artisan serve`. Para SQLite, isso é viável de verdade — sem servidor de banco, sem container obrigatório (`stacks/database/sqlite/SKILL.md` › Infra).
- OU está numa hospedagem simples onde **qualquer pessoa abre um link** e usa.
- O Idealizador (não-técnico) consegue chegar ao app sem você por perto — um endereço e pronto.

**O que NÃO conta:**
- "Roda na minha máquina" depois de 12 passos manuais, variáveis de ambiente misteriosas, ou dependências não documentadas.
- Build que só você sabe fazer.
- App que depende de um serviço externo que precisa ser configurado a cada vez.

**Por quê:** se ninguém além de você consegue abrir o app, ele não prova viabilidade — prova que você sabe rodá-lo. O ponto do POC é mostrar a ideia funcionando para outras pessoas (sócios, investidores, primeiros usuários). O `README` do POC (`templates/poc-readme.md`) documenta o **um comando**.

### 2. Grava em banco

**O que conta:**
- O app **persiste de verdade** em SQLite. O Idealizador cria algo, **fecha o app, reabre, e o dado continua lá**.
- A persistência passa pelo ORM (Eloquent) com migrations versionadas — não SQL na mão (`stacks/database/sqlite/SKILL.md`, `stacks/laravel/SKILL.md`).
- `foreign_keys=ON` ligado (custa nada e mantém a integridade que o Postgres vai exigir depois).

**O que NÃO conta:**
- Dado fixo na tela (mock, array chumbado, JSON estático).
- Tela "morta" que parece funcionar mas não guarda nada.
- Estado só em memória que some quando o app reinicia.

**Por quê:** "grava de verdade" é o que separa um POC de um clicável de Figma. A ideia precisa ser provada com o app **fazendo a coisa**, não fingindo. E persistir em SQLite com migrations desde o dia 1 é o que deixa a graduação para Postgres barata (`references/graduation.md`).

### 3. Testes mínimos

**O que conta:**
- **Pelo menos um teste de fumaça:** o app sobe e a tela principal responde (em Laravel, um teste de feature que bate na rota raiz e espera 200).
- **Pelo menos um teste de feature por fluxo principal**, no caminho feliz: o fluxo acontece de ponta a ponta e **o dado é gravado** (`assertDatabaseHas` confirma a persistência).
- Tudo **automatizado** — Pest (preferência) ou PHPUnit (`stacks/laravel/SKILL.md` › Testes). Roda com `php artisan test` / `./vendor/bin/pest`.

**O que NÃO conta:**
- "Eu cliquei no app e funcionou" — teste manual não é teste automatizado.
- Nenhum teste ("é só um POC").
- Teste que não exercita a gravação (passa sem provar que persistiu).

**Por quê:** o teste mínimo não é burocracia — é a **rede que deixa o Idealizador mudar de ideia sem quebrar o que já funcionava** (ver `testing-for-non-coders.md`). Num POC, a pessoa **vai** iterar muito; sem a rede, cada mudança vira roleta-russa. Um punhado de testes de feature paga esse seguro barato.

### 4. Cobre fluxos alternativos mínimos

**O que conta:**
- Para **cada** fluxo principal, pelo menos **o caminho de erro/exceção mais importante** está **desenhado E testado**:
  - dado inválido (campo obrigatório vazio, formato errado),
  - lista/recurso vazio (o que o usuário vê quando não há nada),
  - recurso não encontrado (acessou algo que não existe).
- O teste prova o desfecho alternativo (ex.: submeter formulário vazio → erro mostrado, **nada gravado**).
- A tela tem o estado correspondente (mensagem de erro no campo, empty-state) — em Livewire isso é `@error` e `@if($items->isEmpty())` (`stacks/livewire/SKILL.md`).

**O que NÃO conta:**
- **Só caminho feliz.** Esta é a regra mais importante do piso: um POC que só faz a coisa dar certo **não está pronto**. Software real encontra dado ruim, lista vazia, clique errado — e a ideia só se prova se o app não quebrar feio diante disso.
- Erro que derruba o app (tela branca, stack trace cru).

**Por quê:** "só caminho feliz" é a ilusão mais comum de POC. Ela esconde se a ideia aguenta o uso real. Cobrir o erro principal de cada fluxo é o mínimo para a prova ser honesta — e é justamente onde o Idealizador descobre regras de negócio que nem sabia que tinha ("e se o cliente não preencher o telefone?"). Essas descobertas vão para o handoff.

### 5. Tem aparência apresentável e crível

**O que conta:**
- O POC **parece um produto de verdade** numa demo: visual consistente (espaçamento, tipografia, cores), telas alinhadas, hierarquia clara — nada de HTML cru ou layout quebrado.
- Usa **Tailwind + um kit de componentes pronto** da stack (`stacks/livewire/SKILL.md`): botões, formulários, cards, navegação com cara profissional **sem desenhar do zero**.
- Os estados que o item 4 exige (vazio, erro, carregando) também são **apresentáveis** — empty-state cuidado, mensagem de erro clara e bem posicionada.
- Funciona e parece bem no tamanho em que vai ser **demonstrado** (em geral desktop para investidor; mobile se a ideia for mobile).

**O que NÃO conta:**
- Tela sem estilo, layout quebrado, elementos desalinhados, "cara de protótipo de aula".
- "Depois a gente deixa bonito" — a aparência **é** parte da prova, porque o POC é mostrado a investidores e clientes. É o único item do piso que **não** é adiável como o resto do acabamento.
- No outro extremo, exagero também não conta: **design system completo, identidade visual final, telas pixel-perfect** é trabalho do Designer no MVP. O piso é *crível*, não *finalizado*.

**Por quê:** o POC é um **artefato de venda** — sócios, investidores e primeiros clientes decidem pelo que veem. Uma ideia boa numa demo amadora é descartada; a mesma ideia numa demo que parece um produto real é levada a sério. Como aqui a aparência sai **barata** (kit pronto da stack), não há desculpa para entregar feio. O que **não** entra no piso é o acabamento de produção (design system, branding) — isso o Designer faz no MVP.

## O que está fora do piso (deliberadamente adiado para o MVP)

Estes itens são **trabalho real e necessário** — feito pelo **time de MVP**, não no POC. Não os faça agora; anote o que for relevante como "lacuna conhecida / o que falta endurecer" no pacote de handoff (`templates/handoff-packet.md`).

| Fora do piso de POC | De quem é (no MVP) |
|---|---|
| Cobertura 80% geral / 98% núcleo | Programador (`programador/references/quality-standards.md` via PO) |
| E2E em browser real (Dusk), todos os caminhos | Programador + Validador |
| Toda borda e todo caso inválido testado | Programador (4 categorias) |
| Segurança de produção (auth/autorização robusta) | Arquiteto + Programador |
| Observabilidade (logs estruturados, métricas, health) | Programador |
| Escala, performance, filas, cache | Arquiteto |
| Infra de produção, CI/CD maduro | Arquiteto |
| **SQLite → Postgres** | Arquiteto (ADR — `stacks/database/sqlite/SKILL.md`) |
| Design system completo, identidade visual/branding, telas pixel-perfect | Designer |

> Atenção a esta última linha: o que vai para o MVP é o **acabamento de produção** da aparência — não a aparência em si. O *parecer crível* (item 5) é piso de POC; o *design system finalizado* é MVP. Não use "design é do Designer" como desculpa para entregar um POC feio.

A regra de ouro: **se a sub-skill de stack descreve como cuidado de produção, é do MVP.** O POC usa as sub-skills só na profundidade do essencial (ver `idealizador/SKILL.md` › stack do POC).

## Checklist rápido — o POC está pronto?

Antes de chamar um POC de "pronto para a decisão de viabilidade", confirme, **para o conjunto inteiro**:

- [ ] Sobe com **um comando** e qualquer pessoa abre (item 1).
- [ ] Fecha e reabre — o dado **continua lá** (item 2).
- [ ] `php artisan test` passa: fumaça + **um feature por fluxo principal** (item 3).
- [ ] **Cada** fluxo principal tem o erro mais importante **desenhado e testado** (item 4).
- [ ] **Parece um produto** numa demo: visual consistente com kit pronto, estados (vazio/erro/carregando) apresentáveis (item 5).
- [ ] O pacote de handoff (`docs/prototipo/`) está atualizado, incluindo o que é stub/faked e as lacunas conhecidas.
- [ ] O `README` do POC documenta o "um comando" e o que é real vs. encenado.

Se algum item está aberto, o POC **não está pronto** — falta piso. Não suba a barra acima disso por iniciativa própria (isso afoga a pessoa e rouba tempo); não desça abaixo disso por pressa (isso entrega prova falsa).
