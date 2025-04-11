# Улучшение документации API с помощью Scribe

## Обзор текущей ситуации

В проекте используется пакет `knuckleswtf/scribe` версии 5.0 для генерации документации API. Текущая документация маршрутов хорошо структурирована, с разделением на группы доступа (гостевые, авторизованные и административные маршруты), но может быть улучшена с точки зрения индексации и дизайна.

## Рекомендации по улучшению документации

### 1. Аннотации для контроллеров и методов

Для улучшения индексации и полноты документации, необходимо добавить PHPDoc аннотации к контроллерам и их методам:

```php
/**
 * @group Профиль пользователя
 * @description Управление профилем пользователя
 */
class UserProfileController extends Controller
{
    /**
     * Получение профиля пользователя
     *
     * Возвращает полную информацию о профиле пользователя по его slug.
     *
     * @urlParam slug string required Уникальный идентификатор пользователя. Example: john-doe
     * @response 200 {
     *   "success": true,
     *   "data": {
     *     "id": 1,
     *     "username": "johndoe",
     *     "slug": "john-doe",
     *     "description": "Разработчик программного обеспечения",
     *     "email": "john@example.com",
     *     "created_at": "2023-01-15T10:00:00.000000Z",
     *     "updated_at": "2023-01-15T10:00:00.000000Z"
     *   }
     * }
     * @response 404 {
     *   "success": false,
     *   "message": "Пользователь не найден"
     * }
     */
    public function show($slug)
    {
        // Код метода
    }
}
```

### 2. Конфигурация Scribe

Необходимо обновить конфигурацию Scribe для улучшения генерации документации:

```php
// config/scribe.php

return [
    'theme' => 'default',
    
    'title' => 'API Документация',
    
    'description' => 'Полная документация API с примерами запросов и ответов.',
    
    'base_url' => env('APP_URL', 'http://localhost'),
    
    'routes' => [
        [
            'match' => [
                'prefixes' => ['api/*'],
                'domains' => ['*'],
            ],
            'include' => [
                // Включаем все маршруты API
            ],
            'exclude' => [
                // Исключаем внутренние маршруты
            ],
            'apply' => [
                'headers' => [
                    'Content-Type' => 'application/json',
                    'Accept' => 'application/json',
                ],
            ],
        ],
    ],
    
    'groups' => [
        'auth' => 'Аутентификация',
        'profile' => 'Профиль пользователя',
        'posts' => 'Посты',
        'comments' => 'Комментарии',
        'notifications' => 'Уведомления',
        'messages' => 'Сообщения',
        'admin' => 'Административная панель',
    ],
    
    'logo' => false,
    
    'try_it_out' => [
        'enabled' => true,
        'base_url' => null,
    ],
    
    'example_languages' => [
        'bash',
        'javascript',
        'php',
    ],
];
```

### 3. Улучшение дизайна документации

#### 3.1. Создание пользовательского CSS

Создайте файл `public/docs/css/custom.css`:

```css
:root {
    --primary-color: #4F46E5;
    --secondary-color: #1E40AF;
    --text-color: #1F2937;
    --light-bg: #F9FAFB;
    --border-color: #E5E7EB;
    --success-color: #10B981;
    --warning-color: #F59E0B;
    --error-color: #EF4444;
}

body {
    font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
    color: var(--text-color);
    line-height: 1.6;
}

.sidebar {
    background-color: var(--light-bg);
    border-right: 1px solid var(--border-color);
}

.sidebar h2 {
    color: var(--secondary-color);
    font-weight: 600;
    font-size: 1.25rem;
    margin-top: 2rem;
}

.sidebar ul li a {
    color: var(--text-color);
    display: block;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    text-decoration: none;
}

.sidebar ul li a:hover {
    background-color: rgba(79, 70, 229, 0.1);
}

.sidebar ul li a.active {
    background-color: var(--primary-color);
    color: white;
}

.endpoint {
    border: 1px solid var(--border-color);
    border-radius: 0.5rem;
    margin-bottom: 2rem;
    overflow: hidden;
}

.endpoint-header {
    padding: 1rem;
    background-color: var(--light-bg);
    border-bottom: 1px solid var(--border-color);
    display: flex;
    align-items: center;
    gap: 1rem;
}

.method {
    font-weight: 600;
    padding: 0.25rem 0.5rem;
    border-radius: 0.25rem;
    min-width: 60px;
    text-align: center;
}

.method.get {
    background-color: #10B981;
    color: white;
}

.method.post {
    background-color: #3B82F6;
    color: white;
}

.method.put, .method.patch {
    background-color: #F59E0B;
    color: white;
}

.method.delete {
    background-color: #EF4444;
    color: white;
}

.endpoint-body {
    padding: 1rem;
}

.tabs {
    display: flex;
    border-bottom: 1px solid var(--border-color);
    margin-bottom: 1rem;
}

.tab {
    padding: 0.5rem 1rem;
    cursor: pointer;
    border-bottom: 2px solid transparent;
}

.tab.active {
    border-bottom-color: var(--primary-color);
    font-weight: 500;
}

pre {
    background-color: #1F2937 !important;
    border-radius: 0.375rem;
    margin: 1rem 0;
}

code {
    font-family: 'Fira Code', monospace;
    font-size: 0.9rem;
}

.badge {
    display: inline-block;
    padding: 0.25rem 0.5rem;
    border-radius: 9999px;
    font-size: 0.75rem;
    font-weight: 500;
}

.badge.auth {
    background-color: #FEF3C7;
    color: #92400E;
}

.badge.required {
    background-color: #FEE2E2;
    color: #B91C1C;
}

table {
    width: 100%;
    border-collapse: collapse;
    margin: 1rem 0;
}

table th {
    text-align: left;
    padding: 0.75rem;
    background-color: var(--light-bg);
    border-bottom: 1px solid var(--border-color);
}

table td {
    padding: 0.75rem;
    border-bottom: 1px solid var(--border-color);
}

.try-it-out {
    background-color: var(--primary-color);
    color: white;
    border: none;
    padding: 0.5rem 1rem;
    border-radius: 0.375rem;
    font-weight: 500;
    cursor: pointer;
    transition: background-color 0.2s;
}

.try-it-out:hover {
    background-color: var(--secondary-color);
}
```

#### 3.2. Пользовательская тема Scribe

Создайте свою тему для Scribe, опубликовав его шаблоны:

```bash
php artisan vendor:publish --tag=scribe-views
```

Затем отредактируйте файл `resources/views/vendor/scribe/themes/default/index.blade.php`:

```blade
<!doctype html>
<html lang="ru">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <title>{!! $metadata['title'] !!}</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&family=Fira+Code&display=swap" rel="stylesheet">
    
    <link rel="stylesheet" href="{{ asset('/docs/css/custom.css') }}">
    <link rel="stylesheet" href="{{ asset('/vendor/scribe/css/theme-default.style.css') }}">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/styles/nord.min.css">

    <script src="https://cdnjs.cloudflare.com/ajax/libs/highlight.js/11.7.0/highlight.min.js"></script>
    <script>hljs.highlightAll();</script>
    
    <script src="https://cdn.jsdelivr.net/npm/lodash@4.17.21/lodash.min.js"></script>
    <script src="https://unpkg.com/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
</head>

<body>
    <div class="app" x-data="{ sidebarOpen: false }">
        <header class="header">
            <div class="container">
                <div class="header-content">
                    <div class="logo">
                        <h1>{!! $metadata['title'] !!}</h1>
                    </div>
                    <button @click="sidebarOpen = !sidebarOpen" class="sidebar-toggle">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-menu"><line x1="3" y1="12" x2="21" y2="12"></line><line x1="3" y1="6" x2="21" y2="6"></line><line x1="3" y1="18" x2="21" y2="18"></line></svg>
                    </button>
                </div>
            </div>
        </header>

        <div class="main">
            <div class="sidebar" :class="{ 'open': sidebarOpen }">
                <div class="sidebar-header">
                    <h2>Содержание</h2>
                    <button @click="sidebarOpen = false" class="close-sidebar">
                        <svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" class="feather feather-x"><line x1="18" y1="6" x2="6" y2="18"></line><line x1="6" y1="6" x2="18" y2="18"></line></svg>
                    </button>
                </div>
                <div class="sidebar-content">
                    <nav class="sidebar-nav">
                        <ul>
                            <li><a href="#introduction">Введение</a></li>
                            <li><a href="#authentication">Аутентификация</a></li>
                            @foreach($groupedEndpoints as $group)
                                <li>
                                    <a href="#{{ Str::slug($group['name']) }}">{{ $group['name'] }}</a>
                                </li>
                            @endforeach
                        </ul>
                    </nav>
                </div>
                <div class="sidebar-footer">
                    <div class="links">
                        @foreach($metadata['links'] ?? [] as $link)
                            {!! $link !!}
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="content">
                <div class="container">
                    <div class="introduction-section" id="introduction">
                        {!! $intro !!}
                    </div>

                    <div class="auth-section" id="authentication">
                        {!! $auth !!}
                    </div>

                    @include("scribe::themes.default.groups")
                    
                    @if($append)
                        <div class="append-section">
                            {!! $append !!}
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function() {
            // Активация текущего пункта меню при прокрутке
            const sections = document.querySelectorAll('.content > div.container > div');
            const navLinks = document.querySelectorAll('.sidebar-nav a');
            
            function onScroll() {
                let current = '';
                
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (scrollY >= sectionTop - 100) {
                        current = section.getAttribute('id');
                    }
                });
                
                navLinks.forEach(link => {
                    link.classList.remove('active');
                    if (link.getAttribute('href') === `#${current}`) {
                        link.classList.add('active');
                    }
                });
            }
            
            window.addEventListener('scroll', onScroll);
            
            // Переключение вкладок
            const tabContainers = document.querySelectorAll('.tabbed-content');
            
            tabContainers.forEach(container => {
                const tabs = container.querySelectorAll('.tab');
                const tabContents = container.querySelectorAll('.tab-content');
                
                tabs.forEach((tab, index) => {
                    tab.addEventListener('click', () => {
                        tabs.forEach(t => t.classList.remove('active'));
                        tabContents.forEach(c => c.classList.remove('active'));
                        
                        tab.classList.add('active');
                        tabContents[index].classList.add('active');
                    });
                });
            });
        });
    </script>
</body>
</html>
```

### 4. Индексация и группировка маршрутов

Для лучшей индексации важно правильно группировать маршруты по функциональным блокам. Рекомендуется следующая структура:

1. **Общая информация**
   - Введение и обзор API
   - Аутентификация и авторизация
   - Обработка ошибок

2. **Гостевые маршруты**
   - Аутентификация
   - Общедоступные данные

3. **Пользовательские маршруты** (требующие авторизации)
   - Профиль
   - Посты
   - Комментарии
   - Уведомления
   - Сообщения

4. **Административные маршруты**
   - Управление пользователями
   - Управление контентом
   - Статистика и аналитика
   - Настройки системы

### 5. Добавление описаний полей

Для улучшения описания API добавьте аннотации `@responseField` для всех ключевых полей ответа:

```php
/**
 * @responseField id integer ID поста
 * @responseField title string Заголовок поста
 * @responseField content string Содержимое поста
 * @responseField author_id integer ID автора поста
 * @responseField created_at datetime Дата и время создания поста
 * @responseField updated_at datetime Дата и время последнего обновления поста
 */
```

### 6. Добавление примеров запросов и ответов

Используйте аннотации `@response` с различными сценариями:

```php
/**
 * @response 200 scenario="Успешный запрос" {
 *   "success": true,
 *   "data": [...]
 * }
 * @response 404 scenario="Ресурс не найден" {
 *   "success": false,
 *   "message": "Ресурс не найден"
 * }
 * @response 422 scenario="Ошибка валидации" {
 *   "success": false,
 *   "message": "Ошибка валидации данных",
 *   "errors": {
 *     "email": ["Поле email обязательно для заполнения"],
 *     "password": ["Пароль должен содержать не менее 8 символов"]
 *   }
 * }
 */
```

### 7. Генерация и размещение документации

После внесения всех изменений, сгенерируйте документацию API:

```bash
php artisan scribe:generate
```

## Преимущества предложенных улучшений

1. **Улучшенная индексация**: Группировка маршрутов по функциональным блокам делает документацию более понятной и структурированной.

2. **Современный дизайн**: Кастомный CSS и JavaScript улучшают внешний вид и удобство использования документации.

3. **Полнота информации**: Подробные аннотации для контроллеров и методов предоставляют разработчикам всю необходимую информацию.

4. **Интерактивность**: Интеграция Try It Out позволяет разработчикам тестировать API непосредственно из документации.

5. **Поддержка мобильных устройств**: Адаптивный дизайн обеспечивает удобное использование документации на различных устройствах.

## Заключение

Предложенные улучшения значительно повысят качество документации API, сделав её более полной, структурированной и удобной для использования как внутренними разработчиками, так и внешними пользователями API. Внедрение этих рекомендаций поможет ускорить процесс интеграции с API и уменьшит количество ошибок при его использовании. 