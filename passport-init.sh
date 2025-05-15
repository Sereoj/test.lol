#!/bin/bash
set -e

echo "Начинаем инициализацию Laravel Passport..."

# Переходим в директорию проекта
cd /var/www

# Проверяем существование директории storage
if [ ! -d "/var/www/storage" ]; then
    echo "Ошибка: Директория /var/www/storage не существует"
    mkdir -p /var/www/storage
    echo "Директория /var/www/storage создана"
fi

# Используем нашу новую команду для инициализации Passport
echo "Запускаем команду passport:init..."
php artisan passport:init

# Если команда не сработала, используем старый метод
if [ $? -ne 0 ]; then
    echo "Команда passport:init завершилась с ошибкой, используем альтернативный метод..."
    
    # Генерируем ключи для Laravel Passport
    echo "Запускаем passport:install --uuids..."
    php artisan passport:install --uuids || {
        echo "Ошибка при выполнении passport:install"
        # Продолжаем выполнение, так как следующая команда может исправить проблему
    }

    echo "Запускаем passport:keys --force..."
    php artisan passport:keys --force || {
        echo "Ошибка при выполнении passport:keys"
        exit 1
    }

    # Проверяем, созданы ли ключи
    if [ ! -f "/var/www/storage/oauth-private.key" ] || [ ! -f "/var/www/storage/oauth-public.key" ]; then
        echo "Ошибка: Ключи не были созданы"
        
        # Создаем директорию вручную, если она не существует
        mkdir -p /var/www/storage
        
        # Пробуем создать ключи вручную
        echo "Пробуем создать ключи вручную..."
        php artisan passport:keys --force
        
        # Проверяем еще раз
        if [ ! -f "/var/www/storage/oauth-private.key" ] || [ ! -f "/var/www/storage/oauth-public.key" ]; then
            echo "Критическая ошибка: Не удалось создать ключи OAuth"
            exit 1
        fi
    fi

    # Запускаем сидер для создания клиентов Passport
    echo "Запускаем сидер PassportClientsSeeder..."
    php artisan db:seed --class=PassportClientsSeeder || {
        echo "Предупреждение: Не удалось запустить сидер PassportClientsSeeder"
        # Продолжаем выполнение, так как это может быть не критично
    }
fi

# Устанавливаем правильные права
echo "Устанавливаем права на директорию storage..."
chmod -R 775 /var/www/storage
chown -R www-data:www-data /var/www/storage

echo "Инициализация Laravel Passport завершена успешно" 