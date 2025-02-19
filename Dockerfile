# Dockerfile for laravel application, mount the laravel application to /var/www/html

FROM php:8.2.8-fpm

# Install dependencies
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_pgsql mbstring zip exif pcntl bcmath gd

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create a 'service' group and user, and assign home directory permissions
RUN groupadd -g 1000 service && \
    useradd -u 1000 -g service -m service

# Set working directory
WORKDIR /var/www/html

# Ensure working directory is owned by the 'service' user
RUN chown -R service:service /var/www/html

# Switch to the non-root 'service' user
USER service

# Temporarily switch to root to copy code
USER root
COPY . /var/www/html
RUN chown -R service:service /var/www/html

# Switch back to 'service' user
USER service

# Now run composer install and dump-autoload
RUN composer install --no-scripts --no-autoloader
RUN composer dump-autoload

# Expose port 9000 for PHP-FPM
EXPOSE 9000

# Update to start PHP-FPM instead of Apache
CMD ["php-fpm"]

