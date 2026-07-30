FROM php:8.4-fpm-alpine

RUN apk add --no-cache \
        icu-dev \
        postgresql-dev \
        libzip-dev \
        $PHPIZE_DEPS \
    && docker-php-ext-install intl pdo_pgsql zip opcache bcmath \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

COPY composer.json composer.lock symfony.lock ./
RUN composer install --no-scripts --no-interaction --prefer-dist --no-progress

COPY . .

RUN touch .env \
    && mkdir -p var/cache var/log \
    && chown -R www-data:www-data var \
    && composer dump-autoload --optimize \
    && php bin/console importmap:install --env=prod \
    && php bin/console asset-map:compile --env=prod \
    && chown -R www-data:www-data var assets/vendor public/assets public/uploads
