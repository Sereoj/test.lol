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

# Копирование только composer файлов для кеширования
COPY composer.json composer.lock ./

# Установка зависимостей Composer
RUN composer install --optimize-autoloader --no-scripts

# Теперь копируем остальные файлы проекта
COPY . .

# Запускаем скрипты композера
RUN composer dump-autoload --optimize

# Копируем скрипт инициализации Passport
COPY passport-init.sh /usr/local/bin/passport-init.sh
RUN chmod +x /usr/local/bin/passport-init.sh

# Создаем скрипт для запуска PHP-FPM с правильными правами
RUN echo '#!/bin/bash\n\
# Создаем необходимые директории\n\
mkdir -p /var/www/storage/logs\n\
mkdir -p /var/www/storage/framework/cache\n\
mkdir -p /var/www/storage/framework/sessions\n\
mkdir -p /var/www/storage/framework/views\n\
mkdir -p /var/www/bootstrap/cache\n\
mkdir -p /var/www/storage/app/settings\n\
\n\
# Копируем settings.json, если он существует в исходном коде\n\
if [ -f /var/www/storage/app/settings/settings.json.example ]; then\n\
  cp /var/www/storage/app/settings/settings.json.example /var/www/storage/app/settings/settings.json\n\
fi\n\
\n\
# Устанавливаем полные права на директории storage и bootstrap/cache\n\
chmod -R 777 /var/www/storage\n\
chmod -R 777 /var/www/bootstrap/cache\n\
\n\
# Устанавливаем права на скрипт инициализации Passport\n\
chmod +x /usr/local/bin/passport-init.sh\n\
\n\
# Запускаем скрипт инициализации Passport\n\
/usr/local/bin/passport-init.sh\n\
\n\
# Меняем владельца на www-data\n\
chown -R www-data:www-data /var/www/storage\n\
chown -R www-data:www-data /var/www/bootstrap/cache\n\
\n\
# Запускаем PHP-FPM\n\
exec php-fpm\n'\
> /usr/local/bin/start-php-fpm.sh && \
chmod +x /usr/local/bin/start-php-fpm.sh

# Экспорт порта
EXPOSE 9000

# Запуск PHP-FPM через скрипт-обертку
CMD ["/usr/local/bin/start-php-fpm.sh"]
