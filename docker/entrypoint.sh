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

# Функция для подстановки переменных окружения в php.ini
configure_php_ini() {
    local php_ini="/usr/local/etc/php/conf.d/99-custom.ini"

    if [ -f "$php_ini" ]; then
        echo "🔧 Configuring PHP settings..."

        # Заменяем переменные на их значения
        sed -i "s|\${PHP_MEMORY_LIMIT}|${PHP_MEMORY_LIMIT:-512M}|g" "$php_ini"
        sed -i "s|\${PHP_MAX_UPLOAD}|${PHP_MAX_UPLOAD:-50M}|g" "$php_ini"
        sed -i "s|\${PHP_MAX_FILE_UPLOAD}|${PHP_MAX_FILE_UPLOAD:-200}|g" "$php_ini"

        echo "✅ PHP configuration updated"
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

# Функция проверки подключения к базе данных
check_database_connection() {
    # Проверяем наличие необходимых переменных
    if [ -z "$DB_HOST" ]; then
        echo "⚠️  DB_HOST not set, skipping database check"
        return 0
    fi

    if [ -z "$DB_DATABASE" ] || [ -z "$DB_USERNAME" ]; then
        echo "❌ ERROR: DB_DATABASE or DB_USERNAME not set!"
        if [ "$APP_ENV" = "production" ]; then
            exit 1
        fi
        return 1
    fi

    echo "⏳ Checking database connection..."
    echo "   Host: ${DB_HOST}:${DB_PORT:-3306}"
    echo "   Database: ${DB_DATABASE}"
    echo "   Username: ${DB_USERNAME}"

    local MAX_TRIES=30
    local COUNT=0
    local CONNECTION_ERROR=""

    # Проверяем подключение к серверу БД
    while [ $COUNT -lt $MAX_TRIES ]; do
        CONNECTION_ERROR=$(php -r "
            try {
                \$pdo = new PDO(
                    'mysql:host=${DB_HOST};port=${DB_PORT:-3306};dbname=${DB_DATABASE}',
                    '${DB_USERNAME}',
                    '${DB_PASSWORD}',
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_TIMEOUT => 2
                    ]
                );
                \$pdo->query('SELECT 1');
                echo 'SUCCESS';
                exit(0);
            } catch (PDOException \$e) {
                echo \$e->getMessage();
                exit(1);
            }
        " 2>&1)

        if [ $? -eq 0 ] && [ "$CONNECTION_ERROR" = "SUCCESS" ]; then
            echo "✅ Database connection successful!"
            return 0
        fi

        COUNT=$((COUNT + 1))
        echo "   Attempt $COUNT/$MAX_TRIES failed: $CONNECTION_ERROR"

        if [ $COUNT -lt $MAX_TRIES ]; then
            sleep 2
        fi
    done

    # Подключение не удалось после всех попыток
    echo "❌ ERROR: Could not connect to database after $MAX_TRIES attempts"
    echo "   Last error: $CONNECTION_ERROR"

    if [ "$APP_ENV" = "production" ]; then
        echo "🛑 Stopping container due to database connection failure in production"
        exit 1
    else
        echo "⚠️  Warning: Continuing in non-production environment..."
        return 1
    fi
}

# Функция проверки Redis
check_redis() {
    if [ -z "$REDIS_HOST" ]; then
        echo "⚠️  REDIS_HOST not set, skipping Redis check"
        return 0
    fi

    echo "⏳ Checking Redis connection..."
    echo "   Host: ${REDIS_HOST}:${REDIS_PORT:-6379}"

    local MAX_TRIES=30
    local COUNT=0

    while [ $COUNT -lt $MAX_TRIES ]; do
        if php -r "
            \$redis = new Redis();
            try {
                \$redis->connect('${REDIS_HOST}', ${REDIS_PORT:-6379}, 2);
                echo 'SUCCESS';
                exit(0);
            } catch (Exception \$e) {
                echo \$e->getMessage();
                exit(1);
            }
        " 2>&1 | grep -q "SUCCESS"; then
            echo "✅ Redis connection successful!"
            return 0
        fi

        COUNT=$((COUNT + 1))
        echo "   Attempt $COUNT/$MAX_TRIES failed"

        if [ $COUNT -lt $MAX_TRIES ]; then
            sleep 2
        fi
    done

    echo "⚠️  Warning: Could not connect to Redis after $MAX_TRIES attempts"

    if [ "$APP_ENV" = "production" ]; then
        echo "⚠️  Redis is not available, but continuing (non-critical service)"
    fi

    return 1
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

# Конфигурация PHP с подстановкой переменных
configure_php_ini

# Проверка .env файла
check_env_file

# Проверка Composer зависимостей
check_composer_dependencies

# Генерация ключей если нужно
generate_app_key
generate_reverb_keys

# Проверка подключения к базе данных
check_database_connection

# Проверка Redis
check_redis

# Функция проверки S3
check_s3() {
    if [ "$FILESYSTEM_DISK" != "s3" ]; then
        echo "ℹ️  S3 is not configured as default filesystem, skipping check"
        return 0
    fi

    if [ -z "$AWS_ENDPOINT" ] || [ -z "$AWS_BUCKET" ]; then
        echo "⚠️  S3 environment variables not set, skipping check"
        return 0
    fi

    echo "⏳ Checking S3 connection..."
    echo "   Endpoint: ${AWS_ENDPOINT}"
    echo "   Bucket: ${AWS_BUCKET}"

    # Используем artisan команду для проверки
    if php artisan storage:check-s3 2>&1 | grep -q "working correctly"; then
        echo "✅ S3 connection successful!"
        return 0
    else
        echo "⚠️  Warning: S3 connection check failed"
        if [ "$APP_ENV" = "production" ]; then
            echo "⚠️  S3 is not available, but continuing (will use fallback if configured)"
        fi
        return 1
    fi
}

# Проверка S3
check_s3

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

    # Инициализация Laravel Passport (ключи + клиенты)
    echo "🔐 Setting up Laravel Passport..."
    php artisan passport:setup || {
        echo "⚠️  Warning: Passport setup failed, retrying with force..."
        php artisan passport:setup --force || {
            echo "❌ ERROR: Passport setup failed"
            if [ "$APP_ENV" = "production" ]; then
                exit 1
            fi
        }
    }
    echo "✅ Passport setup complete"

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

    # Явно устанавливаем права на OAuth ключи
    if [ -f "storage/oauth-private.key" ]; then
        echo "🔑 Setting permissions for OAuth keys..."
        chown www-data:www-data storage/oauth-private.key storage/oauth-public.key 2>/dev/null || true
        chmod 600 storage/oauth-private.key 2>/dev/null || true
        chmod 644 storage/oauth-public.key 2>/dev/null || true
        echo "✅ OAuth keys permissions updated"
    fi
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
