FROM php:8.2-cli-alpine

RUN apk add --no-cache $PHPIZE_DEPS linux-headers \
    && docker-php-ext-install pdo_mysql sockets \
    && apk del $PHPIZE_DEPS

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
WORKDIR /var/www
COPY composer.json composer.lock ./
RUN composer install --no-dev --no-scripts --prefer-dist --optimize-autoloader
COPY . .
RUN php artisan package:discover --ansi

EXPOSE 3002
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=3002"]
