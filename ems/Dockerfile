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

RUN a2enmod rewrite headers \
    && sed -ri -e 's!/var/www/html!/var/www/html/public!g' /etc/apache2/sites-available/*.conf \
    && echo '<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>' > /etc/apache2/conf-available/laravel.conf \
    && a2enconf laravel

# อัปเดตรายการแพ็กเกจ (จำเป็นสำหรับการติดตั้งส่วนขยาย)
RUN apt-get update \
    && apt-get install -y --no-install-recommends \
    # ติดตั้งแพ็กเกจที่จำเป็นสำหรับส่วนขยาย pdo_mysql (เช่น libmysqlclient-dev)
    # ขั้นตอนนี้อาจแตกต่างกันไปตาม Docker image พื้นฐาน
    && rm -rf /var/lib/apt/lists/*

# ติดตั้งส่วนขยาย pdo_mysql อย่างเป็นทางการ
RUN docker-php-ext-install pdo_mysql
