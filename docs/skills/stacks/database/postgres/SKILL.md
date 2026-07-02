---
name: stack-postgres
description: Sub-skill de stack — PostgreSQL. Conhecimento idiomático e opinativo de PostgreSQL que os papéis (Arquiteto, Programador, Validador, Designer) consultam quando PostgreSQL faz parte da stack ativa do projeto (ver _project.md › Stack ativa). Complementa o método dos papéis com o como-fazer específico de PostgreSQL; não o substitui.
---

> **Sub-skill de stack.** Ativada quando PostgreSQL está na stack do projeto (`_project.md` › Stack ativa). Os papéis trazem o método; esta sub-skill traz o idiomático de PostgreSQL.

# PostgreSQL — sub-skill de stack

Postgres é o **default de banco deste template** — e a posição não é tímida. O argumento "Postgres-first" mora aqui, como conhecimento da stack, e não no método do Arquiteto: o Arquiteto sabe *como decidir armazenamento*; esta sub-skill sabe *o que o Postgres entrega* para alimentar essa decisão. Quando Postgres está na stack ativa, o Arquiteto avalia toda proposta de armazenamento adicional contra o que está escrito aqui.

## Postgres-first: prove com números antes de adicionar outro armazenamento

> O Postgres faz muito mais do que se imagina. Antes de adicionar **qualquer** outro armazenamento ou serviço de dados, **prove com números** que o Postgres não dá conta.

**Por quê.** Cada novo serviço de dados (Redis, ElasticSearch, MongoDB, vector DB dedicado, fila externa, S3 para dado quente) adiciona um sistema novo pra operar, outra fonte de consistência pra raciocinar, outro backup pra cuidar, outra fonte de incidente, outra biblioteca cliente, outra credencial, outra cobrança no mês. Boa parte da complexidade de stacks modernas vem de adicionar serviços para resolver problemas que o Postgres já resolveria bem. Antes de "vamos adicionar X", a pergunta é: **o Postgres não faz isso?**

Esta posição reforça os princípios centrais do Arquiteto (`arquiteto/references/architecture-principles.md`): "Simples é o belo" (#1) e "Tudo começa em monolito" (#2). Um Postgres bem exercido é uma peça móvel a menos.

**Como aplicar (Arquiteto).**
- Toda ADR que propuser armazenamento adicional **deve** começar com uma seção "Por que o Postgres não dá conta", com evidência: números medidos, requisitos quantificados, restrição arquitetural específica. Sem isso, a ADR é rejeitada na origem.
- Saída legítima: volume concreto, latência mensurada, ou restrição que o Postgres genuinamente não atende.
- Saída ilegítima: "Redis é mais rápido pra cache" sem benchmark do caso real; "MongoDB é mais flexível pra JSON" (Postgres tem `jsonb` indexável); "ElasticSearch é o padrão pra busca" sem dimensionar a carga; "precisamos de vector DB pra IA" (`pgvector` resolve a maioria absoluta); "precisamos de fila" (Postgres faz fila bem para muitos casos).
- Quando uma **extensão** resolve, prefira a extensão: você ganha tudo que o Postgres já dá (backup, replicação, transações ACID, observabilidade, ferramentas) sem adicionar sistema.

**Não é dogma cego.** Há casos legítimos onde outra ferramenta vence mesmo com o Postgres "tecnicamente dando conta": ergonomia dramaticamente superior (fila gerenciada com retry/DLQ embutidos), operação muito mais simples (S3 para arquivos vs `BYTEA`), prazo apertado real (solução pronta vs semanas de implementação). Nesses casos, registre o trade-off **honestamente** no ADR — "Postgres faria, mas X vence por Y motivo concreto". O que viola o princípio é fingir que o Postgres não dá conta quando dá, ou cair em "Postgres é dogma, vamos sofrer".

## Quando esta stack se aplica (e quando NÃO)

**Aplica-se** quando o projeto precisa de um banco relacional ACID durável, multi-conexão, com requisitos de integridade referencial, transações reais e crescimento de volume — que é o caso da esmagadora maioria dos produtos. É o default para qualquer sistema que vá além de POC.

**NÃO se aplica (ou pesa mal)** quando:
- O projeto é POC/protótipo de vida curta, single-user, sem concorrência de escrita → veja a sub-skill `stacks/database/sqlite`.
- A necessidade real é exclusivamente analítica em escala massiva (data warehouse colunar) — outro tipo de banco, outra ADR.
- O domínio genuinamente exige bilhões de vetores com latência sub-ms, ingest de time-series acima do que o Timescale aguenta, ou volume sustentado de fila muito alto — casos raros, sempre comprovados com número.

## O que o Postgres já entrega (não reinvente)

Catálogo do que o Postgres faz nativamente ou com extensão estável. Antes de adicionar sistema, confira esta tabela:

| Capacidade | Como o Postgres faz | Pular para outra coisa só se |
|---|---|---|
| Fila / job queue | `SELECT ... FOR UPDATE SKIP LOCKED` + tabela + worker | Volume sustentado > ~10k msg/s ou roteamento complexo entre múltiplos consumidores |
| Full-text search | `tsvector` + `tsquery` + índice GIN | Volume massivo + ranking sofisticado (e ainda assim avalie pg + extensões antes) |
| JSON queryable | `jsonb` + operadores + índices GIN | Praticamente nunca |
| Vector / embeddings | extensão `pgvector` | Bilhões de vetores com latência sub-ms (raríssimo) |
| Time-series | extensão `TimescaleDB` | Ingest sustentado acima do que o Timescale aguenta |
| Busca por similaridade textual | extensão `pg_trgm` | Casos de fuzzy match muito específicos |
| Cache temporário | `UNLOGGED TABLE` ou índice quente | Cache realmente compartilhado entre processos com TTL agressivo |
| Geo / spatial | extensão `PostGIS` | Praticamente nunca |
| Cálculo geo simples (distância, raio) | fórmula no app, sem extensão | Quando precisar de índice espacial / match geo → PostGIS |
| Audit / event log | tabela append-only + triggers | Volume massivo com replay sofisticado |
| Pub/Sub leve | `LISTEN` / `NOTIFY` | Pub/Sub durável, multi-consumer com replay e particionamento |
| Read replicas | replicação nativa | Quando latência de réplica é problema de domínio |
| Criptografia at-rest em coluna | extensão `pgcrypto` (ou no nível de app) | Requisito de gestão de chave que exija KMS dedicado |
| Multi-tenancy com isolamento forte | Row-Level Security (RLS) | Quando RLS não cobre o modelo de isolamento (raro) |

Cruza com `arquiteto/references/integration-architecture.md` (cálculo geo simples vs PostGIS) e `security-architecture.md` (RLS, pgcrypto, append-only via trigger).

## Padrões idiomáticos

> O **método** (a mentalidade; convenções de modelagem; ORM × SQL bruto; N+1; quando criar índice; transações; locking; migrations seguras e em volume; paginação offset × cursor; soft delete; JSON; full-text; operações pesadas; connection pooling) vive em `stacks/database/database-method.md`. Aqui fica só a **expressão no Postgres** — tipos, sintaxe e gotchas concretos. O *porquê* de cada regra está no method; não o repetimos.

### Expressão no Postgres das convenções de modelagem

Cada convenção do method → como se escreve no Postgres:

| Convenção (porquê em `database-method.md`) | Expressão no Postgres |
|---|---|
| Chaves primárias UUIDv7 | tipo nativo **`uuid`** (não `text`); gerado no app (`Str::uuid7()`) ou no banco onde houver `uuidv7()` (Postgres 18+) |
| Dinheiro/valores exatos | **`numeric(p, s)`** (cálculo contábil) ou **inteiro em centavos** (`bigint` quando performance importa) — nunca `float`/`double` |
| Tempo com fuso | **`timestamptz`** (UTC, ciente de fuso) — nunca `timestamp` sem fuso; data sem hora em `date` |
| Texto | **`text`** em vez de `varchar(n)` (armazenamento idêntico no Postgres; `varchar(n)` só acrescenta checagem de comprimento que vira migração chata); limite real de negócio via `CHECK (char_length(col) <= n)` ou no app |
| Invariantes no banco | colunas **`NOT NULL` por padrão**; `UNIQUE`/`CHECK`/`FK` para o que o domínio exige |
| Chave natural + soft delete | **índice único parcial** `CREATE UNIQUE INDEX ON usuarios (email) WHERE deleted_at IS NULL`; case-insensitive via `citext` ou índice em `lower(email)` |
| Enums | **`text` + `CHECK (status IN ('aberto','pago',...))`** (ou o enum do framework com cast) — não o tipo `ENUM` nativo, chato de evoluir |
| Tipos certos + nomes | `boolean` (não `0/1`), `date`/`timestamptz`, `numeric`, `inet`, `jsonb`; snake_case, tabela no plural, FK `<tabela>_id` |

### Idiomático Postgres (o resto do method, expresso aqui)

- **Índices.** O Postgres **não** indexa FK automaticamente — indexe **toda FK** (pegadinha clássica). GIN para `tsvector` e para `jsonb` queryado. **`EXPLAIN ANALYZE`** é o comando para ler o plano (full scan? índice usado? nested loop caro?).
- **Transações / isolation.** Default do Postgres é **`READ COMMITTED`** (OK para a maioria); para concorrência crítica considere `SERIALIZABLE` ou locking explícito — e registre em IDR (`programador/templates/idr.md`).
- **Locking / fila.** Pessimistic via `SELECT ... FOR UPDATE`; **`SKIP LOCKED`** é o padrão para job queue (worker pega o próximo job não-locked sem bloquear outros).
- **`jsonb` (não `json`).** Use `jsonb` para schema flexível — índice GIN, indexação por chave, operadores `->`, `->>`, `@>`. `json` guarda texto cru (sem dedup, sem GIN, reparse a cada operação).
- **Migrations em volume.** `ADD COLUMN NULL` + backfill em batches + depois `NOT NULL` (três passos, não um `UPDATE`); **`CREATE INDEX CONCURRENTLY`** para não travar escrita; expand-contract para rename; `DROP COLUMN/TABLE` praticamente irreversível; **`ANALYZE`** após backfill grande para o planner ter estatística atualizada.
- **Connection pooling.** Pool integrado do framework; para alto volume considere **`pgbouncer`** (decisão arquitetural, não local).


## Testes nesta stack

Conecta com a disciplina de testes de `programador/SKILL.md` (gates inegociáveis) e a verificação de `validador/`. O idiomático Postgres:

- **Teste contra Postgres real, não substituto.** Rode os testes contra um Postgres de verdade (container em dev/CI, idêntico em major version ao de produção). Substituir por SQLite "porque é mais rápido" mascara divergências de comportamento (tipos, `jsonb`, full-text, constraints, locking) e leva ao bug que só aparece em produção. Se a stack ativa é Postgres, teste em Postgres.
- **Banco de teste dedicado, separado do de dev.** Método em `database-method.md` › "Bancos segmentados por ambiente": a suíte (unit, integração e E2E) roda num **banco `testing`** próprio, nunca no banco de dev — senão o reset/truncate/E2E apaga seu dado de trabalho. No **Laravel Sail** esse banco já vem pronto: o serviço `pgsql` monta `create-testing-database.sql` em `docker-entrypoint-initdb.d` e **cria o banco `testing` automaticamente** ao subir o container; os testes o alcançam via `phpunit.xml`/`.env.dusk.local` (ver `stacks/laravel/SKILL.md` › Testes). No caminho "nativo + Postgres em container", crie o segundo banco você mesmo (`CREATE DATABASE <app>_testing;` ou `createdb`).
- **Isolamento por teste via transação.** O padrão idiomático: cada teste roda dentro de uma transação que é revertida (`ROLLBACK`) ao final — banco sempre limpo, testes rápidos, sem vazamento de estado entre eles. A maioria dos frameworks opinativos já oferece isso (rollback automático por teste). Quando o teste precisa de commit real (ex.: testar trigger pós-commit, `LISTEN/NOTIFY`), use truncate/recreate consciente em vez de transação.
- **Factories, não fixtures gigantes.** Use factories (factory_boy, FactoryBot, Mommy/Bakery, etc.) para construir só o dado relevante a cada teste. Fixtures estáticas globais apodrecem e acoplam testes.
- **Prove bug/fix de privilégio com role não-superuser** (ver gotchas) — `EXPLAIN ANALYZE` e privilégio se comportam diferente conectado como superuser.
- **Migrations entram na suíte.** A suíte de CI roda as migrations do zero (e idealmente o `down`/`up`) contra Postgres limpo — uma migration que só roda na sua máquina não está testada.

## Infra / operação / deploy

Conecta com `arquiteto/` (NFR, deploy, observabilidade). O Programador escreve código compatível com o que o Arquiteto opera.

- **Funcionamento 100% local (princípio #6 do Arquiteto).** O Postgres sobe via Docker Compose, em versão idêntica à de produção. "Clonou, um comando, app sobe" inclui o banco. Nada de depender de banco compartilhado remoto para desenvolver. **Local, dev e teste ainda são bancos separados**: o container de dev serve o banco `<app>` (seu dado de trabalho) e o banco `testing` (descartável, que a suíte reseta) — nunca o mesmo, para o teste não apagar o dev (ver Testes acima).
- **Backup e recuperação.** Backup contínuo via WAL archiving atinge RPO baixo facilmente (cruza com `nfr-architecture.md`: RTO 4h / RPO 1h é confortável). **Backup que nunca foi testado para restore é fantasia** — o restore é exercitado no início do projeto e periodicamente. Não invente "guardar arquivo no banco" (`BYTEA` gigante) sem alinhar com o Arquiteto: afeta o tamanho e o tempo de backup.
- **Read replicas** entram quando a leitura genuinamente pressiona o primário — não antes. Sugestão de partida (NFR): Postgres único, read replica eventualmente.
- **Observabilidade.** O Postgres expõe `pg_stat_statements` (queries lentas/frequentes), `pg_stat_activity` (conexões e locks ativos), e métricas de conexão/replicação. Cruza com a observabilidade RED exigida em `arquiteto/references/architecture-principles.md` (#8) e `programador/references/observability-discipline.md`. Alerta para: pool esgotando, queries lentas crescendo, lag de réplica, deadlocks.
- **Operações pesadas com cuidado.** `COUNT(*)` em tabela enorme sem condição é lento (use `EXPLAIN` ou aproximação via `pg_stat`). `DELETE` em massa em batches com `LIMIT` e pausa, evitando lock contention.

## Defaults deste template (e como divergir via ADR)

- **Postgres é o banco principal** — definido em `_project.md` (`PostgreSQL` = PostgreSQL).
- **`jsonb`** (não `json`) sempre que JSON for armazenado.
- **Soft delete** como default para entidade de negócio em domínio com dado sensível/financeiro.
- **Extensão antes de novo sistema** — `pgvector`/`PostGIS`/`TimescaleDB`/`pg_trgm` preferidas a serviço externo equivalente.
- **Pool integrado do framework**; `pgbouncer` só com volume que justifique.

Para divergir de qualquer um destes — sair do Postgres, adicionar armazenamento, trocar o default de soft delete num módulo — abra **ADR** (`arquiteto/templates/adr.md`) com a seção "Por que o Postgres não dá conta" e o trade-off honesto. Decisões de implementação local (qual extensão, índice composto, padrão de locking de um módulo) podem ir em **IDR** (`programador/templates/idr.md`).

## Armadilhas conhecidas (gotchas)

- **Superuser local mascara permissões do banco gerenciado.** Em muitos setups o Postgres local (dev/CI/test) conecta com um usuário **superuser** que **ignora GRANT/REVOKE**, enquanto em homolog/prod (banco gerenciado tipo Cloud SQL) o mesmo papel é usuário comum. Resultado: qualquer bug de privilégio numa migração passa **verde local e em todos os testes**, e só explode no `migrate+seed` de homolog. Confira o setup em `_project.md`/ADRs antes de mexer em privilégios. Para reproduzir de verdade: `CREATE ROLE t LOGIN; GRANT ...; SET ROLE t;` e rode a query — conectado como superuser você não reproduz nada.
- **`REVOKE UPDATE` em tabela referenciada por FK quebra todo INSERT no filho.** Ao inserir o filho, o Postgres valida a FK com `SELECT ... FOR KEY SHARE` na tabela-pai — lock que **exige privilégio UPDATE** na pai. Para append-only, revogue só `DELETE` e proteja `UPDATE` com **trigger** (`prevent_*_mutation`), não com `REVOKE`. (Incidente real: tabela de versionamento append-only revogou UPDATE e gerou `42501 permission denied` só no seed de homolog; o fix moveu a proteção para trigger.)
- **O erro de privilégio do `migrate+seed` de homolog frequentemente não aparece no log do CI** (só "execution failed") — pode estar no sistema de logging do ambiente gerenciado. Saiba onde esse log vive.
- **FK sem índice.** O Postgres não cria índice em FK automaticamente. FK sem índice transforma `DELETE`/`UPDATE` na pai em scan da filha — gargalo silencioso. Indexe FK quase sempre.
- **`ADD COLUMN NOT NULL` com default** podia reescrever a tabela inteira em versões antigas; é geralmente seguro no Postgres moderno com default não-volátil — mas **confira a versão mínima do projeto** antes de confiar.
- **`json` vs `jsonb`.** `json` guarda o texto cru (sem dedup de chave, sem índice GIN, reparse a cada operação). Use `jsonb` salvo necessidade explícita de preservar formatação/ordem exata.
- **Transação longa por engano.** ORM que abre transação por request + uma chamada HTTP lá dentro = transação presa pela latência da rede, segurando locks. Tire o I/O externo de dentro da transação.
- **Sequences/IDs em restore.** IDs gerados pelo banco têm comportamento específico no restore — confie no que o ORM e o backup do projeto fazem, não invente esquema próprio de geração de ID.
