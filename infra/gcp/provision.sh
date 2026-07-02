#!/usr/bin/env bash
# ─────────────────────────────────────────────────────────────────────────────
# Provisionamento da VM de homologação do Quantah (ADR-007).
#
# O GCP entra AQUI e SÓ AQUI: este script levanta uma VM Compute Engine tratada
# como VPS genérica. Todo o resto da infra vive dentro da VM via Docker Compose.
# Para migrar de provedor, troque este arquivo por um equivalente (Hetzner/DO/...)
# que produza: 1 host Ubuntu com Docker, um IP público e as portas 22/80/443.
#
# Idempotente: reexecutar não recria recursos que já existem.
#
# Uso:
#   PUBKEY_FILE=/caminho/deploy.pub ./provision.sh
#
# Requer: gcloud autenticado na conta/projeto certos (ver infra/README.md).
# ─────────────────────────────────────────────────────────────────────────────
set -euo pipefail

PROJECT="${PROJECT:-command-center-8026a}"
REGION="${REGION:-southamerica-east1}"
ZONE="${ZONE:-southamerica-east1-c}"
VM_NAME="${VM_NAME:-quantah-homolog}"
MACHINE_TYPE="${MACHINE_TYPE:-e2-small}"
IMAGE_FAMILY="${IMAGE_FAMILY:-ubuntu-2404-lts-amd64}"
IMAGE_PROJECT="${IMAGE_PROJECT:-ubuntu-os-cloud}"
DISK_SIZE="${DISK_SIZE:-20GB}"
IP_NAME="${IP_NAME:-quantah-homolog-ip}"
NET_TAG="${NET_TAG:-quantah-homolog}"
DEPLOY_USER="${DEPLOY_USER:-quantah}"
PUBKEY_FILE="${PUBKEY_FILE:?defina PUBKEY_FILE=/caminho/para/deploy.pub}"

here="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"

gcloud config set project "$PROJECT" >/dev/null

echo "> Habilitando Compute API..."
gcloud services enable compute.googleapis.com --project "$PROJECT" >/dev/null

echo "> IP estático ($IP_NAME) em $REGION..."
gcloud compute addresses create "$IP_NAME" --region "$REGION" 2>/dev/null \
  || echo "  (já existe)"
STATIC_IP="$(gcloud compute addresses describe "$IP_NAME" --region "$REGION" --format='value(address)')"
echo "  IP = $STATIC_IP"

echo "> Regras de firewall (tag: $NET_TAG)..."
gcloud compute firewall-rules create quantah-allow-web \
  --direction=INGRESS --action=ALLOW --rules=tcp:80,tcp:443 \
  --target-tags="$NET_TAG" --source-ranges=0.0.0.0/0 2>/dev/null \
  || echo "  (web já existe)"
gcloud compute firewall-rules create quantah-allow-ssh \
  --direction=INGRESS --action=ALLOW --rules=tcp:22 \
  --target-tags="$NET_TAG" --source-ranges=0.0.0.0/0 2>/dev/null \
  || echo "  (ssh já existe)"

echo "> Chave SSH de deploy → metadata ($DEPLOY_USER)..."
SSH_KEYS_FILE="$(mktemp)"
printf '%s:%s\n' "$DEPLOY_USER" "$(cat "$PUBKEY_FILE")" > "$SSH_KEYS_FILE"

echo "> Criando VM $VM_NAME ($MACHINE_TYPE, $IMAGE_FAMILY)..."
if gcloud compute instances describe "$VM_NAME" --zone "$ZONE" >/dev/null 2>&1; then
  echo "  (VM já existe — atualizando ssh-keys e startup-script)"
  gcloud compute instances add-metadata "$VM_NAME" --zone "$ZONE" \
    --metadata-from-file ssh-keys="$SSH_KEYS_FILE",startup-script="$here/startup.sh" >/dev/null
else
  gcloud compute instances create "$VM_NAME" \
    --zone "$ZONE" \
    --machine-type "$MACHINE_TYPE" \
    --image-family "$IMAGE_FAMILY" \
    --image-project "$IMAGE_PROJECT" \
    --boot-disk-size "$DISK_SIZE" \
    --boot-disk-type pd-balanced \
    --address "$STATIC_IP" \
    --tags "$NET_TAG" \
    --metadata-from-file ssh-keys="$SSH_KEYS_FILE",startup-script="$here/startup.sh" >/dev/null
fi
rm -f "$SSH_KEYS_FILE"

echo ""
echo "OK: Provisionado."
echo "   IP público : $STATIC_IP"
echo "   Host homolog: quantah-homolog.${STATIC_IP}.sslip.io"
echo "   SSH        : ssh $DEPLOY_USER@$STATIC_IP"
