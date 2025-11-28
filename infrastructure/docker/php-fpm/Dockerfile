# ---------------------------------------------------------
# BASE: PHP-FPM 8.3
# ---------------------------------------------------------
FROM php:8.3-fpm

# ---------------------------------------------------------
# System dependencies
# ---------------------------------------------------------
RUN apt-get update && apt-get install -y \
    git \
    curl \
    zip \
    unzip \
    libpq-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    pkg-config \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# ---------------------------------------------------------
# PHP Extensions
# ---------------------------------------------------------
RUN docker-php-ext-configure gd --with-freetype --with-jpeg && \
    docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    bcmath \
    gd \
    zip \
    opcache

# ---------------------------------------------------------
# Install Composer
# ---------------------------------------------------------
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# ---------------------------------------------------------
# Create non-root user
# ---------------------------------------------------------
RUN useradd -u 1000 -ms /bin/bash laravel

# ---------------------------------------------------------
# Workdir (project itself will be mounted by Docker Compose)
# ---------------------------------------------------------
WORKDIR /var/www/backend

# ---------------------------------------------------------
# Permissions (Laravel storage + cache + logs)
# ---------------------------------------------------------
RUN mkdir -p /var/www/backend/storage \
    && mkdir -p /var/www/backend/bootstrap/cache \
    && chown -R laravel:laravel /var/www/backend

USER laravel

# ---------------------------------------------------------
# Composer install if vendor exists
# (allows caching during docker build in CI)
# ---------------------------------------------------------
COPY --chown=laravel:laravel backend/composer.json backend/composer.lock ./
RUN composer install --no-interaction --prefer-dist || true

# ---------------------------------------------------------
# Default command
# ---------------------------------------------------------
CMD ["php-fpm"]
