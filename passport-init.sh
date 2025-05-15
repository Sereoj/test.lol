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

# Применяем миграции
echo "Применяем миграции..."
php artisan migrate --force || {
    echo "Предупреждение: Не удалось применить все миграции"
    # Продолжаем выполнение
}

# Пытаемся использовать команду passport:init
if php artisan passport:init; then
    echo "Команда passport:init выполнена успешно"
else
    echo "Команда passport:init завершилась с ошибкой, используем альтернативный метод..."
    
    # Генерируем ключи для Laravel Passport
    echo "Запускаем passport:keys --force..."
    php artisan passport:keys --force || {
        echo "Критическая ошибка: Не удалось создать ключи OAuth"
        exit 1
    }
    
    # Устанавливаем Passport
    echo "Запускаем passport:install..."
    php artisan passport:install || {
        echo "Предупреждение: Не удалось выполнить passport:install"
        # Продолжаем выполнение, так как ключи уже созданы
    }
fi

# Устанавливаем правильные права
echo "Устанавливаем права на директорию storage..."
chmod -R 775 /var/www/storage
chown -R www-data:www-data /var/www/storage

echo "Инициализация Laravel Passport завершена успешно" 