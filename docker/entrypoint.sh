#!/bin/bash
set -Eeuo pipefail
cd /var/www

export PORT="${PORT:-10000}"
envsubst '${PORT}' < /etc/nginx/templates/default.conf.template > /etc/nginx/http.d/default.conf
mkdir -p storage/framework/{cache/data,sessions,views} storage/logs storage/app/public bootstrap/cache
chown -R www-data:www-data storage bootstrap/cache

su-exec www-data php artisan config:cache
if [ "${RUN_MIGRATIONS:-true}" = "true" ]; then
    su-exec www-data php artisan migrate --force --no-interaction
fi
if [ "${RUN_SEEDERS:-false}" = "true" ]; then
    su-exec www-data php artisan db:seed --force --no-interaction
fi
su-exec www-data php artisan route:cache
su-exec www-data php artisan view:cache

php-fpm -D &
nginx -g 'daemon off;'