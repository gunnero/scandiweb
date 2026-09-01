FROM node:20.20-alpine AS frontend

WORKDIR /workspace/frontend
COPY frontend/package.json frontend/package-lock.json ./
RUN npm ci
COPY frontend/ ./
RUN npm run build

FROM composer:2 AS backend

WORKDIR /workspace/backend
COPY backend/composer.json backend/composer.lock ./
RUN composer install --no-dev --classmap-authoritative --no-interaction --no-progress
COPY backend/ ./
RUN composer dump-autoload --no-dev --classmap-authoritative

FROM php:8.2-apache

RUN apt-get update \
    && apt-get install -y --no-install-recommends libonig-dev \
    && docker-php-ext-install mbstring pdo_mysql \
    && a2enmod headers rewrite \
    && rm -rf /var/lib/apt/lists/*

COPY --from=backend /workspace/backend /var/www/app
COPY --from=frontend /workspace/build /var/www/html
COPY deploy/apache-vhost.conf /etc/apache2/sites-available/000-default.conf

WORKDIR /var/www/app

EXPOSE 80

CMD ["sh", "-c", "php bin/initialize.php && apache2-foreground"]
