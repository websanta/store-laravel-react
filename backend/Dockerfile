FROM php:8.3-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    git unzip libpq-dev libzip-dev curl npm \
    && docker-php-ext-install pdo pdo_pgsql pgsql zip

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/backend

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader

COPY . .

RUN chown -R www-data:www-data /var/www/backend \
    && chmod -R 755 /var/www/backend

EXPOSE 9000

CMD ["php-fpm"]
