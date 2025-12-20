# ===============================
# Base Stage - Общие зависимости
# ===============================
FROM php:8.2-fpm AS base

# Установка системных зависимостей
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
    libmagickwand-dev \
    && apt-get clean && rm -rf /var/lib/apt/lists/*

# Установка расширений PHP
RUN docker-php-ext-install \
    pdo_mysql \
    mbstring \
    exif \
    pcntl \
    bcmath \
    gd \
    zip \
    ftp

# Установка Imagick
RUN pecl install imagick && docker-php-ext-enable imagick

# Установка Redis extension
RUN pecl install redis && docker-php-ext-enable redis

# Установка Composer
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Рабочая директория
WORKDIR /var/www

# ===============================
# Development Stage
# ===============================
FROM base AS development

# Установка Xdebug для отладки
RUN pecl install xdebug && docker-php-ext-enable xdebug

# PHP конфигурация для разработки
RUN echo "display_errors = On" > /usr/local/etc/php/conf.d/dev.ini \
    && echo "error_reporting = E_ALL" >> /usr/local/etc/php/conf.d/dev.ini \
    && echo "memory_limit = 512M" >> /usr/local/etc/php/conf.d/dev.ini

# Копирование composer файлов
COPY composer.json composer.lock ./

# Установка зависимостей с dev пакетами
RUN composer install \
    --no-scripts \
    --no-interaction \
    --prefer-dist

# Копирование остальных файлов
COPY . .

# Autoload
RUN composer dump-autoload

# Создание необходимых директорий
RUN mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/settings \
    bootstrap/cache

# Права доступа
RUN chown -R www-data:www-data /var/www

EXPOSE 9000

CMD ["php-fpm"]

# ===============================
# Production Stage
# ===============================
FROM base AS production

# Установка OPcache для production
RUN docker-php-ext-install opcache

# Копирование PHP конфигураций
COPY docker/php/php.ini /usr/local/etc/php/conf.d/99-custom.ini
COPY docker/php/opcache.ini /usr/local/etc/php/conf.d/opcache.ini

# Копирование composer файлов для кеширования
COPY composer.json composer.lock ./

# Установка зависимостей без dev пакетов
RUN composer install \
    --no-dev \
    --no-scripts \
    --no-interaction \
    --prefer-dist \
    --optimize-autoloader

# Копирование остальных файлов
COPY . .

# Финальная оптимизация autoloader (без скриптов, чтобы избежать ошибок с dev-зависимостями)
RUN composer dump-autoload --optimize --no-dev --classmap-authoritative --no-scripts

# Создание необходимых директорий
RUN mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/settings \
    bootstrap/cache

# Установка gosu для переключения пользователя в entrypoint
RUN set -eux; \
    apt-get update; \
    apt-get install -y gosu; \
    rm -rf /var/lib/apt/lists/*; \
    gosu nobody true

# Установка прав
RUN chown -R www-data:www-data /var/www/storage /var/www/bootstrap/cache \
    && chmod -R 775 /var/www/storage /var/www/bootstrap/cache

# Копирование и настройка entrypoint
COPY docker/entrypoint.sh /usr/local/bin/entrypoint.sh
RUN chmod +x /usr/local/bin/entrypoint.sh

EXPOSE 9000

# Entrypoint для инициализации (выполняется от root для установки прав)
ENTRYPOINT ["/usr/local/bin/entrypoint.sh"]

# Запуск PHP-FPM (будет запущен от www-data через entrypoint)
CMD ["php-fpm"]
