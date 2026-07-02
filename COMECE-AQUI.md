# Bem-vindo ao Quantah!

Seu projeto está montado. O **Quantah** é uma plataforma de inteligência de preços via NFC-e: os
consumidores enviam os cupons fiscais que já recebem, e isso vira uma base de preços do varejo em tempo
quase real — vendida como inteligência de mercado (B2B).

Este projeto nasceu em fase **MVP** (engenharia desde o dia 1), a partir do documento de visão em
`docs/visao.md`.

## Para ver o app rodando (uma vez)

1. **Abra esta pasta no Claude Code.** (É o aplicativo do Claude que roda aqui na sua máquina.)
2. **Digite:** *"prepare o ambiente e suba o preview"*.
3. **Pronto.** O Claude instala o que faltar (PHP, banco, dependências), monta o esqueleto do app e abre
   no navegador, em **http://localhost:8000**.

> Pode aparecer um instalador no meio do caminho — é normal, é só permitir/continuar. O Claude conduz;
> você só autoriza. (O esqueleto do app ainda não foi criado aqui no chat porque isso depende de
> ferramentas que só existem na sua máquina — a `setup-ambiente` cuida disso no primeiro comando.)

Depois que o app estiver de pé, **volte ao Cowork** para continuar. O app recarrega sozinho conforme as
mudanças acontecem.

## O que é cada pasta

- **`app/`** — será o seu aplicativo (Laravel + React). O Claude trata disso.
- **`docs/visao.md`** — a visão completa do produto, negócio e marca (a fonte de verdade).
- **`docs/`** — a documentação do projeto: as skills, o estado (épicos, decisões), a especificação.

## O próximo passo de produto

Como o projeto é **MVP**, quem conduz é o **Product Owner** (a skill `po`): rodar o Fluxo 0 (visão,
personas, north-star) lendo a `docs/visao.md`, e então o **Arquiteto** ratifica ou troca a stack pelo
**ADR-000** já criado. É só pedir ao Claude: *"assuma o papel de PO e comece pelo Fluxo 0"*.
