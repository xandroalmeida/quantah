#!/usr/bin/env bash
# Entrypoint da imagem de produção. Roda a cada start/deploy (idempotente).
# NÃO roda migrations aqui (isso é passo explícito do deploy) para evitar corrida
# entre múltiplos containers. Só prepara caches de produção.
set -e

# route:cache é omitido de propósito: há rotas com Closure (/, /dashboard) que não
# são serializáveis. config:cache + view:cache cobrem o ganho de produção.
php artisan config:cache
php artisan view:cache
php artisan event:cache || true

exec "$@"
