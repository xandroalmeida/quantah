# Método de banco de dados (agnóstico de stack) — Reference compartilhada da camada de stacks

Esta reference é a fonte **única** do método de banco agnóstico. Vale para qualquer datastore relacional e para as **duas fases**: o Programador (MVP) e o Idealizador (POC, que chega aqui pelas sub-skills de stack). O *como-fazer* específico de cada banco (sintaxe, tipos, extensões, gotchas) vive na sub-skill da stack ativa (`stacks/database/postgres/SKILL.md`, `stacks/database/sqlite/SKILL.md`). Aqui ficam o **porquê** e o **hábito**.

## A mentalidade

- **Banco é estado real e durável.** Bug em código você corrige no próximo deploy; bug em migração você convive por anos.
- **Volume cresce.** Código que funciona com 100 linhas em desenvolvimento pode quebrar com 100k em produção. Pense em volume **sempre**.
- **O banco do projeto é poderoso.** Antes de inventar, pergunte: "isso o datastore primário já faz?" — o catálogo do que ele entrega é da sub-skill de stack ativa.
- **Transações importam.** Operação que envolve múltiplas escritas relacionadas precisa estar em transação — ou você terá inconsistência em algum ponto.

## Bancos segmentados por ambiente — dev e teste nunca compartilham

**Cada ambiente tem o seu próprio banco.** Dev local, teste automatizado (incluindo E2E), CI, homolog e produção são bancos **distintos**. O motivo é concreto e recorrente:

- **O banco de teste é descartável — o de dev é o seu estado de trabalho.** A suíte de testes **recria, trunca ou reseta** o banco a cada rodada (é o próprio mecanismo que dá isolamento entre testes — transação-com-rollback, truncate, migrate-fresh). O banco de dev guarda o dado que você (ou, num protótipo, a pessoa não-técnica) inseriu à mão para trabalhar.
- **Se os dois forem o mesmo banco, o teste apaga o dev.** Rodar a suíte — em especial o **E2E** (que roda num processo separado, contra um servidor real, e por isso costuma truncar/migrar em vez de usar transação) — limpa o banco apontado pela conexão corrente. Se essa conexão é a de dev, **cada rodada de teste destrói seu ambiente local**. Esse é um bug de configuração comum e caro; não é comportamento aceitável.

**A regra:** o ambiente de teste aponta para um **banco de teste dedicado e isolado**, nunca para o banco de dev. Toda a suíte (unit, integração e E2E) usa esse banco de teste; o banco de dev fica intocado. Um único banco de teste compartilhado por toda a suíte é o suficiente e o mais simples — separe o E2E num terceiro banco só se precisar rodar a suíte de integração e a de E2E **ao mesmo tempo** (aí é decisão de ADR).

**Como isso se materializa** (arquivo de configuração de ambiente de teste próprio, nome de banco/arquivo separado, criação do segundo banco no ambiente local) é **idiomático da stack** — ver a sub-skill ativa (`stacks/database/postgres/SKILL.md`, `stacks/database/sqlite/SKILL.md`, `stacks/laravel/SKILL.md`). Aqui fica só o hábito: **teste em banco de teste; dev em banco de dev; jamais o mesmo.** É o par de ambiente do gotcha "o banco local pode mentir" (ver Migrações › privilégios): o ambiente de teste tem de ser fiel a produção **e** isolado do dev.

## Convenções de modelagem

O **porquê** e a regra de cada convenção moram aqui (agnósticas). A **expressão** concreta — qual tipo, qual sintaxe — é da sub-skill da stack ativa (ver `stacks/...`).

- **Chaves primárias: UUIDv7 por padrão.** Toda tabela do **domínio** usa **UUIDv7** como chave primária — não auto-increment, não UUIDv4. UUIDv7 é **ordenado por tempo**: insere no fim do índice (localidade de B-tree como um id sequencial), sem a fragmentação do UUIDv4 aleatório nem o gargalo/vazamento de contagem do auto-increment, e ainda é **seguro de expor** em URL/API. Gere no app (helper do framework, ex.: `Str::uuid7()`) ou no banco onde houver geração nativa. **Exceção:** tabelas criadas por **frameworks/bibliotecas de terceiros** (migrations de pacotes; tabelas internas como `jobs`, `cache`, `sessions`, `migrations`, `failed_jobs`) — mantêm o que o framework define; não brigue com elas.
- **Dinheiro e quantidades exatas: nunca `float`.** Valor monetário em **decimal exato** ou **inteiro em centavos** — `float`/`double` arredondam e viram erro financeiro silencioso.
- **Tempo sempre com fuso.** Armazene em **UTC**, ciente de fuso; converta para o fuso do usuário só na **borda de apresentação**. Nunca guarde um horário ambíguo (sem fuso).
- **Data/hora: valor canônico ISO 8601; seletor é entrada, não persistência.** Complementa "Tempo sempre com fuso": só-data é `AAAA-MM-DD` (tipo `date`, sem fuso); hora é `HH:MM`; data+hora é ISO 8601 em **UTC** (tipo com fuso). Use o **tipo nativo** de data/tempo, nunca texto solto. O **seletor (picker)** do FE é a **borda de entrada** (UX — ver sub-skill de FE), assim como a conversão ao fuso do usuário é a **borda de apresentação**; o banco guarda o valor canônico. (No SQLite: ISO-8601 em UTC como `TEXT` — ver `stacks/database/sqlite/SKILL.md`.)
- **Invariantes no banco, não só no app.** O banco é a **última linha de defesa** (dois processos concorrentes, um import, o console burlam a validação do app). Colunas **`NOT NULL` por padrão** (nullable é exceção justificada); `UNIQUE`/`CHECK`/`FK` para o que o domínio exige. Regra que dá pra expressar em constraint, expresse — não confie só no código.
- **Chaves naturais com `UNIQUE` — e índice único parcial para soft delete.** email/slug/documento ganham `UNIQUE` mesmo com a PK surrogate (uuid). Para conviver com soft delete (`deleted_at`), use **índice único parcial** (`... WHERE deleted_at IS NULL`) — permite "reusar" o valor após exclusão lógica.
- **Documentos/identificadores formatados: armazene o valor canônico sem máscara.** CPF, CNPJ, PIS/PASEP, telefone, CEP são persistidos **só com os dígitos** (canônico), tamanho fixo, nunca com a máscara (`.`, `-`, `/`, `(`, `)`). Máscara é **apresentação** (responsabilidade do FE — ver sub-skill de FE); o banco guarda o dado limpo, o que mantém `UNIQUE`/índice/`CHECK` consistentes e as buscas confiáveis. Formatar na leitura é da borda de apresentação, como o fuso.
- **Enums como valor restrito.** Prefira **texto + checagem** (ou o enum do app) a um tipo de enum nativo difícil de evoluir (adicionar/remover valor vira migração delicada).
- **Use o tipo certo e nomes consistentes.** Tipo nativo correto (booleano, data/tempo, decimal, etc.) em vez de empilhar tudo em texto/inteiro. Nomes: **snake_case**, tabela no **plural** (`pedidos`), FK `<tabela>_id` (`cliente_id`). Consistência ajuda o time **e** o agente.

## ORM vs SQL bruto

Frameworks opinativos vêm com ORM (Django ORM, ActiveRecord, Ecto, Eloquent, Prisma, TypeORM, SQLAlchemy etc). Use ORM por padrão — você ganha:

- Parameterização automática (defesa contra SQL injection, veja `programador/references/security-discipline.md`).
- Migrações geradas a partir do modelo.
- Validações no nível de modelo.
- Helpers úteis (paginação, relacionamentos, eager loading).

**SQL bruto é justificado quando:**

- Query envolve operação que o ORM expressa mal (window functions complexas, CTEs encadeados, lateral joins, full-text search, JSON paths sofisticados).
- Performance precisa controle fino que o ORM não dá.
- Operação de manutenção pontual (backfill controlado, correção de dado).

**Quando usar SQL bruto:**

- Sempre com **bind parameters** (`?`, `$1`, `:nome`). **Nunca concatene** string.
- Comente o porquê (por que o ORM não atende).
- Encapsule em função/módulo claro; não espalhe SQL bruto pelo código.

## N+1 query — o anti-padrão clássico

Você tem uma lista de N itens, e para cada item você dispara uma query adicional para buscar relacionado. Resultado: 1 + N queries em vez de 1 ou 2.

Exemplo conceitual:

```python
# Vocabulário ilustrativo (uma entidade "Oferta" e seus "interessados")
# ❌ N+1
ofertas = Oferta.objects.filter(status='aberta')   # 1 query
for o in ofertas:
    print(o.interessados.count())                  # N queries, uma por oferta
# total: 1 + N

# ✅ Com prefetch / eager loading
ofertas = Oferta.objects.filter(status='aberta').prefetch_related('interessados')  # 2 queries no total
for o in ofertas:
    print(len(o.interessados.all()))               # nenhuma query adicional
```

Cada ORM tem seu jeito (`prefetch_related`/`select_related` em Django, `includes` em Rails, `with` em Laravel, `preload` em Ecto, `include` em Prisma, `joinedload` em SQLAlchemy).

**Como detectar:**

- Em **desenvolvimento**, ligue logging de queries SQL. Se ver 50 queries em uma view de listagem, há N+1.
- Em **CI**, use ferramenta que detecta N+1 nos testes (ex: Bullet em Rails, django-silk em Django, etc).
- Em **produção**, observe latência crescendo com volume — sintoma típico de N+1 que escapou.

N+1 é uma das primeiras coisas a olhar em qualquer code review.

## Índices — quando criar, quando não

**Crie índice quando:**

- Coluna é usada frequentemente em `WHERE`, `JOIN`, `ORDER BY`.
- Coluna tem alta cardinalidade (muitos valores distintos) — índice em coluna booleana raramente ajuda.
- Foreign key (FK) — muitos bancos não criam índice de FK automaticamente; índice em FK é quase sempre desejável (confirme o comportamento na sub-skill de stack ativa).
- Coluna de busca textual (o tipo de índice idiomático é da stack — ver `stacks/...`).
- Coluna JSON/documento que vai ser queryada (idem — a sintaxe de índice depende do banco).

**NÃO crie índice quando:**

- Tabela é pequena (algumas centenas de linhas) — full scan é tão rápido quanto.
- Coluna é raramente filtrada.
- Tabela tem write rate alto e leitura é tolerável sem índice — índice penaliza INSERT/UPDATE/DELETE.
- Você está "preparando para o futuro" — adicione quando a query for real, não imaginada.

**Composite index importa:** índice em `(a, b)` ajuda buscas por `a` ou `(a, b)` — mas **não** por `b` sozinho. Ordem das colunas no índice importa.

**Leia o plano de execução:** quando uma query está lenta, peça o plano de execução do banco antes de palpitar sobre causa. O plano te diz se está fazendo full scan, se está usando índice, se está fazendo nested loop caro. O comando exato (ex.: `EXPLAIN ANALYZE`) é da sua stack — ver `stacks/...`.

## Transações

Operação que envolve múltiplas escritas relacionadas: **transação**. ORMs frequentemente abrem transação por request HTTP, mas verifique a configuração do seu projeto.

```python
# Conceitual — exato depende do framework/ORM (vocabulário ilustrativo)
with transaction.atomic():
    acordo = Acordo.create(oferta=oferta, lado_a=parte_a, status='confirmado', ...)
    Oferta.objects.filter(id=oferta.id).update(posicoes_restantes=F('posicoes_restantes') - 1)
    Pagamento.create(acordo=acordo, pre_auth_id=gateway_pre_auth_id, ...)
# se qualquer um falhar, tudo é desfeito
```

**Heurísticas:**

- **Curtas.** Transações longas seguram locks e bloqueiam outras escritas. Faça o trabalho dentro da transação ser estritamente necessário ali.
- **Sem I/O externo dentro.** Não chame API HTTP dentro de transação — se a API demorar 30s, a transação demora 30s. Faça a chamada antes ou depois, ou empurre para job assíncrono.
- **Isolation level:** conheça o default do seu banco (a sub-skill de stack documenta qual é) — costuma ser suficiente. Quando precisar consistência mais forte (concorrência crítica), considere um nível mais estrito ou explicit locking — mas **registre em IDR** com motivo.

## Locking e concorrência

Operação concorrente em mesmo recurso pode causar **race condition**. Padrões úteis (conceitos; a sintaxe é da stack):

- **Optimistic locking** (versioning): tabela tem coluna `version`; update inclui `WHERE version = X`; se não atualizou linha, alguém alterou enquanto você processava. Ideal para baixa contenção.
- **Pessimistic locking** (trava a linha até a transação acabar): ideal para alta contenção. Use com cuidado — pode causar deadlock. A sintaxe concreta é da stack.
- **Fila no banco**: reservar "próximo job sem lock" sem bloquear outros workers é o padrão para job queue no datastore primário (princípio arquitetural #3). A mecânica idiomática (cláusula de skip-lock, etc.) é da sub-skill de stack ativa — ver `stacks/...`.

Para a maioria das estórias, transação normal resolve. Quando você sentir cheiro de race condition, **pare e pense** — não é a hora de inventar.

## Migrações

Migração mexe em produção. Erro aqui é caro. Hábitos:

### Sempre

- **Escrita em código** (não cliquete manual no banco).
- **Versionada** com a feature que precisa dela.
- **Reversível**: escreva o `down` mesmo achando improvável usar. Eventualmente você vai precisar.
- **Idempotente quando possível**: se rodar duas vezes, segunda não quebra.
- **Testada em ambiente similar a produção antes do PR de produção** — não só local.
- **Migration aplicada é imutável:** corrija com uma migration **nova** — nunca edite uma que já rodou em outro ambiente (some do histórico e diverge entre máquinas).

### Cuidados com volume

Em tabela grande (milhões de linhas), operações inocentes ficam perigosas. O **método** (agnóstico):

- **Adicionar coluna obrigatória com default**: pode reescrever a tabela inteira dependendo do banco e da versão. Verifique o comportamento da sua stack antes (a sub-skill de stack documenta os casos seguros).
- **`ADD COLUMN` seguido de backfill**: separe em duas migrações:
  1. Adiciona coluna anulável.
  2. Backfill em **batches** (por faixa de id ou job em background) — não em um UPDATE único.
  3. Eventualmente torna obrigatória quando estiver populada.
- **Adicionar índice em tabela grande**: prefira a forma que **não trava escrita** (muitos bancos têm um modo de criação concorrente — a sintaxe é da sua stack, ver `stacks/...`).
- **Renomear coluna**: quebra app que ainda usa o nome antigo. Padrão "expand-contract": adicionar nova → fazer app escrever em ambas → migrar leitura → parar de escrever na antiga → remover antiga. Várias migrações, mas zero downtime.
- **DROP COLUMN/TABLE**: praticamente irreversível em produção (rollback de migração não traz dado de volta). Pense duas vezes.

### Cuidados com privilégios — **o banco local pode mentir**

O **método**: o banco local (dev/CI/test) pode conectar com um usuário privilegiado que **ignora as regras de privilégio**, enquanto homolog/prod usa um usuário comum. Quando isso acontece, um bug de privilégio numa migração **passa verde local e em todos os testes, e só explode no `migrate+seed` de homolog**. Confira o seu setup em `_project.md`/ADRs antes de mexer em privilégios, e **prove bug/fix de privilégio com um usuário não-privilegiado**, não com o superusuário.

> Os gotchas concretos de privilégio do banco (ex.: por que revogar UPDATE quebra INSERT em tabela referenciada por FK; quando proteger append-only com trigger em vez de REVOKE; onde o log real do `migrate+seed` aparece) são idiomáticos da stack e vivem na sub-skill ativa — ver `stacks/database/postgres/SKILL.md`. Aqui fica só o hábito: reproduza com o usuário certo, antes de confiar no verde local.

### Backfill de dado existente

Quando a migração precisa preencher coluna nova com dado calculado:

- **Não faça em uma transação gigante**. Quebre em batches.
- **Faça em background job** quando o volume justifica.
- **Tolere parada**: o backfill deve poder ser pausado e retomado.
- **Verifique resultado** com query de consistência antes de remover passos antigos.

## Paginação

Listagem sem paginação = bomba de tempo. Mesmo em "tabela que não vai crescer".

**Offset pagination** (simples mas ruim em volume):
```sql
SELECT ... LIMIT 20 OFFSET 5000
```
PostgreSQL precisa **ler e descartar** as 5000 primeiras. Em página fundo, fica lento.

**Cursor pagination** (escalável):
```sql
SELECT ... WHERE id > $cursor ORDER BY id LIMIT 20
```
Salta direto para o cursor — performance constante.

Para listagens administrativas ou de baixo volume, offset OK. Para listagens de usuário em volume crescente, cursor.

## Soft delete vs hard delete

- **Soft delete** (coluna `deleted_at`): registro fica no banco, é marcado como deletado. Vantagem: rastreabilidade, recuperação, auditoria. Desvantagem: todo query precisa filtrar deleted, índices crescem, complexidade.
- **Hard delete**: registro é removido. Vantagem: simplicidade, performance. Desvantagem: dado perdido.

Em domínios com dados sensíveis ou financeiros sob lei de proteção de dados, **default = soft delete** para entidades de negócio. Excluir dado de usuário (direito do titular) provavelmente exige hard delete em algumas tabelas e mascaramento em outras (audit log). Decisão caso a caso, em alinhamento com PO/legal.

## JSON / documento

Quando usar uma coluna JSON/documento (o tipo indexável e a sintaxe de query são da sua stack — ver `stacks/...`):

- ✅ Conteúdo de schema flexível (configuração, payload de evento externo, dados que mudam por integração).
- ✅ Cache de dado calculado complexo.
- ❌ **Não para campo que tem schema estável** — modele como coluna normal.
- ❌ **Não como "fugir" de modelar o domínio** — colunas dedicadas continuam melhor para regras de negócio claras.

## Full-text search

Antes de adicionar um motor de busca dedicado (que viola princípio arquitetural #3 sem prova robusta), olhe o que o **datastore primário** já oferece em busca textual e por similaridade. Para a esmagadora maioria dos casos de busca, isso resolve. As capacidades concretas e a sintaxe são da sub-skill de stack ativa — ver `stacks/database/postgres/SKILL.md`.

## Operações pesadas

- **Não rode COUNT(*) em tabela enorme** sem condição — pode ser lento. Considere aproximação via estatísticas do banco (a forma é da stack).
- **DELETE em massa**: faça em batches, com `LIMIT`, em loop com pausa — evita lock contention.
- **Atualize as estatísticas do planner** após backfill grande (o comando é da sua stack — ver `stacks/...`).

## Connection pooling

Sua aplicação não abre conexão direto no banco a cada request — usa pool. O datastore primário tem limite duro de conexões; sem pool, esgota rápido.

- Frameworks opinativos têm pool integrado.
- Para alto volume, considere um pooler dedicado (mas isso é decisão arquitetural, não sua) — a ferramenta idiomática é da sub-skill de stack.
- **Não vaze conexão**: garantir que cada conexão pega volta para o pool (frameworks fazem isso por você se você usar API normal).

## Backup awareness

Você não opera o backup, mas escreve código compatível com ele:

- Não invente esquemas de "guardar arquivo grande no banco" (blob gigante) sem alinhamento com Arquiteto — afeta backup.
- Sequences/IDs gerados pelo banco têm comportamento específico em restore — confie no que o ORM e o backup do projeto fazem.
