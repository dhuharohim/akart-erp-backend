# ── Stage 1: Composer install ──
FROM composer:2 AS vendor
WORKDIR /app
COPY composer.json composer.lock ./
RUN composer install --no-interaction --prefer-dist --no-dev --no-scripts --no-autoloader
COPY . .
RUN composer dump-autoload --optimize --no-dev

# ── Stage 2: Production image ──
FROM php:8.3-fpm-alpine

# Install system deps + PHP extensions
RUN apk add --no-cache \
    nginx \
    supervisor \
    libpq-dev \
    mysql-client \
    icu-dev \
    libzip-dev \
    freetype-dev \
    libjpeg-turbo-dev \
    libpng-dev \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install \
        pdo \
        pdo_mysql \
        pdo_pgsql \
        intl \
        zip \
        gd \
        pcntl \
        opcache \
    && pecl install redis \
    && docker-php-ext-enable redis \
    && rm -rf /var/cache/apk/*

# PHP production config
RUN mv "$PHP_INI_DIR/php.ini-production" "$PHP_INI_DIR/php.ini"

# Set opcache settings
RUN echo "opcache.enable=1" >> "$PHP_INI_DIR/conf.d/opcache.ini" && \
    echo "opcache.memory_consumption=128" >> "$PHP_INI_DIR/conf.d/opcache.ini" && \
    echo "opcache.max_accelerated_files=10000" >> "$PHP_INI_DIR/conf.d/opcache.ini" && \
    echo "opcache.validate_timestamps=0" >> "$PHP_INI_DIR/conf.d/opcache.ini"

# Set upload limits
RUN echo "upload_max_filesize=64M" >> "$PHP_INI_DIR/conf.d/uploads.ini" && \
    echo "post_max_size=64M" >> "$PHP_INI_DIR/conf.d/uploads.ini"

WORKDIR /var/www/html

# Copy application
COPY --from=vendor /app .

# Set permissions
RUN chown -R www-data:www-data storage bootstrap/cache && \
    chmod -R 775 storage bootstrap/cache

# Nginx config
COPY docker/nginx.conf /etc/nginx/http.d/default.conf

# Supervisor config (runs nginx + php-fpm + queue worker)
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 8000

ENTRYPOINT ["entrypoint.sh"]
