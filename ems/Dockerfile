#-----------------------------------------------
# Stage 1: Build Assets (Node.js)
#-----------------------------------------------
FROM node:20-alpine AS asset-builder

# เปลี่ยน WORKDIR ให้เป็นมาตรฐาน (หรือใช้ /ems ตามที่คุณสะดวก แต่ต้องระบุให้ตรงกัน)
WORKDIR /app/ems

# 1. คัดลอก package จากโฟลเดอร์ ems มาวางที่ root ของ builder
COPY ems/package*.json ./
RUN npm install

# 2. คัดลอกไฟล์ทั้งหมด (รวมโฟลเดอร์ ems) เข้ามา
WORKDIR /app
COPY . .

# 3. รัน Build โดยระบุว่าโปรเจกต์หลักอยู่ที่โฟลเดอร์ ems
# วิธีนี้จะทำให้ Vite หา vite.config.js และโฟลเดอร์ resources เจอ
WORKDIR /app/ems
RUN npm run build

#-----------------------------------------------
# Stage 2: Production Environment (PHP-FPM)
#-----------------------------------------------
FROM php:8.2-fpm-alpine

# 1. ติดตั้ง System Dependencies (Alpine version จะใช้ apk)
RUN apk add --no-cache \
    git curl unzip libpng-dev libjpeg-turbo-dev freetype-dev libzip-dev icu-dev oniguruma-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl

# 2. ติดตั้ง Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# 3. คัดลอกไฟล์ทั้งหมด
COPY . .
COPY --from=asset-builder /app/ems/public/build ./ems/public/build

# 4. ติดตั้ง PHP Dependencies และปรับ Permission
RUN cd ems && \
    composer install --no-dev --optimize-autoloader --no-interaction && \
    chown -R www-data:www-data storage bootstrap/cache

# 5. ใช้ PHP Built-in Server แทน Apache เพื่อแก้ปัญหา MPM
# วิธีนี้จะทำให้ Laravel คุยกับพอร์ต $PORT ของ Railway ได้โดยตรง
EXPOSE 80
CMD ["php", "ems/artisan", "serve", "--host=0.0.0.0", "--port=80"]