#!/usr/bin/env bash
# startup-script da VM de homologação do Quantah.
# Roda na primeira inicialização (metadata startup-script). Idempotente: pode reexecutar.
# Instala Docker + plugin compose e prepara o diretório de deploy. NADA específico do GCP aqui
# além do fato de rodar como startup-script — o mesmo passo serve em qualquer VPS (ver infra/README.md).
set -euo pipefail

export DEBIAN_FRONTEND=noninteractive

# 1) Docker Engine + compose plugin (repo oficial)
if ! command -v docker >/dev/null 2>&1; then
  apt-get update -y
  apt-get install -y ca-certificates curl gnupg
  install -m 0755 -d /etc/apt/keyrings
  curl -fsSL https://download.docker.com/linux/ubuntu/gpg -o /etc/apt/keyrings/docker.asc
  chmod a+r /etc/apt/keyrings/docker.asc
  echo \
    "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.asc] https://download.docker.com/linux/ubuntu \
    $(. /etc/os-release && echo "$VERSION_CODENAME") stable" > /etc/apt/sources.list.d/docker.list
  apt-get update -y
  apt-get install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin
  systemctl enable --now docker
fi

# 2) Usuário de deploy no grupo docker (o mesmo criado via ssh-keys metadata)
DEPLOY_USER="${DEPLOY_USER:-quantah}"
if id "$DEPLOY_USER" >/dev/null 2>&1; then
  usermod -aG docker "$DEPLOY_USER" || true
fi

# 3) Diretório de deploy (onde o compose de produção e o .env vivem)
install -d -o "$DEPLOY_USER" -g "$DEPLOY_USER" /opt/quantah

echo "startup.sh: Docker $(docker --version 2>/dev/null || echo indisponível) — pronto."
