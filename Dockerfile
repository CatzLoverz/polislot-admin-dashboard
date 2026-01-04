# === Stage 1: Composer ===
FROM composer:2.8 AS composer

# === Stage 2: App ===
FROM php:8.3-apache

WORKDIR /var/www/html

# A. Install Dependensi System
# PENTING: 'mariadb-client' wajib ada untuk menjalankan mysqldump & mysql restore
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    libpng-dev \
    libjpeg62-turbo-dev \
    libfreetype6-dev \
    mariadb-client \
    libexif-dev \
    libonig-dev \
    acl \
    && rm -rf /var/lib/apt/lists/*

# B. Install PHP Extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd \
    && docker-php-ext-install -j$(nproc) pdo_mysql mbstring zip exif pcntl bcmath

# C. Config Apache
COPY apache-vhost.conf /etc/apache2/sites-available/000-default.conf
RUN a2enmod rewrite

# D. Copy Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# E. Copy & Install Code
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts

COPY . .
RUN composer dump-autoload --optimize

# F. Setup Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# G. Final Ownership Fix
RUN chown -R www-data:www-data /var/www/html

# H. Expose Port 80 (Internal Container)
EXPOSE 80

# I. Jalankan Entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["apache2-foreground"]