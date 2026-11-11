#!/bin/sh
set -eu

cd /var/www/html
echo "boot: start port=${PORT:-10000}"

mkdir -p storage/framework/cache/data \
    storage/framework/sessions \
    storage/framework/views \
    storage/logs \
    bootstrap/cache \
    database

chmod -R ug+rwx storage bootstrap/cache database

DB_CONNECTION="${DB_CONNECTION:-sqlite}"
IS_RENDER=0
if [ -n "${RENDER:-}" ] || [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    IS_RENDER=1
fi

# Render free web: SQLite is intentional for demo/dummy data (wiped on redeploy).
# Set STRICT_PERSISTENT_DB=1 only if you must refuse ephemeral SQLite boots.
if [ "$IS_RENDER" -eq 1 ] && [ "$DB_CONNECTION" = "sqlite" ]; then
    echo "boot: DB_CONNECTION=sqlite (demo data; reset on each redeploy)."
    if [ "${STRICT_PERSISTENT_DB:-0}" = "1" ]; then
        echo "boot: ERROR — STRICT_PERSISTENT_DB=1 blocks sqlite boot."
        exit 1
    fi
fi

# Only create a local sqlite file when explicitly using sqlite.
# External MySQL/Postgres must already exist — never wipe or recreate them.
if [ "$DB_CONNECTION" = "sqlite" ]; then
    if [ ! -f database/database.sqlite ]; then
        touch database/database.sqlite
        chmod ug+rw database/database.sqlite
    fi
fi

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

if [ -z "${APP_KEY:-}" ]; then
    echo "boot: WARNING — APP_KEY is empty; set a stable APP_KEY in host env so encrypted data survives redeploys"
fi

wait_for_db() {
    if [ "$DB_CONNECTION" = "sqlite" ]; then
        return 0
    fi

    echo "boot: waiting for database (${DB_CONNECTION})"
    i=0
    while [ "$i" -lt 30 ]; do
        if php -r '
            try {
                new PDO(
                    sprintf("%s:host=%s;port=%s;dbname=%s", getenv("DB_CONNECTION") === "pgsql" ? "pgsql" : "mysql", getenv("DB_HOST") ?: "127.0.0.1", getenv("DB_PORT") ?: (getenv("DB_CONNECTION") === "pgsql" ? "5432" : "3306"), getenv("DB_DATABASE") ?: ""),
                    getenv("DB_USERNAME") ?: "",
                    getenv("DB_PASSWORD") ?: ""
                );
                exit(0);
            } catch (Throwable $e) {
                exit(1);
            }
        '; then
            echo "boot: database ready"
            return 0
        fi
        i=$((i + 1))
        sleep 2
    done

    echo "boot: database wait timed out; continuing to migrate anyway"
}

wait_for_db

# SAFE: migrate --force applies pending migrations only.
# It never drops tables, truncates rows, or reseeds.
# Never run migrate:fresh / migrate:refresh / db:wipe in this script.
echo "boot: migrate (pending only; will not delete existing data)"
php artisan migrate --force --no-interaction

echo "boot: listen"
cd public
exec php -S "0.0.0.0:${PORT:-10000}" ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
