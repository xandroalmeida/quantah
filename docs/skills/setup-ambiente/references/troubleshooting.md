# Troubleshooting — quando algo não responde

Problemas comuns ao preparar o ambiente, em **linguagem simples**. No POC você está com uma pessoa não-técnica: **não a assuste** — explique que isso é normal, e conduza a solução você mesmo. Para cada problema: **sintoma → o que o agente checa → como resolver**.

Antes de qualquer coisa, confirme o estado com `detectar-e-verificar.md`.

## 1. "Instalei, mas o comando não é encontrado" (PATH / terminal não reaberto)

- **Sintoma:** acabou de instalar/baixar (PHP estático local, Composer, Node, ou Herd…), mas o comando responde "comando não encontrado".
- **O agente checa:** **No POC com PHP local**, use o **caminho completo** — `app/.bin/php` não está no PATH por desenho; chame-o por `app/.bin/php` (ou `.bin/php` de dentro de `app/`) e confirme que o arquivo existe e tem `chmod +x`. Para toolchain **global** (Herd/sistema): o terminal foi **reaberto**? O binário está no PATH? (`which php` no Mac/Linux, `where php` no Windows.)
- **Como resolver:** **PHP local:** invoque pelo caminho project-local (`app/.bin/php`), garanta `chmod +x .bin/php` e que o binário foi baixado para o OS/arch certos. **Global (Herd/sistema):** **feche e reabra o terminal** (o PATH só atualiza em sessões novas); confirme que o instalador concluiu e que o app (Herd) está aberto. No Linux/fallback, garanta que o diretório do binário (ex. `/usr/local/bin`) está no PATH.
- **Ao não-técnico:** *"Isso é normal logo depois de instalar. Vou fechar e reabrir o terminal e tentar de novo — não é erro seu."*

## 2. Versão antiga do PHP (ou Composer/Node)

- **Sintoma:** `php -v` responde, mas é **8.1** (abaixo do mínimo 8.2); ou Composer 1.x; ou Node abaixo de 20.
- **O agente checa:** qual versão respondeu e **de onde** vem (`which php` / `where php`). É possível que haja **duas instalações** e o PATH esteja apontando para a velha.
- **Como resolver:**
  - **POC com PHP local:** o binário estático em `app/.bin/php` é a versão correta — **chame-o pelo caminho project-local** em vez do `php` do sistema (que pode ser velho). Se baixou um binário abaixo de 8.2, rebaixe a versão certa (ver `instalar-poc-php.md`).
  - **POC com Herd (alternativa):** o Herd traz PHP atual; garanta que o `php` do PATH é o do Herd (não um PHP velho do sistema). Reordene o PATH ou desinstale o PHP antigo conflitante (com cuidado, avisando antes).
  - **Fallback nativo:** instale a versão 8.2+ pelo gerenciador e ajuste o PATH/`update-alternatives` para ela ser a padrão.
- **Cuidado:** não remova versões cegamente — confirme que nada do sistema depende delas.

## 3. Permissões (instalador pede senha / acesso negado)

- **Sintoma:** o instalador pede senha de administrador; ou um comando falha com "permission denied".
- **O agente checa:** se a ação realmente precisa de privilégio (instalar app, escrever em diretório do sistema).
- **Como resolver:** no instalador gráfico (Herd/Docker), **autorizar** o pedido de senha/permissão é esperado — explique ao não-técnico que é seguro. No Linux, comandos de sistema usam `sudo`; permissões de arquivo de projeto se resolvem com o dono correto (não com `chmod 777` cego).
- **Ao não-técnico:** *"O instalador vai pedir sua senha do computador para se instalar. Pode digitar — é o passo normal de qualquer instalação."*

## 4. Porta ocupada (app não sobe; "port already in use")

- **Sintoma:** ao subir o app (`app/.bin/php artisan serve`, Herd, ou `sail up`), erro de **porta em uso** (ex. 8000, 80, 5432 do Postgres).
- **O agente checa:** qual processo está usando a porta (`lsof -i :8000` no Mac/Linux; `netstat -ano` no Windows).
- **Como resolver:** subir o app em **outra porta** (ex. `app/.bin/php artisan serve --port=8001`), ou fechar o programa que ocupa a porta. No Sail, ajustar a porta exposta no `.env`/compose. Outro servidor local rodando é causa comum.
- **Ao não-técnico:** *"Já tem outro programa usando o 'endereço' do app. Vou colocar o app num endereço diferente — nada quebrou."*

## 5. Docker não iniciado (MVP)

- **Sintoma:** `docker --version` responde, mas `docker info` dá erro tipo "Cannot connect to the Docker daemon"; `sail up` falha.
- **O agente checa:** o **Docker Desktop está aberto** e o daemon **subiu** (ícone ativo na barra/bandeja)?
- **Como resolver:** **abrir o Docker Desktop e esperar** ele iniciar por completo (alguns segundos até o ícone ficar estável). No Linux, iniciar o serviço (`sudo systemctl start docker`) e confirmar que o usuário está no grupo `docker`. Depois, repetir `docker info` e `sail up`.

## 6. Falta de `pdo_sqlite` (POC não grava em banco)

- **Sintoma:** `app/.bin/php -m | grep sqlite` (ou `php -m | grep sqlite`) **não lista** `pdo_sqlite`; o app POC falha ao acessar o banco.
- **O agente checa:** qual PHP está em uso (no POC, deve ser `app/.bin/php`) e se a extensão está embutida/habilitada.
- **Como resolver:**
  - **PHP estático local (padrão POC):** o binário estático já **embute** `pdo_sqlite` — se não lista, é porque foi baixado o binário **errado** (sem as extensões comuns) ou para o OS/arch errado. Rebaixe o binário com `pdo_sqlite` para o OS/arquitetura certos (ver `instalar-poc-php.md` § 1).
  - **Herd (alternativa):** o PHP do Herd já traz SQLite — em geral o problema é o PATH apontar para outro PHP (ver item 2). Garanta o PHP do Herd.
  - **Fallback nativo (Linux):** instale o pacote da extensão (ex. `php8.2-sqlite3`) e reabra o terminal; se ela existe mas está off, habilite a linha `extension=pdo_sqlite` no `php.ini` e refaça `php -m | grep sqlite`.
- **Por que importa:** sem `pdo_sqlite` o POC não persiste — fere o item 2 da barra de viabilidade (`idealizador/SKILL.md`).

## 7. Erro de DNS ao baixar ("Could not resolve host") — sandbox, não máquina quebrada

- **Sintoma:** um download (curl/wget/git/composer — ex.: baixar o **PHP estático**, o **Composer** ou as **dependências**) falha com **`Could not resolve host`**, **`Temporary failure in name resolution`** ou **`Name or service not known`**.
- **O agente checa:** **onde** o comando rodou. Se foi no **sandbox efêmero do Cowork**, ele **não tem rede real** — bloqueio de DNS ali é **esperado**, não defeito da máquina do usuário.
- **Diagnóstico correto:** quase sempre é **restrição de rede do ambiente sandbox**, **NÃO** a máquina do usuário com DNS quebrado. **Nunca** conclua nem diga ao usuário que "a máquina dele não resolve DNS".
- **Como resolver:** rode o comando **fora do sandbox** — no **ambiente real** (Claude Code na máquina do usuário, com rede), ou peça à pessoa para rodá-lo no terminal dela. Os downloads (PHP estático, Composer, dependências) precisam de **rede real**; se o ambiente atual bloqueia DNS, **troque de ambiente** em vez de declarar falha de máquina. (Mesma lógica do "setup roda no Claude Code, não no Cowork" — ver `cowork-e-claude-code.md`.)
- **Ao não-técnico:** *"O download não passou porque este ambiente de testes não tem internet aberta — não é problema do seu computador. Vou rodar isso no Claude Code, na sua máquina, onde a internet funciona."*

## 8. Quando nada resolve

- Volte ao **princípio**: detectar → instalar só o que falta → verificar (`detectar-e-verificar.md`). Confirme OS, versões e PATH do zero.
- No POC, se a instalação nativa estiver difícil, **Docker é o plano B** (ver `instalar-mvp-sail.md`) — roda o app em container sem depender do toolchain local.
- Nunca rode comando destrutivo para "forçar" — prefira isolar a causa. Em último caso, reinstalar a ferramenta problemática (só ela) pelo caminho da reference de instalação.
