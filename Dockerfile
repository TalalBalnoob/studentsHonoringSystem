# Use official PHP 8.2 FPM image as the base
FROM php:8.2-fpm

# Install system dependencies needed by Laravel and common PHP extensions
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    zip \
    unzip \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Install PHP extensions Laravel requires
# pdo_pgsql = PostgreSQL driver (Supabase uses Postgres)
# mbstring, exif, pcntl, bcmath, gd = standard Laravel requirements
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# Install Composer from its official image (no manual download needed)
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory inside the container
WORKDIR /var/www

# Copy composer files first — this lets Docker cache the dependency
# layer so it doesn't re-download packages on every build
COPY composer.json composer.lock ./

# Install PHP dependencies (no dev deps, optimized for production)
RUN composer install --no-dev --optimize-autoloader --no-scripts --no-interaction

# Copy the rest of the application code into the container
COPY . .

# Copy custom PHP config (upload limits, etc.)
COPY docker/php/local.ini /usr/local/etc/php/conf.d/local.ini

# Set correct permissions so Laravel can write to storage and cache
RUN chown -R www-data:www-data /var/www \
    && chmod -R 755 /var/www/storage \
    && chmod -R 755 /var/www/bootstrap/cache

# Run composer scripts now that full app code is present
RUN composer run-script post-autoload-dump || true

# Expose port 9000 for PHP-FPM (Nginx talks to this)
EXPOSE 9000

CMD ["php-fpm"]
