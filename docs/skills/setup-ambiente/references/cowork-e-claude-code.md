# Cowork × Claude Code — onde construir e onde rodar o preview

O agente pode atuar em **duas superfícies** que apontam para a **mesma pasta conectada** do projeto. Entender a divisão de trabalho entre elas é o que faz o ciclo "mudar e ver ao vivo" funcionar sem atrito — sobretudo para o Idealizador, que é uma pessoa não-técnica.

## As duas superfícies e a pasta compartilhada

- **Cowork (o chat).** É onde a pessoa **conversa** com o agente e onde o agente **escreve e edita o código** do app, direto na pasta conectada. Ótimo para construir. Mas o shell do Cowork é um **sandbox Linux efêmero**: **não tem `sudo`** (não dá para `apt install` nem instalar toolchain de sistema) e **nenhum processo sobrevive ao fim de uma chamada** — um servidor que você suba ali nasce e morre em segundos. Logo, no Cowork **não** se instala ambiente de máquina nem se roda servidor de preview persistente.
- **Claude Code (o terminal na máquina real).** É o ambiente **persistente**, rodando na própria máquina do Idealizador. Lá o download do **PHP estático local** (em `app/.bin/php`) e os comandos do app funcionam — há **rede real** e os processos sobrevivem — e um servidor de preview **fica de pé** enquanto a pessoa olha. É onde se prepara o ambiente e onde se sobe o preview.

Como **as duas apontam para a mesma pasta**, o que o Cowork edita aparece imediatamente para o servidor que está rodando no Claude Code.

## O que fazer em cada superfície

| Tarefa | Cowork (chat, sandbox efêmero) | Claude Code (máquina real, persistente) |
|---|---|---|
| Conversar com o Idealizador, decidir produto/fluxos/telas | ✅ aqui | — |
| **Escrever/editar o código** do app (em `app/`) na pasta | ✅ aqui | (também dá, mas o normal é editar no Cowork) |
| **Setup do ambiente** (baixar PHP estático local, Composer local, Node, Docker/Sail) | ❌ sem rede real / não persiste | ✅ aqui |
| `.bin/php .bin/composer install`, `.bin/php artisan migrate`, testes longos | ❌ processo morre por chamada | ✅ aqui (persiste) |
| **Rodar o servidor de preview** (`composer run dev`) | ❌ morre em segundos | ✅ aqui — fica de pé |

Regra de bolso: **o Cowork constrói; o Claude Code prepara a máquina e roda o que precisa ficar de pé.** Tudo que é "instalar no sistema" ou "manter um processo vivo" é Claude Code.

## O fluxo do Idealizador

O modelo de trabalho validado é um vai-e-volta simples:

1. **Constrói no Cowork** — pede mudanças no chat; o agente edita os arquivos do app (em `app/`) na pasta conectada.
2. **Sobe o preview no Claude Code** — quando quer ver rodando, abre o Claude Code na própria máquina e **diz uma frase**: *"prepare o ambiente e suba o preview"*. A skill `setup-ambiente` faz o resto sozinha (entra em `app/`, instala o que falta e sobe o preview com `composer run dev`). **A pessoa não copia comando nenhum.** O servidor fica de pé.
3. **Volta ao Cowork e continua** — pede a próxima mudança. Não precisa derrubar nem reiniciar o preview: ele recarrega sozinho (ver abaixo).

A construção segue sempre no Cowork; o Claude Code fica como a "janela ao vivo" do que está sendo construído.

## O hot reload garantido

A ponte entre as duas superfícies é o **hot reload**: como ambas veem a mesma pasta, quando o Cowork salva um arquivo o servidor rodando no Claude Code **recarrega sozinho** e a pessoa vê a mudança ao vivo, sem reiniciar nada. Para garantir isso no scaffold:

- **Vite com HMR ligado.** O Laravel já vem com Vite; o HMR (hot module replacement) atualiza CSS/JS sem recarregar a página inteira.
- **Um comando para subir tudo.** No Laravel atual, `composer run dev` sobe **servidor + fila + Vite** num comando único. É o comando do preview.
- **Livewire já é reativo.** Componentes Livewire atualizam sem reload cheio da página, o que reforça a sensação de "ao vivo".
- **Fallback de file-watching (`usePolling`).** Quando a pasta é sincronizada e o Vite não percebe as mudanças do Cowork, ligue o polling no `vite.config.js`:

  ```js
  // vite.config.js
  export default defineConfig({
    // ...
    server: {
      watch: { usePolling: true },
    },
  });
  ```

  Quando aplicável, os equivalentes de ambiente são `CHOKIDAR_USEPOLLING=true` e `WATCHPACK_POLLING=true`.

**Preview e URL:** no Claude Code, a pessoa só pede *"prepare o ambiente e suba o preview"* — a skill `setup-ambiente` entra em `app/`, usa o **php do projeto** (`app/.bin/php`) e sobe o preview por ela (`.bin/php artisan serve` ou `composer run dev` ajustado ao php local) — e então abre **`http://localhost:8000`** (ou a URL do site, se a pessoa optou pelo Laravel Herd). Deixe rodando enquanto trabalha no Cowork.

## Preview de desenvolvimento ≠ deploy durável

Não confunda os dois:

- **Preview de desenvolvimento** — é o `composer run dev` rodando **localmente no Claude Code**, com hot reload, enquanto o Idealizador olha. Some quando ele fecha o Claude Code. Serve para construir e validar.
- **Deploy durável** — um link estável num **host**, que roda **sem** o Claude Code aberto (ex.: para mandar a um investidor olhar no fim de semana). É um passo **separado e futuro**, de outro momento, e **não** é coberto aqui.

Em particular: **não existe "túnel de preview ao vivo a partir do sandbox" do Cowork** — isso foi descartado por teste, porque os processos morrem a cada chamada. O preview ao vivo é sempre local, no Claude Code.
