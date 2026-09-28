# Use PHP 8.4 Apache
FROM php:8.4-apache

# Set working directory
WORKDIR /app

# 1. Install system dependencies & PHP extensions
RUN apt-get update -y && apt-get install -y \
    openssl \
    zip \
    unzip \
    git \
    libcurl4-openssl-dev \
    && docker-php-ext-install pdo pdo_mysql \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# 2. Install latest Composer & allow superuser execution
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer
ENV COMPOSER_ALLOW_SUPERUSER=1

# 3. Copy ONLY dependency files first (optimizes Docker build caching)
COPY composer.json composer.lock ./

# 4. Install PHP dependencies
RUN composer install --no-dev --no-scripts --no-autoloader

# 5. Copy remaining application files
COPY . .

# 6. Generate optimized autoloader after copying full app
RUN composer dump-autoload --optimize

EXPOSE 8000

CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]