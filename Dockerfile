FROM php:8.4-cli

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git curl unzip zip libzip-dev libpng-dev libonig-dev libxml2-dev \
    && docker-php-ext-install pdo pdo_sqlite mbstring xml zip opcache \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Install Node.js 20
RUN curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /app

# Copy dependency files first for cache
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

COPY package.json package-lock.json ./
RUN npm ci

# Copy app files
COPY . .

# Build frontend
RUN npm run build

# Laravel setup
RUN php artisan config:cache \
    && php artisan route:cache \
    && php artisan view:cache

# Permissions
RUN chmod -R 775 storage bootstrap/cache

EXPOSE 8000

CMD php artisan migrate --force && php artisan storage:link --force && php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
