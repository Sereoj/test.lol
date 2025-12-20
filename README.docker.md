# Запуск Laravel-проекта в Docker

## Предварительные требования
- Установленный Docker
- Установленный Docker Compose

## Настройка окружения

1. Скопируйте `.env.example` в `.env` и настройте параметры подключения к удаленной базе данных:
```bash
cp .env.example .env
```

2. В файле `.env` укажите параметры подключения к вашей удаленной БД:
```
DB_CONNECTION=mysql
DB_HOST=ваш_удаленный_хост
DB_PORT=3306
DB_DATABASE=имя_базы
DB_USERNAME=пользователь
DB_PASSWORD=пароль
```

## Запуск проекта

1. Соберите Docker-образы:
```bash
docker-compose build
```

2. Запустите контейнеры:
```bash
docker-compose up -d
```

3. Выполните миграции и другие необходимые команды:
```bash
docker-compose exec app php artisan migrate
docker-compose exec app php artisan key:generate
docker-compose exec app php artisan passport:install
docker-compose exec app php artisan storage:link
```

## Доступ к проекту

После запуска, проект будет доступен по адресу: http://localhost

## Остановка проекта

Для остановки проекта выполните:
```bash
docker-compose down
```

## Важные замечания

- В Dockerfile уже настроен путь к ffmpeg для Windows и Linux
- Все данные базы хранятся на удаленном сервере
- Для работы с файлами в контейнере используйте команду `docker-compose exec app bash`
