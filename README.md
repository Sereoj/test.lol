# Art Platform - Платформа для художников

## О проекте
Art Platform - это платформа для художников, которая позволяет создавать и представлять портфолио, обмениваться опытом, продавать свои работы и находить клиентов. Платформа включает в себя социальные функции, возможности монетизации и продвижения творческого контента.

## Основные функции

- **Профили пользователей**: Детальные профили с указанием навыков, достижений и статуса занятости
- **Портфолио работ**: Возможность публикации работ с медиа-контентом
- **Категоризация**: Система категорий и тегов для удобного поиска работ
- **Социальные взаимодействия**: Подписки, лайки, комментарии и репосты
- **Монетизация**: Возможность продажи работ, платные подписки
- **Система платежей**: Баланс пользователей в нескольких валютах (USD, RUB), пополнения и снятия средств
- **Геймификация**: Система заданий и достижений для повышения вовлеченности
- **Система сообщений**: Обмен личными сообщениями между пользователями с автоматическим уведомлением
- **Уведомления**: Уведомления о новых подписчиках, лайках, комментариях и упоминаниях

## Технологический стек

### Backend
- **Laravel 10** – фреймворк приложения
- **PHP 8.2+** – язык программирования
- **MySQL** – реляционная база данных
- **Laravel Passport** – OAuth2 аутентификация
- **Laravel Reverb** – WebSocket сервер для реал-тайм уведомлений
- **Redis** – кеширование, очереди задач, сессии
- **FFmpeg** – обработка и кодирование видео
- **Intervention Image** – обработка и оптимизация изображений

### Frontend
- **Vite** – сборщик модулей и dev-сервер
- **Laravel Blade** – шаблонизатор
- **Laravel Echo** – JavaScript клиент для WebSocket
- **Pusher JS** – поддержка реал-тайм событий

### Infrastructure & DevOps
- **Docker** – контейнеризация приложения
- **Docker Compose** – оркестрация контейнеров
- **Caddy** – веб-сервер с автоматическим SSL (production)
- **PHP-FPM** – FastCGI процесс менеджер
- **Composer** – менеджер PHP зависимостей
- **npm** – менеджер Node.js зависимостей

### Development Tools
- **PHPUnit** – unit тестирование
- **PHP CS Fixer** – форматирование кода
- **PHPStan** – статический анализ кода
- **Husky** – git hooks для качества кода

### Обоснование выбора ключевых технологий

#### Redis – Кеширование и очереди

**Зачем использовался:**
- **Реал-тайм система сообщений**: Чаты между пользователями требуют быстрого доступа к активным сессиям. Хранение в MySQL был бы узким местом, Redis дает доступ за микросекунды
- **Обработка видео (FFmpeg)**: Когда художник загружает видео, это тяжелая операция (кодирование, создание превью). Redis очередь позволяет обрабатывать это асинхронно через `queue:work`, не блокируя пользователя
- **Кеширование поиска и фильтров**: Поиск работ по категориям, тегам, фильтрация по цене и рейтингу. Результаты кешируются в Redis с TTL, уменьшая нагрузку на БД
- **Статистика и счетчики**: Подсчет просмотров работ, лайков, репостов без частых обновлений БД
- **Сессии пользователей**: Хранение данных авторизации для мгновенного восстановления состояния

**Реальный пример**: Художник загружает 500MB видео → Laravel job отправляется в Redis очередь → worker обрабатывает FFmpeg конвертацию в фоне → пользователь может продолжать работу → когда видео готово, приходит WebSocket уведомление.

---

#### Laravel Reverb – WebSocket для реал-тайм уведомлений

**Зачем использовался:**
- **Система уведомлений**: Когда кто-то лайкует работу, комментирует или подписывается, художник видит уведомление сразу (не за 10 минут при обновлении страницы)
- **Онлайн статус в чатах**: При отправке сообщения оба пользователя видят статус "в сети" / "печатает" без задержек
- **Публикация новых работ**: Подписчики художника мгновенно видят его новую работу в ленте без перезагрузки страницы
- **Система платежей**: Когда платеж прошел, транзакция мгновенно появляется в истории баланса
- **Уведомления о заявках**: Если художник получил заказ или запрос на сотрудничество, он видит это в реальном времени

**Реальный пример**:
1. Художник публикует новую работу в 15:30
2. Reverb WebSocket отправляет событие `work.created` всем, кто подписан на этого художника
3. JavaScript слушатель (Laravel Echo) в браузере получает событие и добавляет работу в ленту без перезагрузки
4. Все подписчики видят новую работу одновременно

---

#### Laravel Passport (OAuth2) – Безопасная аутентификация

**Зачем использовался:**
- **API безопасность для мобильного приложения**: Если в будущем будет мобильное приложение (iOS/Android), оно не сможет использовать сессии. Passport OAuth2 токены − стандарт для мобильных приложений
- **Защита платежных операций**: При выводе денег или пополнении баланса в USD/RUB, требуется надежная токен-based аутентификация (безопаснее, чем сессия cookie)
- **Интеграция с партнерами**: Если платформа будет интегрироваться со сторонними сервисами (например, платежные системы, галереи), им выдаются API ключи с Passport
- **Разделение прав доступа (scopes)**: Можно выдать токен с ограниченными правами, например:
  - `read:profile` – только чтение профиля
  - `write:messages` – только отправка сообщений
  - `read:balance` – только чтение баланса (для финансовых интеграций)
- **Отзыв токена**: При компрометации или смене пароля, все токены аккаунта можно отозвать одной командой

**Реальный пример**:
- Пользователь устанавливает мобильное приложение → логинится → получает OAuth2 токен на 1 год
- Токен хранится в защищенном хранилище приложения
- Все API запросы идут с этим токеном (не с паролем!)
- Если пользователь меняет пароль, токен все еще работает (не нужно перелогиниваться в приложении)

---

#### Caddy – Веб-сервер вместо Nginx

**Зачем использовался:**
- **Автоматические SSL сертификаты**: Caddy автоматически получает и обновляет Let's Encrypt сертификаты. С Nginx пришлось бы писать cron скрипты для обновления, рисковать истечением сертификата
- **HTTPS по умолчанию**: На production важно, чтобы все было через HTTPS. Caddy это гарантирует из коробки
- **Простота конфигурации**: Вместо 50+ строк Nginx конфига, Caddy требует 5-7 строк Caddyfile
- **Меньше ошибок при деплое**: Чем проще конфиг, тем меньше вероятность ошибки при развертывании на production
- **HTTP/2 и HTTP/3 из коробки**: Современные протоколы для быстрой доставки контента. Особенно важно для загрузки портфолио с видео и изображениями
- **Встроенный reverse proxy**: Caddy может проксировать запросы к PHP-FPM и WebSocket (Reverb) с минимумом конфигурации

**Реальный пример конфига:**
```
artplatform.com {
    encode gzip
    reverse_proxy localhost:9000
}
```

Вместо Nginx:
```nginx
server {
    listen 443 ssl http2;
    server_name artplatform.com;

    ssl_certificate /etc/letsencrypt/live/artplatform.com/fullchain.pem;
    ssl_certificate_key /etc/letsencrypt/live/artplatform.com/privkey.pem;
    ssl_protocols TLSv1.2 TLSv1.3;

    location / {
        proxy_pass http://127.0.0.1:9000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    # WebSocket поддержка для Reverb
    location /ws {
        proxy_pass http://127.0.0.1:8080;
        proxy_http_version 1.1;
        proxy_set_header Upgrade $http_upgrade;
        proxy_set_header Connection "upgrade";
    }
}
```

---

### Как технологии работают вместе

```
Художник публикует работу
        ↓
Laravel Controller получает POST запрос
        ↓
Валидация (Laravel Request) → Сохранение в MySQL
        ↓
Event WorkPublished отправляется
        ↓
Listener обрабатывает событие:
  - Отправляет уведомление в Reverb WebSocket
  - Кеширует работу в Redis
  - Отправляет email (через Redis очередь с FFmpeg обработкой миниатюр)
        ↓
Reverb WebSocket отправляет подписчикам
        ↓
JavaScript (Laravel Echo) в браузере получает событие
        ↓
Новая работа появляется в ленте подписчиков БЕЗ перезагрузки
```

**Итоговая архитектура:**
- **Caddy** (HTTPS, reverse proxy)
  - **PHP-FPM** (Laravel приложение)
    - **MySQL** (основные данные)
    - **Redis** (кеш, очереди, сессии)
    - **Reverb** (WebSocket сервер)
      - **JavaScript Echo** (реал-тайм уведомления в браузере)
    - **Passport OAuth2** (API безопасность)

## Навыки и знания, продемонстрированные в проекте

Этот учебный проект демонстрирует следующие реальные навыки и знания:

### Backend Development

**Laravel Advanced Patterns:**
- **Service Layer Pattern** — 67 сервисов в модулях Users, Posts, Media, Billing, Authentication, Acquiring, ChallengeService
- **Repository Pattern** — 16 репозиториев для абстракции доступа к данным (PostRepository, ChallengeRepository, UserRepository и др.)
- **DTO (Data Transfer Objects)** — классы для передачи данных между слоями приложения
- **Events & Listeners** — 26 событий и 18 слушателей для асинхронной обработки (SubscriptionActivated, SubscriptionCancelled, PostPublished, ChallengeCreated и др.)
- **Queue Jobs** — фоновая обработка тяжелых задач через Redis очереди
- **Strategy Pattern** — 7 стратегий для вариативного поведения (Acquiring, Media processing)

**Database & SQL:**
- **Database Locks** — использование `lockForUpdate()` для защиты от race conditions в финансовых операциях
- **Raw SQL vs Query Builder** — понимание когда использовать `selectRaw()` vs `select()`, защита от SQL injection
- **Unique Indexes** — уникальные индексы для защиты от дубликатов и idempotency
- **Complex Queries** — JOIN, GROUP BY, агрегатные функции (COUNT, SUM, COALESCE), CASE выражения
- **Transactions** — `DB::transaction()` для атомарных операций в платежной системе
- **Soft Deletes** — мягкое удаление записей в моделях пользователей
- **Migrations** — версионирование схемы БД с комментариями и примерами использования

**Security:**
- **SQL Injection Prevention** — использование параметризованных запросов, понимание рисков интерполяции переменных
- **OAuth2 Authentication** — Laravel Passport для API безопасности с токенами
- **Idempotency Keys** — защита от повторных запросов при платежах
- **Input Validation** — 68 Form Request классов для валидации входных данных
- **Rate Limiting** — защита от abuse API
- **Password Hashing** — bcrypt для безопасного хранения паролей
- **Email Verification** — верификация email через токены

**Architecture:**
- **MVC Pattern** — 58 контроллеров, четкое разделение ответственности
- **SOLID Principles** — Single Responsibility, Dependency Injection через сервисы
- **Clean Code** — именование, комментарии, типизация
- **Strict Typing** — `declare(strict_types=1)` для всех файлов
- **Modular Structure** — разделение на модули (Users, Posts, Billing, Media, Authentication, Challenge)
- **Layered Architecture** — Controller → Service → Repository → Model (подтверждено кодом)

**Business Logic Domains:**
- **Billing System** — полный цикл платежей (пополнения, списания, переводы, подписки, комиссии)
- **User Management** — профили, настройки, уровни, бейджи, опыт, статус занятости
- **Content Management** — посты, медиа, категории, теги, поиск, фильтрация
- **Social Features** — подписки, лайки, комментарии, репосты, уведомления
- **Challenge System** — конкурсы, голосования, призы, победители
- **Messaging** — личные сообщения, чаты в реальном времени

### DevOps & Infrastructure

**Docker & Containerization:**
- **Docker Compose** — оркестрация контейнеров для dev и production
- **Multi-stage Builds** — оптимизация образов
- **Service Orchestration** — связывание app, mysql, redis, reverb
- **Environment Management** — разделение конфигураций для dev/staging/production

**Web Server:**
- **Caddy** — современный веб-сервер с автоматическим SSL
- **Reverse Proxy** — проксирование к PHP-FPM и WebSocket
- **HTTP/2 & HTTP/3** — современные протоколы
- **Automatic HTTPS** — Let's Encrypt сертификаты без ручной настройки

**Caching & Queues:**
- **Redis** — кеширование, очереди, сессии
- **Queue Workers** — обработка фоновых задач
- **Cache Strategies** — TTL, invalidation, tagging
- **Session Management** — хранение сессий в Redis для горизонтального масштабирования

### Real-time Features

**WebSocket:**
- **Laravel Reverb** — WebSocket сервер для реал-тайм уведомлений
- **Laravel Echo** — клиент для WebSocket в браузере
- **Broadcasting** — события в реальном времени (уведомления, онлайн-статус, чаты)
- **Presence Channels** — отслеживание пользователей онлайн

### API Development

**REST API:**
- **API Resources** — 62 ресурса для трансформации данных в JSON
- **API Controllers** — 58 контроллеров для обработки запросов
- **Request Validation** — 68 Form Request классов для валидации
- **Middleware** — 12 middleware для аутентификации, логирования, CORS
- **Rate Limiting** — защита API от abuse
- **API Documentation** — Swagger/OpenAPI документация

**API Features:**
- **Pagination** — пагинация больших списков данных ([PostRepository](app/Repositories/PostRepository.php), [ChallengeRepository](app/Repositories/ChallengeRepository.php))
- **Filtering** — сложная фильтрация по множеству параметров ([PostFilteringService](app/Services/Posts/Assistants/PostFilteringService.php), [TimeFrameFilterService](app/Services/Posts/Assistants/TimeFrameFilterService.php), [MediaTypeFilterService](app/Services/Posts/Assistants/MediaTypeFilterService.php))
- **Sorting** — вариативная сортировка результатов ([SortingService](app/Services/Posts/Assistants/SortingService.php), [Sorting Strategies](app/Strategies/Posts/))
- **Search** — полнотекстовый поиск с релевантностью ([PostSearchService](app/Services/Posts/PostSearchService.php), [SearchSuggestionService](app/Services/Posts/SearchSuggestionService.php))
- **Versioning** — подготовка к версионированию API ([RouteServiceProvider](app/Providers/RouteServiceProvider.php))

### Code Quality

**Testing:**
- **PHPUnit** — unit и feature тесты
- **Test Coverage** — покрытие кода тестами

**Static Analysis:**
- **PHPStan** — статический анализ кода
- **PHP CS Fixer** — форматирование по стандартам PSR
- **Git Hooks (Husky)** — автоматические проверки перед коммитом

**Code Organization:**
- **Helpers** — 3 вспомогательных класса для общих функций
- **Traits** — 2 трейта для переиспользуемого функционала
- **Processors** — 6 процессоров для обработки медиа и данных
- **Logging** — структурированное логирование с контекстом

## Требования

### Для локальной разработки
- **PHP 8.2+** с расширениями: openssl, pdo, mbstring, tokenizer, json, curl, gd, bcmath, zip, xml
- **MySQL 8.0+** или **MariaDB 10.4+**
- **Redis 6.0+** (опционально для локальной разработки)
- **Node.js 18+** и **npm 9+**
- **Composer 2.0+**
- **FFmpeg 4.0+** (для обработки видео)

### Для Docker
- **Docker 20.10+**
- **Docker Compose 2.0+**

## Установка и настройка

### 1. Локальная разработка

**Клонирование репозитория:**
```bash
git clone https://github.com/your-organization/art-platform.git
cd art-platform
```

**Установка зависимостей:**
```bash
composer install
npm install
```

**Настройка переменных окружения:**
```bash
cp .env.example .env
php artisan key:generate
```

**Генерация ключей OAuth (для Passport):**
```bash
php artisan passport:keys
php artisan passport:setup
```

**Подготовка базы данных:**
```bash
php artisan migrate
php artisan db:seed  # опционально, для загрузки тестовых данных
```

**Создание символической ссылки для хранилища:**
```bash
php artisan storage:link
```

**Запуск в режиме разработки:**
```bash
# Терминал 1 - PHP-сервер
php artisan serve

# Терминал 2 - Vite dev-сервер
npm run dev

# Терминал 3 - Обработка очереди задач (опционально)
php artisan queue:work
```

Приложение будет доступно по адресу: `http://localhost:8000`

### 2. Docker (для production и локального development)

**Детальная инструкция:** см. [DOCKER.md](DOCKER.md)

**Быстрый старт:**
```bash
# Development
docker-compose -f docker-compose.dev.yml up -d

# Production
docker-compose -f docker-compose.prod.yml up -d
```

## Переменные окружения

Основные переменные в `.env`:

```env
# Приложение
APP_NAME=ArtPlatform
APP_ENV=local              # local, development, production
APP_KEY=base64:...         # Генерируется автоматически
APP_DEBUG=true
APP_URL=http://localhost:8000

# База данных
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=art_platform
DB_USERNAME=root
DB_PASSWORD=

# Redis (опционально)
REDIS_HOST=127.0.0.1
REDIS_PORT=6379

# Mail (если используется)
MAIL_DRIVER=smtp
MAIL_HOST=smtp.mailtrap.io
MAIL_PORT=465
MAIL_USERNAME=your_username
MAIL_PASSWORD=your_password

# S3 (опционально для хранилища)
AWS_ACCESS_KEY_ID=
AWS_SECRET_ACCESS_KEY=
AWS_DEFAULT_REGION=us-east-1
AWS_BUCKET=

# Reverb (WebSocket)
REVERB_APP_ID=
REVERB_APP_KEY=
REVERB_APP_SECRET=
REVERB_HOST=localhost
REVERB_PORT=8080
```

## API документация

API документация доступна по адресу `/api/documentation` после запуска проекта.

Документация генерируется автоматически из аннотаций контроллеров с использованием Swagger/OpenAPI.

## Разработка

### Структура проекта

```
app/
├── Console/          # Консольные команды
├── Events/           # События приложения
├── Exceptions/       # Обработчики исключений
├── Http/
│   ├── Controllers/  # API контроллеры
│   ├── Requests/     # Form Request классы валидации
│   └── Resources/    # JSON API ресурсы
├── Jobs/             # Задачи для очереди
├── Listeners/        # Слушатели событий
├── Mail/             # Классы писем
├── Models/           # Eloquent модели
├── Notifications/    # Классы уведомлений
├── Repositories/     # Слой доступа к данным
├── Services/         # Бизнес-логика
├── Strategies/       # Паттерн Strategy
├── Traits/           # Переиспользуемые функции
└── Utils/            # Вспомогательные функции

database/
├── migrations/       # Миграции базы данных
├── seeders/          # Заполнение тестовых данных
└── factories/        # Factory для создания тестовых объектов

routes/
├── api.php           # API маршруты
├── web.php           # Web маршруты
├── channels.php      # WebSocket каналы
└── console.php       # Консольные команды

tests/
├── Feature/          # Feature тесты
└── Unit/             # Unit тесты

resources/
├── views/            # Blade шаблоны
├── js/               # JavaScript файлы
├── css/              # CSS стили
└── lang/             # Локализация
```

### Конвенции кода

- **Типизация**: Строгая типизация для всех методов (`declare(strict_types=1)`)
- **Исключения**: Обработка исключений с блоками try-catch в контроллерах
- **Логирование**: Использование `Log::error()`, `Log::info()` для отслеживания
- **Ответы**: Форматированные JSON ответы через методы `successResponse()` и `errorResponse()`
- **Валидация**: Form Request классы для валидации входных данных
- **Именование**: camelCase для методов, PascalCase для классов, snake_case для таблиц БД

### Архитектура системы сообщений и уведомлений

Система использует:
- **Сервисный слой** для управления сообщениями и уведомлениями
- **События и слушатели** для асинхронной обработки
- **WebSocket (Reverb)** для реал-тайм обновлений
- **Автоматическое приветствие** от администратора (UserID: 1) новым пользователям
- **Очередь задач** для отправки тяжелых операций

## Тестирование

### Запуск тестов

```bash
# Все тесты
php artisan test

# С отчетом о покрытии кода
php artisan test --coverage

# Конкретный тестовый файл
php artisan test tests/Feature/Auth/LoginTest.php

# Только unit тесты
php artisan test tests/Unit
```

### Написание тестов

Используется PHPUnit с Laravel тестовыми утилитами:

```php
<?php

namespace Tests\Feature;

use Tests\TestCase;

class UserTest extends TestCase
{
    public function test_user_can_register(): void
    {
        $response = $this->post('/api/auth/register', [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'password123',
        ]);

        $response->assertStatus(201);
        $this->assertDatabaseHas('users', [
            'email' => 'john@example.com',
        ]);
    }
}
```

## Контроль качества кода

### Форматирование кода

```bash
# Проверка по стандартам
composer run lint

# Автоматическое форматирование
composer run format
```

### Статический анализ

```bash
# PHPStan анализ
composer run phpstan

# PHPCS проверка
composer run phpcs
```

### Git Hooks

Проект использует Husky для автоматических проверок перед коммитом:
- Форматирование кода
- Линтинг
- Статический анализ

## Развертывание

### Production чеклист

- [ ] Установить `APP_ENV=production` и `APP_DEBUG=false`
- [ ] Сгенерировать крепкий `APP_KEY`
- [ ] Настроить все переменные окружения для production
- [ ] Выполнить `php artisan config:cache`
- [ ] Выполнить `php artisan route:cache`
- [ ] Выполнить миграции: `php artisan migrate --force`
- [ ] Настроить HTTPS/SSL сертификаты
- [ ] Настроить backup для БД
- [ ] Настроить мониторинг приложения
- [ ] Настроить логирование и логротацию

### Миграция на production (Docker)

```bash
docker-compose -f docker-compose.prod.yml up -d
docker-compose -f docker-compose.prod.yml exec app php artisan migrate --force
```

## Поиск и решение проблем

### Частые проблемы

**1. "SQLSTATE[HY000]: General error: 1030"**
```bash
# Проверить подключение к БД и права доступа
php artisan tinker
DB::connection()->getPdo();
```

**2. "Class not found" при использовании моделей**
```bash
composer dump-autoload
```

**3. Проблемы с хранилищем файлов**
```bash
php artisan storage:link
chmod -R 775 storage/
chmod -R 775 bootstrap/cache/
```

**4. WebSocket (Reverb) не работает**
```bash
# Проверить, что порт открыт
netstat -an | grep 8080

# Перезапустить Reverb сервер
php artisan reverb:start
```

## Документация проекта

- [DOCKER.md](DOCKER.md) – Инструкция по Docker

## Авторские права и сотрудничество

**ВАЖНО**: Все права на данный проект принадлежат автору. Создание аналогов или копирование концепции запрещено.

Однако, мы приветствуем вклад в развитие проекта! Вы можете помочь:
- Улучшая существующий код путем рефакторинга
- Предлагая оптимизации производительности
- Находя и исправляя ошибки
- Делясь опытом в разработке аналогичных функций

Для сотрудничества:
1. Создайте форк репозитория
2. Создайте ветку для вашей функции (`git checkout -b feature/AmazingFeature`)
3. Сделайте коммиты с описанием изменений (`git commit -m 'Add some AmazingFeature'`)
4. Отправьте изменения в свой форк (`git push origin feature/AmazingFeature`)
5. Отправьте pull request с описанием улучшений

## Лицензия

Проект распространяется под лицензией [MIT](https://opensource.org/licenses/MIT) с дополнительными ограничениями на копирование концепции и создание аналогов.

## Контакты и поддержка

Вопросы и предложения отправляйте через Issues в репозитории.

---

**Последнее обновление**: 6 января 2026 г.
