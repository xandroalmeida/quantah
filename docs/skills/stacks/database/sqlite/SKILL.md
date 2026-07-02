---
name: stack-sqlite
description: Sub-skill de stack — SQLite. Conhecimento idiomático e opinativo de SQLite que os papéis (Arquiteto, Programador, Validador, Designer) consultam quando SQLite faz parte da stack ativa do projeto (ver _project.md › Stack ativa). Complementa o método dos papéis com o como-fazer específico de SQLite; não o substitui.
---

> **Sub-skill de stack.** Ativada quando SQLite está na stack do projeto (`_project.md` › Stack ativa). Os papéis trazem o método; esta sub-skill traz o idiomático de SQLite.

# SQLite — sub-skill de stack

SQLite é o **default de banco quando o projeto é POC/protótipo**. Ele é genuinamente excelente no que faz — um banco relacional ACID completo dentro de um único arquivo, sem servidor, sem porta, sem credencial, sem operação. Para validar uma ideia rápido, é difícil bater.

**A tese desta sub-skill em uma frase:** SQLite é **trampolim**, não destino. Ele te leva do zero ao protótipo funcionando em minutos; mas quando o projeto passa de POC — concorrência de escrita real, múltiplos processos, deploy sério —, a decisão correta é **migrar para Postgres** (o default deste template; ver `stacks/database/postgres`). O trabalho desta sub-skill é deixar essa migração **barata**: você escreve o código POC de forma que a troca seja uma ADR e uma config, não uma reescrita.

## Quando esta stack se aplica (e quando NÃO)

**Aplica-se quando:**
- O projeto é **POC, protótipo, spike ou MVP descartável** — validar hipótese, mostrar fluxo, testar UX antes de comprometer infra.
- **Single-writer**: um processo (ou pouquíssimos) escreve por vez. Leitura concorrente é tranquila; escrita concorrente é o limite real.
- O dado cabe e roda local sem necessidade de servidor de banco separado.
- Você quer **zero operação**: sem container de banco, sem backup configurado, sem pool — o arquivo é o banco.

**NÃO se aplica (hora de migrar para Postgres) quando:**
- Há **concorrência de escrita** de verdade — múltiplos workers/requests gravando ao mesmo tempo. SQLite serializa escrita (um writer por vez, lock no banco inteiro); sob carga isso vira `SQLITE_BUSY` e fila.
- O deploy é **multi-processo / multi-instância** (vários app servers, autoescala). Um arquivo local não é compartilhável entre instâncias de forma sã.
- O projeto **saiu de POC** e vai para produção com usuários reais. Esse é o gatilho: **abra uma ADR para migrar a Postgres** antes que a dor apareça em produção.
- Você precisa de capacidades que o SQLite não tem ou tem fracas: `jsonb` indexável robusto, full-text avançado, extensões tipo `pgvector`/`PostGIS`/`pg_trgm`, RLS, replicação, tipos ricos com checagem forte.

## O que SQLite já entrega (não reinvente)

SQLite é mais capaz do que a fama de "banco de brinquedo" sugere — use o que ele dá antes de inventar:

| Capacidade | Como o SQLite faz | Observação |
|---|---|---|
| ACID transacional | transações nativas, rollback, savepoints | Sólido; é um banco de verdade |
| Concorrência de leitura | modo **WAL** (`PRAGMA journal_mode=WAL`) permite leitores enquanto há um writer | Ligue WAL — melhora muito a leitura concorrente |
| Integridade referencial | FK constraints — **mas off por default** | `PRAGMA foreign_keys=ON` em **toda** conexão, senão a FK é ignorada silenciosamente |
| Full-text search | extensão `FTS5` | Boa para busca simples; não é o full-text do Postgres |
| JSON | funções `json_*` / operador `->`,`->>` | Funciona para ler/escrever; indexação é limitada vs `jsonb` |
| Índices | B-tree, parciais, em expressão | Cobre o básico bem |
| Arquivo único | o banco é um `.db` no disco | Backup = copiar o arquivo (com o banco quiescente ou via `.backup`) |

**Importante:** ao usar `FTS5`, `json_*` ou qualquer feature específica do SQLite, você está criando acoplamento que **dói na migração**. Veja "Caminho de migração" abaixo.

## Padrões idiomáticos

> O **método** (mentalidade; convenções de modelagem; ORM × SQL bruto; N+1; índices em FK; transações curtas; locking; migrations reversíveis; paginação; soft delete; etc.) vive em `stacks/database/database-method.md` e vale igual aqui. Esta seção traz só a **expressão no SQLite** — e os cuidados que existem *porque* é SQLite (tipagem fraca, FK-off-por-default, escrita serializada).

### Expressão no SQLite das convenções de modelagem

Cada convenção do method → como se escreve no SQLite (sempre mirando a migração barata para o Postgres):

| Convenção (porquê em `database-method.md`) | Expressão no SQLite |
|---|---|
| Chaves primárias UUIDv7 | **`TEXT`** (não há tipo `uuid`), gerado no app (`Str::uuid7()`); na migração essa coluna vira `uuid` no Postgres. Não use `INTEGER AUTOINCREMENT` nem UUIDv4 |
| Dinheiro/valores exatos | **inteiro em centavos** — SQLite não tem decimal de verdade; nada de `REAL` para dinheiro (arredonda e vira erro silencioso) |
| Tempo com fuso | **ISO-8601 em UTC** como `TEXT` (`2026-01-31T14:00:00Z`) — ordenável e migra limpo para `timestamptz`; converta para o fuso do usuário só na apresentação |
| Invariantes no banco | `NOT NULL` por padrão; `UNIQUE`/`CHECK`/`FK` (com `PRAGMA foreign_keys = ON`, senão a FK é decorativa) |
| Chave natural + soft delete | **índice único parcial** (suportado): `CREATE UNIQUE INDEX ... ON usuarios(email) WHERE deleted_at IS NULL`; case-insensitive via **`COLLATE NOCASE`** |
| Enums | **`text` + `CHECK (status IN ('aberto','pago',...))`** (ou o enum do framework) — a mesma forma que migra direto para o Postgres |
| Nomes consistentes | snake_case, tabela no plural (`pedidos`), FK `<tabela>_id` (`cliente_id`) |

### Cuidados que existem *porque* é SQLite

**Ligue os PRAGMAs certos em toda conexão.** SQLite tem defaults conservadores herdados de décadas. No mínimo:
- `PRAGMA foreign_keys = ON;` — FK é **desligada por default**; sem isso suas constraints são decorativas.
- `PRAGMA journal_mode = WAL;` — leitura concorrente decente; reduz lock de leitura durante escrita.
- `PRAGMA busy_timeout = 5000;` — em vez de falhar na hora com `SQLITE_BUSY`, espera o writer liberar.

Frameworks opinativos com ORM frequentemente já aplicam parte disso; **confirme** — não assuma.

**Cuidado com a tipagem fraca (type affinity).** SQLite usa *type affinity*, não tipos rígidos: você declara `INTEGER` e ele aceita uma string sem reclamar. Isso é uma armadilha que **não existe** no Postgres. Defesa: valide tipos na camada de aplicação/ORM, e nunca confie no banco para barrar tipo errado. Esse é um dos pontos onde **"passou no SQLite" não significa "passa no Postgres"**.

**Escrita é serializada — projete para isso.** Há um writer por vez no banco inteiro. Transações de escrita devem ser **curtas**; nada de I/O externo dentro (mesma regra do method, mas aqui a penalidade é maior porque a escrita já é o gargalo). Não tente paralelizar gravação — não vai escalar; se você *precisa* paralelizar escrita, esse é o sinal de migrar.

**Migration aplicada é imutável** (vale igual ao method): corrija com uma migration **nova** — nunca edite uma que já rodou.

**Use ORM/migrations — e fuja de SQL específico do SQLite.** Este é o hábito que mantém a migração barata: trabalhe via ORM e migrations geradas, escrevendo SQL agnóstico. Evite sintaxe SQLite-only (`AUTOINCREMENT`, quirks de `INSERT OR REPLACE`, datas como texto solto, `FTS5`) a menos que registrado como dívida consciente. Quando precisar de algo que só o SQLite faz, **pare e pergunte se não é a hora de migrar**.

## Caminho de migração para Postgres (sem dor)

O design POC correto torna a troca trivial. Regras:

1. **Código agnóstico de banco.** Toda persistência passa pelo ORM. Zero SQL bruto com dialeto SQLite espalhado pelo código. Se precisar de SQL bruto, isole em um lugar e marque que terá variante por banco.
2. **Migrations desde o dia 1**, mesmo em POC. Schema versionado em código (não criado à mão no `.db`) é o que permite recriar a estrutura no Postgres sem arqueologia.
3. **Tipos explícitos e disciplinados.** Como o SQLite não força tipo, *você* força — no modelo/ORM. Datas como tipo de data (não string), booleano como booleano, dinheiro como inteiro/decimal consciente. Assim o Postgres, que é rígido, não rejeita seu dado na migração.
4. **Evite features SQLite-only** ou registre-as como dívida em IDR (`programador/templates/idr.md`), sabendo que terão equivalente Postgres na migração (`FTS5` → `tsvector`/`pg_trgm`; `json_*` → `jsonb`).
5. **Nunca dependa do comportamento de FK-desligada-por-default.** Rode com `foreign_keys=ON` desde sempre, para que a integridade que o Postgres vai exigir já valha no POC.
6. **A migração em si** é uma ADR: recriar schema via migrations no Postgres, exportar/importar dados (cuidando de tipos, sequences/IDs, datas), revisar índices (incluindo FK, que o Postgres também não indexa sozinho), e validar a suíte de testes contra Postgres real.

**O gatilho explícito:** quando o projeto deixar de ser POC — primeiro usuário real de produção, necessidade de concorrência de escrita, deploy multi-instância —, **abra ADR para migrar a Postgres**. Não espere a dor chegar em produção; o custo de migrar cresce com o volume de dado e de código acoplado.

## Testes nesta stack

Conecta com `programador/SKILL.md` (gates de teste) e `validador/`. Aqui mora **o aviso mais importante desta sub-skill**:

> **SQLite em memória é rápido, mas pode divergir do Postgres em produção.** Se o destino do projeto é Postgres, testar exclusivamente contra SQLite (`:memory:`) é uma armadilha: tipagem fraca, FK off por default, `jsonb`/full-text/funções de data diferentes, locking diferente, constraints e tipos que se comportam de outro jeito. Um teste verde no SQLite **não prova** que o código passa no Postgres. Isso é a versão de teste do mesmo gotcha que o `stacks/database/postgres` descreve sobre superuser local: o ambiente de teste mente sobre produção.

Orientação prática:
- **Enquanto o projeto é genuinamente POC SQLite-em-produção**, testar contra SQLite (idealmente o **mesmo arquivo/engine** que roda, não só `:memory:`) é coerente. SQLite em memória é ótimo para velocidade da suíte.
- **No momento em que a decisão for migrar a Postgres** (mesmo que o POC ainda rode em SQLite por um tempo), a suíte de CI passa a rodar contra **Postgres real** — porque é nele que o produto vai viver. Testar no banco de produção é a regra; ver `stacks/database/postgres` › Testes.
- Use **transação-por-teste com rollback** e **factories** (mesmo padrão do Postgres) para isolamento e velocidade.
- **FK ligada nos testes.** Garanta `foreign_keys=ON` no ambiente de teste, senão testes de integridade passam falsamente.
- **Banco de teste é um arquivo separado do de dev.** Método em `database-method.md` › "Bancos segmentados por ambiente": o dev usa `database/database.sqlite` (seu dado de trabalho, inclusive o que a pessoa não-técnica cadastrou à mão); a suíte usa um **arquivo à parte**, `database/testing.sqlite`, apontado por `phpunit.xml` (e por `.env.dusk.local` no E2E). Sem isso, `RefreshDatabase`/Dusk apaga o `database.sqlite` de dev a cada rodada. `:memory:` é ótimo para os feature tests rápidos, mas o **Dusk precisa de um arquivo real** — use `testing.sqlite` para ele. O `.gitignore` do projeto já ignora `database/*.sqlite`, então `testing.sqlite` não vai para o git.

## Infra / operação / deploy

Conecta com `arquiteto/`. A grande vantagem operacional do SQLite é não ter operação:

- **Arquivo único.** O banco é um `.db` no disco da aplicação. Sem servidor, sem porta, sem container de banco, sem credencial, sem pool de conexão. Setup local é trivial — reforça o princípio "funcionamento 100% local" (#6 do Arquiteto) de graça.
- **Backup trivial — com um cuidado.** Backup é copiar o arquivo; mas copie com o banco quiescente ou use o `.backup` / a API de backup online do próprio SQLite — copiar o `.db` no meio de uma escrita pode gerar arquivo inconsistente. Em WAL, lembre dos arquivos `-wal` e `-shm` associados.
- **Não é multi-instância.** O arquivo vive numa máquina. Escalar horizontalmente (vários app servers) com um SQLite compartilhado via rede é caminho de sofrimento — é justamente o sinal de migrar para Postgres.
- **Persistência do arquivo em deploy.** Em ambientes com filesystem efêmero (containers que reiniciam, plataformas serverless), o `.db` some no redeploy se não estiver em volume persistente. Para POC isso pode até ser aceitável; para qualquer dado que importe, é mais um motivo de migrar.

## Defaults deste template (e como divergir via ADR)

- **SQLite é default apenas para POC/protótipo.** Para qualquer coisa que vá a produção com usuários reais, o default do template é **Postgres** (`stacks/database/postgres`, `_project.md` › `PostgreSQL`).
- **PRAGMAs ligados sempre:** `foreign_keys=ON`, `journal_mode=WAL`, `busy_timeout`.
- **ORM + migrations desde o dia 1**, código agnóstico de banco.
- **Tipos disciplinados na aplicação**, sem confiar na type affinity do SQLite.

Para divergir — manter SQLite além do POC, usar uma feature SQLite-only, ou (o caminho esperado) **migrar a Postgres** — abra **ADR** (`arquiteto/templates/adr.md`). A migração para Postgres é a divergência *prevista* desta sub-skill, não uma exceção: registre-a como ADR quando o gatilho de "saiu de POC" disparar.

## Armadilhas conhecidas (gotchas)

- **FK desligada por default.** Sem `PRAGMA foreign_keys=ON` em **cada** conexão, o SQLite **ignora** suas foreign keys silenciosamente — dado órfão entra sem erro. Pegadinha número um, e a que mais surpreende quem vem do Postgres.
- **Tipagem fraca (type affinity).** O SQLite aceita string numa coluna `INTEGER` sem reclamar. Validação tem que estar na aplicação; o banco não te protege. Esse dado mal-tipado vira erro na migração para o Postgres (rígido).
- **`SQLITE_BUSY` sob escrita concorrente.** Um writer por vez no banco inteiro. Sem `busy_timeout` e sem WAL, escrita concorrente falha de cara. Mesmo com eles, é um teto — não uma solução de escala.
- **Testar só em SQLite quando o destino é Postgres.** Verde no SQLite não prova verde no Postgres. Ver seção Testes — é o gotcha mais caro porque dá falsa confiança.
- **Cópia de `.db` durante escrita corrompe o backup.** Use `.backup`/API de backup; em WAL, não esqueça `-wal`/`-shm`.
- **Filesystem efêmero apaga o banco.** Em container/serverless sem volume persistente, o `.db` desaparece no redeploy.
- **Features SQLite-only viram dívida de migração.** `FTS5`, `json_*`, `AUTOINCREMENT`, datas como texto — cada uso acopla ao SQLite e cobra na hora de ir para o Postgres. Use com consciência (IDR) ou evite.
