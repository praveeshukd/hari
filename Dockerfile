FROM composer:2 AS vendor

WORKDIR /app

COPY composer.json composer.lock ./
RUN composer install --no-dev --no-interaction --no-scripts --prefer-dist --ignore-platform-reqs

COPY . .
RUN composer dump-autoload --optimize --no-dev --no-scripts

FROM php:8.4-cli-bookworm

RUN apt-get update && apt-get install -y --no-install-recommends \
        libzip4 libpng16-16 libjpeg62-turbo libfreetype6 \
        libonig5 libxml2 libsqlite3-0 libcurl4 libpq5 \
        libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libonig-dev libxml2-dev libsqlite3-dev libcurl4-openssl-dev libpq-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j"$(nproc)" \
        pdo_sqlite pdo_pgsql gd zip mbstring xml bcmath curl \
    && apt-get purge -y --auto-remove \
        libzip-dev libpng-dev libjpeg62-turbo-dev libfreetype6-dev \
        libonig-dev libxml2-dev libsqlite3-dev libcurl4-openssl-dev libpq-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

WORKDIR /var/www/html

COPY --from=vendor /app /var/www/html

RUN mkdir -p storage/framework/cache/data \
        storage/framework/sessions \
        storage/framework/views \
        storage/logs \
        bootstrap/cache \
        database \
    && chmod -R ug+rwx storage bootstrap/cache database \
    && chmod +x docker/start.sh

ENV PORT=10000
EXPOSE 10000

CMD ["docker/start.sh"]
