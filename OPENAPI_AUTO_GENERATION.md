# 🚀 OpenAPI Auto-Generation - Быстрый старт

## Что это?

Система автоматической генерации OpenAPI/Swagger документации для вашего Laravel API.

**Больше не нужно вручную писать аннотации!** 🎉

## Установка

```bash
# 1. Установите зависимости
composer install

# 2. Опубликуйте конфигурацию L5-Swagger
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

## Использование

### Одна команда для всего ⭐

```bash
php artisan openapi:generate-all
```

Эта команда автоматически:
- ✅ Анализирует все маршруты и создает аннотации для контроллеров
- ✅ Генерирует OpenAPI схемы из Request классов (validation rules)
- ✅ Генерирует OpenAPI схемы из Resource классов (API responses)
- ✅ Создает финальную документацию

### Просмотр документации

После генерации откройте:
- **Swagger UI:** http://your-domain/api/documentation
- **JSON:** http://your-domain/api/v1/openapi.json
- **YAML:** http://your-domain/api/v1/openapi.yaml

## Доступные команды

### Полная генерация
```bash
# Генерирует всё
php artisan openapi:generate-all

# С перезаписью существующих аннотаций
php artisan openapi:generate-all --force
```

### Частичная генерация

```bash
# Только аннотации контроллеров
php artisan openapi:generate-annotations

# Только для конкретного контроллера
php artisan openapi:generate-annotations --controller=UserController

# Только Request схемы
php artisan openapi:generate-request-schemas

# Только Resource схемы
php artisan openapi:generate-resource-schemas

# Только финальная документация (без автогенерации)
php artisan l5-swagger:generate
```

## Workflow

### При разработке

1. Создайте/измените контроллер, Request или Resource
2. Запустите:
   ```bash
   php artisan openapi:generate-all
   ```
3. Обновите страницу Swagger UI

### При деплое

Добавьте в ваш deploy скрипт:
```bash
php artisan openapi:generate-all
```

### Git Hooks (опционально)

Создайте `.git/hooks/pre-commit`:
```bash
#!/bin/sh
php artisan openapi:generate-all
git add storage/api-docs/ app/Http/
```

## Что генерируется автоматически?

### Для контроллеров
Анализирует маршруты и создает:
- HTTP методы (GET, POST, PUT, DELETE)
- Параметры пути (`{id}`, `{userId}`)
- Query параметры (page, per_page для index)
- Request Body (для POST/PUT/PATCH)
- Response схемы
- Security (Bearer auth)
- Теги

### Для Request классов
Анализирует `rules()` метод:
- Типы данных (string, integer, email, url и т.д.)
- Обязательные поля (required)
- Nullable поля
- Validation constraints (min, max)
- Примеры значений

### Для Resource классов
Анализирует `toArray()` метод:
- Структуру ответа
- Типы полей
- Вложенные объекты

## Примеры

### До (без автогенерации)
```php
// UserController.php
public function show($id)
{
    return $this->userService->getById($id);
}

// ❌ Нет документации!
```

### После (с автогенерацией)
```bash
php artisan openapi:generate-all
```

```php
// UserController.php - автоматически добавлено:
/**
 * @OA\Get(
 *     path="/api/v1/users/{id}",
 *     tags={"Users"},
 *     summary="Get user by ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, description="Success", @OA\JsonContent(ref="#/components/schemas/User")),
 *     @OA\Response(response=404, description="Not found")
 * )
 */
public function show($id)
{
    return $this->userService->getById($id);
}

// ✅ Полная документация создана автоматически!
```

## Кастомизация

Если нужно изменить автогенерированные аннотации:

1. Сгенерируйте базовую версию:
   ```bash
   php artisan openapi:generate-all
   ```

2. Отредактируйте вручную в контроллере

3. При следующей генерации используйте без `--force`:
   ```bash
   php artisan openapi:generate-all
   ```
   (существующие аннотации НЕ будут перезаписаны)

## Преимущества

✅ **Экономия времени** - не нужно писать аннотации вручную
✅ **Актуальность** - документация всегда соответствует коду
✅ **Консистентность** - единый стиль для всех endpoint'ов
✅ **Автоматизация** - интеграция в CI/CD
✅ **Гибкость** - можно кастомизировать автогенерированные аннотации

## Troubleshooting

### Аннотации не генерируются
```bash
# Проверьте маршруты
php artisan route:list

# Используйте --force для перезаписи
php artisan openapi:generate-all --force
```

### Схемы Request не создаются
Убедитесь что в Request классе есть метод `rules()`:
```php
public function rules(): array
{
    return [
        'name' => 'required|string',
    ];
}
```

### Документация не обновляется
```bash
# Очистите кеш
php artisan cache:clear
php artisan config:clear

# Регенерируйте
php artisan openapi:generate-all --force
```

## Дополнительная информация

Подробная документация: [SWAGGER_SETUP.md](SWAGGER_SETUP.md)

## Поддержка

- GitHub Issues: [ваш-репозиторий/issues]
- Документация L5-Swagger: https://github.com/DarkaOnLine/L5-Swagger
- OpenAPI Specification: https://swagger.io/specification/
