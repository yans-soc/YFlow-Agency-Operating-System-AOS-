# Dockerfile for YFlow Backend

# --- Base Stage ---
FROM php:8.4-fpm-alpine AS base

# Install system dependencies
RUN apk add --no-cache \
    curl \
    nginx \
    supervisor \
    libzip-dev \
    libpng-dev \
    libxml2-dev \
    postgresql-dev

# Install PHP extensions
RUN docker-php-ext-install \
    pdo_pgsql \
    zip \
    gd \
    sockets \
    pcntl \
    exif

# --- Builder Stage ---
FROM base AS builder

# Set working directory
WORKDIR /var/www/html

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Copy application files
COPY . .

# Install dependencies
RUN composer install --no-dev --no-interaction --optimize-autoloader

# --- Final Stage ---
FROM base AS final

# Set working directory
WORKDIR /var/www/html

# Copy vendor files from builder
COPY --from=builder /var/www/html/vendor ./vendor

# Copy application files
COPY . .

# Set permissions
RUN chown -R www-data:www-data /var/www/html

# Expose port
EXPOSE 8000

# Start command
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
