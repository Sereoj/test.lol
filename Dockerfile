FROM php:8.2-fpm

# Установка зависимостей
RUN apt-get update && apt-get install -y \
    git \
    curl \
    libpng-dev \
    libonig-dev \
    libxml2-dev \
    zip \
    unzip \
    libzip-dev \
    libgd-dev \
    ffmpeg \
    imagemagick \
    libmagickwand-dev

# Очистка кеша apt
RUN apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install pdo_mysql mbstring exif pcntl bcmath gd zip ftp

# Установка и настройка Imagick
RUN pecl install imagick && \
    docker-php-ext-enable imagick

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Настройка рабочей директории
WORKDIR /var/www

# Копирование проекта
COPY . /var/www

# Установка зависимостей Composer (включая dev-зависимости)
RUN composer install --optimize-autoloader

# Права на директории
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache

# Экспорт порта
EXPOSE 9000

# Запуск PHP-FPM
CMD ["php-fpm"] 