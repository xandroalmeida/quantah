# Detectar e verificar — o que já existe na máquina

Esta reference cobre **dois momentos** com a **mesma checklist**:

1. **Antes de instalar** — descobrir o OS e o que já está instalado (com versão), para instalar **só o que falta**.
2. **No fim** — verificação final: tudo responde, nas versões mínimas. O `init-project` só segue para o scaffold quando esta checklist passa por inteiro.

Princípio: **detectar antes de instalar**. Nunca reinstale o que já está bom.

## 1. Detectar o sistema operacional

| OS | Como confirmar |
|---|---|
| **macOS** | `uname -s` → `Darwin`; `sw_vers` mostra a versão |
| **Linux** | `uname -s` → `Linux`; `cat /etc/os-release` mostra a distro (Ubuntu/Debian/etc.) |
| **Windows** | `ver` (cmd) ou `$PSVersionTable` (PowerShell); presença de `winget` |

O OS define **como** instalar (Herd só Mac/Windows; apt no Linux; winget no Windows) — ver as references de instalação.

## 2. Detectar cada ferramenta (com versão)

Rode cada comando de checagem. Se o comando **responde com versão**, a ferramenta existe — compare com o mínimo. Se **"comando não encontrado"** (ou erro), falta instalar (ou está fora do PATH — ver `troubleshooting.md`).

> **No POC, prefira o PHP/Composer project-local.** Cheque **primeiro** `app/.bin/php -v` e `app/.bin/php app/.bin/composer --version`; só se não existirem, caia para o `php`/`composer` do sistema. O padrão do POC é o **binário estático local** em `app/.bin/php` (sem instalação global) — ver `instalar-poc-php.md`.

| Ferramenta | Comando de checagem | Versão mínima | Se faltar… |
|---|---|---|---|
| **PHP** | **POC:** `app/.bin/php -v` (preferido) → senão `php -v` | **8.2+** | Sem o motor que roda o app. Baixar o **PHP estático local** em `app/.bin/php` (POC); nativo/Docker no MVP. Ver `instalar-poc-php.md`. |
| **Composer** | **POC:** `app/.bin/php app/.bin/composer --version` → senão `composer -V` | **2.x** | Sem o instalador de dependências PHP. Baixar o Composer local em `app/.bin/composer` (POC). |
| **Node** | `node -v` | **20+** | Sem a ferramenta que monta a aparência (Vite/Tailwind). Usar o do sistema, ou instalar Node local (POC). |
| **npm** | `npm -v` | vem com Node | Idem Node. |
| **Git** | `git --version` | recente | Sem versionamento. Instalar pelo gerenciador do OS. |
| **Docker** | `docker --version` **e** `docker info` | recente | **Só MVP** — sem container para Sail/Postgres. `docker info` falhando = Docker instalado mas **não iniciado** (ver `troubleshooting.md`). |

> `docker --version` só prova que o binário existe; `docker info` prova que o **daemon está rodando**. No MVP, cheque os dois.

### Versão velha conta como "falta"

Se `php -v` responde **8.1** (abaixo do mínimo 8.2), trate como ferramenta a corrigir — não como "ok". O mesmo vale para Composer 1.x e Node abaixo de 20. Ver `troubleshooting.md` › versão antiga.

## 3. Detectar o PHP do POC (project-local; Herd opcional)

No POC, o padrão é o **PHP estático local** em `app/.bin/php`. Para saber o que já existe:

- **PHP local presente:** `app/.bin/php -v` responde 8.2+ e `app/.bin/php -m | grep sqlite` lista `pdo_sqlite`. Se sim, **não baixe de novo** — o motor do app já está no projeto.
- **Composer local presente:** `app/.bin/php app/.bin/composer --version` responde 2.x.
- **Nada local:** se `app/.bin/php` não existe **e** o `php` do sistema também falta (ou está abaixo do mínimo), baixe o **PHP estático local** (ver `instalar-poc-php.md`).
- **Alternativa Herd (opcional):** se a pessoa optou por um ambiente **global** com GUI, o Herd aparece em Aplicativos (macOS) / Menu Iniciar (Windows) e coloca `php`/`composer`/`node` no PATH — confirme com `which php` / `where php` apontando para dentro do Herd. Ver `instalar-poc-php.md` › Alternativa: Laravel Herd.

## 4. Confirmar a extensão `pdo_sqlite` (banco do POC)

O SQLite (`stacks/database/sqlite/SKILL.md`) é o banco do POC e depende da extensão `pdo_sqlite` do PHP — que **vem embutida** no binário PHP estático local (e no PHP do Herd). Confirme (preferindo o php local no POC):

```bash
app/.bin/php -m | grep sqlite   # POC: deve listar pdo_sqlite (e sqlite3)
php -m | grep sqlite                    # senão, o php do sistema
```

Se **não aparecer**, falta habilitar/instalar a extensão — ver `troubleshooting.md` › falta de `pdo_sqlite`. Sem ela, o app POC não grava em banco (item 2 da barra de viabilidade do Idealizador).

## 5. Checklist de verificação final

Use esta lista como o **gate** antes do scaffold. Tudo precisa estar verde:

- [ ] **OS identificado** (macOS / Windows / Linux) **e arquitetura** (x86_64 / arm64).
- [ ] **POC:** `app/.bin/php -v` responde **8.2+** (ou, se Herd/sistema, `php -v`).
- [ ] **POC:** `app/.bin/php app/.bin/composer --version` responde **2.x** (ou `composer -V`).
- [ ] `node -v` responde **20+** e `npm -v` responde.
- [ ] `git --version` responde.
- [ ] **POC:** `app/.bin/php -m | grep sqlite` lista `pdo_sqlite`.
- [ ] **MVP:** `docker --version` responde **e** `docker info` mostra o daemon rodando.
- [ ] **POC (alternativa Herd):** se optou por Herd, o `php`/`composer`/`node` do PATH são os do Herd.

Se algum item falha, **não** siga para o scaffold: volte à reference de instalação do caminho ou a `troubleshooting.md`.
