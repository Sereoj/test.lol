#!/bin/bash
# =============================================================================
# Скрипт для безопасного деплоя .env файла на production сервер
# =============================================================================

set -e  # Остановка при ошибке

# Цвета для вывода
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# =============================================================================
# КОНФИГУРАЦИЯ - ИЗМЕНИТЕ ПОД ВАШИ ДАННЫЕ
# =============================================================================

# SSH параметры
SERVER_USER="your_username"           # Имя пользователя на сервере
SERVER_HOST="your_server_ip"          # IP адрес или домен сервера
SERVER_PORT="22"                       # SSH порт (по умолчанию 22)
SSH_KEY_PATH="$HOME/.ssh/id_rsa"      # Путь к SSH ключу

# Пути на сервере
REMOTE_PROJECT_PATH="/var/www/wallone"  # Путь к проекту на сервере
REMOTE_ENV_PATH="$REMOTE_PROJECT_PATH/.env"
REMOTE_BACKUP_PATH="$REMOTE_PROJECT_PATH/.env.backup"

# Локальный путь к .env
LOCAL_ENV_PATH=".env"

# Docker Compose команды
DOCKER_COMPOSE_FILE="docker-compose.prod.yml"

# =============================================================================
# ФУНКЦИИ
# =============================================================================

# Вывод сообщений
log_info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

log_success() {
    echo -e "${GREEN}✓${NC} $1"
}

log_warning() {
    echo -e "${YELLOW}⚠${NC} $1"
}

log_error() {
    echo -e "${RED}✗${NC} $1"
}

# Проверка наличия локального .env
check_local_env() {
    log_info "Проверка локального .env файла..."

    if [ ! -f "$LOCAL_ENV_PATH" ]; then
        log_error "Файл $LOCAL_ENV_PATH не найден!"
        exit 1
    fi

    # Проверка наличия критичных переменных
    local required_vars=("APP_KEY" "DB_HOST" "DB_DATABASE" "DB_USERNAME" "DB_PASSWORD")
    local missing_vars=()

    for var in "${required_vars[@]}"; do
        if ! grep -q "^${var}=" "$LOCAL_ENV_PATH"; then
            missing_vars+=("$var")
        fi
    done

    if [ ${#missing_vars[@]} -ne 0 ]; then
        log_error "Отсутствуют критичные переменные в .env:"
        printf '  - %s\n' "${missing_vars[@]}"
        exit 1
    fi

    log_success "Локальный .env файл корректен"
}

# Проверка SSH подключения
check_ssh_connection() {
    log_info "Проверка SSH подключения к серверу..."

    if ! ssh -i "$SSH_KEY_PATH" -p "$SERVER_PORT" -o ConnectTimeout=5 "$SERVER_USER@$SERVER_HOST" "echo 'OK'" > /dev/null 2>&1; then
        log_error "Не удалось подключиться к серверу!"
        log_info "Проверьте параметры SSH:"
        echo "  User: $SERVER_USER"
        echo "  Host: $SERVER_HOST"
        echo "  Port: $SERVER_PORT"
        echo "  Key:  $SSH_KEY_PATH"
        exit 1
    fi

    log_success "SSH подключение успешно"
}

# Создание бэкапа старого .env на сервере
backup_remote_env() {
    log_info "Создание бэкапа текущего .env на сервере..."

    ssh -i "$SSH_KEY_PATH" -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" << EOF
        if [ -f "$REMOTE_ENV_PATH" ]; then
            cp "$REMOTE_ENV_PATH" "$REMOTE_BACKUP_PATH"
            echo "Бэкап создан: $REMOTE_BACKUP_PATH"
        else
            echo "Текущий .env не найден, бэкап не создан"
        fi
EOF

    log_success "Бэкап создан"
}

# Загрузка .env на сервер
upload_env() {
    log_info "Загрузка .env на сервер..."

    # Копирование файла через SCP
    scp -i "$SSH_KEY_PATH" -P "$SERVER_PORT" "$LOCAL_ENV_PATH" "$SERVER_USER@$SERVER_HOST:$REMOTE_ENV_PATH"

    if [ $? -eq 0 ]; then
        log_success "Файл .env успешно загружен"
    else
        log_error "Ошибка при загрузке .env"
        exit 1
    fi
}

# Установка прав доступа
set_permissions() {
    log_info "Установка прав доступа..."

    ssh -i "$SSH_KEY_PATH" -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" << EOF
        chmod 600 "$REMOTE_ENV_PATH"
        chown www-data:www-data "$REMOTE_ENV_PATH" 2>/dev/null || true
EOF

    log_success "Права доступа установлены (600)"
}

# Проверка .env на сервере
verify_remote_env() {
    log_info "Проверка .env на сервере..."

    ssh -i "$SSH_KEY_PATH" -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" << EOF
        if [ -f "$REMOTE_ENV_PATH" ]; then
            echo "✓ Файл существует"
            echo "Размер: \$(stat -f%z "$REMOTE_ENV_PATH" 2>/dev/null || stat -c%s "$REMOTE_ENV_PATH") байт"
            echo "Права: \$(stat -f%A "$REMOTE_ENV_PATH" 2>/dev/null || stat -c%a "$REMOTE_ENV_PATH")"
        else
            echo "✗ Файл не найден!"
            exit 1
        fi
EOF

    log_success "Файл .env успешно загружен и проверен"
}

# Перезапуск Docker контейнеров
restart_containers() {
    log_warning "Требуется перезапуск Docker контейнеров для применения изменений"

    read -p "Перезапустить контейнеры сейчас? (y/n): " -n 1 -r
    echo

    if [[ $REPLY =~ ^[Yy]$ ]]; then
        log_info "Перезапуск контейнеров..."

        ssh -i "$SSH_KEY_PATH" -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" << EOF
            cd "$REMOTE_PROJECT_PATH"
            docker-compose -f $DOCKER_COMPOSE_FILE down
            docker-compose -f $DOCKER_COMPOSE_FILE up -d
            echo "Контейнеры перезапущены"
EOF

        log_success "Контейнеры успешно перезапущены"

        # Показать логи
        log_info "Показать логи контейнеров? (y/n)"
        read -p "> " -n 1 -r
        echo

        if [[ $REPLY =~ ^[Yy]$ ]]; then
            ssh -i "$SSH_KEY_PATH" -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" << EOF
                cd "$REMOTE_PROJECT_PATH"
                docker-compose -f $DOCKER_COMPOSE_FILE logs --tail=50
EOF
        fi
    else
        log_warning "Не забудьте перезапустить контейнеры вручную:"
        echo "  ssh $SERVER_USER@$SERVER_HOST"
        echo "  cd $REMOTE_PROJECT_PATH"
        echo "  docker-compose -f $DOCKER_COMPOSE_FILE down"
        echo "  docker-compose -f $DOCKER_COMPOSE_FILE up -d"
    fi
}

# Откат к предыдущей версии .env
rollback() {
    log_warning "Откат к предыдущей версии .env..."

    ssh -i "$SSH_KEY_PATH" -p "$SERVER_PORT" "$SERVER_USER@$SERVER_HOST" << EOF
        if [ -f "$REMOTE_BACKUP_PATH" ]; then
            cp "$REMOTE_BACKUP_PATH" "$REMOTE_ENV_PATH"
            echo "✓ Откат выполнен"
        else
            echo "✗ Файл бэкапа не найден!"
            exit 1
        fi
EOF

    log_success "Откат выполнен успешно"
    restart_containers
}

# Показать справку
show_help() {
    cat << EOF
Использование: $0 [ОПЦИИ]

Скрипт для безопасного деплоя .env файла на production сервер

ОПЦИИ:
    --help, -h          Показать эту справку
    --rollback          Откатить к предыдущей версии .env
    --check-only        Только проверить подключение, без загрузки
    --no-restart        Не перезапускать контейнеры после загрузки

КОНФИГУРАЦИЯ:
    Отредактируйте переменные в начале скрипта:
    - SERVER_USER       Имя пользователя на сервере
    - SERVER_HOST       IP адрес или домен сервера
    - SERVER_PORT       SSH порт
    - SSH_KEY_PATH      Путь к SSH ключу

ПРИМЕРЫ:
    $0                  # Обычный деплой
    $0 --check-only     # Проверка без загрузки
    $0 --rollback       # Откат к предыдущей версии

EOF
}

# =============================================================================
# ОСНОВНАЯ ЛОГИКА
# =============================================================================

main() {
    echo "═══════════════════════════════════════════════════"
    echo "  🚀 Деплой .env на production сервер"
    echo "═══════════════════════════════════════════════════"
    echo ""

    # Обработка аргументов
    CHECK_ONLY=false
    NO_RESTART=false
    ROLLBACK=false

    for arg in "$@"; do
        case $arg in
            --help|-h)
                show_help
                exit 0
                ;;
            --check-only)
                CHECK_ONLY=true
                ;;
            --no-restart)
                NO_RESTART=true
                ;;
            --rollback)
                ROLLBACK=true
                ;;
            *)
                log_error "Неизвестная опция: $arg"
                show_help
                exit 1
                ;;
        esac
    done

    # Откат
    if [ "$ROLLBACK" = true ]; then
        check_ssh_connection
        rollback
        exit 0
    fi

    # Основной процесс
    check_local_env
    check_ssh_connection

    if [ "$CHECK_ONLY" = true ]; then
        log_success "Проверка завершена успешно"
        exit 0
    fi

    # Подтверждение
    log_warning "Вы собираетесь загрузить .env на production сервер:"
    echo "  Сервер: $SERVER_USER@$SERVER_HOST"
    echo "  Путь:   $REMOTE_ENV_PATH"
    echo ""
    read -p "Продолжить? (yes/no): " -r
    echo

    if [[ ! $REPLY =~ ^[Yy][Ee][Ss]$ ]]; then
        log_info "Отменено пользователем"
        exit 0
    fi

    # Деплой
    backup_remote_env
    upload_env
    set_permissions
    verify_remote_env

    if [ "$NO_RESTART" = false ]; then
        restart_containers
    else
        log_warning "Контейнеры не перезапущены (флаг --no-restart)"
    fi

    echo ""
    echo "═══════════════════════════════════════════════════"
    log_success "Деплой .env завершен успешно!"
    echo "═══════════════════════════════════════════════════"
}

# Запуск
main "$@"
