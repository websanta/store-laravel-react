# Base image for Laravel - PHP 8.3
FROM php:8.3-fpm-alpine

# Install system dependencies and PHP extensions
RUN apk update && apk add --no-cache \
    postgresql-dev \
    libpq \
    libzip-dev \
    unzip \
    git \
    supervisor \
    openssl \
    oniguruma-dev \
    && docker-php-ext-install \
    pdo pdo_pgsql \
    zip \
    mbstring \
    exif \
    bcmath

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy custom php.ini configuration
COPY php.ini /usr/local/etc/php/conf.d/40-custom.ini

# Copy entrypoint script and make it executable
COPY entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

ENTRYPOINT ["entrypoint.sh"]
CMD ["php-fpm"]