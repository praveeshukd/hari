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

# Only create a local sqlite file when explicitly using sqlite.
# External MySQL/Postgres must already exist — never wipe or recreate them.
if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
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

wait_for_db() {
    if [ "${DB_CONNECTION:-sqlite}" = "sqlite" ]; then
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

# migrate --force only applies pending migrations; it does not drop/clear data.
echo "boot: migrate"
php artisan migrate --force

echo "boot: listen"
cd public
exec php -S "0.0.0.0:${PORT:-10000}" ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
