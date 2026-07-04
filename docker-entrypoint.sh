#!/bin/sh
# LaravelAdmin container boot sequence (FlazHost / CapRover):
#   1) listen port: CapRover injects $PORT (default 80)
#   2) DB env mapping: platform DB_TYPE/DB_* → Laravel DB_CONNECTION/DB_*
#   3) secrets: APP_KEY / JWT_SECRET / SESSION_SECRET — honour env, otherwise
#      generate once and persist in /app/data/.runtime-secrets
#   4) bundled local Redis when REDIS_URL points at localhost
#   5) migrate --force (WARN on failure), idempotent admin seed, storage:link
#   6) exec `php artisan serve` on 0.0.0.0:$PORT
set -eu

DATA_DIR=/app/data
SECRETS_FILE="$DATA_DIR/.runtime-secrets"
mkdir -p "$DATA_DIR"

# ── 1. Port (CapRover contract) ───────────────────────────────────────────────
: "${PORT:=80}"
export PORT

# ── 2. Database env mapping ───────────────────────────────────────────────────
# The platform sends DB_TYPE/DB_HOST/DB_PORT/DB_USERNAME/DB_PASSWORD/DB_DATABASE.
# Laravel reads DB_CONNECTION (+ the same DB_HOST/DB_PORT/DB_USERNAME/... names),
# so only DB_TYPE needs translating — and only when DB_CONNECTION isn't set.
if [ -z "${DB_CONNECTION:-}" ]; then
    case "${DB_TYPE:-sqlite}" in
        mysql)                     DB_CONNECTION=mysql ;;
        mariadb)                   DB_CONNECTION=mariadb ;;
        postgres|postgresql|pgsql) DB_CONNECTION=pgsql ;;
        *)                         DB_CONNECTION=sqlite ;;
    esac
fi
export DB_CONNECTION

# Default SQLite database lives on the writable /app/data path.
if [ "$DB_CONNECTION" = "sqlite" ]; then
    export DB_DATABASE="${DB_DATABASE:-/app/data/database.sqlite}"
    mkdir -p "$(dirname "$DB_DATABASE")" 2>/dev/null || true
    [ -f "$DB_DATABASE" ] || touch "$DB_DATABASE"
fi

echo "[entrypoint] DB_CONNECTION=$DB_CONNECTION DB_DATABASE=${DB_DATABASE:-} PORT=$PORT"

# ── 3. Secrets (generate once, persist across restarts via /app/data) ─────────
get_secret() { # get_secret KEY → prints stored value (empty if none)
    [ -f "$SECRETS_FILE" ] || return 0
    sed -n "s/^$1=//p" "$SECRETS_FILE" | head -n1
}
gen_hex() { php -r 'echo bin2hex(random_bytes(32));'; }

if [ -z "${APP_KEY:-}" ]; then APP_KEY="$(get_secret APP_KEY)"; fi
if [ -z "$APP_KEY" ]; then
    APP_KEY="$(php artisan key:generate --show --no-ansi 2>/dev/null || true)"
    [ -n "$APP_KEY" ] || APP_KEY="base64:$(php -r 'echo base64_encode(random_bytes(32));')"
    echo "APP_KEY=$APP_KEY" >> "$SECRETS_FILE"
    echo "[entrypoint] Generated APP_KEY (persisted in $SECRETS_FILE)"
fi
export APP_KEY

if [ -z "${JWT_SECRET:-}" ]; then JWT_SECRET="$(get_secret JWT_SECRET)"; fi
if [ -z "$JWT_SECRET" ]; then
    JWT_SECRET="$(gen_hex)"
    echo "JWT_SECRET=$JWT_SECRET" >> "$SECRETS_FILE"
    echo "[entrypoint] Generated JWT_SECRET (persisted in $SECRETS_FILE)"
fi
export JWT_SECRET

if [ -z "${SESSION_SECRET:-}" ]; then SESSION_SECRET="$(get_secret SESSION_SECRET)"; fi
if [ -z "$SESSION_SECRET" ]; then
    SESSION_SECRET="$(gen_hex)"
    echo "SESSION_SECRET=$SESSION_SECRET" >> "$SECRETS_FILE"
    echo "[entrypoint] Generated SESSION_SECRET (persisted in $SECRETS_FILE)"
fi
export SESSION_SECRET

# ── 4. Bundled Redis (cache store; only when targeting localhost) ─────────────
# A managed Redis is used by pointing REDIS_URL at a non-local host.
case "${REDIS_URL:-}" in
    ""|*127.0.0.1*|*localhost*)
        echo "[entrypoint] Starting bundled redis-server (REDIS_URL=${REDIS_URL:-default})"
        redis-server --daemonize yes --save "" --appendonly no >/dev/null 2>&1 || \
            echo "[entrypoint] WARN: could not start bundled redis-server"
        ;;
    *)
        echo "[entrypoint] Using external Redis at $REDIS_URL"
        ;;
esac

# ── 5. Migrate + seed (never crash the boot) ──────────────────────────────────
echo "[entrypoint] Running database migrations..."
if php artisan migrate --force; then
    echo "[entrypoint] Migrations applied."
else
    echo "[entrypoint] WARN: migrate failed — continuing (an existing schema may still serve)"
fi

# Database\Seeders\AdminSeeder is idempotent (updateOrCreate) → safe every boot.
# Seeds default admin: admin@admin.com / 12345678.
echo "[entrypoint] Seeding default admin (admin@admin.com)..."
php artisan db:seed --force || \
    echo "[entrypoint] WARN: db:seed failed — continuing"

# public/storage → storage/app/public (best-effort; already-exists is fine).
php artisan storage:link >/dev/null 2>&1 || true

# ── 6. Start the HTTP server ──────────────────────────────────────────────────
echo "[entrypoint] Starting Laravel on 0.0.0.0:${PORT}"
exec php artisan serve --host=0.0.0.0 --port="$PORT"
