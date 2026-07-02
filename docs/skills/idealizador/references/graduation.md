# Graduação POC → MVP — o gate e a cerimônia de handoff

> Este é o momento em que o Idealizador entrega o bastão. Esta reference descreve o gate (quando graduar), o que muda no projeto, a cerimônia de handoff, como o PO usa o pacote, e o que o Idealizador vira depois. Leia antes de conduzir qualquer graduação.

A graduação é a transição da fase **POC** para a fase **MVP** (`_project.md` › `phase`). É binária: ou o projeto está provando a ideia (POC, só o Idealizador atua), ou está virando produto de verdade (MVP, os 5 papéis profissionais assumem). Não há meio-termo nem fase híbrida.

## O gate: quando graduar

**O critério é um só: a ideia se provou comercialmente viável.** E **a decisão é do humano** — Alexandro, não do agente. O Idealizador (e você, agente) **não promove o projeto sozinho**. Você prepara o terreno e sinaliza; quem puxa o gatilho é a pessoa dona do produto.

O que "provou-se viável" significa é uma decisão de negócio, não técnica: há sinal de mercado, de demanda, de disposição a pagar — o suficiente para justificar investir em construir o produto de verdade. Isso varia por projeto; não é você quem julga.

**Pré-condição técnica para graduar bem:** a barra de viabilidade está cumprida (`viability-bar.md` — roda, grava, testes mínimos, fluxos alternativos mínimos) **e** o pacote de handoff está completo e atualizado. Graduar com um POC pela metade entrega ao time um ponto de partida ruim. Seu papel é garantir que, **quando** o humano decidir graduar, o handoff esteja pronto.

> Cuidado simétrico: **não force a graduação** porque "já está bonito". Um POC pode rodar lindo e a ideia ainda não ter se provado. O gate é viabilidade **comercial**, não completude técnica.

## O que muda na graduação

### 1. A fase no `_project.md`

`phase: POC` → `phase: MVP`. A partir desse instante:

- O **Idealizador para de programar.** Você não escreve mais código de produto. Vira fonte de domínio (ver abaixo).
- Os **5 papéis acordam.** PO, Arquiteto, Designer, Programador, Validador entram em cena com todo o seu rigor (TDD, ADRs, cobertura, E2E, validação independente).

### 2. O regime de qualidade

O piso de POC dá lugar à barra profissional. O que era "deliberadamente adiado" agora é trabalho ativo do time — e nada disso é mais do Idealizador:

| Adiado no POC | Agora é de | Onde |
|---|---|---|
| **SQLite → Postgres** | Arquiteto (via ADR) | `stacks/database/sqlite/SKILL.md` › caminho de migração; `stacks/database/postgres` |
| Endurecimento (auth robusta, autorização fina) | Arquiteto + Programador | `programador/references/security-discipline.md` |
| Cobertura completa (80%/98%), todas as bordas | Programador | `programador/references/testing-discipline.md` |
| E2E em browser real (Dusk), todos os caminhos | Programador + Validador | `stacks/livewire/SKILL.md` › Testes |
| Observabilidade, escala, infra de produção, CI/CD | Arquiteto + Programador | `arquiteto/`, `programador/references/observability-discipline.md` |

> **A migração SQLite → Postgres e o endurecimento NÃO são trabalho do Idealizador.** São do time de MVP (Arquiteto abre a ADR de migração; Programador endurece). O Idealizador escolheu SQLite + migrations desde o dia 1 justamente para essa migração ser barata — mas executá-la é do time.

## A cerimônia de handoff

A graduação é marcada por uma entrega concreta. O Idealizador entrega ao time **duas coisas**:

1. **O pacote de handoff** (`docs/prototipo/`) — completo e em linguagem simples: ideia e valor, personas, fluxos (com alternativas), telas, entidades de dados, regras de negócio descobertas, o que está stub/faked, lacunas conhecidas, como rodar (`templates/handoff-packet.md`).
2. **O app do POC rodando** — sobe com um comando, grava em SQLite, com seus testes mínimos verdes.

A entrega não é "joga por cima do muro". O Idealizador apresenta ao time, percorre os fluxos no app rodando, e fica disponível para perguntas. O **código** pode ser endurecido ou reescrito pelo time — mas o **entendimento** capturado no handoff é o que não pode se perder (`poc-mindset.md` › descartável-consciente).

## Como o PO usa o pacote de handoff

Aqui está a continuidade que dá sentido a todo o trabalho do Idealizador: **o pacote de handoff em `docs/prototipo/` é exatamente o protótipo/fonte de verdade que o PO já foi desenhado para ler** ao nascer um projeto.

Quando o PO assume (`po/SKILL.md`), sua **primeira responsabilidade concreta** é, literalmente:

1. **Entender o protótipo a fundo** — abrir `docs/prototipo/`, percorrer os fluxos de Colaborador e Analista B2B, capturar o vocabulário do domínio.
2. **Criar as especificações** em `docs/especificacao/` a partir do que o protótipo demonstra — vocabulário, regras, fluxos, telas, estados.
3. **Planejar a implementação** em ondas/épicos/estórias (com a estória de spike de arquitetura para o Arquiteto, que decidirá a migração para Postgres).

Enquanto a especificação consolidada não existir, **o pacote de handoff é a fonte de verdade do projeto** — exatamente como o PO espera. Por isso o Idealizador o mantém à altura: nomes claros, regras explícitas, fluxos com alternativas, honestidade sobre stubs. Um handoff bom faz o MVP começar com o pé direito; um handoff vago faz o PO arqueólogo reconstruir do zero o que o POC já sabia.

## O que o Idealizador vira depois

Graduado o projeto, o Idealizador **não some** — muda de papel:

- **Vira fonte de domínio / stakeholder.** Continua disponível para responder "por que essa regra existe?", "o que o usuário esperava ver aqui?", "qual o caso de erro que mais aparecia?". É a memória viva do que o POC descobriu.
- **Para de programar.** Não escreve mais código, não decide implementação. Quando tem uma ideia nova de produto, ela entra como **desejo/wishlist** ou conversa com o PO (`po/references/wishlist.md`) — não como código que ele mesmo escreve.
- **Aprova produto, não técnica.** Se Alexandro e o Idealizador são a mesma pessoa, ela agora atua pelos papéis profissionais conscientemente — quando é PO, decide produto; quando precisa de decisão técnica, escala para Arquiteto (a disciplina de "cruzar papéis conscientemente" do `po/SKILL.md`).

## Checklist da graduação

Antes de virar a chave `phase: POC` → `phase: MVP`:

- [ ] Alexandro **decidiu** que a ideia se provou comercialmente viável (decisão de negócio, registrada).
- [ ] A barra de viabilidade está cumprida (`viability-bar.md`).
- [ ] O pacote de handoff (`docs/prototipo/`) está completo, atualizado e em linguagem simples — incluindo stubs e lacunas conhecidas.
- [ ] O app do POC sobe com um comando e os testes mínimos estão verdes.
- [ ] O Idealizador apresentou o handoff ao time e ficou disponível para perguntas.
- [ ] `_project.md` atualizado: `phase: MVP`.

Feito isso, o Idealizador entra em modo stakeholder e o PO toca o Fluxo 0. O POC cumpriu seu papel: provou a ideia **e** deixou o ponto de partida pronto.
