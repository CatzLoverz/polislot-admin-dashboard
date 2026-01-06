# === Stage 1: Composer ===
FROM composer:2.8 AS composer

# === Stage 2: App ===
FROM dunglas/frankenphp:php8.4

WORKDIR /var/www/html

# A. Install System Dependencies
# We use apt-get because the default image is Debian-based
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    mariadb-client \
    acl \
    curl \
    procps \
    && rm -rf /var/lib/apt/lists/*

# B. Install PHP Extensions
# FrankenPHP includes 'install-php-extensions' script
RUN install-php-extensions \
    gd \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    opcache

# C. Copy Composer
COPY --from=composer /usr/bin/composer /usr/bin/composer

# D. Copy & Install Code
COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts
COPY . .
RUN composer dump-autoload --optimize

# E. Setup Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# F. Final Ownership Fix
RUN chown -R www-data:www-data /var/www/html

# G. Expose Port
EXPOSE 80

# H. Start Server
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["frankenphp", "php-server", "--root", "public/"]