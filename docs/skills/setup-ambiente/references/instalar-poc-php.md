# Instalar para o POC — PHP estático, project-local

No POC quem opera é o **Idealizador, pessoa não-técnica** (`idealizador/SKILL.md`). O padrão é o **PHP estático, project-local**: um binário PHP-CLI estático que **você (agente) baixa** e coloca **dentro do projeto**, em **`app/.bin/php`** — **sem instalação global na máquina**, sem `sudo`, sem poluir o sistema. É autocontido e reproduzível, e ainda **mais turnkey** para o leigo: nada de instalador gráfico — o agente baixa o binário e roda.

Antes de baixar, **detecte** (ver `detectar-e-verificar.md`): se já existe `app/.bin/php` funcionando (ou, em segundo lugar, um `php` do sistema nas versões mínimas), **não baixe de novo**. O PHP local é o caminho quando o motor do app não está presente no projeto.

> **Alternativa opcional — Laravel Herd (ambiente global/GUI).** Para quem **prefere** um ambiente global com interface gráfica (PHP + Composer + Node + Nginx + sites locais num instalador all-in-one), o Herd continua sendo uma opção válida no macOS/Windows — ver a seção "Alternativa: Laravel Herd" no fim deste arquivo. Mas o **padrão do POC é o PHP estático local**: turnkey, sem sudo, sem instalação global.

## O que vamos montar (project-local, em `app/.bin/`)

| Peça | Onde | Por que importa |
|---|---|---|
| **PHP-CLI estático** (8.2+, com extensões comuns + `pdo_sqlite`) | `app/.bin/php` | O motor que roda o app — sem instalar PHP global |
| **Composer** (`composer.phar`) | `app/.bin/composer` | Instala as dependências do app, rodado pelo PHP local |
| **Node + npm** (20+) | sistema, se já existe; senão Node local | Monta a aparência (Vite/Tailwind) |

Tudo isso fica **dentro do projeto** e **não vai para o git** — o binário é por OS/arquitetura e **recriável** (o `.gitignore` do projeto ignora `app/.bin/`; ver `init-project/references/generation.md`).

## 1. Baixar o PHP-CLI estático para `app/.bin/php`

A ideia: baixar um **binário PHP-CLI estático** — já com as **extensões comuns + `pdo_sqlite`** embutidas — para o **OS e a arquitetura corretos**, colocá-lo em `app/.bin/php` e dar `chmod +x`.

**Detecte OS/arquitetura primeiro** (ver `detectar-e-verificar.md`) e baixe o binário compatível:

| OS | Arquitetura | Observação |
|---|---|---|
| **Linux** | `x86_64` ou `aarch64` (arm64) | binário estático |
| **macOS** | `arm64` (Apple Silicon) ou `x86_64` (Intel) | binário estático |
| **Windows** | `x86_64` | binário estático **ou** o Herd (alternativa) |

**Fontes idiomáticas** (descreva a ação e a fonte — **não** fixe URL/versão frágil, que envelhece):

- **static-php-cli** — projeto que **publica binários PHP-CLI estáticos** prontos (distribuídos via **static-php.dev**). Pegue o binário do OS/arquitetura detectados, com as extensões comuns + `pdo_sqlite`.
- **FrankenPHP** — **binário único estático** que embute o PHP e **também serve a app** (servidor app). Útil quando se quer um único executável que roda e serve; também provê um PHP-CLI estático.

Esqueleto do que o agente faz (ajuste a fonte/URL real e o nome do arquivo ao OS/arch detectados — **não invente** a URL exata):

```bash
cd app/
mkdir -p .bin
# baixe o binário PHP-CLI estático correto para o OS/arquitetura detectados
# (de static-php.dev / static-php-cli, ou o FrankenPHP) para .bin/php
#   curl -L <url-do-binario-estatico-para-este-OS-arch> -o .bin/php
chmod +x .bin/php
```

> **Os downloads precisam de rede real.** Baixar o binário (e depois o Composer e as dependências) exige acesso à internet **com DNS funcionando**. Se o comando falhar ao **resolver nome** (ex. `Could not resolve host`), quase sempre é **restrição de rede do ambiente sandbox** — **não** a máquina do usuário. Rode no ambiente real (Claude Code na máquina, com rede) e veja a nota em `SKILL.md` › "Erro de DNS ao baixar" e o item próprio em `troubleshooting.md`.

## 2. Composer local em `app/.bin/composer`

O Composer também fica **local**, executado pelo **PHP local**. Baixe o `composer.phar` para `app/.bin/composer` (ou um wrapper que chame o php local):

```bash
cd app/
# baixe o composer.phar oficial para .bin/composer
#   .bin/php -r "copy('https://getcomposer.org/installer', '.bin/composer-setup.php');"
#   .bin/php .bin/composer-setup.php --install-dir=.bin --filename=composer
#   rm .bin/composer-setup.php
```

A partir daí, **todo Composer roda pelo PHP local**:

```bash
app/.bin/php app/.bin/composer install
```

(de dentro de `app/`, fica `.bin/php .bin/composer install`).

## 3. Node (para o Vite/Tailwind)

O Node é necessário para o **build do frontend** (Vite/Tailwind). Mantenha simples — o foco do usuário é o PHP:

- **Se `node` já existe no sistema** (`node -v` ≥ 20), **use-o**. Nada a instalar.
- **Se não existe**, instale um **Node local** (sem global) — por exemplo via um gerenciador de versões que isola a instalação (nvm/fnm) ou um Node portátil dentro do projeto. Não instale Node global na máquina do usuário.

Confirme `node -v` (20+) e `npm -v` respondendo, seja o do sistema, seja o local.

## 4. Verificar

Depois de montar, confirme — de dentro de `app/` (ou com o caminho completo):

```bash
app/.bin/php -v            # responde 8.2+
app/.bin/php -m | grep sqlite   # lista pdo_sqlite (e sqlite3)
app/.bin/php app/.bin/composer --version   # Composer 2.x via php local
node -v                            # 20+ (sistema ou local)
```

- `app/.bin/php -v` **responde** → o motor do app está de pé.
- `app/.bin/php -m` **lista `pdo_sqlite`** → o banco do POC funciona (SQLite é um arquivo único; ver `stacks/database/sqlite/SKILL.md`).

Volte a `detectar-e-verificar.md` › checklist final antes de o `init-project` seguir para o scaffold.

## SQLite no POC

O **SQLite vem embutido** no binário PHP estático (a extensão `pdo_sqlite`), então **não há banco a instalar** no POC — o banco é um arquivo único (`app/database/database.sqlite`), criado no scaffold pelo `init-project`. Detalhe idiomático em `stacks/database/sqlite/SKILL.md` (use só o essencial de POC: `foreign_keys=ON`, WAL). Basta confirmar `app/.bin/php -m | grep sqlite`.

## O binário é por OS/arquitetura e NÃO vai pro git

`app/.bin/php` (e o Composer/Node locais) é específico do **OS e da arquitetura** da máquina e é **recriável** — quem clonar o projeto em outra máquina baixa o binário certo de novo. Por isso `app/.bin/` está no `.gitignore` do projeto (ver `init-project/references/generation.md` › passo 10). Não versione binários.

## Alternativa: Laravel Herd (ambiente global/GUI — opcional)

Para quem **prefere** um ambiente **global** com interface gráfica em vez do PHP local, o **Laravel Herd** é um instalador all-in-one que traz **PHP + Composer + Node + Nginx + sites locais** — quase nada de terminal. Cobre **macOS e Windows** (no Linux não se aplica).

> **Quando faz sentido:** a pessoa quer um PHP/Composer/Node **globais** na máquina e gosta de uma GUI. **Trade-off:** instala na máquina toda (não fica project-local), pode pedir senha/permissão e "polui" o sistema com um toolchain global. O **padrão do POC é o PHP estático local** — prefira-o salvo pedido explícito por um ambiente global.

**macOS:** baixe o instalador do Herd no site oficial (`https://herd.laravel.com`), arraste para Aplicativos e abra; na primeira execução ele configura PATH e serviços — **autorize** quando o sistema pedir. (Se o Homebrew já existe: `brew install --cask herd`.)

**Windows:** baixe o instalador do site oficial e siga "Avançar / Instalar"; quando o Controle de Conta de Usuário perguntar, clique **Sim**. (Ou `winget install --id Laravel.Herd`.)

> **O que dizer ao Idealizador (se for por Herd):** *"Vou baixar uma ferramenta chamada Herd — ela faz seu app rodar. Quando o instalador abrir, é só seguir 'continuar/permitir'. É seguro, sou eu conduzindo."*

Depois de instalar o Herd, reabra o terminal e rode o checklist de `detectar-e-verificar.md`: `php -v` (8.2+), `composer -V` (2.x), `node -v` (20+), e `php -m | grep sqlite` lista `pdo_sqlite`. Confirme que o `php` do PATH é o do Herd (`which php` / `where php`).

## Linux — PHP estático local (padrão) ou fallback nativo

No Linux **o padrão continua sendo o PHP estático local** (`app/.bin/php`, seções 1–4 acima — baixe o binário Linux `x86_64`/`aarch64`). Herd não existe no Linux.

Se preferir um toolchain **nativo** do sistema (em vez do binário local), instale **só o que faltou** pelo gerenciador da distro. Exemplo idiomático em Debian/Ubuntu (ajuste à distro — **não invente** nomes de pacote):

```bash
# PHP 8.2+ com extensões comuns (inclui pdo_sqlite)
sudo apt update
sudo apt install -y php8.2-cli php8.2-sqlite3 php8.2-mbstring php8.2-xml php8.2-curl php8.2-zip git
# Composer 2.x e Node 20+ pelo caminho oficial/gerenciador de versões
```

> O importante: PHP 8.2+ **com a extensão sqlite**, Composer 2.x, Node 20+. Confirme com `detectar-e-verificar.md`.

### Docker

Se a pessoa preferir não baixar binário nem instalar nada nativo, dá para rodar o POC em container — siga `instalar-mvp-sail.md` (Docker + Sail funciona também no POC; o banco pode continuar SQLite, ou já usar Postgres do Sail).

## Verificação final

Volte a `detectar-e-verificar.md` › checklist final. Tudo verde antes de o `init-project` seguir para o scaffold (`init-project/references/generation.md` › passo 7, POC).
