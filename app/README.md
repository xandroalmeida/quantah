# Quantah — app (Laravel + Inertia/React + PostgreSQL)

Plataforma de inteligência de preços via NFC-e. Este diretório é o app: **Laravel 13** servindo
**React via Inertia** (PWA), com **PostgreSQL**. Stack ratificada no
[`ADR-000`](../docs/project-state/decisions/adr/ADR-000-stack-default.md).

## Ambiente de desenvolvimento — um comando

Pré-requisitos: **Docker** (com Compose). Não é preciso ter PHP/Node na máquina — tudo roda no
[Laravel Sail](https://laravel.com/docs/sail).

```bash
cd app
make up
```

`make up`, a partir de um clone limpo: cria o `.env`, instala o vendor (inclusive o próprio Sail)
sem depender de PHP local, sobe **app + PostgreSQL**, gera a `APP_KEY`, instala o front, faz o build
e roda `migrate --seed`. Ao final, o app está em **http://localhost:8000** (usuário de seed:
`test@example.com`).

Outros atalhos (veja `make help`):

| Comando | O quê |
|---|---|
| `make test` | Suíte unit + feature |
| `make e2e` | E2E em browser real (Laravel Dusk via Selenium) |
| `make fresh` | Recria o banco do zero com seed |
| `make down` | Derruba os containers |

> **Vários agentes na mesma máquina?** Suba um stack isolado com nome de projeto e portas próprias
> (`COMPOSE_PROJECT_NAME`, `APP_PORT`, `FORWARD_DB_PORT`, `VITE_PORT`) para não colidir com outro
> Sail rodando o mesmo repositório (ver evidência do STORY-000).

## Testes

- **Unit/Feature** (PHPUnit): `make test` — rodam contra o banco de teste (`testing`), nunca o de dev.
- **E2E** (Laravel Dusk — [`ADR-008`](../docs/project-state/decisions/adr/ADR-008-ferramenta-e2e-dusk.md)):
  `make e2e`. Sobe um Chrome real via Selenium e exercita o fluxo no navegador. O `.env.dusk.local`
  é gerado a partir do `.env` (modelo em `.env.dusk.example`).

## Pipeline e homologação (CI/CD)

Definido em [`.github/workflows/ci-cd.yml`](../.github/workflows/ci-cd.yml):

- **CI** (todo push/PR na `main`): testes + build + E2E Dusk. Vermelho barra a entrega.
- **CD** (push na `main`, com CI verde): build da imagem de produção (`Dockerfile`), push no GHCR e
  **deploy automático para homologação** numa VPS (GCP só provisiona a VM —
  [`ADR-007`](../docs/project-state/decisions/adr/ADR-007-infra-vps-generica-gcp.md)). O provisionamento
  da VM é versionado em [`infra/`](../infra/).

Homologação: **https://quantah-homolog.\<IP\>.sslip.io** (TLS automático via Caddy).

## Estrutura relevante

- `routes/web.php` — rota `/` serve a hello-world do Quantah via Inertia.
- `resources/js/Pages/Hello.jsx` — a página React da hello-world.
- `Dockerfile` + `docker/` — imagem de produção (nginx + php-fpm).
- `tests/Feature/HelloWorldTest.php` / `tests/Browser/HelloWorldTest.php` — testes da entrega.
