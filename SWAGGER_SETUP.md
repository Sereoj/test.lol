# Swagger/OpenAPI Documentation Setup

## Установка завершена! ✅

Swagger/OpenAPI документация была успешно настроена для вашего Laravel приложения.

## Что было сделано:

### 1. Установлен пакет darkaonline/l5-swagger
- Добавлен в `composer.json`
- Для установки выполните: `composer install`

### 2. Конфигурация
- Создан файл конфигурации `config/l5-swagger.php`
- Настроены пути для генерации документации
- Добавлены папки для сканирования аннотаций

### 3. OpenAPI аннотации добавлены в модели:
- `app/Models/Users/User.php` - модель пользователя
- `app/Models/Media/Media.php` - модель медиа-файлов
- `app/Models/Media/Avatar.php` - модель аватаров
- `app/Models/Notifications/Notification.php` - модель уведомлений

### 4. OpenAPI аннотации добавлены в контроллеры:
- `app/Http/Controllers/Controller.php` - базовый контроллер с общей информацией API
- `app/Http/Controllers/Users/UserController.php` - методы: index, show
- `app/Http/Controllers/Media/AvatarController.php` - методы: uploadAvatar, getUserAvatars, deleteAvatar
- `app/Http/Controllers/NotificationController.php` - методы: index, unread
- `app/Http/Controllers/Posts/MediaController.php` - метод: store

### 5. Создан OpenApiController
- `app/Http/Controllers/OpenApiController.php`
- Методы для экспорта OpenAPI спецификации в JSON и YAML

### 6. Добавлены маршруты
В `routes/api/v1/guest.php`:
- `/api/v1/openapi.json` - JSON формат спецификации
- `/api/v1/openapi.yaml` - YAML формат спецификации

### 7. Создана команда для генерации документации
- `app/Console/Commands/GenerateSwaggerDocs.php`

### 8. Создана система автоматической генерации аннотаций ⭐ NEW!
- **GenerateOpenApiAnnotations** - автоматически генерирует аннотации для контроллеров на основе маршрутов
- **GenerateRequestSchemas** - генерирует OpenAPI схемы из Laravel Request классов
- **GenerateResourceSchemas** - генерирует OpenAPI схемы из Laravel Resource классов
- **GenerateAllOpenApiDocs** - запускает все генераторы одной командой
- **AnnotationGenerator** - сервис для генерации аннотаций

## Как использовать:

### 1. Установите зависимости:
```bash
composer install
```

### 2. Опубликуйте конфигурацию L5-Swagger:
```bash
php artisan vendor:publish --provider="L5Swagger\L5SwaggerServiceProvider"
```

### 3. Сгенерируйте документацию:

#### Автоматическая генерация (РЕКОМЕНДУЕТСЯ) ⭐
```bash
# Генерирует ВСЁ автоматически:
# - Аннотации для всех контроллеров
# - Схемы для всех Request классов
# - Схемы для всех Resource классов
# - Финальную документацию
php artisan openapi:generate-all

# С перезаписью существующих аннотаций:
php artisan openapi:generate-all --force
```

#### Частичная генерация (по необходимости)
```bash
# Только аннотации контроллеров (на основе маршрутов):
php artisan openapi:generate-annotations

# Только для конкретного контроллера:
php artisan openapi:generate-annotations --controller=UserController

# С перезаписью:
php artisan openapi:generate-annotations --force

# Только схемы Request классов:
php artisan openapi:generate-request-schemas

# Только схемы Resource классов:
php artisan openapi:generate-resource-schemas

# Только финальная документация (без автогенерации):
php artisan l5-swagger:generate
# или
php artisan swagger:generate
```

### 4. Доступ к документации:

#### Swagger UI (интерактивная документация):
```
http://your-domain/api/documentation
```

#### JSON спецификация:
```
http://your-domain/api/v1/openapi.json
```

#### YAML спецификация:
```
http://your-domain/api/v1/openapi.yaml
```

## Автоматическая генерация

### Рекомендуемый workflow:

1. **После изменения контроллеров/маршрутов:**
   ```bash
   php artisan openapi:generate-all
   ```

2. **После изменения Request классов:**
   ```bash
   php artisan openapi:generate-request-schemas
   php artisan l5-swagger:generate
   ```

3. **После изменения Resource классов:**
   ```bash
   php artisan openapi:generate-resource-schemas
   php artisan l5-swagger:generate
   ```

### Интеграция в процесс деплоя:

Добавьте в ваш deploy скрипт:
```bash
php artisan openapi:generate-all
```

### Git hooks (pre-commit):

Создайте файл `.git/hooks/pre-commit`:
```bash
#!/bin/sh
php artisan openapi:generate-all
git add storage/api-docs/
git add app/Http/Controllers/
git add app/Http/Requests/
git add app/Http/Resources/
```

### Composer scripts:

Уже настроено в `composer.json`:
```bash
# Автогенерация после composer update:
composer update

# Или вручную:
composer swagger
```

## Как работает автогенерация ⭐

### 1. Генерация аннотаций контроллеров
Команда `openapi:generate-annotations` анализирует все маршруты вашего приложения и автоматически:
- Определяет HTTP метод (GET, POST, PUT, DELETE)
- Извлекает параметры из пути (например, `{id}`, `{userId}`)
- Определяет нужна ли авторизация (Bearer token)
- Генерирует Request Body для POST/PUT/PATCH методов
- Добавляет pagination параметры для index методов
- Создает соответствующие Response схемы
- Автоматически назначает теги на основе имени контроллера

**Пример:** Для маршрута `GET /api/v1/users/{id}` в `UserController::show()` автоматически создаст:
```php
/**
 * @OA\Get(
 *     path="/api/v1/users/{id}",
 *     tags={"Users"},
 *     summary="Get user by ID",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(name="id", in="path", required=true, @OA\Schema(type="integer")),
 *     @OA\Response(response=200, ...)
 * )
 */
```

### 2. Генерация Request схем
Команда `openapi:generate-request-schemas` анализирует правила валидации в Request классах:
- Извлекает поля из метода `rules()`
- Определяет тип данных (string, integer, boolean, email и т.д.)
- Определяет обязательные поля (required)
- Генерирует примеры значений
- Добавляет validation constraints в описание

**Пример:** Для класса с правилами:
```php
public function rules() {
    return [
        'email' => 'required|email',
        'age' => 'nullable|integer|min:18',
    ];
}
```
Создаст схему с полями email (required, format: email) и age (nullable, integer, min: 18).

### 3. Генерация Resource схем
Команда `openapi:generate-resource-schemas` анализирует метод `toArray()` в Resource классах:
- Парсит возвращаемый массив
- Определяет типы полей
- Генерирует структуру ответа API

## Ручное добавление аннотаций (опционально)

Если вы хотите добавить аннотации вручную или кастомизировать автогенерированные:

### Пример аннотации для GET метода:

```php
/**
 * @OA\Get(
 *     path="/api/v1/endpoint",
 *     tags={"TagName"},
 *     summary="Short description",
 *     description="Detailed description",
 *     security={{"bearerAuth":{}}},
 *     @OA\Parameter(
 *         name="id",
 *         in="path",
 *         required=true,
 *         @OA\Schema(type="integer")
 *     ),
 *     @OA\Response(
 *         response=200,
 *         description="Success",
 *         @OA\JsonContent(ref="#/components/schemas/ModelName")
 *     )
 * )
 */
public function method() { }
```

Пример аннотации для POST метода с телом запроса:

```php
/**
 * @OA\Post(
 *     path="/api/v1/endpoint",
 *     tags={"TagName"},
 *     summary="Create resource",
 *     security={{"bearerAuth":{}}},
 *     @OA\RequestBody(
 *         required=true,
 *         @OA\JsonContent(
 *             @OA\Property(property="name", type="string", example="Example"),
 *             @OA\Property(property="email", type="string", format="email")
 *         )
 *     ),
 *     @OA\Response(response=201, description="Created")
 * )
 */
public function store() { }
```

## Добавление аннотаций в модели

```php
/**
 * @OA\Schema(
 *     schema="ModelName",
 *     type="object",
 *     title="Model Title",
 *     @OA\Property(property="id", type="integer"),
 *     @OA\Property(property="name", type="string"),
 *     @OA\Property(property="created_at", type="string", format="date-time")
 * )
 */
class ModelName extends Model { }
```

## Полезные ссылки

- [L5-Swagger Documentation](https://github.com/DarkaOnLine/L5-Swagger)
- [OpenAPI Specification](https://swagger.io/specification/)
- [Swagger UI](https://swagger.io/tools/swagger-ui/)

## Troubleshooting

### Документация не генерируется:
1. Проверьте права доступа к папке `storage/api-docs/`
2. Убедитесь, что аннотации корректны
3. Проверьте логи Laravel: `storage/logs/laravel.log`

### Swagger UI не отображается:
1. Очистите кеш: `php artisan cache:clear`
2. Очистите кеш конфигурации: `php artisan config:clear`
3. Проверьте настройки в `config/l5-swagger.php`

### Ошибки в аннотациях:
1. Убедитесь, что используете правильное пространство имен: `use OpenApi\Attributes as OA;`
2. Проверьте синтаксис аннотаций
3. Используйте валидатор OpenAPI: https://editor.swagger.io/
