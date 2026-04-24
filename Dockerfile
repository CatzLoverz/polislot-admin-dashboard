# ============================================================
# Stage 1: Composer — Install PHP Dependencies
# ============================================================
FROM composer:2.8 AS composer-stage

WORKDIR /build

COPY composer.json composer.lock ./
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction --ignore-platform-reqs

# ============================================================
# Stage 2: Node — Build Vite Assets
# ============================================================
FROM node:22-alpine AS node-stage

WORKDIR /build

COPY package.json package-lock.json ./
RUN npm ci

# Copy source files needed for build
COPY vite.config.js ./
COPY resources/ ./resources/

# VITE build-time values (bukan secret — ini public key yang terekspos di browser)
# Default sudah disetel untuk Docker deployment, tidak perlu --build-arg
ARG VITE_APP_NAME="PoliSlot"
ARG VITE_REVERB_APP_KEY="xcubvd4inm14ayepjhro"
ARG VITE_REVERB_HOST="127.0.0.1"
ARG VITE_REVERB_PORT="6001"
ARG VITE_REVERB_SCHEME="http"

RUN npm run build

# ============================================================
# Stage 3: Final — FrankenPHP Production Image
# ============================================================
FROM dunglas/frankenphp:php8.4

WORKDIR /var/www/html

# A. Install System Dependencies
RUN apt-get update && apt-get install -y \
    git \
    unzip \
    libzip-dev \
    mariadb-client \
    acl \
    curl \
    procps \
    supervisor \
    && rm -rf /var/lib/apt/lists/*

# B. Install PHP Extensions
RUN install-php-extensions \
    gd \
    pdo_mysql \
    mbstring \
    zip \
    exif \
    pcntl \
    bcmath \
    opcache

# C. Copy Composer binary & vendor dari stage 1
COPY --from=composer-stage /usr/bin/composer /usr/bin/composer
COPY --from=composer-stage /build/vendor ./vendor

# D. Copy seluruh source code aplikasi
COPY . .

# E. Copy hasil Vite build (public/build) dari stage 2
COPY --from=node-stage /build/public/build ./public/build

# F. Dump autoload setelah semua file ada
RUN composer dump-autoload --optimize

# G. Setup Entrypoint
COPY docker-entrypoint.sh /usr/local/bin/
RUN chmod +x /usr/local/bin/docker-entrypoint.sh

# H. Copy Supervisor config (dari root project, bukan docker/)
COPY supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# I. Copy Caddyfile — proxy WebSocket /app/* ke Reverb internal
COPY Caddyfile /etc/caddy/Caddyfile

# J. Final Ownership Fix
RUN chown -R www-data:www-data /var/www/html

# K. Expose Port — 80 (Web + WebSocket via Caddy reverse proxy)
EXPOSE 80

# K. Start via Entrypoint
ENTRYPOINT ["docker-entrypoint.sh"]
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]