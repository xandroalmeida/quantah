# Instalar para o MVP — Docker Desktop + Laravel Sail

No MVP quem opera é o **time profissional**. A escolha é **Docker Desktop + Laravel Sail**: ambiente **reproduzível** em container que espelha produção e entrega **Postgres de graça** via serviço do Sail — sem instalar banco na máquina. Casa com o princípio de ambiente local reproduzível do Arquiteto (ver `stacks/laravel/SKILL.md` › Infra) e com o `PostgreSQL`-first do template (ver `stacks/database/postgres/SKILL.md`).

Antes de instalar, **detecte** (`detectar-e-verificar.md`): `docker --version` **e** `docker info`. Se o Docker já roda, não reinstale.

> Aqui a comunicação pode ser técnica — é o time. Sem necessidade de traduzir jargão como no POC.

## 1. Instalar o Docker Desktop

| OS | Como |
|---|---|
| **macOS** | Baixe o Docker Desktop do site oficial (`https://www.docker.com/products/docker-desktop`), instale o `.dmg`, abra o Docker e aguarde o daemon iniciar. Alternativa: `brew install --cask docker`. |
| **Windows** | Baixe e instale o Docker Desktop; ele usa o backend **WSL 2** (habilite o WSL 2 se solicitado). Alternativa: `winget install --id Docker.DockerDesktop`. |
| **Linux** | Instale o **Docker Engine** pelo gerenciador da distro (pacotes `docker-ce`/`docker.io` conforme a distro) ou o Docker Desktop para Linux. Adicione seu usuário ao grupo `docker` para não precisar de `sudo`. |

Depois de instalar, **abra o Docker e espere o daemon subir** — `docker info` precisa responder sem erro (ver `troubleshooting.md` › Docker não iniciado).

## 2. Subir o app com Laravel Sail

O Sail é o ambiente Docker oficial do Laravel: PHP, o banco e serviços auxiliares em containers, controlados por um script. O `init-project` instala o Sail no scaffold do MVP (`init-project/references/generation.md` › passo 6); com Postgres:

```bash
# dentro da pasta do app (já com Sail instalado pelo scaffold):
php artisan sail:install --with=pgsql      # adiciona Postgres ao docker-compose
./vendor/bin/sail up -d                     # sobe os containers (app + Postgres) em background
./vendor/bin/sail artisan migrate           # roda as migrations dentro do container
```

Comandos do dia a dia rodam **através do Sail** (dentro do container), não na máquina:

| Em vez de… | Use |
|---|---|
| `php artisan ...` | `./vendor/bin/sail artisan ...` |
| `composer ...` | `./vendor/bin/sail composer ...` |
| `npm ...` | `./vendor/bin/sail npm ...` |
| subir/descer o ambiente | `./vendor/bin/sail up -d` / `./vendor/bin/sail down` |

> Dica idiomática: criar um alias `sail='./vendor/bin/sail'` deixa os comandos mais curtos (`sail artisan ...`).

## 3. Postgres via Sail

O Sail provisiona o **Postgres em container** — não há banco a instalar na máquina. As credenciais e a porta vêm do `.env` (`DB_CONNECTION=pgsql`, host = o nome do serviço no compose). O idiomático de Postgres (tipos, índices em FK, testar contra Postgres real) é de `stacks/database/postgres/SKILL.md`; a infra Laravel/Sail é de `stacks/laravel/SKILL.md` › Infra.

**Dev e teste em bancos separados — de graça.** O serviço `pgsql` do Sail monta `create-testing-database.sql` em `docker-entrypoint-initdb.d`, então ao subir o container ele cria **dois bancos**: o de dev (`DB_DATABASE` do `.env`) e um banco **`testing`** dedicado. Os testes/E2E apontam para o `testing` via `phpunit.xml`/`.env.dusk.local` (ver `stacks/laravel/SKILL.md` › Testes), de modo que rodar a suíte **nunca apaga o banco de dev**. O porquê é de `stacks/database/database-method.md` › "Bancos segmentados por ambiente".

## Alternativa: nativo + Postgres em container

Se o time já tem **PHP/Composer nativos** instalados e prefere rodar o app fora de container, dá para usar só o Docker **para o Postgres**:

- App roda nativo (`php artisan serve`), Composer/Node nativos.
- Postgres sobe num container isolado (via um `docker-compose` mínimo ou `docker run` de uma imagem `postgres`), com `.env` apontando para `localhost:5432`.

É um meio-termo legítimo; o objetivo continua sendo **Postgres em container** (reproduzível) sem instalar o banco na máquina.

## Verificação

Rode o checklist de `detectar-e-verificar.md`, mais os específicos do MVP:

- [ ] `docker --version` responde **e** `docker info` mostra o daemon rodando.
- [ ] `./vendor/bin/sail up -d` sobe os containers sem erro.
- [ ] `./vendor/bin/sail artisan migrate` roda (prova que o **Postgres está acessível**).
- [ ] O banco **`testing`** existe (criado pelo Sail) e `./vendor/bin/sail artisan test` roda **sem alterar o banco de dev**.
- [ ] O app responde no endereço local (Sail expõe a porta web).

Se `sail up` falhar por porta ocupada ou Docker parado, ver `troubleshooting.md`. Só depois de tudo verde o `init-project` segue (ou conclui) o scaffold do MVP.
