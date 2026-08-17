#!/bin/bash
set -e

# Run migrations
php artisan migrate --force

# Create storage link
php artisan storage:link || true

# Cache config/routes/views
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Seed admin if not exists
php artisan db:seed --class=AdminSeeder --force 2>/dev/null || true

# Start Apache
apache2-foreground
