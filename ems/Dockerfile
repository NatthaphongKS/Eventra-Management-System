# -----------------------------------------------------------
# Stage 1: Build Frontend (Vue.js)
# -----------------------------------------------------------
FROM node:20 AS build-stage

WORKDIR /app

# Install frontend dependencies
COPY package*.json ./
RUN npm install

# Build assets
COPY . .
RUN npm run build

# -----------------------------------------------------------
# Stage 2: Setup Laravel + Apache
# -----------------------------------------------------------
FROM php:8.2-apache

# System dependencies and PHP extensions
RUN apt-get update && apt-get install -y \
        git curl unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libonig-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl \
    && a2enmod rewrite headers \
    && rm -rf /var/lib/apt/lists/*

# Point Apache to the Laravel public directory
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
    && printf "<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

WORKDIR /var/www/html

# Copy backend code
COPY . .

# Copy built frontend assets
COPY --from=build-stage /app/public/build public/build

# Install composer dependencies
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
RUN composer install --optimize-autoloader --no-dev

# Permissions for storage/bootstrap
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]
