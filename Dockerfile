FROM php:8.4-apache

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

RUN docker-php-ext-install \
    pdo \
    pdo_pgsql \
    pgsql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd

# Fix MPM conflict
RUN a2dismod mpm_event 2>/dev/null || true
RUN a2dismod mpm_worker 2>/dev/null || true
RUN a2enmod mpm_prefork
RUN a2enmod rewrite
RUN a2enmod headers

COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

WORKDIR /var/www/html
COPY . .

RUN composer install --no-dev --optimize-autoloader --no-interaction

RUN printf '<VirtualHost *:${PORT}>\n\
    DocumentRoot /var/www/html/public\n\
    <Directory /var/www/html/public>\n\
    AllowOverride All\n\
    Require all granted\n\
    </Directory>\n\
    SetEnvIf Authorization "(.*)" HTTP_AUTHORIZATION=$1\n\
    </VirtualHost>\n' > /etc/apache2/sites-available/000-default.conf

RUN echo 'Listen ${PORT}' > /etc/apache2/ports.conf

RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html/storage \
    && chmod -R 755 /var/www/html/bootstrap/cache

EXPOSE 8080
CMD ["sh", "-c", "php artisan config:clear && php artisan cache:clear && apache2-foreground"]
