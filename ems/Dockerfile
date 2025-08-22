FROM php:8.2-apache

# PHP Extensions + Apache rewrite
RUN apt-get update && apt-get install -y \
    git curl unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libonig-dev libicu-dev \
 && docker-php-ext-configure gd --with-freetype --with-jpeg \
 && docker-php-ext-install gd pdo pdo_mysql zip intl \
 && a2enmod rewrite headers \
 && rm -rf /var/lib/apt/lists/*

# เปลี่ยน DocumentRoot -> public และเปิดใช้ .htaccess
RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
 && printf "<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/laravel.conf \
 && a2enconf laravel

# Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html
EXPOSE 80
CMD ["apache2-foreground"]
