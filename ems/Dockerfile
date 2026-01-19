#-----------------------------------------------
# Stage 1: Build Assets (Node.js)
#-----------------------------------------------
FROM node:20-alpine AS asset-builder

# เปลี่ยน WORKDIR ให้เป็นมาตรฐาน (หรือใช้ /ems ตามที่คุณสะดวก แต่ต้องระบุให้ตรงกัน)
WORKDIR /app

# 1. คัดลอก package จากโฟลเดอร์ ems มาวางที่ root ของ builder
COPY ems/package*.json ./
RUN npm install

# 2. คัดลอกไฟล์ทั้งหมด (รวมโฟลเดอร์ ems) เข้ามา
COPY . .

# 3. รัน Build โดยระบุว่าโปรเจกต์หลักอยู่ที่โฟลเดอร์ ems
# วิธีนี้จะทำให้ Vite หา vite.config.js และโฟลเดอร์ resources เจอ
RUN npx vite build --config ems/vite.config.js --root ems

#-----------------------------------------------
# Stage 2: Production Environment (PHP + Apache)
#-----------------------------------------------
FROM php:8.2-apache

# 1. ติดตั้ง System Dependencies (เหมือนเดิมของคุณ)
RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libonig-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# 2. ตั้งค่า Apache ให้ชี้ไปที่ ems/public (ตามโครงสร้างจริงของคุณ)
ENV APACHE_DOCUMENT_ROOT /var/www/html/ems/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2enmod rewrite headers

# 3. ติดตั้ง Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# 4. คัดลอก Source Code ทั้งหมด
COPY . .

# 5. คัดลอก Assets ที่ Build เสร็จแล้วมาจาก Stage 1
# ต้นทางคือ /app/ems/public/build (เพราะเราสั่ง build --root ems ใน Stage 1)
# ปลายทางคือ ./ems/public/build
COPY --from=asset-builder /app/ems/public/build ./ems/public/build

# 6. ติดตั้ง PHP Dependencies (ต้องรันภายในโฟลเดอร์ ems)
RUN cd ems && composer install --no-dev --optimize-autoloader --no-interaction

# 7. ปรับ Permission ให้ถูกต้องภายในโฟลเดอร์ ems
RUN chown -R www-data:www-data ems/storage ems/bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]