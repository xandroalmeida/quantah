# Disciplina de banco de dados — visão do papel Programador

O Arquiteto definiu o princípio **datastore-first** (princípio arquitetural #3): começar pelo datastore primário e provar com números antes de adicionar outro armazenamento. Isso só funciona se o Programador **exercer bem o banco do projeto** — caso contrário a decisão arquitetural vira ADR letra-morta e o time acaba reclamando "tinha que ser outra coisa".

> **O método de banco (agnóstico) e as convenções de modelagem vivem em `stacks/database/database-method.md`** — mentalidade, modelagem (UUIDv7, dinheiro, tempo, invariantes, soft delete, enums, nomes), ORM × SQL bruto, N+1, índices, transações, locking, migrations (volume e privilégio), paginação, JSON, full-text, operações pesadas, pooling, backup. **O idiomático de cada banco** (tipos, sintaxe, gotchas) vive na sub-skill da stack ativa — `stacks/database/postgres/SKILL.md`, ou `stacks/database/sqlite/SKILL.md` em POC (ver `_project.md` › Stack ativa). **Esta reference cobre o que é do _papel Programador_** no fim de uma estória que mexe em banco: o checklist operacional, as amarrações com outras disciplinas e o que vai para IDR.

## O que é do papel (e não do método)

O método te diz *como* escrever queries que escalam e migrations seguras. O **papel** te diz *o que verificar e registrar* antes de marcar uma estória pronta — e como o banco se amarra às outras disciplinas:

- **SQL injection é disciplina de segurança.** ORM por padrão (parametriza sozinho) e, em SQL bruto, **sempre bind parameters, nunca concatenação** — isso é exigência de `security-discipline.md`, não só boa prática de banco. Toda vez que você escrever SQL bruto, é um ponto de revisão de segurança.
- **Erro de banco é disciplina de erro.** Falha de constraint, deadlock, violação de unicidade, timeout de lock — tratar como erro de domínio (mensagem útil, retry quando cabe, sem vazar stack/SQL para o usuário) é assunto de `error-handling.md`.
- **Decisões de isolation/locking vão para IDR.** Quando você sair do default do banco (isolation mais estrito, locking explícito, padrão de fila, índice composto não-óbvio, uso de feature específica da stack), **registre em IDR** (`programador/templates/idr.md`) com o motivo — é decisão de implementação que o time precisa enxergar depois.

## Checklist operacional

Antes de marcar pronta uma estória que mexe em banco:

- [ ] Queries verificadas — sem N+1, sem concatenação de SQL (ver `security-discipline.md`).
- [ ] Índices criados onde necessário (e **não** onde desnecessário).
- [ ] Plano de execução analisado em queries críticas em ambiente representativo (comando da stack — ver `stacks/...`).
- [ ] Migrações reversíveis e testadas em ambiente equivalente a produção.
- [ ] Migrações em volume: backfill em batches, índice criado sem travar escrita (modo concorrente da stack).
- [ ] Bug/fix de privilégio reproduzido com **usuário não-privilegiado**, não com o superusuário (o banco local pode mentir — método em `stacks/database/database-method.md`; gotchas concretos na sub-skill da stack).
- [ ] Testes/E2E rodam em **banco de teste dedicado**, segmentado do de dev — a suíte não destrói o dado de desenvolvimento (`stacks/database/database-method.md` › "Bancos segmentados por ambiente").
- [ ] Transações curtas, sem I/O externo dentro.
- [ ] Paginação onde lista cresce.
- [ ] Soft delete onde domínio exige rastro.
- [ ] Erros de banco tratados como erro de domínio (ver `error-handling.md`).
- [ ] Decisão de isolation/locking fora do default registrada em **IDR**.
