#-----------------------------------------------
# Stage 1: Build Assets (Node.js)
#-----------------------------------------------
FROM node:20-alpine AS asset-builder
WORKDIR /app

# คัดลอก package.json (ตอนนี้จะมองเห็นอยู่ที่ root เพราะตั้ง ems เป็น root แล้ว)
COPY package*.json ./
RUN npm install

# คัดลอกไฟล์ทั้งหมดและ Build
COPY . .
RUN npm run build

#-----------------------------------------------
# Stage 2: Production Environment (PHP-FPM)
#-----------------------------------------------
FROM php:8.2-fpm-alpine

# ติดตั้ง System Dependencies
RUN apk add --no-cache \
    git curl unzip libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl

# ติดตั้ง Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# คัดลอกไฟล์ทั้งหมดจาก Root Directory (ems) เข้ามา
COPY . .

# คัดลอกไฟล์ที่ Build แล้วจาก Stage แรก (Vite assets)
COPY --from=asset-builder /app/public/build ./public/build

# ติดตั้ง PHP Dependencies และปรับ Permission
RUN composer install --no-dev --optimize-autoloader --no-interaction && \
    chown -R www-data:www-data storage bootstrap/cache

# ตั้งค่า Port ให้รับจาก Railway ($PORT)
# และรัน Migration ก่อนเริ่มแอป
CMD php artisan config:clear && php artisan migrate:fresh --force --seed && php artisan serve --host=0.0.0.0 --port=${PORT:-8080}