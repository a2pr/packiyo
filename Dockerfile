# Use an official PHP runtime as a parent image
FROM php:8.2-fpm

# Set the working directory to /var/www/html
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && \
    apt-get install -y \
    git \
    curl \
    libzip-dev \
    libonig-dev \
    unzip \
    && docker-php-ext-install pdo_mysql zip

# Install Composer
RUN curl -sS https://getcomposer.org/installer | php -- --install-dir=/usr/local/bin --filename=composer

# Copy the current directory contents into the container at /var/www/html
COPY ./ /var/www/html

RUN rm -f ./.env.local1
RUN mv .env.local1.example .env.local1

# Install Laravel dependencies
RUN composer install --no-scripts --no-interaction --prefer-dist

# Generate the Laravel application key
RUN php artisan key:generate

# Expose port 8000 and start php-fpm server
EXPOSE 8000
CMD ["php", "artisan", "serve", "--host=0.0.0.0", "--port=8000"]
