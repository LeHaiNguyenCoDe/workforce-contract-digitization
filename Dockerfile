# Multi-stage build for optimized production image

# Stage 1: Build Frontend (Node.js)
FROM node:20-alpine AS frontend-builder

WORKDIR /app/frontend

COPY package*.json ./

RUN npm ci

COPY . .

RUN npm run build

# Stage 2: Build Backend (PHP)
FROM php:8.2-fpm-alpine AS backend-builder

WORKDIR /app

# Install system dependencies
RUN apk add --no-cache \
    libpng-dev \
    libjpeg-turbo-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql bcmath ctype fileinfo json tokenizer xml

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

COPY composer*.json ./

RUN composer install --no-dev --prefer-dist --optimize-autoloader

COPY . .

# Stage 3: Production Image (Nginx + PHP-FPM)
FROM php:8.2-fpm-alpine

WORKDIR /app

# Install runtime dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    curl \
    libpng \
    libjpeg-turbo \
    libfreetype6

# Install PHP extensions
RUN docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install -j$(nproc) gd pdo pdo_mysql bcmath ctype fileinfo json tokenizer xml

# Copy PHP configuration
COPY docker/php/php.ini /usr/local/etc/php/php.ini
COPY docker/php/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf

# Copy Nginx configuration
COPY docker/nginx/nginx.conf /etc/nginx/nginx.conf
COPY docker/nginx/default.conf /etc/nginx/conf.d/default.conf

# Copy Supervisor configuration
COPY docker/supervisor/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Copy backend from builder
COPY --from=backend-builder /app /app

# Copy frontend build from builder
COPY --from=frontend-builder /app/frontend/dist /app/public/dist

# Create necessary directories
RUN mkdir -p /app/storage/logs \
    && mkdir -p /app/storage/framework/cache \
    && mkdir -p /app/storage/framework/sessions \
    && mkdir -p /app/storage/framework/views \
    && chown -R www-data:www-data /app/storage \
    && chmod -R 775 /app/storage

# Health check
HEALTHCHECK --interval=30s --timeout=10s --start-period=40s --retries=3 \
    CMD curl -f http://localhost/health || exit 1

# Expose ports
EXPOSE 80 443 9000

# Start services
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]

