# syntax=docker/dockerfile:1
# ─────────────────────────────────────────────────────────────────────────────
# Imagem de produção do Quantah (ADR-007). Self-contained: nginx + php-fpm
# servindo a app na porta 80. TLS é responsabilidade do Caddy (reverse proxy),
# não desta imagem — assim a imagem é portável para qualquer VPS.
# Build feito no CI (GitHub Actions) e publicado no GHCR; a VM só faz pull.
# ─────────────────────────────────────────────────────────────────────────────

# 1) Assets do frontend (Vite/React)
FROM node:22-bookworm-slim AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js tailwind.config.js postcss.config.js jsconfig.json ./
COPY resources ./resources
RUN npm run build

# 2) Dependências PHP (sem dev)
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --no-autoloader --prefer-dist --no-interaction

# 3) Runtime: php-fpm + nginx + supervisor
FROM php:8.4-fpm-bookworm AS runtime

RUN apt-get update && apt-get install -y --no-install-recommends \
        nginx supervisor \
        libpq-dev libonig-dev libzip-dev unzip \
    && docker-php-ext-install -j"$(nproc)" pdo_pgsql pgsql mbstring bcmath zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Config de PHP para produção (opcache ligado)
COPY docker/php.ini /usr/local/etc/php/conf.d/zz-quantah.ini
COPY docker/nginx.conf /etc/nginx/sites-available/default
COPY docker/supervisord.conf /etc/supervisor/conf.d/quantah.conf

WORKDIR /var/www/html

# Binário do composer (para o dump-autoload otimizado no runtime)
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Código + vendor + assets
COPY --from=vendor /app/vendor ./vendor
COPY . .
COPY --from=assets /app/public/build ./public/build

# Autoloader otimizado (agora que o código está presente)
RUN composer dump-autoload --no-dev --optimize --classmap-authoritative

# Permissões do Laravel
RUN chown -R www-data:www-data storage bootstrap/cache \
    && chmod -R ug+rwx storage bootstrap/cache

COPY docker/entrypoint.sh /usr/local/bin/entrypoint
RUN chmod +x /usr/local/bin/entrypoint

EXPOSE 80
ENTRYPOINT ["/usr/local/bin/entrypoint"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/quantah.conf"]
