FROM node:24-alpine AS frontend

WORKDIR /var/www

COPY package.json .npmrc ./
RUN npm install --ignore-scripts

COPY vite.config.js ./
COPY resources ./resources
COPY public ./public
RUN npm run build

FROM php:8.3-fpm AS app

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl libpng-dev libonig-dev libxml2-dev zip unzip \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd \
    && rm -rf /var/lib/apt/lists/*

COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
COPY docker/laravel-entrypoint.sh /usr/local/bin/laravel-entrypoint
RUN chmod +x /usr/local/bin/laravel-entrypoint

WORKDIR /var/www

COPY . .
COPY --from=frontend /var/www/public/build ./public/build

RUN composer install --no-dev --no-interaction --prefer-dist --optimize-autoloader

EXPOSE 9000
ENTRYPOINT ["laravel-entrypoint"]
CMD ["php-fpm"]

FROM nginx:alpine AS nginx

COPY docker/nginx.conf /etc/nginx/conf.d/default.conf
COPY public /var/www/public
COPY --from=frontend /var/www/public/build /var/www/public/build
