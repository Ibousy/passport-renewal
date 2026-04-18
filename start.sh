#!/bin/bash
set -e

echo "=== Starting PasseportSN ==="
echo "PORT: ${PORT:-8000}"
echo "APP_ENV: $APP_ENV"

# Ensure database exists
mkdir -p database
touch database/database.sqlite
chmod 664 database/database.sqlite

# Ensure storage dirs exist
mkdir -p storage/logs storage/framework/cache storage/framework/sessions storage/framework/views
chmod -R 775 storage bootstrap/cache

# Generate config cache with runtime env vars
php artisan config:clear 2>/dev/null || true
php artisan cache:clear 2>/dev/null || true
php artisan config:cache
php artisan route:cache

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Storage link (ignore if already exists)
php artisan storage:link --force 2>/dev/null || true

echo "Starting server on 0.0.0.0:${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
