# Docker - Инструкция по командам

## Структура проекта

```
├── docker/
│   ├── entrypoint.sh          # Entrypoint скрипт для инициализации
│   └── php/
│       ├── php.ini            # PHP конфигурация для production
│       └── opcache.ini        # OPcache конфигурация для production
├── Dockerfile                 # Универсальный Dockerfile (dev + prod)
├── docker-compose.dev.yml     # Docker Compose для разработки
├── docker-compose.prod.yml    # Docker Compose для production
└── Caddyfile                  # Конфигурация Caddy (только prod)
```

## Окружения

### Development (разработка)
- PHP-FPM на порту 9000
- Redis для кеширования
- Reverb для WebSocket (порт 8080)
- Queue worker для обработки очередей
- Без веб-сервера (используйте локальный сервер или Caddy отдельно)
- Внешняя база данных MySQL

### Production (продакшн)
- PHP-FPM + Caddy (веб-сервер с автоматическим SSL)
- Redis для кеширования
- Reverb для WebSocket
- Queue worker для обработки очередей
- Scheduler для cron задач
- Внешняя база данных MySQL

---

## Development - Команды

### 1. Первый запуск (сборка образов)
```bash
docker compose -f docker-compose.dev.yml build
```

### 2. Запуск всех сервисов
```bash
docker compose -f docker-compose.dev.yml up -d
```

### 3. Остановка всех сервисов
```bash
docker compose -f docker-compose.dev.yml down
```

### 4. Просмотр логов
```bash
# Все сервисы
docker compose -f docker-compose.dev.yml logs -f

# Конкретный сервис
docker compose -f docker-compose.dev.yml logs -f app
docker compose -f docker-compose.dev.yml logs -f reverb
docker compose -f docker-compose.dev.yml logs -f queue
```

### 5. Выполнение команд в контейнере
```bash
# Artisan команды
docker compose -f docker-compose.dev.yml exec app php artisan migrate
docker compose -f docker-compose.dev.yml exec app php artisan db:seed
docker compose -f docker-compose.dev.yml exec app php artisan cache:clear

# Composer
docker compose -f docker-compose.dev.yml exec app composer install
docker compose -f docker-compose.dev.yml exec app composer update

# Bash
docker compose -f docker-compose.dev.yml exec app bash
```

### 6. Перезапуск сервиса
```bash
docker compose -f docker-compose.dev.yml restart app
docker compose -f docker-compose.dev.yml restart reverb
docker compose -f docker-compose.dev.yml restart queue
```

### 7. Пересборка образа после изменений
```bash
# Пересобрать и перезапустить
docker compose -f docker-compose.dev.yml up -d --build

# Пересобрать без кеша
docker compose -f docker-compose.dev.yml build --no-cache
```

### 8. Очистка
```bash
# Остановить и удалить контейнеры + volumes
docker compose -f docker-compose.dev.yml down -v

# Удалить неиспользуемые образы
docker image prune -a
```

---

## Production - Команды

### 1. Первый запуск (сборка образов)
```bash
docker compose -f docker-compose.prod.yml build
```

### 2. Запуск всех сервисов
```bash
docker compose -f docker-compose.prod.yml up -d
```

### 3. Остановка всех сервисов
```bash
docker compose -f docker-compose.prod.yml down
```

### 4. Просмотр логов
```bash
# Все сервисы
docker compose -f docker-compose.prod.yml logs -f

# Конкретный сервис
docker compose -f docker-compose.prod.yml logs -f app
docker compose -f docker-compose.prod.yml logs -f caddy
docker compose -f docker-compose.prod.yml logs -f reverb
docker compose -f docker-compose.prod.yml logs -f queue
docker compose -f docker-compose.prod.yml logs -f scheduler
```

### 5. Выполнение команд в контейнере
```bash
# Artisan команды (от пользователя www-data)
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache

# Bash (будет под пользователем www-data)
docker compose -f docker-compose.prod.yml exec app bash
```

### 6. Инициализация базы данных (первый запуск)
```bash
# Запустить миграции при первом запуске
docker compose -f docker-compose.prod.yml exec app sh -c "INIT_DB=true php artisan migrate --force"
```

### 7. Обновление приложения
```bash
# 1. Остановить сервисы
docker compose -f docker-compose.prod.yml down

# 2. Получить изменения из git
git pull

# 3. Пересобрать образ
docker compose -f docker-compose.prod.yml build --no-cache

# 4. Запустить сервисы
docker compose -f docker-compose.prod.yml up -d

# 5. Выполнить миграции если нужно
docker compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

### 8. Перезапуск сервиса
```bash
docker compose -f docker-compose.prod.yml restart app
docker compose -f docker-compose.prod.yml restart caddy
docker compose -f docker-compose.prod.yml restart reverb
docker compose -f docker-compose.prod.yml restart queue
docker compose -f docker-compose.prod.yml restart scheduler
```

### 9. Очистка кешей Laravel
```bash
docker compose -f docker-compose.prod.yml exec app php artisan config:clear
docker compose -f docker-compose.prod.yml exec app php artisan route:clear
docker compose -f docker-compose.prod.yml exec app php artisan view:clear
docker compose -f docker-compose.prod.yml exec app php artisan cache:clear
```

### 10. Пересборка кешей для оптимизации
```bash
docker compose -f docker-compose.prod.yml exec app php artisan config:cache
docker compose -f docker-compose.prod.yml exec app php artisan route:cache
docker compose -f docker-compose.prod.yml exec app php artisan view:cache
docker compose -f docker-compose.prod.yml exec app php artisan event:cache
```

---

## Полезные команды Docker

### Проверка статуса контейнеров
```bash
docker ps
docker ps -a  # включая остановленные
```

### Проверка использования ресурсов
```bash
docker stats
```

### Проверка volumes
```bash
docker volume ls
docker volume inspect <volume_name>
```

### Проверка networks
```bash
docker network ls
docker network inspect <network_name>
```

### Очистка системы Docker
```bash
# Удалить все остановленные контейнеры
docker container prune

# Удалить неиспользуемые образы
docker image prune -a

# Удалить неиспользуемые volumes
docker volume prune

# Удалить всё неиспользуемое
docker system prune -a --volumes
```

---

## Переменные окружения

Убедитесь что у вас настроен файл `.env` с правильными параметрами:

### Для Development
```env
APP_ENV=local
APP_DEBUG=true
DB_HOST=<внешний_хост_mysql>
DB_PORT=3306
DB_DATABASE=<имя_бд>
DB_USERNAME=<пользователь>
DB_PASSWORD=<пароль>
REDIS_HOST=redis
```

### Для Production
```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=<сгенерированный_ключ>
DB_HOST=<внешний_хост_mysql>
DB_PORT=3306
DB_DATABASE=<имя_бд>
DB_USERNAME=<пользователь>
DB_PASSWORD=<пароль>
REDIS_HOST=redis
```

---

## Проблемы и решения

### Проблема: Ошибки прав доступа (Permission Denied)

**Причина:** Пользователь www-data не имеет прав на запись в директории `storage/` и `bootstrap/cache/`.

**Автоматическое решение:** Entrypoint скрипт (`docker/entrypoint.sh`) автоматически устанавливает правильные права при запуске контейнера.

**Ручное решение (если автоматическое не сработало):**
```bash
# Development
docker compose -f docker-compose.dev.yml exec app chmod -R 775 storage bootstrap/cache

# Production (контейнер работает от www-data, нужны права root)
docker compose -f docker-compose.prod.yml exec -u root app chown -R www-data:www-data storage bootstrap/cache
docker compose -f docker-compose.prod.yml exec -u root app chmod -R 775 storage bootstrap/cache
```

**На хост-системе (если используются volume mounts):**
```bash
# На хосте, где запущен Docker
sudo chown -R 82:82 storage bootstrap/cache  # UID 82 = www-data в Alpine
sudo chmod -R 775 storage bootstrap/cache
```

### Проблема: Redis Memory Overcommit Warning

**Симптомы:** В логах Redis появляется предупреждение:
```
WARNING Memory overcommit must be enabled!
WARNING overcommit_memory is set to 0! Background save may fail under low memory condition.
```

**Причина:** Параметр ядра `vm.overcommit_memory` не настроен, что может привести к сбоям Redis при высокой нагрузке.

**Решение 1: Настройка на хост-системе (рекомендуется для production):**
```bash
# Linux
sudo sysctl vm.overcommit_memory=1
# Для постоянного применения после перезагрузки
echo "vm.overcommit_memory=1" | sudo tee -a /etc/sysctl.conf
sudo sysctl -p

# Windows (WSL2)
# Создайте или отредактируйте файл %UserProfile%\.wslconfig
[wsl2]
kernelCommandLine = sysctl.vm.overcommit_memory=1
# После этого перезапустите WSL: wsl --shutdown
```

**Решение 2: В docker-compose (уже настроено):**
Redis контейнер уже настроен с параметром `sysctls.net.core.somaxconn=1024`, что помогает снизить проблемы с сетевыми соединениями.

**Проверка:**
```bash
# Проверить настройку на хосте
sysctl vm.overcommit_memory

# Проверить логи Redis (не должно быть предупреждений)
docker compose -f docker-compose.prod.yml logs redis | grep -i warning
```

### Проблема: База данных недоступна
- Проверьте параметры подключения в `.env`
- Убедитесь что внешняя БД запущена и доступна
- Проверьте firewall правила

### Проблема: Не работает Caddy (SSL)
```bash
# Проверить логи Caddy
docker compose -f docker-compose.prod.yml logs -f caddy

# Убедиться что порты 80 и 443 открыты
# Убедиться что домен правильно настроен в DNS
```

### Проблема: Queue worker не обрабатывает задачи
```bash
# Перезапустить queue worker
docker compose -f docker-compose.prod.yml restart queue

# Проверить логи
docker compose -f docker-compose.prod.yml logs -f queue

# Проверить подключение к Redis
docker compose -f docker-compose.prod.yml exec redis redis-cli ping
```
