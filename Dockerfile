FROM php:8.4-apache

# Install system dependencies
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

# Install PHP extensions
RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# Enable Apache mod_rewrite (required for Laravel routing)
RUN a2enmod rewrite

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html

# Copy app
COPY . .

# Install dependencies
RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN printf '<VirtualHost *:${PORT}>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1\n\
    </VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

# Render injects $PORT — Apache must listen on it
RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf \
    && sed -i 's|<VirtualHost \*:80>|<VirtualHost *:${PORT}>|g' /etc/apache2/sites-available/000-default.conf

EXPOSE 8080


CMD ["sh", "-c", "php artisan config:clear && php artisan cache:clear && apache2-foreground"]

# CMD ["apache2-foreground"]
