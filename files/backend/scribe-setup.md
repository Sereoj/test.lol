# Настройка и использование Scribe v5.0 для документирования API

## Установка и настройка Scribe

### 1. Установка пакета

Пакет `knuckleswtf/scribe` версии 5.0 уже установлен в проекте. Если вам нужно установить его в другом проекте, используйте команду:

```bash
composer require knuckleswtf/scribe:^5.0
```

### 2. Публикация файлов конфигурации

Для настройки Scribe необходимо опубликовать файлы конфигурации:

```bash
php artisan vendor:publish --tag=scribe-config
```

Эта команда создаст файл `config/scribe.php` с настройками по умолчанию.

### 3. Публикация шаблонов (опционально)

Если вы хотите настроить внешний вид документации, опубликуйте шаблоны Scribe:

```bash
php artisan vendor:publish --tag=scribe-views
```

Шаблоны будут доступны в директории `resources/views/vendor/scribe/`.

## Базовая конфигурация Scribe

Основные настройки находятся в файле `config/scribe.php`:

```php
// Название API и документации
'title' => 'API Документация',
'description' => 'Документация для работы с API',

// URL-адрес API
'base_url' => env('APP_URL', 'http://localhost'),

// Выходной формат (static - HTML, laravel - Blade views)
'type' => 'static',
'static' => [
    'output_path' => 'public/docs',
],

// Маршруты, которые будут включены в документацию
'routes' => [
    [
        'match' => [
            'prefixes' => ['api/*'],
            'domains' => ['*'],
        ],
        'apply' => [
            'headers' => [
                'Content-Type' => 'application/json',
                'Accept' => 'application/json',
            ],
        ],
    ],
],

// Авторизация
'auth' => [
    'enabled' => true,
    'default' => false,
    'in' => 'bearer',
    'name' => 'token',
    'use_value' => env('SCRIBE_AUTH_KEY'),
    'placeholder' => '{YOUR_AUTH_KEY}',
],

// Интерактивное тестирование API
'try_it_out' => [
    'enabled' => true,
    'base_url' => null,
],

// Языки примеров запросов
'example_languages' => [
    'bash',
    'javascript',
    'php',
],
```

## Документирование API-методов

### 1. Документирование контроллеров

Используйте PHPDoc-аннотации в контроллерах для описания групп API:

```php
/**
 * @group Управление пользователями
 *
 * API для управления пользователями системы
 */
class UserController extends Controller
{
    // Методы контроллера
}
```

### 2. Документирование методов

Для каждого метода контроллера добавьте PHPDoc-аннотации:

```php
/**
 * Получение списка пользователей
 *
 * Возвращает пагинированный список всех активных пользователей.
 *
 * @queryParam page integer Номер страницы. Example: 1
 * @queryParam per_page integer Количество элементов на странице (10-100). Example: 15
 * @queryParam search string Поиск по имени или email пользователя. Example: john
 *
 * @response status=200 scenario="успешный запрос" {
 *     "data": [
 *         {
 *             "id": 1,
 *             "name": "Иван Иванов",
 *             "email": "ivan@example.com",
 *             "created_at": "2023-01-15T10:00:00.000000Z"
 *         }
 *     ],
 *     "links": {
 *         "first": "http://example.com/api/users?page=1",
 *         "last": "http://example.com/api/users?page=3",
 *         "prev": null,
 *         "next": "http://example.com/api/users?page=2"
 *     },
 *     "meta": {
 *         "current_page": 1,
 *         "from": 1,
 *         "last_page": 3,
 *         "path": "http://example.com/api/users",
 *         "per_page": 15,
 *         "to": 15,
 *         "total": 40
 *     }
 * }
 * 
 * @response status=401 scenario="не авторизован" {
 *     "message": "Unauthenticated."
 * }
 */
public function index(Request $request)
{
    // Код метода
}
```

### 3. Документирование параметров запроса

#### URL-параметры

```php
/**
 * @urlParam id integer required ID пользователя. Example: 1
 */
```

#### Query-параметры

```php
/**
 * @queryParam sort string Сортировка результатов (name, created_at). Example: name
 * @queryParam order string Порядок сортировки (asc, desc). Example: asc
 */
```

#### Body-параметры

```php
/**
 * @bodyParam name string required Имя пользователя. Example: Иван Иванов
 * @bodyParam email string required Email пользователя. Example: ivan@example.com
 * @bodyParam password string required Пароль (мин. 8 символов). Example: secret123
 * @bodyParam role_id integer ID роли пользователя. Example: 2
 */
```

### 4. Документирование ответов

#### Успешные ответы

```php
/**
 * @response status=200 scenario="успешный запрос" {
 *     "id": 1,
 *     "name": "Иван Иванов",
 *     "email": "ivan@example.com",
 *     "created_at": "2023-01-15T10:00:00.000000Z",
 *     "updated_at": "2023-01-15T10:00:00.000000Z"
 * }
 */
```

#### Ответы с ошибками

```php
/**
 * @response status=404 scenario="пользователь не найден" {
 *     "message": "Пользователь не найден"
 * }
 * 
 * @response status=422 scenario="ошибка валидации" {
 *     "message": "Переданные данные невалидны.",
 *     "errors": {
 *         "email": [
 *             "Email уже занят другим пользователем."
 *         ],
 *         "password": [
 *             "Пароль должен содержать не менее 8 символов."
 *         ]
 *     }
 * }
 */
```

### 5. Документирование полей ответа

```php
/**
 * @responseField id integer ID пользователя
 * @responseField name string Имя пользователя
 * @responseField email string Email пользователя
 * @responseField created_at datetime Дата создания пользователя
 * @responseField updated_at datetime Дата последнего обновления пользователя
 */
```

## Генерация документации

После добавления всех необходимых аннотаций в код, сгенерируйте документацию:

```bash
php artisan scribe:generate
```

Документация будет доступна по адресу:
- HTML-документация: http://your-app.test/docs/index.html
- Postman-коллекция: http://your-app.test/docs/collection.json
- OpenAPI-спецификация: http://your-app.test/docs/openapi.yaml

## Дополнительные возможности Scribe

### 1. Пользовательские ответы через фабрики

Вы можете использовать фабрики Laravel для генерации примеров ответов:

```php
/**
 * @response status=200 scenario="успешный запрос" {
 *     "data": [
 *         {
 *             "id": 1,
 *             "title": "Первая публикация",
 *             "content": "Содержимое первой публикации...",
 *             "author": {
 *                 "id": 1,
 *                 "name": "Иван Иванов"
 *             },
 *             "created_at": "2023-01-15T10:00:00.000000Z"
 *         }
 *     ],
 *     "links": {
 *         "first": "http://example.com/api/posts?page=1",
 *         "last": "http://example.com/api/posts?page=1",
 *         "prev": null,
 *         "next": null
 *     },
 *     "meta": {
 *         "current_page": 1,
 *         "from": 1,
 *         "last_page": 1,
 *         "path": "http://example.com/api/posts",
 *         "per_page": 15,
 *         "to": 1,
 *         "total": 1
 *     }
 * }
 */
```

### 2. Исключение эндпоинтов из документации

Если вы хотите исключить определенный метод из документации, используйте аннотацию `@hideFromAPIDocumentation`:

```php
/**
 * @hideFromAPIDocumentation
 */
public function internalMethod()
{
    // Этот метод не будет включен в документацию
}
```

### 3. Добавление аутентификации

Для эндпоинтов, требующих аутентификацию, добавьте аннотацию `@authenticated`:

```php
/**
 * Получение профиля текущего пользователя
 * 
 * @authenticated
 * 
 * @response {
 *     "id": 1,
 *     "name": "Иван Иванов",
 *     "email": "ivan@example.com"
 * }
 */
public function profile()
{
    // Код метода
}
```

### 4. Группировка эндпоинтов

Для логического разделения API используйте аннотацию `@group`:

```php
/**
 * @group Аутентификация
 */
class AuthController extends Controller
{
    // Методы аутентификации
}

/**
 * @group Профиль пользователя
 */
class ProfileController extends Controller
{
    // Методы работы с профилем
}
```

## Советы для эффективного использования Scribe

1. **Структурируйте документацию**: Используйте аннотации `@group` для логического разделения API.
2. **Указывайте реалистичные примеры**: Добавляйте примеры параметров и ответов, близкие к реальным данным.
3. **Документируйте все возможные ответы**: Включайте как успешные ответы, так и ответы с ошибками.
4. **Придерживайтесь стандартов**: Соблюдайте единый стиль документирования во всех контроллерах.
5. **Регулярно обновляйте документацию**: Запускайте генерацию документации после внесения изменений в API.

## Обновление документации

Для обновления документации после внесения изменений в код API, выполните команду:

```bash
php artisan scribe:generate
```

Рекомендуется добавить эту команду в процесс CI/CD для автоматического обновления документации при деплое. 