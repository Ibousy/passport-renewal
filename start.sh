#!/bin/bash
set -e

echo "Starting PasseportSN..."

# Create SQLite database if not exists
mkdir -p database
touch database/database.sqlite

# Run migrations
php artisan migrate --force

# Storage link
php artisan storage:link --force 2>/dev/null || true

# Clear config cache and rebuild with correct APP_URL
php artisan config:clear
php artisan config:cache

echo "Starting server on port ${PORT:-8000}..."
exec php artisan serve --host=0.0.0.0 --port=${PORT:-8000}
