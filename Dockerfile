FROM node:22-bookworm-slim AS frontend

WORKDIR /app

COPY package.json package-lock.json ./
RUN npm ci

COPY . .

RUN npm run build

FROM php:8.3-cli-bookworm AS app

WORKDIR /var/www/html

ENV COMPOSER_ALLOW_SUPERUSER=1 \
    APP_ENV=production \
    APP_DEBUG=false \
    PORT=10000

RUN apt-get update && apt-get install -y --no-install-recommends \
    $PHPIZE_DEPS \
    git \
    unzip \
    libicu-dev \
    libonig-dev \
    libzip-dev \
    libsqlite3-dev \
    libxml2-dev \
    libcurl4-openssl-dev \
    && docker-php-ext-install \
    bcmath \
    curl \
    intl \
    mbstring \
    opcache \
    pdo_sqlite \
    sqlite3 \
    xml \
    zip \
    && apt-get purge -y --auto-remove $PHPIZE_DEPS \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

COPY composer.json composer.lock ./
COPY artisan ./artisan
COPY bootstrap ./bootstrap
COPY config ./config
COPY app ./app
COPY routes ./routes
COPY database ./database
RUN composer install --no-dev --prefer-dist --no-interaction --no-progress --optimize-autoloader

COPY . .
COPY --from=frontend /app/public/build ./public/build

RUN mkdir -p storage/framework/cache storage/framework/sessions storage/framework/views bootstrap/cache \
    && php artisan storage:link || true \
    && chown -R www-data:www-data storage bootstrap/cache database \
    && chmod -R ug+rwX storage bootstrap/cache

USER www-data

EXPOSE 10000

CMD ["sh", "-c", "php artisan config:cache && php artisan view:cache && php artisan serve --host 0.0.0.0 --port ${PORT:-10000}"]
