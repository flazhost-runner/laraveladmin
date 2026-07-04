# syntax=docker/dockerfile:1
# ── LaravelAdmin starter kit · FlazHost PaaS (CapRover) ─────────────────────
# Multi-stage build:
#   1) node:20-alpine     — Vite/Tailwind 4 asset build → public/build
#   2) composer:2         — vendor/ (no-dev, optimized autoloader)
#   3) php:8.3-cli-alpine — runtime: `php artisan serve` on $PORT (default 80)
#
# Zero-config boot: SQLite in /app/data + bundled local Redis (cache store).
# A managed DB is driven purely by env — the platform sends DB_TYPE/DB_HOST/
# DB_PORT/DB_USERNAME/DB_PASSWORD/DB_DATABASE and the entrypoint maps
# DB_TYPE → Laravel's DB_CONNECTION.

# 1) Frontend assets ──────────────────────────────────────────────────────────
FROM node:20-alpine AS assets
WORKDIR /app

# No package-lock.json in the repo → npm install (not npm ci).
# .npmrc sets ignore-scripts=true (native binaries come via optional deps).
COPY package.json .npmrc ./
RUN npm install --no-audit --no-fund

# Tailwind 4 auto-detects sources → copy everything it may scan for classes.
COPY vite.config.js ./
COPY resources ./resources
COPY app ./app
COPY Modules ./Modules
COPY public ./public
RUN npm run build

# 2) PHP dependencies ─────────────────────────────────────────────────────────
FROM composer:2 AS vendor
WORKDIR /app

COPY composer.json composer.lock ./
# --no-scripts: post-autoload-dump runs `artisan package:discover`, which needs
# the full app — the runtime stage takes care of that. --ignore-platform-reqs:
# the lock is resolved against the pinned platform (php 8.3.0), not this image.
RUN composer install --no-dev --no-scripts --no-interaction --no-progress \
        --prefer-dist --ignore-platform-reqs

# Full source for the optimized autoload dump (App\, Modules\, Database\,
# app/Helpers/helpers.php are all classmap/psr-4 targets).
COPY . .
RUN composer dump-autoload --optimize --no-scripts

# 3) Runtime ──────────────────────────────────────────────────────────────────
FROM php:8.3-cli-alpine
WORKDIR /app

# redis : bundled local cache store for zero-config deploys (CACHE_STORE=redis)
# tini  : proper PID 1 / signal handling
# libpq : runtime library for pdo_pgsql
# pdo_sqlite ships with the base image; pdo_mysql/pdo_pgsql are compiled here.
RUN apk add --no-cache redis tini libpq \
 && apk add --no-cache --virtual .build-deps postgresql-dev \
 && docker-php-ext-install -j"$(nproc)" pdo_mysql pdo_pgsql \
 && apk del .build-deps

# App source — selective copy (no .git / tests / node_modules in the image).
COPY artisan composer.json composer.lock modules_statuses.json ./
COPY app ./app
COPY bootstrap ./bootstrap
COPY config ./config
COPY database ./database
COPY Modules ./Modules
COPY public ./public
COPY resources ./resources
COPY routes ./routes
COPY storage ./storage
COPY --from=vendor /app/vendor ./vendor
COPY --from=assets /app/public/build ./public/build

COPY docker-entrypoint.sh /usr/local/bin/docker-entrypoint.sh

# /app/data holds the default SQLite DB + persisted runtime secrets.
RUN chmod +x /usr/local/bin/docker-entrypoint.sh \
 && mkdir -p /app/data \
        storage/framework/cache/data storage/framework/sessions \
        storage/framework/views storage/logs storage/app/public \
        bootstrap/cache \
 && chmod -R ug+rwX storage bootstrap/cache \
 && (php artisan package:discover --ansi || true)

# ── Zero-config defaults (every value overridable via env) ───────────────────
# DB_CONNECTION / DB_DATABASE are intentionally NOT set here: the entrypoint
# derives them from the platform's DB_TYPE/DB_* env (default: sqlite).
ENV APP_NAME=LaravelAdmin \
    APP_ENV=production \
    APP_DEBUG=false \
    APP_URL=http://localhost \
    APP_MODE=full \
    LOG_CHANNEL=stderr \
    PORT=80 \
    SESSION_DRIVER=database \
    QUEUE_CONNECTION=database \
    CACHE_STORE=redis \
    REDIS_CLIENT=predis \
    REDIS_URL=redis://127.0.0.1:6379

EXPOSE 80
ENTRYPOINT ["/sbin/tini", "--", "/usr/local/bin/docker-entrypoint.sh"]
