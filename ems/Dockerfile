#-----------------------------------------------
# Stage 1: Build Assets (Node.js)
#-----------------------------------------------
FROM node:20-alpine AS asset-builder

WORKDIR /ems
COPY ems/package*.json ./
# ลงทั้ง devDependencies เพื่อใช้ build (เช่น vite, tailwind)
RUN npm install
COPY . .
# รันคำสั่ง build เพื่อสร้างไฟล์ใน public/build
RUN ls -la
RUN npx vite build --config ems/vite.config.js

#-----------------------------------------------
# Stage 2: Production Environment (PHP + Apache)
#-----------------------------------------------
FROM php:8.2-apache

RUN apt-get update && apt-get install -y --no-install-recommends \
    git curl unzip libpng-dev libjpeg-dev libfreetype6-dev libzip-dev libonig-dev libicu-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql zip intl \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/lib/apt/lists/*

# 2. ตั้งค่า Apache (DocumentRoot -> /public)
ENV APACHE_DOCUMENT_ROOT /var/www/html/public
RUN sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/sites-available/*.conf \
    && sed -ri -e "s!/var/www/html!${APACHE_DOCUMENT_ROOT}!g" /etc/apache2/apache2.conf /etc/apache2/conf-available/*.conf \
    && a2dismod mpm_event || true \
    && a2enmod mpm_prefork \
    && a2enmod rewrite headers

# 3. ติดตั้ง Composer
COPY --from=composer:2 /usr/bin/composer /usr/local/bin/composer

WORKDIR /var/www/html

# 4. คัดลอก Source Code ของ PHP
COPY . .

# 5. คัดลอก Assets ที่ Build เสร็จแล้วมาจาก Stage 1 (จุดสำคัญ!)
COPY --from=asset-builder /app/public/build ./public/build

# 6. ติดตั้ง PHP Dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

# 7. ปรับ Permission ให้ Apache (www-data)
RUN chown -R www-data:www-data storage bootstrap/cache

EXPOSE 80
CMD ["apache2-foreground"]