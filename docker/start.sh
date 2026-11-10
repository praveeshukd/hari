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

touch database/database.sqlite
chmod -R ug+rwx storage bootstrap/cache database

if [ ! -f .env ]; then
    cp .env.example .env
fi

if [ -z "${APP_URL:-}" ] && [ -n "${RENDER_EXTERNAL_URL:-}" ]; then
    export APP_URL="$RENDER_EXTERNAL_URL"
fi

echo "boot: migrate"
php artisan migrate --force

echo "boot: listen"
cd public
exec php -S "0.0.0.0:${PORT:-10000}" ../vendor/laravel/framework/src/Illuminate/Foundation/resources/server.php
