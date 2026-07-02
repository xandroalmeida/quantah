# infra/ — homologação e produção do Quantah

Implementa o [`ADR-007`](../docs/project-state/decisions/adr/ADR-007-infra-vps-generica-gcp.md):
homolog/prod rodam numa **VPS genérica**. O GCP entra **só para levantar a VM** (IaaS); todo o resto
vive dentro dela via **Docker Compose**.

## Conteúdo

| Arquivo | Papel |
|---|---|
| `gcp/provision.sh` | Provisiona a VM no GCP (IP estático, firewall 22/80/443, VM Ubuntu, chave SSH de deploy). **Único ponto GCP.** |
| `gcp/startup.sh` | Startup-script da VM: instala Docker + Compose e prepara `/opt/quantah`. Serve em qualquer VPS. |
| `docker-compose.prod.yml` | O stack que roda na VM: Caddy (TLS) + app (imagem do GHCR) + PostgreSQL. |
| `Caddyfile` | Reverse proxy + HTTPS automático (Let's Encrypt) para `$SITE_ADDRESS`. |

## Provisionar (uma vez)

```bash
# gcloud autenticado na conta/projeto certos (config dedicada 'quantah'):
gcloud config configurations activate quantah   # conta rhhub, projeto command-center-8026a

PUBKEY_FILE=/caminho/deploy_ed25519.pub ./gcp/provision.sh
# -> imprime o IP público e o host de homolog (quantah-homolog.<IP>.sslip.io)
```

A chave privada correspondente vira o secret `DEPLOY_SSH_KEY` no GitHub (ver abaixo). Se a zona
estiver esgotada (`ZONE_RESOURCE_POOL_EXHAUSTED`), rode com `ZONE=southamerica-east1-a` (ou `-c`).

## Deploy (automático)

O deploy é feito pelo workflow [`.github/workflows/ci-cd.yml`](../.github/workflows/ci-cd.yml) a cada
push verde na `main`: build da imagem → GHCR → `scp` do compose/Caddyfile/`.env` → `docker compose
pull && up -d --wait && artisan migrate --force`. **Nenhum passo manual.**

### Secrets do GitHub necessários

| Secret | Conteúdo |
|---|---|
| `DEPLOY_HOST` | IP público da VM |
| `DEPLOY_USER` | `quantah` |
| `DEPLOY_SSH_KEY` | chave **privada** ed25519 de deploy |
| `PROD_ENV` | conteúdo do `.env` de produção (APP_KEY, DB_*, APP_URL, SITE_ADDRESS…) |

O pull da imagem no host usa o `GITHUB_TOKEN` do próprio workflow (sem PAT).

## Portabilidade (trocar de provedor)

Só `gcp/provision.sh` é específico do GCP. Para migrar (Hetzner/DigitalOcean/on-prem): crie 1 VM
Ubuntu com Docker, abra 22/80/443, coloque a chave de deploy, aponte `DEPLOY_HOST` para o novo IP.
O `docker-compose.prod.yml` e o `Caddyfile` não mudam.
