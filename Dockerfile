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
    && curl -fsSL https://deb.nodesource.com/setup_20.x | bash - \
    && apt-get install -y nodejs \
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
COPY composer.json composer.lock package*.json ./
RUN composer install --no-dev --optimize-autoloader --no-scripts \
    && npm install

COPY . .
RUN cp .env.example .env \
    && php artisan install:broadcasting --force -n \
    && composer dump-autoload --optimize \
    && npm run build

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