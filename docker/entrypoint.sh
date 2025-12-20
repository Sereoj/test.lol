#!/bin/bash
set -e

echo "🚀 Starting Laravel application initialization..."

# Переменные окружения по умолчанию
export PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT:-512M}
export PHP_MAX_UPLOAD=${PHP_MAX_UPLOAD:-50M}
export PHP_MAX_FILE_UPLOAD=${PHP_MAX_FILE_UPLOAD:-200}

# Ожидание готовности базы данных (внешняя БД)
if [ -n "$DB_HOST" ]; then
    echo "⏳ Waiting for database to be ready..."
    MAX_TRIES=30
    COUNT=0
    until php -r "new PDO('mysql:host=${DB_HOST};port=${DB_PORT:-3306}', '${DB_USERNAME}', '${DB_PASSWORD}');" 2>/dev/null || [ $COUNT -eq $MAX_TRIES ]; do
        echo "Database at ${DB_HOST} is unavailable - sleeping (attempt $COUNT/$MAX_TRIES)"
        COUNT=$((COUNT + 1))
        sleep 2
    done

    if [ $COUNT -eq $MAX_TRIES ]; then
        echo "⚠️  Warning: Could not connect to database after $MAX_TRIES attempts. Continuing anyway..."
    else
        echo "✅ Database is ready!"
    fi
fi

# Создание необходимых директорий если их нет
echo "📁 Ensuring required directories exist..."
mkdir -p storage/logs \
    storage/framework/cache \
    storage/framework/sessions \
    storage/framework/views \
    storage/app/settings \
    bootstrap/cache 2>/dev/null || true

# Копирование примера settings.json если нужно
if [ ! -f storage/app/settings/settings.json ] && [ -f storage/app/settings/settings.json.example ]; then
    echo "📋 Copying settings.json.example..."
    cp storage/app/settings/settings.json.example storage/app/settings/settings.json
fi

# Проверка переменных окружения
if [ "$APP_ENV" = "production" ]; then
    echo "🔒 Production environment detected"

    if [ -z "$APP_KEY" ]; then
        echo "❌ ERROR: APP_KEY is not set in production!"
        exit 1
    fi
fi

# Только при первом запуске или если передан флаг INIT_DB=true
if [ "$INIT_DB" = "true" ] || [ ! -f /var/www/storage/.initialized ]; then
    echo "🗄️  Running database migrations..."
    php artisan migrate --force || {
        echo "⚠️  Warning: Migration failed, continuing..."
    }

    # Инициализация Laravel Passport
    if [ ! -f /var/www/storage/oauth-private.key ]; then
        echo "🔐 Initializing Laravel Passport..."
        php artisan passport:keys --force || {
            echo "⚠️  Warning: Passport keys generation failed"
        }

        php artisan passport:client --personal --no-interaction || {
            echo "⚠️  Warning: Passport client creation failed"
        }
    fi

    # Отметка об инициализации
    touch /var/www/storage/.initialized
    echo "✅ Initialization complete"
fi

# Очистка и оптимизация кешей для production
if [ "$APP_ENV" = "production" ]; then
    echo "⚡ Optimizing for production..."

    # Кеширование конфигурации
    php artisan config:cache

    # Кеширование маршрутов
    php artisan route:cache

    # Кеширование view
    php artisan view:cache

    # Кеширование событий
    php artisan event:cache

    echo "✅ Production optimization complete"
fi

# Установка прав доступа
echo "🔧 Setting permissions..."
# Устанавливаем права на запись для группы (775), чтобы www-data мог писать
chmod -R 775 storage bootstrap/cache 2>/dev/null || true

# Пытаемся установить владельца (работает только если запущены от root)
if [ "$(whoami)" != "www-data" ]; then
    chown -R www-data:www-data storage bootstrap/cache 2>/dev/null || true
fi

echo "✨ Initialization complete! Starting application..."

# Запуск команды из CMD
# Если мы root и установлен gosu, запускаем от www-data
# В development режиме контейнер может работать от root, поэтому используем gosu если он доступен
if [ "$(id -u)" = "0" ] && command -v gosu >/dev/null 2>&1; then
    exec gosu www-data "$@"
else
    exec "$@"
fi
