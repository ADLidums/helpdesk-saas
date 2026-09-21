#!/bin/sh

set -eu

storage_path=/var/www/storage
cache_path=/var/www/bootstrap/cache
app_key_path="$storage_path/.app-key"

mkdir -p \
    "$storage_path/app/private" \
    "$storage_path/app/public" \
    "$storage_path/framework/cache/data" \
    "$storage_path/framework/sessions" \
    "$storage_path/framework/testing" \
    "$storage_path/framework/views" \
    "$storage_path/logs" \
    "$cache_path"

if [ ! -s "$app_key_path" ]; then
    umask 077
    php -r 'echo "base64:".base64_encode(random_bytes(32));' > "$app_key_path"
fi

cp /var/www/.env.example /var/www/.env
app_key=$(tr -d '\r\n' < "$app_key_path")
sed -i "s|^APP_KEY=.*|APP_KEY=$app_key|" /var/www/.env

if [ "$(id -u)" = "0" ]; then
    chown root:www-data /var/www/.env
    chmod 640 /var/www/.env
    chown -R www-data:www-data "$storage_path" "$cache_path"
    chmod -R ug+rwX "$storage_path" "$cache_path"
fi

exec docker-php-entrypoint "$@"
