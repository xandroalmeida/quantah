---
name: stack-laravel
description: Sub-skill de stack — Laravel (backend). Conhecimento idiomático e opinativo de Laravel que os papéis (Arquiteto, Programador, Validador) consultam quando Laravel é o backend da stack ativa do projeto (ver _project.md › Stack ativa). Complementa o método dos papéis com o como-fazer específico de Laravel; não o substitui.
---

> **Sub-skill de stack.** Ativada quando Laravel é o backend do projeto (`_project.md` › Stack ativa). Os papéis trazem o método; esta sub-skill traz o idiomático de Laravel.

# Laravel — sub-skill de stack (backend)

Laravel é o backend **default** deste template — compartilhado entre o frontend web (Livewire ou Inertia) e o app mobile (BE para um app Flutter, consumindo API via token). Este documento não é tutorial: assume que você sabe PHP e Laravel, e diz **como fazer o certo aqui** quando o método dos papéis precisa aterrissar nesta stack. A disciplina (TDD, segurança, observabilidade, libs) continua sendo a dos papéis — esta sub-skill só preenche o "como em Laravel".

## Quando esta stack se aplica (e quando NÃO)

**Aplica-se** quando `_project.md` › Stack ativa declara Laravel como backend. Vale para o BE web (servindo Livewire/Inertia) **e** para o BE do app Flutter (servindo API com Sanctum). É o mesmo Laravel; muda só a borda (Blade/Inertia vs. JSON).

**NÃO se aplica** — e você deve parar e consultar a ADR vigente — quando:

- O projeto trocou o backend por outro framework/linguagem via ADR. Esta sub-skill fica inerte; siga a sub-skill da stack ativa.
- A decisão é **arquitetural** (trocar ORM, adicionar outro datastore, quebrar o monolito, escolher fila externa). Isso é do Arquiteto via ADR — não se resolve "idiomaticamente". Veja `docs/skills/arquiteto/`.
- A decisão é **de produto** (escopo, regra de negócio, critério de aceite). Isso é do PO.

O default do template é **monolito Laravel + `PostgreSQL`** (PostgreSQL por padrão; SQLite para POC). Divergir disso é ADR, não preferência.

## O que o Laravel já entrega (não reinvente)

A disciplina de bibliotecas do Programador (`programador/references/library-discipline.md`, Pergunta 2) manda olhar **o que o framework opinativo já faz antes de instalar lib**. Em Laravel, a resposta é "muita coisa". Antes de trazer dependência, confira:

| Necessidade | O Laravel já entrega | Não traga (sem ADR/IDR) |
|---|---|---|
| ORM + query builder | Eloquent + Query Builder | Doctrine, ORM externo |
| Schema / versionamento de banco | Migrations | SQL solto, ferramenta externa |
| Validação de input | FormRequest / `Validator` | Respect/Validation, validação manual avulsa |
| Autorização por recurso | Policies / Gates | RBAC caseiro, checagem espalhada em controller |
| Regra de negócio | Actions / Service classes | "fat controller", lógica no model |
| Trabalho assíncrono | Queues + Jobs (`ShouldQueue`) | thread caseira, `exec()` em background |
| Trabalho por horário | Scheduler (`schedule()`) | crontab manual com lógica de negócio |
| Eventos de domínio | Events + Listeners | observer caseiro |
| E-mail / notificação | Mailables / Notifications | SMTP cru, integração manual de provider |
| Auth web / API | Breeze, Fortify, Sanctum | rolar sessão/token na mão |
| HTTP client | `Http::` (wrapper Guzzle) | Guzzle cru, cURL na mão |
| Cache, rate limit, locks | `Cache::`, `RateLimiter`, `Cache::lock()` | implementação própria |
| Logger estruturado | Monolog via `Log::` (canais configuráveis) | `error_log()`, echo |

Lutar contra os defaults do framework é red flag — quase sempre você é quem está errado (princípio de código #3 do Programador). Quando precisar mesmo divergir, justifique em IDR.

## Padrões idiomáticos

**Eloquent + Migrations.** Modelo por tabela; relacionamentos declarados (`hasMany`, `belongsTo`, `belongsToMany`, `morphMany`). Toda mudança de schema é uma **migration** — nunca altere o banco à mão. Use `$fillable` (ou `$guarded`) sempre; mass assignment sem isso é vetor de segurança. Casts (`casts()`) para tipos ricos (datas, enums, `array`, value objects). Cuidado com **N+1**: use eager loading (`with(...)`) e considere `Model::preventLazyLoading()` em dev/teste para o N+1 explodir cedo — alinha com "falhe cedo, falhe alto" do `error-handling.md`.

**Chaves primárias UUIDv7 no Eloquent.** Tabela do domínio usa **UUIDv7** como PK (convenção das skills de banco — ver `stacks/database/postgres/SKILL.md` / `stacks/database/sqlite/SKILL.md`). Na **migration**, troque `$table->id()` por `$table->uuid('id')->primary()`, e FKs por `foreignUuid(...)`:

```php
Schema::create('pedidos', function (Blueprint $table) {
    $table->uuid('id')->primary();                       // não $table->id()
    $table->foreignUuid('cliente_id')->constrained();    // FK uuid → uuid
    $table->text('titulo');                              // text, não string(255), no Postgres
    $table->timestamps();
});
```

No **model**, use o trait `HasUuids` e **sobrescreva `newUniqueId()`** para gerar v7 (o `HasUuids` puro gera *ordered UUID*, não v7). O trait já marca a chave como string não-incremental:

```php
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Support\Str;

class Pedido extends Model
{
    use HasUuids;

    public function newUniqueId(): string
    {
        return (string) Str::uuid7();           // Laravel atual; ou Ramsey\Uuid\Uuid::uuid7()
    }

    public function uniqueIds(): array          // quais colunas recebem uuid
    {
        return ['id'];
    }
}
```

**Exceção:** tabelas de **terceiros/framework** (`jobs`, `cache`, `sessions`, `migrations`, `failed_jobs`, migrations de pacotes) ficam com o `bigint` auto-increment delas — **não** as converta. `$table->string('col')` continua valendo no SQLite; no Postgres, prefira `$table->text('col')` (ver `stacks/database/postgres/SKILL.md`).

**Validação → FormRequest.** Validação de entrada vive em `FormRequest` (`rules()`, `authorize()`, `messages()`), não espalhada no controller. Use as regras nativas (`required`, `email`, `exists:`, `unique:`, `Rule::*`). Mensagens amigáveis para o usuário, contexto técnico no log — conforme `error-handling.md`. Validação que falha retorna 422 com erros por campo; não invente formato próprio.

**Documentos brasileiros → validação canônica no servidor.** A máscara que o FE aplica (CPF, CNPJ, PIS/PASEP, telefone, CEP) é **só UX** — nunca é validação. A validação canônica (dígitos verificadores de CPF/CNPJ/PIS, formato) mora no servidor, como **rule** no `FormRequest`. Default do template: pacote de rules dedicado (ex.: `laravel-validation-rules/cpf-cnpj` ou equivalente) — divergir/trocar via ADR, sem cravar versão aqui. **Normalize antes de validar/persistir:** remova a máscara no `prepareForValidation()` do `FormRequest` (deixe só os dígitos) e valide/salve o **valor canônico sem máscara**. O que persiste é o dígito puro (ver `stacks/database/database-method.md`); formatar é papel do FE.

**Data/hora → validação canônica de formato e limites no servidor.** O seletor (picker) do FE é **só UX**. No `FormRequest`, valide com rules nativas: `date` ou `date_format:Y-m-d` (só-data), `date_format:H:i` (só-hora), `date`/ISO 8601 (data+hora); e `after`/`before`/`after_or_equal`/`before_or_equal` para limites (ex.: `'nascimento' => 'required|date|before:today'`). Se o cliente mandar formato localizado, normalize no `prepareForValidation()` (deixe ISO 8601). No model, `cast` para `datetime`/`immutable_datetime`/`date`; persista em **UTC** (ver `stacks/database/database-method.md`). O valor canônico é ISO 8601, nunca string localizada — `datetime-local` nativo não carrega fuso, então a conversão para UTC é no servidor/model.

**Autorização → Policies/Gates.** Toda checagem "esse usuário pode fazer isso com este recurso?" é uma **Policy** (`UserPolicy`, `PostPolicy`) ou um **Gate** para regras transversais. No controller, `$this->authorize('update', $post)` ou `Gate::authorize(...)`. Não espalhe `if ($user->role === ...)` pelo código — isso é o anti-padrão de RBAC caseiro. 403 para autenticado-sem-permissão, 401 para não-autenticado (`error-handling.md`).

**Regra de negócio → Actions/Services, não no controller nem no model.** Controller fino: valida (via FormRequest), autoriza (via Policy), delega para uma **Action** (`App\Actions\...`) ou **Service**, devolve resposta. O model guarda relacionamentos, casts e scopes — não orquestra regra de negócio complexa. "Fat controller" e "fat model" são os dois extremos a evitar.

**Filas + Jobs.** Trabalho que pode ser assíncrono (e-mail, notificação, processamento pesado, chamada a fornecedor externo) é um **Job** `implements ShouldQueue`, despachado com `dispatch(...)` de onde o evento acontece. Jobs têm `$tries`, `backoff()` e `failed()` — alinhe com retry/backoff/jitter do `error-handling.md`. Se a fila usa o próprio banco como backend (`database` driver), o `INSERT` do job entra na **transação corrente**: o worker só vê o job após o commit, e o rollback o desfaz junto — transação-seguro sem `afterCommit` explícito (confirme o driver). Para garantir mesmo com outros drivers, use `->afterCommit()`.

**Scheduler.** Trabalho **por horário/intervalo** (relatório noturno, limpeza diária) vai no `schedule()` (em `routes/console.php` ou `app/Console/Kernel.php`, conforme a versão). **Atenção:** o scheduler só roda se algo chamar `schedule:run` a cada minuto no ambiente — veja a armadilha na seção de gotchas. Trabalho disparado por **evento** (não por horário) é **Job de fila**, não comando agendado.

**Eventos/Listeners.** Para desacoplar efeitos colaterais de um fato de domínio: `UserRegistered` dispara, listeners reagem (enviar boas-vindas, criar registro associado). Listeners que fazem trabalho lento devem `implements ShouldQueue` para ir à fila. Use eventos quando há **múltiplos** reagentes ou quando quer desacoplar; para um único efeito direto, chamar a Action é mais simples (KISS).

**Mail / Notifications.** E-mail transacional → **Mailable**; notificação multi-canal (mail, database, broadcast, push) → **Notification**. Ambos enfileiráveis (`ShouldQueue`). Nunca monte SMTP na mão.

## Testes nesta stack

A disciplina é a do Programador (`programador/references/testing-discipline.md`) e o gate é o do Validador (`validador/`): **teste vermelho antes do código**, as 4 categorias obrigatórias (feliz, inválido, exceção, borda), caminho feliz sozinho não conta, suíte completa verde antes do push. Aqui está o **como em Laravel**:

| Recurso | Ferramenta Laravel | Uso |
|---|---|---|
| Runner | **Pest** (preferência) ou PHPUnit | Pest pela ergonomia; PHPUnit é a base. Escolha um e seja consistente (IDR se mudar). |
| Banco em teste | `RefreshDatabase` (ou `DatabaseTransactions`) | Banco limpo por teste. Prefira o **`PostgreSQL` real** em container, não SQLite-com-truques, conforme `testing-discipline.md`. Roda contra um **banco de teste dedicado** (via `phpunit.xml`), **nunca** o de dev — ver "Banco de teste segmentado" abaixo. |
| Dados de teste | **Factories** (`UserFactory`) + Seeders | Factory para o caso do teste; seeder para baseline. Nunca dados hardcoded espalhados. |
| Teste de feature (HTTP) | `$this->get/post/put(...)`, `actingAs()`, `assertStatus`, `assertJson`, `assertDatabaseHas` | O grosso dos testes: bate no endpoint real, exercita validação + autorização + persistência. |
| Fila | `Queue::fake()` → `Queue::assertPushed(Job::class)` | Não roda o job; só prova que foi despachado. |
| E-mail | `Mail::fake()` → `Mail::assertSent(...)` | Prova envio sem mandar e-mail real. |
| Eventos | `Event::fake()` → `Event::assertDispatched(...)` | Isola o disparo do efeito. |
| HTTP externo | `Http::fake([...])` | Mock no nível do cliente HTTP — o nível certo segundo `testing-discipline.md`. |
| Notificações | `Notification::fake()` | Idem fila/mail. |
| Tempo | `$this->travelTo(...)` / `Carbon::setTestNow()` | Determinismo de datas. |

**E2E em browser real.** O gate de E2E do Programador (FE web tocado = E2E em browser real, um cenário por caminho mapeado) usa **Laravel Dusk** quando o frontend é Blade/Livewire renderizado pelo Laravel. Dusk roda Chrome de verdade — atende a exigência de "browser real" do `testing-discipline.md` ("não simule DOM e diga que validou a UI"). Quando o FE é Inertia/React ou o app Flutter, a ferramenta de E2E é decisão de ADR (Playwright/Cypress para web; teste de integração de app para Flutter) — não invente, consulte a ADR.

**Banco de teste segmentado — o teste não pode apagar o banco de dev.** O método é de `stacks/database/database-method.md` › "Bancos segmentados por ambiente": dev e teste **nunca** compartilham banco. A materialização em Laravel:

- **`phpunit.xml`** define `APP_ENV=testing` e sobrescreve a conexão para o **banco de teste dedicado** — ex.: `<env name="DB_DATABASE" value="testing"/>` (Postgres) ou `value="database/testing.sqlite"` (SQLite). É o que faz `artisan test`/Pest/PHPUnit (unit + feature, com `RefreshDatabase`/`DatabaseTransactions`) rodarem no banco de teste, não no de dev.
- **`.env.dusk.local`** é o env que o **Dusk** carrega ao rodar o E2E — aponte `DB_DATABASE` para o **mesmo banco de teste**. Este é o ponto crítico: o Dusk roda num processo separado e **não** usa a transação-com-rollback dos testes de feature; ele trunca/migra o banco de verdade. Sem `.env.dusk.local` apontando para o banco de teste, o Dusk roda contra o `.env` de dev e **apaga seus dados**.
- O **banco de teste** (`testing` no Postgres, `database/testing.sqlite` no SQLite) é dedicado, isolado e recriável — como criá-lo é da sub-skill de banco ativa (`stacks/database/postgres/SKILL.md` — o Sail já o cria; `stacks/database/sqlite/SKILL.md` — arquivo à parte). O scaffold do `init-project` já gera `phpunit.xml` e `.env.dusk.local` apontando para ele.

**TDD na prática.** Comece pelo teste de feature vermelho do CA (bate no endpoint, espera o comportamento). Implemente o mínimo (rota → FormRequest → Policy → Action → resposta) até o verde. Refatore. Cada caso inválido/exceção/borda vira um teste antes do código que o trata. O histórico de commits precisa mostrar teste antes de implementação — sem isso é teatro de cobertura (Programador, "Gates de teste inegociáveis").

## Auth

| Cenário | Solução Laravel | Quando |
|---|---|---|
| Web com sessão, scaffolding simples | **Breeze** | App web monolítico (Blade/Livewire/Inertia) que quer login/registro/reset prontos e enxutos. |
| Web/headless, sem views opinadas | **Fortify** | Quando você quer o backend de auth (rotas, lógica) sem o scaffold de UI — controla a view você mesmo. |
| API / SPA / **mobile (Flutter)** | **Sanctum** | Tokens de API para o app Flutter; cookies de sessão para SPA mesma-origem. É o caminho default para o BE do app. |
| Login social (opcional) | **Socialite** | Só se o produto exige OAuth de terceiros. Não traga "por garantia". |

Autorização por recurso é **Policies/Gates** (ver Padrões idiomáticos), independente do mecanismo de autenticação. A disciplina de segurança (input externo, dado sensível, o que nunca logar) continua sendo a do Programador (`programador/references/security-discipline.md` e `observability-discipline.md` — tokens e senhas **nunca** vão para log).

## Infra / operação / deploy

A infra é decisão do **Arquiteto** (`docs/skills/arquiteto/`); esta seção só mapeia o método dele para ferramentas idiomáticas de Laravel. Não decida infra aqui — registre ADR.

- **Local 100% em container → Laravel Sail.** Casa com o princípio de ambiente local reproduzível do Arquiteto: Docker com PHP, `PostgreSQL`, Redis, mailpit. O ambiente local sobe com um comando e espelha produção o quanto possível.
- **Deploy / servidor → Forge** (provisionamento e deploy) é o caminho de menor atrito. **Octane** (Swoole/RoadRunner) só quando houver necessidade de performance comprovada com números — é otimização, não default (alinha com "provar com números antes" do Arquiteto).
- **Filas em produção → worker rodando** (`queue:work`, supervisionado) e **Horizon** quando o backend de fila é Redis (dashboard, métricas, tuning). Filas precisam de worker vivo no ambiente; isso é configuração de infra, não código.
- **Migrations no pipeline de deploy.** `php artisan migrate --force` roda no deploy, não à mão em produção. Migrations são parte do artefato versionado.
- **Config por `.env` / `config()`.** Segredos em variáveis de ambiente, nunca commitados. No código, leia via `config('...')`, não `env()` direto fora dos arquivos de config (cache de config quebra `env()` em runtime).
- **Observabilidade.** O `Log::` usa Monolog com canais; configure canal estruturado (JSON) para produção conforme `observability-discipline.md`. Health checks (`/health`, `/ready`) e métricas RED são exigência do Programador — implemente, não "depois".

## Defaults deste template (e como divergir via ADR)

| Dimensão | Default do template | Como divergir |
|---|---|---|
| Backend | **Laravel** (monolito) | Outro framework/linguagem → ADR; esta sub-skill fica inerte. |
| ORM | **Eloquent** | Outro ORM (Doctrine) → ADR + IDR; é mudança estrutural. |
| Banco | **`PostgreSQL`** — PostgreSQL (default) ou SQLite (POC) | Outro datastore → ADR (`PostgreSQL`-first do Arquiteto). |
| Banco de teste | **Dedicado e separado do dev** (`testing` / `database/testing.sqlite`), via `phpunit.xml` + `.env.dusk.local` | E2E em banco próprio (terceiro) só se rodar integração e E2E em paralelo → ADR. |
| Validação | **FormRequest** | Outra lib → quase nunca se justifica; IDR se mesmo assim. |
| Validação de documentos BR (CPF/CNPJ/PIS) | **Rule dedicada no servidor** (pacote de validação BR); normalizar sem máscara antes | Outro pacote/rule própria → IDR. |
| Validação de data/hora | **Rules nativas** (`date`, `date_format`, `after`/`before`) no FormRequest; `cast` datetime/date; armazenar em UTC | — (nativo do framework). |
| Auth API/mobile | **Sanctum** | Passport (OAuth2 completo) só com requisito real → ADR. |
| Runner de teste | **Pest** | PHPUnit puro é aceitável; trocar default → IDR. |
| Fila | **database** (default) / **Redis + Horizon** (escala) | Fila externa (SQS) → ADR. |

A regra: divergência **local de um módulo** (uma lib pontual) é IDR no PR; divergência que **muda padrão estrutural** (ORM, banco, fila externa, quebrar monolito) é **ADR do Arquiteto**. Veja `programador/references/library-discipline.md` para a fronteira IDR vs. ADR.

## Armadilhas conhecidas (gotchas)

**Fila vs. scheduler — confirme o que de fato roda em cada ambiente.** Esta é a armadilha mais cara desta stack, e está registrada em `programador/references/error-handling.md`. `queue:work` (worker de fila) e `schedule:run` (drenagem do scheduler de cron, a cada minuto) são **dois processos diferentes**, e é comum um ambiente rodar **só o worker de fila** e **não** rodar o scheduler. Quando é assim:

> Todo trabalho agendado via `schedule()` **NÃO executa** em homolog/prod — só localmente, quando alguém roda `schedule:run` à mão. Quem processa trabalho no ambiente é a **fila**.

Regra prática: trabalho recorrente/background que **precisa rodar em homolog/prod** deve ser **Job de fila** (`ShouldQueue`, com `$tries`/`backoff`/`failed`), despachado de onde o evento acontece — **nunca** um comando agendado "que o cron drena", a menos que você tenha **confirmado** que `schedule:run` realmente roda no ambiente (está na IaC/infra/ADR, não no seu `.env` local). Padrão de incidente real: um e-mail entregue como comando agendado passou em todos os testes e no deploy, mas em homolog a notificação era criada e **o e-mail nunca saía** — porque nada disparava o scheduler. Custou um ciclo inteiro de diagnóstico.

Outras armadilhas:

- **`env()` depois de `config:cache`.** Em produção a config é cacheada e `env()` fora dos arquivos `config/` retorna `null`. Sempre leia via `config('chave')`, não `env('CHAVE')` no código de aplicação.
- **N+1 silencioso.** Eloquent torna fácil iterar relacionamentos sem eager loading. Ative `Model::preventLazyLoading()` em dev/teste para o N+1 falhar alto cedo; em produção é degradação invisível até virar incidente de latência.
- **Mass assignment sem `$fillable`/`$guarded`.** `Model::create($request->all())` sem proteção é vetor de escalonamento de privilégio. Use FormRequest + `$fillable`.
- **Job que não é transação-seguro.** Se você despacha um Job dentro de uma transação e a fila **não** usa o banco como backend, o worker pode pegar o job antes do commit (ou rodar após um rollback). Use `->afterCommit()` quando houver dúvida sobre o driver.
- **`SQLite` em teste mascarando comportamento de produção.** SQLite-em-memória é rápido, mas esconde diferenças do PostgreSQL (tipos, constraints, transações, `JSON`, concorrência). Para integração, use o `PostgreSQL` real em container — `testing-discipline.md` é explícito sobre isso.
- **Teste/E2E truncando o banco de dev.** Sem `phpunit.xml` (para Pest/PHPUnit) e `.env.dusk.local` (para o Dusk) apontando `DB_DATABASE` para um **banco de teste dedicado**, `RefreshDatabase` e o Dusk truncam/migram o banco do `.env` de **dev** — e cada rodada de teste **apaga os dados que você inseriu à mão** para trabalhar. É o mesmo espírito do gotcha acima (o ambiente de teste tem de ser fiel a produção **e** isolado do dev). Ver "Banco de teste segmentado" em Testes e `stacks/database/database-method.md` › "Bancos segmentados por ambiente".
- **Privilégio de banco local mascarando grants gerenciados.** DB local como superuser esconde grants que só explodem no banco gerenciado de produção. Cuidado análogo está em `programador/references/database-discipline.md`.
- **Listener pesado síncrono.** Um listener que faz I/O lento sem `ShouldQueue` bloqueia a request que disparou o evento. Para efeitos colaterais lentos, enfileire.

## Referências

| Quando | Leia |
|---|---|
| Disciplina de testes (gates, 4 categorias, E2E) | `programador/references/testing-discipline.md` |
| Tratamento de erro, retry, idempotência, fila vs. scheduler | `programador/references/error-handling.md` |
| Antes de adicionar lib (stdlib/framework primeiro) | `programador/references/library-discipline.md` |
| Log estruturado, métricas, o que nunca logar | `programador/references/observability-discipline.md` |
| Decisões de infra, ambiente, banco, monolito | `docs/skills/arquiteto/` (ADRs vigentes) |
| Gate de qualidade / aceite | `validador/` |
| Stack ativa e defaults do projeto | `_project.md` |
