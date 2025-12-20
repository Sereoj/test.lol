#!/bin/bash
set -e

# Получение версии приложения из composer.json
APP_VERSION=$(php -r "echo json_decode(file_get_contents('composer.json'), true)['version'] ?? 'unknown';")

echo "🚀 Starting Laravel application initialization..."
echo "📦 Application Version: $APP_VERSION"
echo ""

# Переменные окружения по умолчанию
export PHP_MEMORY_LIMIT=${PHP_MEMORY_LIMIT:-512M}
export PHP_MAX_UPLOAD=${PHP_MAX_UPLOAD:-50M}
export PHP_MAX_FILE_UPLOAD=${PHP_MAX_FILE_UPLOAD:-200}

# Функция для проверки существования .env файла
check_env_file() {
    if [ ! -f ".env" ]; then
        echo "⚠️  Warning: .env file not found!"
        if [ -f ".env.example" ]; then
            echo "📋 Copying .env.example to .env..."
            cp .env.example .env
        else
            echo "❌ ERROR: Neither .env nor .env.example found!"
            exit 1
        fi
    fi
}

# Функция для генерации APP_KEY
generate_app_key() {
    local current_key=$(grep "^APP_KEY=" .env | cut -d '=' -f2-)

    if [ -z "$current_key" ] || [ "$current_key" = "" ]; then
        echo "🔑 Generating APP_KEY..."
        php artisan key:generate --force
        echo "✅ APP_KEY generated successfully"
    else
        echo "✅ APP_KEY already exists"
    fi
}

# Функция для генерации Reverb ключей
generate_reverb_keys() {
    local reverb_app_key=$(grep "^REVERB_APP_KEY=" .env | cut -d '=' -f2-)
    local reverb_app_secret=$(grep "^REVERB_APP_SECRET=" .env | cut -d '=' -f2-)

    if [ -z "$reverb_app_key" ] || [ "$reverb_app_key" = "" ]; then
        echo "🔑 Generating REVERB_APP_KEY..."
        NEW_KEY=$(openssl rand -base64 16 | tr -d '/+=' | cut -c1-20)
        sed -i "s|^REVERB_APP_KEY=.*|REVERB_APP_KEY=$NEW_KEY|" .env
        echo "✅ REVERB_APP_KEY generated: $NEW_KEY"
    fi

    if [ -z "$reverb_app_secret" ] || [ "$reverb_app_secret" = "" ]; then
        echo "🔑 Generating REVERB_APP_SECRET..."
        NEW_SECRET=$(openssl rand -base64 16 | tr -d '/+=' | cut -c1-20)
        sed -i "s|^REVERB_APP_SECRET=.*|REVERB_APP_SECRET=$NEW_SECRET|" .env
        echo "✅ REVERB_APP_SECRET generated: $NEW_SECRET"
    fi
}

# Функция проверки Redis
check_redis() {
    if [ -n "$REDIS_HOST" ]; then
        echo "⏳ Waiting for Redis to be ready..."
        MAX_TRIES=30
        COUNT=0
        until php -r "
            \$redis = new Redis();
            try {
                \$redis->connect('${REDIS_HOST}', ${REDIS_PORT:-6379}, 2);
                exit(0);
            } catch (Exception \$e) {
                exit(1);
            }
        " 2>/dev/null || [ $COUNT -eq $MAX_TRIES ]; do
            echo "Redis at ${REDIS_HOST}:${REDIS_PORT:-6379} is unavailable - sleeping (attempt $COUNT/$MAX_TRIES)"
            COUNT=$((COUNT + 1))
            sleep 2
        done

        if [ $COUNT -eq $MAX_TRIES ]; then
            echo "⚠️  Warning: Could not connect to Redis after $MAX_TRIES attempts."
        else
            echo "✅ Redis is ready!"
        fi
    fi
}

# Функция создания символической ссылки storage
create_storage_link() {
    if [ ! -L "public/storage" ]; then
        echo "🔗 Creating storage link..."
        php artisan storage:link || {
            echo "⚠️  Warning: Failed to create storage link"
        }
    else
        echo "✅ Storage link already exists"
    fi
}

# Функция очистки кешей
clear_caches() {
    echo "🧹 Clearing application caches..."
    php artisan cache:clear || true
    php artisan config:clear || true
    php artisan route:clear || true
    php artisan view:clear || true
    echo "✅ Caches cleared"
}

# Функция проверки Composer зависимостей
check_composer_dependencies() {
    if [ "$APP_ENV" = "local" ] || [ "$APP_ENV" = "development" ]; then
        if [ ! -d "vendor" ] || [ ! -f "vendor/autoload.php" ]; then
            echo "📦 Installing Composer dependencies..."
            composer install --no-interaction --prefer-dist || {
                echo "⚠️  Warning: Composer install failed"
            }
        else
            echo "✅ Composer dependencies already installed"
        fi
    fi
}

# Проверка .env файла
check_env_file

# Проверка Composer зависимостей
check_composer_dependencies

# Генерация ключей если нужно
generate_app_key
generate_reverb_keys

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

# Проверка Redis
check_redis

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

# Создание символической ссылки storage
create_storage_link

# Проверка переменных окружения
if [ "$APP_ENV" = "production" ]; then
    echo "🔒 Production environment detected"

    if [ -z "$APP_KEY" ]; then
        echo "❌ ERROR: APP_KEY is not set in production!"
        exit 1
    fi

    # Проверка критически важных переменных для production
    REQUIRED_VARS=("DB_HOST" "DB_DATABASE" "DB_USERNAME" "DB_PASSWORD")
    MISSING_VARS=()

    for var in "${REQUIRED_VARS[@]}"; do
        if [ -z "${!var}" ]; then
            MISSING_VARS+=("$var")
        fi
    done

    if [ ${#MISSING_VARS[@]} -ne 0 ]; then
        echo "❌ ERROR: Required environment variables are missing:"
        printf '  - %s\n' "${MISSING_VARS[@]}"
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
    else
        echo "✅ Passport keys already exist"
    fi

    # Создание Personal Access Client если не существует
    local personal_client_id=$(grep "^PASSPORT_PERSONAL_ACCESS_CLIENT_ID=" .env | cut -d '=' -f2-)
    if [ -z "$personal_client_id" ] || [ "$personal_client_id" = "" ]; then
        echo "🔐 Creating Passport Personal Access Client..."

        # Создаем клиент и парсим вывод
        OUTPUT=$(php artisan passport:client --personal --no-interaction 2>&1)

        # Извлекаем Client ID и Client Secret
        CLIENT_ID=$(echo "$OUTPUT" | grep "Client ID:" | awk '{print $3}')
        CLIENT_SECRET=$(echo "$OUTPUT" | grep "Client secret:" | awk '{print $3}')

        if [ -n "$CLIENT_ID" ] && [ -n "$CLIENT_SECRET" ]; then
            # Обновляем .env файл
            sed -i "s|^PASSPORT_PERSONAL_ACCESS_CLIENT_ID=.*|PASSPORT_PERSONAL_ACCESS_CLIENT_ID=$CLIENT_ID|" .env
            sed -i "s|^PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=.*|PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET=$CLIENT_SECRET|" .env
            echo "✅ Personal Access Client created: $CLIENT_ID"
        else
            echo "⚠️  Warning: Could not extract Passport client credentials"
        fi
    else
        echo "✅ Passport Personal Access Client already configured"
    fi

    # Создание Password Grant Client если не существует
    local password_client_id=$(grep "^PASSPORT_PASSWORD_CLIENT_ID=" .env | cut -d '=' -f2-)
    if [ -z "$password_client_id" ] || [ "$password_client_id" = "" ]; then
        echo "🔐 Creating Passport Password Grant Client..."

        # Создаем password клиент
        OUTPUT=$(php artisan passport:client --password --no-interaction --name="Wallone Password Grant" 2>&1)

        # Извлекаем Client ID и Client Secret
        CLIENT_ID=$(echo "$OUTPUT" | grep "Client ID:" | awk '{print $3}')
        CLIENT_SECRET=$(echo "$OUTPUT" | grep "Client secret:" | awk '{print $3}')

        if [ -n "$CLIENT_ID" ] && [ -n "$CLIENT_SECRET" ]; then
            # Обновляем .env файл
            sed -i "s|^PASSPORT_PASSWORD_CLIENT_ID=.*|PASSPORT_PASSWORD_CLIENT_ID=$CLIENT_ID|" .env
            sed -i "s|^PASSPORT_PASSWORD_CLIENT_SECRET=.*|PASSPORT_PASSWORD_CLIENT_SECRET=$CLIENT_SECRET|" .env
            echo "✅ Password Grant Client created: $CLIENT_ID"
        else
            echo "⚠️  Warning: Could not extract Password Grant client credentials"
        fi
    else
        echo "✅ Passport Password Grant Client already configured"
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

# Создаем массив директорий для установки прав
WRITABLE_DIRS=(
    "storage"
    "storage/app"
    "storage/app/public"
    "storage/app/settings"
    "storage/framework"
    "storage/framework/cache"
    "storage/framework/sessions"
    "storage/framework/views"
    "storage/logs"
    "bootstrap/cache"
)

# Устанавливаем права на каждую директорию
for dir in "${WRITABLE_DIRS[@]}"; do
    if [ -d "$dir" ]; then
        # Устанавливаем права 775 (rwxrwxr-x) для директорий
        chmod 775 "$dir" 2>/dev/null || true
        # Устанавливаем права 664 (rw-rw-r--) для файлов внутри
        find "$dir" -type f -exec chmod 664 {} \; 2>/dev/null || true
        # Устанавливаем права 775 для поддиректорий
        find "$dir" -type d -exec chmod 775 {} \; 2>/dev/null || true
    fi
done

# Пытаемся установить владельца (работает только если запущены от root)
if [ "$(id -u)" = "0" ]; then
    echo "🔐 Setting ownership to www-data..."
    for dir in "${WRITABLE_DIRS[@]}"; do
        if [ -d "$dir" ]; then
            chown -R www-data:www-data "$dir" 2>/dev/null || true
        fi
    done
fi

# Проверка конфигурации Laravel
echo "🔍 Checking Laravel configuration..."
php artisan about --only=environment,cache,drivers 2>/dev/null || {
    echo "⚠️  Warning: Could not retrieve application info"
}

# Вывод сводной информации
echo ""
echo "═══════════════════════════════════════"
echo "📊 APPLICATION INFO"
echo "═══════════════════════════════════════"
echo "Version:      $APP_VERSION"
echo "Environment:  $APP_ENV"
echo "Debug:        $APP_DEBUG"
echo "URL:          $APP_URL"
echo "Database:     ${DB_CONNECTION}@${DB_HOST}:${DB_PORT:-3306}/${DB_DATABASE}"
echo "Cache:        ${CACHE_DRIVER:-file}"
echo "Queue:        ${QUEUE_CONNECTION:-sync}"
echo "Session:      ${SESSION_DRIVER:-file}"
echo "Broadcast:    ${BROADCAST_DRIVER:-log}"
echo "Redis:        ${REDIS_HOST:-not configured}:${REDIS_PORT:-6379}"
echo "═══════════════════════════════════════"
echo ""

# Проверка критичных директорий на права записи
echo "🔐 Checking write permissions..."
CRITICAL_DIRS=("storage/logs" "storage/framework/cache" "storage/framework/sessions" "bootstrap/cache")
PERMISSION_ERRORS=0

for dir in "${CRITICAL_DIRS[@]}"; do
    if [ ! -w "$dir" ]; then
        echo "⚠️  Warning: $dir is not writable!"
        PERMISSION_ERRORS=$((PERMISSION_ERRORS + 1))
    fi
done

if [ $PERMISSION_ERRORS -eq 0 ]; then
    echo "✅ All critical directories are writable"
else
    echo "⚠️  Found $PERMISSION_ERRORS permission issues"
fi

echo ""
echo "✨ Initialization complete! Starting application..."
echo ""

# Graceful shutdown handler
cleanup() {
    echo ""
    echo "🛑 Received shutdown signal, gracefully stopping..."
    # Добавьте здесь команды для graceful shutdown если нужно
    exit 0
}

trap cleanup SIGTERM SIGINT

# Запуск команды из CMD
# PHP-FPM должен запускаться от root (он сам переключит воркеры на www-data)
# Для других команд используем gosu
if [ "$(id -u)" = "0" ] && [ "$1" != "php-fpm" ] && command -v gosu >/dev/null 2>&1; then
    exec gosu www-data "$@"
else
    exec "$@"
fi
