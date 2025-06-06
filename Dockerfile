# Dockerfile for laravel application, mount the laravel application to /var/www/html

FROM php:8.4-apache

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

# Install Supervisor
RUN apt-get update && apt-get install -y supervisor

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_pgsql mbstring zip exif pcntl bcmath gd

# Install Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Install and enable xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Install and enable excimer
RUN pecl install excimer && docker-php-ext-enable excimer

# Copy virtual host configuration
COPY ./docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache modules
RUN a2enmod rewrite

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
# COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY . /var/www/html
RUN chown -R service:service /var/www/html

# Ensure Supervisor log directory exists and is writable by 'service'
RUN mkdir -p /var/log/supervisor && chown -R service:service /var/log/supervisor

# Ensure /var/run is writable by 'service' for Supervisor pidfile and Apache
RUN mkdir -p /var/run && chown -R service:service /var/run

# Copy Supervisor config for Laravel queue worker
COPY ./docker/supervisor/laravel-worker.conf /etc/supervisor/conf.d/laravel-worker.conf

# Copy custom supervisord.conf to disable HTTP server
COPY ./docker/supervisor/supervisord.conf /etc/supervisor/supervisord.conf

# Configure Apache to listen on port 8080 instead of 80
RUN sed -i 's/Listen 80/Listen 8080/g' /etc/apache2/ports.conf

# Switch back to 'service' user
USER service

# Now run composer install and dump-autoload
RUN composer install --no-scripts --no-autoloader
RUN composer dump-autoload

# Expose port 8080
EXPOSE 8080

# Start Supervisor (which will manage Apache and the queue worker)
CMD ["/usr/bin/supervisord", "-n", "-c", "/etc/supervisor/supervisord.conf"]
