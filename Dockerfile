FROM php:8.3-fpm

# Установка системных зависимостей
RUN apt-get update && apt-get install -y \
    libzip-dev \
    libpng-dev \
    libjpeg-dev \
    libwebp-dev \
    libfreetype6-dev \
    libicu-dev \
    zlib1g-dev \
    libonig-dev \
    libxml2-dev \
    libcurl4-openssl-dev

# Установка PHP-расширений
RUN docker-php-ext-install \
    pdo_mysql \
    gd \
    intl \
    zip \
    curl \
    opcache

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html
WORKDIR /var/www/html
