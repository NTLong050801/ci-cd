# Base image
FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    build-essential \
    libpng-dev \
    libjpeg-dev \
    libfreetype6-dev \
    zip \
    unzip \
    git \
    curl \
    && docker-php-ext-configure gd --with-freetype --with-jpeg \
    && docker-php-ext-install gd pdo pdo_mysql mbstring opcache

# Thêm Composer từ image chính thức
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer

# Install Node.js (LTS)
RUN curl -fsSL https://deb.nodesource.com/setup_16.x | bash - && \
    apt-get install -y nodejs

# Install npm dependencies
RUN npm install -g npm

RUN composer install

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

# Copy project files
COPY . .

# Expose port
EXPOSE 9000

# Start PHP-FPM
CMD ["php-fpm"]
