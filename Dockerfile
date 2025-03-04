# Dockerfile for laravel application, mount the laravel application to /var/www/html

FROM php:8.3-apache

# Install dependencies including Node.js
RUN apt-get update && apt-get install -y \
    libzip-dev \
    zip \
    unzip \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    libpq-dev \
    nodejs \
    npm

# Clear cache
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Install extensions
RUN docker-php-ext-install pdo_pgsql mbstring zip exif pcntl bcmath gd

# Install and enable xdebug
RUN pecl install xdebug && docker-php-ext-enable xdebug

# Copy virtual host configuration
COPY ./docker/apache/vhost.conf /etc/apache2/sites-available/000-default.conf

# Enable Apache modules
RUN a2enmod rewrite headers

# Get latest Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create a 'service' group and user, and assign home directory permissions
RUN groupadd -g 1000 service && \
    useradd -u 1000 -g service -m service

# Set working directory
WORKDIR /var/www/html

# Ensure working directory is owned by the 'service' user
RUN chown -R service:service /var/www/html

# Temporarily switch to root to copy code
USER root
COPY ./docker/php/xdebug.ini /usr/local/etc/php/conf.d/xdebug.ini
COPY . /var/www/html
RUN chown -R service:service /var/www/html

# Switch to 'service' user
USER service

# Install npm dependencies and build assets in production mode
RUN npm install
RUN npm run build

# Install composer dependencies
RUN composer install --no-scripts --no-autoloader
RUN composer dump-autoload --optimize

# Create directory for public/build if it doesn't exist and ensure proper permissions
USER root
RUN mkdir -p public/build
RUN chown -R service:service public/build
USER service

# Create a public/css and public/js directory as fallback for non-Vite assets
USER root
RUN mkdir -p public/css public/js
RUN chown -R service:service public/css public/js
USER service

# Expose port 80
EXPOSE 80

# Start Apache
CMD ["apache2-foreground"]

