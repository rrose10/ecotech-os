# ---- 1. Build the CSS/JS ----
FROM node:22-alpine AS assets
WORKDIR /app
COPY package.json package-lock.json ./
RUN npm ci
COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

# ---- 2. PHP 8.4 + nginx ----
FROM serversideup/php:8.4-fpm-nginx

ENV PHP_OPCACHE_ENABLE=1 \
    PHP_MEMORY_LIMIT=256M \
    PHP_UPLOAD_MAX_FILE_SIZE=20M \
    PHP_POST_MAX_SIZE=25M

# mysqldump for the nightly backup
USER root
RUN apt-get update \
    && apt-get install -y --no-install-recommends mariadb-client \
    && rm -rf /var/lib/apt/lists/*
USER www-data

WORKDIR /var/www/html
COPY --chown=www-data:www-data . .
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts \
    && mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views storage/app/backups bootstrap/cache
COPY --from=assets --chown=www-data:www-data /app/public/build ./public/build
