#!/bin/sh
set -e

cd /var/www/html

# Run migrations
php artisan migrate --force

# Cache config/routes/views for production
php artisan config:cache
php artisan route:cache
php artisan view:cache

# Create storage link
php artisan storage:link --force 2>/dev/null || true

# Start supervisor (nginx + php-fpm + queue)
exec /usr/bin/supervisord -c /etc/supervisor/conf.d/supervisord.conf
