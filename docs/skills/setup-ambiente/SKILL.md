---
name: setup-ambiente
description: Sub-skill operacional — prepara a MÁQUINA para rodar o app, instalando o toolchain (PHP, Composer, Node, Git, Docker) quando ele não existe. Detecta o que já está instalado e instala só o que falta, respeitando versões mínimas. Crítica porque o Idealizador é uma pessoa NÃO-TÉCNICA: não se assume que nada está instalado — o AGENTE detecta e instala, e a pessoa só clica "permitir/continuar" em instaladores gráficos quando necessário. Serve tanto para POC (PHP estático project-local em `app/.bin/php`, sem instalação global — Herd como alternativa opcional) quanto para MVP (Docker Desktop + Laravel Sail, com Postgres). É chamada pelo init-project ANTES de instalar o esqueleto do app, e pode ser invocada de novo se o ambiente quebrar depois. Também faz o BOOTSTRAP turnkey de um POC recém-gerado pelo init-project: quando acionada no Claude Code sobre um projeto recém-criado, ela faz tudo sozinha (instala o ambiente se faltar, entra na pasta do app, instala dependências, prepara o banco e sobe o preview) e diz à pessoa para abrir http://localhost:8000 — o leigo não copia comando nenhum. Use quando o usuário disser "preparar ambiente", "instalar ferramentas", "não tenho php/composer/node", "o app não roda na minha máquina", "setup da máquina", "configurar ambiente de desenvolvimento", "prepare o ambiente e suba o preview", "suba o preview", "rodar o app", ou quando o init-project precisar garantir o ambiente antes do scaffold.
---

> **Projeto instanciado.** Os valores específicos deste projeto (nome, stack, caminhos, vocabulário) estão no `_project.md` na raiz das skills.

# Setup de ambiente — preparar a máquina para rodar o app

Esta sub-skill prepara a **máquina** onde o app vai rodar: detecta o sistema operacional e o que já está instalado, **instala só o que falta**, e verifica que tudo responde. Não escreve código do produto, não cria o esqueleto do app — só garante que o **toolchain** existe e funciona, para que o scaffold (no `init-project`) e o desenvolvimento sigam sem tropeço.

## Cowork × Claude Code — onde isso roda

O agente pode atuar em duas superfícies que compartilham a **mesma pasta conectada**: o **Cowork** (chat) e o **Claude Code** (terminal na máquina real). No Cowork o shell é um **sandbox efêmero** — **sem `sudo`** e onde **nenhum processo sobrevive ao fim de uma chamada**. Por isso o **setup de máquina e o servidor de preview rodam no Claude Code**, na máquina real do Idealizador; o **Cowork constrói** (o agente escreve/edita o código na pasta). Tudo nesta sub-skill — detectar, instalar toolchain, verificar — pressupõe o **Claude Code**. Detalhe da divisão (e do preview com hot reload) em `references/cowork-e-claude-code.md`.

Ela existe porque uma premissa do template é **não assumir nada**: no caminho POC quem opera é o **Idealizador, uma pessoa não-técnica** (ver `idealizador/SKILL.md`). Não dá para supor que PHP, Composer ou Node estão instalados. Quem detecta e instala é **você, o agente**; a pessoa apenas clica "permitir/continuar" num instalador gráfico quando for o caso.

> ⚠️ **Erro de DNS ao baixar = restrição do sandbox, NÃO máquina quebrada.** Se um download (curl/wget/git/composer — ex.: baixar o PHP estático ou o Composer) falhar com **`Could not resolve host`**, **`Temporary failure in name resolution`** ou **`Name or service not known`**, o diagnóstico correto é quase sempre **restrição de rede do ambiente sandbox** onde o comando rodou — **NÃO** a máquina do usuário com defeito. **Nunca** diga ao Idealizador que "a máquina dele não resolve DNS". **Ação:** rode o comando **fora do sandbox**, no ambiente real (Claude Code na máquina, com rede), ou peça à pessoa para rodá-lo no terminal dela. Os downloads (PHP estático, Composer, dependências) precisam de **rede real**; se o ambiente atual bloqueia DNS, **troque de ambiente** em vez de declarar falha de máquina. Detalhe em `references/troubleshooting.md` › "Erro de DNS ao baixar".

## O que é e quando roda

- **Chamada pelo `init-project` antes do scaffold.** O `init-project` materializa o projeto (ver `init-project/references/generation.md`); antes de instalar o esqueleto do app (passo 6 daquela reference), o ambiente precisa estar de pé. Esta sub-skill é o pré-requisito daquele passo.
- **Invocada avulsa quando o ambiente quebra.** Se mais tarde o app parar de subir por causa de ferramenta faltando, versão velha ou PATH bagunçado, rode esta sub-skill de novo — ela é idempotente por desenho (detecta antes de instalar, não reinstala o que já está bom).

Serve para **os dois caminhos**: POC (Idealizador) e MVP (time profissional). O que muda é **como** se instala (ver a tabela de abordagem abaixo).

## Bootstrap turnkey de um POC recém-gerado (caminho POC)

Quando você é acionada no **Claude Code** sobre um projeto **recém-criado pelo init-project** (o `COMECE-AQUI.md` na raiz mandou a pessoa abrir a pasta e pedir uma frase), faça **tudo de forma turnkey**, sem que o leigo copie comando nenhum. A pessoa diz **uma frase** — "prepare o ambiente e suba o preview", "suba o preview" ou "rodar o app" — e **você executa o resto**:

1. **Detecte/instale o ambiente** — baixe o **PHP estático local** em `app/.bin/php` se faltar (e o Composer local em `app/.bin/composer`); ver a ordem detectar → instalar → verificar abaixo.
2. **Entre na pasta do app** — `cd app/` (o código vive lá, não na raiz; a doc fica em `docs/`).
3. **Rode o bootstrap completo**, na ordem, dentro de `app/` (usando o **PHP/Composer locais**):
   - `.bin/php .bin/composer install`
   - `npm install` (Node do sistema, ou local — ver a reference de instalação)
   - `.bin/php artisan key:generate`
   - `.bin/php artisan migrate`
   - `.bin/php artisan serve` ou `composer run dev` ajustado para o php local (sobe servidor + fila + Vite com hot reload)
4. **Diga à pessoa, em linguagem simples, para abrir `http://localhost:8000`** no navegador — o app está rodando. Depois ela volta ao Cowork para pedir mudanças (o preview recarrega sozinho).

Fale **sem jargão** (ver "Comunicação com a pessoa não-técnica" abaixo): você conduz, ela só autoriza um instalador se aparecer. A regra é **uma frase da pessoa → a skill faz tudo**.

## Princípio central: detectar → instalar só o que falta → verificar

Sempre nesta ordem, sempre os três passos:

1. **Detectar** o OS e cada ferramenta já instalada, com versão (`references/detectar-e-verificar.md`).
2. **Instalar só o que falta**, respeitando as versões mínimas — nunca reinstalar o que já está bom, nunca derrubar o que funciona.
3. **Verificar** que tudo responde e nas versões certas (a mesma `references/detectar-e-verificar.md` serve de checklist final).

> Nunca pule a detecção e instale "por garantia". Reinstalar PHP/Node por cima de uma instalação boa é a forma mais rápida de quebrar o ambiente de alguém.

## Abordagem por caminho

Há três caminhos de instalação. Escolha pelo perfil do projeto (`_project.md` › fase) e pelo OS:

| Caminho | Abordagem | O que entrega | Quando | Reference |
|---|---|---|---|---|
| **POC (Idealizador)** | **PHP estático local em `app/.bin/php`** (sem instalação global; sem sudo) | Binário PHP-CLI estático (com `pdo_sqlite`) + Composer local; Node do sistema ou local. Autocontido e reproduzível | Pessoa não-técnica; qualquer OS — turnkey, o agente baixa e roda | `references/instalar-poc-php.md` |
| **POC — alternativa opcional** | **Laravel Herd** — instalador gráfico all-in-one (PHP/Composer/Node/Nginx **globais**) | Ambiente global com GUI, quase zero terminal | Só se a pessoa **prefere** um ambiente global/GUI (macOS/Windows) | `references/instalar-poc-php.md` › Alternativa: Laravel Herd |
| **MVP (time)** | **Docker Desktop + Laravel Sail** | Ambiente reproduzível em container; **Postgres de graça** via serviço do Sail | Time profissional, ambiente que espelha produção | `references/instalar-mvp-sail.md` |
| **Fallback nativo** | Homebrew (macOS) / winget (Windows) / apt (Linux) | php, composer, node instalados um a um | Sail não serve, ou a pessoa quer toolchain nativo do sistema no POC | nas duas references acima, na seção de Linux/fallback |

Regras de escolha (todos os downloads/instaladores abaixo rodam **no Claude Code / máquina real**, nunca no sandbox do Cowork — o sandbox não tem rede real nem persistência):

- **POC (qualquer OS) → PHP estático local.** É o caminho de menor atrito e mais turnkey: o agente **baixa o binário PHP-CLI estático** para o OS/arquitetura certos e o coloca em `app/.bin/php`, com Composer local em `app/.bin/composer`. **Sem instalação global, sem sudo, sem poluir a máquina** — e nada de instalador gráfico. Ver `references/instalar-poc-php.md`.
- **POC com preferência por ambiente global/GUI → Herd (opcional).** Se a pessoa **pede** um PHP/Composer/Node globais com interface gráfica (macOS/Windows), o Herd é a alternativa. Ver `references/instalar-poc-php.md` › Alternativa: Laravel Herd.
- **MVP → Docker Desktop + Sail.** Reproduzível e já entrega Postgres em container, sem instalar banco na máquina. Se o time já tem PHP/Composer nativos, há a alternativa de rodar nativo + Postgres em container (ver `references/instalar-mvp-sail.md`).

## Comunicação com a pessoa não-técnica

No caminho POC você está conversando com o **dono da ideia**, não com um engenheiro. O app é dele. Fale **sem jargão**:

- Explique em linguagem simples o que vai acontecer: *"Vou instalar uma ferramenta que faz o app rodar na sua máquina. Vai abrir um instalador — quando ele pedir, clique em 'continuar' e depois em 'permitir'."*
- **Mostre, não conte.** Diga exatamente o que a pessoa vai ver na tela e o que clicar. Conduza você o processo; ela só autoriza.
- Não despeje saída de terminal nem nomes de extensão de PHP. Traduza: *"PHP"* vira *"o motor que roda o app"*; *"Composer"* vira *"o instalador de peças do app"*; *"Node"* vira *"a ferramenta que monta a aparência"*.
- No caminho MVP, a comunicação pode ser técnica — é o time profissional.

## Procedimento em alto nível

1. **Detectar OS e ferramentas.** Descubra macOS/Windows/Linux e o que já está instalado (com versão). Reference: `references/detectar-e-verificar.md`.
2. **Instalar conforme o caminho e o OS.** POC → `references/instalar-poc-php.md` (PHP estático local; Herd como alternativa opcional); MVP → `references/instalar-mvp-sail.md`. Instale **só o que faltou** no passo 1.
3. **Verificar tudo de pé.** Rode de novo o checklist de `references/detectar-e-verificar.md`: cada ferramenta responde, nas versões mínimas; no POC, `app/.bin/php -m` lista `pdo_sqlite`; no MVP, Docker rodando e Postgres acessível.
4. **Se algo falhar.** Vá a `references/troubleshooting.md` — problemas comuns (PATH, versão velha, porta ocupada, Docker parado, `pdo_sqlite` faltando) em linguagem simples, com sintoma → checagem → solução.

Só depois de o passo 3 passar é que o `init-project` segue para o scaffold do app.

## Versões mínimas (toolchain do template)

| Ferramenta | Mínimo | Para quê |
|---|---|---|
| **PHP** | 8.2+ | Roda o Laravel (ver `stacks/laravel/SKILL.md`); inclui extensões comuns + `pdo_sqlite`. **No POC**: binário estático em `app/.bin/php` (não global) |
| **Composer** | 2.x | Instala as dependências PHP. **No POC**: local em `app/.bin/composer`, rodado pelo php local |
| **Node + npm** | 20+ | Build do frontend (Vite/Tailwind) — ver `stacks/livewire/SKILL.md`. **No POC**: do sistema, ou local se faltar |
| **Git** | qualquer recente | Versionamento |
| **SQLite (`pdo_sqlite`)** | vem com o PHP | Banco do POC — ver `stacks/database/sqlite/SKILL.md` |
| **Docker** | recente | MVP: Sail + Postgres em container — ver `stacks/database/postgres/SKILL.md` |

## O que você NUNCA faz

- **Reinstalar o que já existe e está bom.** Detecte antes; respeite versões mínimas. Reinstalação cega quebra ambientes.
- **Rodar comando destrutivo** (apagar versões, sobrescrever PATH global, remover instalações) sem necessidade clara e sem avisar.
- **Falar em jargão com o não-técnico.** No POC, traduza tudo para linguagem simples e conduza o processo.
- **Seguir para o scaffold com o ambiente não verificado.** O `init-project` só instala o esqueleto depois que `references/detectar-e-verificar.md` passa por inteiro.

## Referências (leia conforme a tarefa)

| Quando | Leia |
|---|---|
| Detectar OS e ferramentas; checklist de verificação final | `references/detectar-e-verificar.md` |
| Onde construir vs. rodar o preview (Cowork constrói, Claude Code roda) | `references/cowork-e-claude-code.md` |
| Instalar para o POC (PHP estático local em `app/.bin/php`; Herd como alternativa opcional) | `references/instalar-poc-php.md` |
| Instalar para o MVP (Docker Desktop + Laravel Sail + Postgres) | `references/instalar-mvp-sail.md` |
| Algo falhou — diagnóstico em linguagem simples | `references/troubleshooting.md` |
| Idiomático de cada stack | `stacks/laravel/SKILL.md`, `stacks/livewire/SKILL.md`, `stacks/database/sqlite/SKILL.md`, `stacks/database/postgres/SKILL.md` |
| Como o init-project usa este setup | `init-project/references/generation.md` |
| O papel do não-técnico (POC) | `idealizador/SKILL.md` |
| Fase atual e stack ativa | `_project.md` |
