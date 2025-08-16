FROM composer/composer:2.8.3 AS composer

WORKDIR /app

COPY composer.json composer.lock ./

RUN composer install \
    --no-interaction \
    --no-plugins \
    --no-scripts \
    --no-dev \
    --prefer-dist

FROM node:23.10-alpine AS node

WORKDIR /app

COPY package.json package-lock.json vite.config.js ./

RUN npm install

COPY --from=composer /app/vendor /app/vendor
COPY resources/ resources/

RUN npm run build

FROM dunglas/frankenphp:php8.3-alpine

WORKDIR /app

RUN install-php-extensions \
    pdo_mysql \
    pcntl

COPY . .

COPY --from=composer /app/vendor /app/vendor
COPY --from=node /app/public/build /app/public/build

EXPOSE 8000

ENTRYPOINT ["php", "artisan", "octane:frankenphp"]