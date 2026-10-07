#!/bin/bash
set -Eeuo pipefail
cd /var/www

# Copy file SSL cert sang /tmp/ca.pem va cap quyen truy cap cho www-data
if [ -f /etc/secrets/ca.pem ]; then
    cp /etc/secrets/ca.pem /tmp/ca.pem
    chown www-data:www-data /tmp/ca.pem
    chmod 644 /tmp/ca.pem
    export MYSQL_ATTR_SSL_CA=/tmp/ca.pem
fi

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

# Khởi chạy php-fpm dạng daemon
php-fpm -D

# Chạy Nginx ở foreground để giữ container luôn chạy
exec nginx -g 'daemon off;'