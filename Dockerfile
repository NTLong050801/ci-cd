# Base image
FROM php:8.1-fpm

# Set working directory
WORKDIR /var/www/html

# Install dependencies
RUN apt-get update && apt-get install -y \
    curl \
    gnupg \
    git \
    unzip \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    npm \
    && apt-get clean


RUN curl -sS https://dl.yarnpkg.com/debian/pubkey.gpg | apt-key add - \
    && echo "deb https://dl.yarnpkg.com/debian/ stable main" | tee /etc/apt/sources.list.d/yarn.list \
    && apt-get update && apt-get install -y yarn

# Thêm Composer từ image chính thức
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer


WORKDIR /var/www/html
COPY . .

RUN composer install

# Cài đặt các gói PHP cần thiết
RUN docker-php-ext-install pdo pdo_mysql

# Cài đặt các gói Yarn (ví dụ như SASS)
RUN yarn install


# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 775 /var/www/html/storage /var/www/html/bootstrap/cache

RUN pecl install redis && docker-php-ext-enable redis
# Cấu hình cổng
EXPOSE 80
EXPOSE 5173

# Lệnh mặc định
CMD ["php-fpm"]
