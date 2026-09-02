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
    libcurl4-openssl-dev \
    libc-client-dev \
    libkrb5-dev

# Приём почты для заявок работает через IMAP, поэтому расширение собирается
# отдельно: ему нужны пути к Kerberos и SSL.
RUN docker-php-ext-configure imap --with-kerberos --with-imap-ssl \
    && docker-php-ext-install imap

# Установка PHP-расширений
RUN docker-php-ext-install \
    pdo_mysql \
    gd \
    intl \
    zip \
    curl \
    opcache

# Лимиты загрузки файлов для вложений в ответах
COPY docker/php/uploads.ini /usr/local/etc/php/conf.d/uploads.ini

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка прав доступа
RUN chown -R www-data:www-data /var/www/html
WORKDIR /var/www/html
