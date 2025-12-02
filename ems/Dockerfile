# -----------------------------------------------------------
# Stage 1: Build Frontend (Vue.js)
# -----------------------------------------------------------
    FROM node:20 as build-stage

    WORKDIR /app

    # Copy ไฟล์ package.json มาลง dependencies ก่อน (เพื่อ cache layer)
    COPY package*.json ./
    RUN npm install

    # Copy โค้ดทั้งหมดและสั่ง Build
    COPY . .
    RUN npm run build

    # -----------------------------------------------------------
    # Stage 2: Setup Laravel + Apache
    # -----------------------------------------------------------
    FROM php:8.2-apache

    # 1. ติดตั้ง Library ที่จำเป็นสำหรับ Laravel และ Image Processing
    RUN apt-get update && apt-get install -y \
        git curl unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libonig-dev libicu-dev \
     && docker-php-ext-configure gd --with-freetype --with-jpeg \
     && docker-php-ext-install gd pdo pdo_mysql zip intl \
     && a2enmod rewrite headers \
     && rm -rf /var/lib/apt/lists/*

    # 2. ตั้งค่า Apache DocumentRoot ให้ชี้ไปที่ folder public ของ Laravel
    RUN sed -i 's#/var/www/html#/var/www/html/public#g' /etc/apache2/sites-available/000-default.conf \
     && printf "<Directory /var/www/html/public>\n\tAllowOverride All\n\tRequire all granted\n</Directory>\n" > /etc/apache2/conf-available/laravel.conf \
     && a2enconf laravel

    # 3. เตรียม Folder สำหรับวางโปรเจกต์
    WORKDIR /var/www/html

    # 4. Copy ไฟล์โปรเจกต์ทั้งหมด (Backend) เข้า Container
    COPY . .

    # 5. Copy ไฟล์ Vue Assets (Frontend) ที่ Build เสร็จแล้วจาก Stage 1 มาไว้ที่ public/build
    COPY --from=build-stage /app/public/build public/build

    # 6. ติดตั้ง Composer Dependencies (Backend)
    COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer
    RUN composer install --optimize-autoloader --no-dev

    # 7. ตั้งค่า Permission ให้ Apache เขียนไฟล์ได้
    RUN chown -R www-data:www-data /var/www/html \
     && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

    # เปิด Port 80 และรัน Apache
    EXPOSE 80
    CMD ["apache2-foreground"]