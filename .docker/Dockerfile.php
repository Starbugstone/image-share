FROM php:8.2-apache

# Install system dependencies
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libzip-dev \
    libicu-dev \
    zip \
    unzip \
    nodejs \
    npm \
    && docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip intl \
    && apt-get clean \
    && rm -rf /var/lib/apt/lists/*

# Enable Apache modules
RUN a2enmod rewrite headers ssl

# Copy Apache configuration
COPY apache.conf /etc/apache2/sites-available/000-default.conf

# Install Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Set working directory
WORKDIR /var/www/html

# Copy composer files
COPY composer.json composer.lock* ./

# Install PHP dependencies (skip scripts to avoid cache:clear before app is ready)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-scripts

# Copy application code
COPY . .

# Create necessary directories and set permissions
RUN mkdir -p /var/www/html/images \
    && mkdir -p /var/www/html/var/cache \
    && mkdir -p /var/www/html/var/log \
    && mkdir -p /var/www/html/var/sessions \
    && mkdir -p /var/www/html/public/uploads/profile_images \
    && chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html \
    && chmod -R 777 /var/www/html/var \
    && chmod -R 777 /var/www/html/images \
    && chmod -R 777 /var/www/html/public/uploads

# Set environment variables for build
ENV APP_ENV=prod
ENV APP_SECRET=build_secret_key_will_be_overridden_at_runtime

# Run composer scripts and cache warmup
RUN composer run-script auto-scripts || true

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]
