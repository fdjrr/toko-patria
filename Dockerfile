FROM composer/composer:2.8.3 AS composer

WORKDIR /app

RUN install-php-extensions \
    gd

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

RUN apk add --no-cache unzip supervisor

RUN install-php-extensions \
    pcntl \
    pdo_mysql \
    gd \
    zip \
    intl \
    curl \
    xml \
    mbstring \
    openssl \
    tokenizer \
    bcmath

COPY . .

COPY --from=composer /app/vendor /app/vendor
COPY --from=node /app/public/build /app/public/build

COPY supervisord.conf /etc/supervisord.conf

EXPOSE 8000

CMD ["supervisord", "-c", "/etc/supervisord.conf"]