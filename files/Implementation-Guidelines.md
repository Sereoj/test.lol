# Рекомендации по имплементации API

## Введение

Этот документ содержит рекомендации по имплементации API для проекта WallOne, основанные на анализе фронтенд-части приложения. Здесь представлены рекомендации по структуре базы данных, технической реализации и бизнес-логике.

## Содержание

1. [Рекомендуемая архитектура](#рекомендуемая-архитектура)
2. [Структура базы данных](#структура-базы-данных)
3. [Авторизация и аутентификация](#авторизация-и-аутентификация)
4. [Обработка медиа-файлов](#обработка-медиа-файлов)
5. [Обеспечение производительности](#обеспечение-производительности)
6. [Реализация уведомлений](#реализация-уведомлений)
7. [Работа с сообщениями](#работа-с-сообщениями)
8. [Безопасность](#безопасность)

## Рекомендуемая архитектура

Для эффективной реализации API рекомендуется использовать следующую архитектуру:

1. **RESTful API** - для большинства операций CRUD.
2. **WebSocket** - для реализации чатов и уведомлений в реальном времени.
3. **Многоуровневая архитектура**:
   - Слой контроллеров
   - Слой сервисов
   - Слой репозиториев
   - Слой моделей

## Структура базы данных

На основе анализа фронтенд-кода рекомендуется следующая структура базы данных:

### Таблицы

1. **users**
   - id (primary key)
   - username (unique)
   - email (unique)
   - password
   - slug (unique)
   - verification (boolean)
   - description (text, nullable)
   - experience (integer)
   - language (string)
   - created_at
   - updated_at

2. **user_settings**
   - id (primary key)
   - user_id (foreign key)
   - is_private (boolean)
   - show_online_status (boolean)
   - enable_two_factor (boolean)
   - created_at
   - updated_at

3. **notification_settings**
   - id (primary key)
   - user_id (foreign key)
   - push_enabled (boolean)
   - email_enabled (boolean)
   - likes_notifications (boolean)
   - comments_notifications (boolean)
   - followers_notifications (boolean)
   - messages_notifications (boolean)
   - created_at
   - updated_at

4. **avatars**
   - id (primary key)
   - user_id (foreign key)
   - path (string)
   - is_primary (boolean)
   - created_at
   - updated_at

5. **specializations**
   - id (primary key)
   - name_ru (string)
   - name_en (string)
   - created_at
   - updated_at

6. **user_specializations**
   - id (primary key)
   - user_id (foreign key)
   - specialization_id (foreign key)
   - created_at
   - updated_at

7. **badges**
   - id (primary key)
   - name_ru (string)
   - name_en (string)
   - description_ru (text)
   - description_en (text)
   - icon (string)
   - created_at
   - updated_at

8. **user_badges**
   - id (primary key)
   - user_id (foreign key)
   - badge_id (foreign key)
   - created_at
   - updated_at

9. **wallets**
   - id (primary key)
   - user_id (foreign key)
   - balance (decimal)
   - currency (string)
   - created_at
   - updated_at

10. **followers**
    - id (primary key)
    - follower_id (foreign key)
    - following_id (foreign key)
    - created_at
    - updated_at

11. **online_status**
    - id (primary key)
    - user_id (foreign key)
    - last_activity (timestamp)
    - created_at
    - updated_at

12. **posts**
    - id (primary key)
    - user_id (foreign key)
    - title (string)
    - slug (string, unique)
    - content (text, nullable)
    - is_adult_content (boolean)
    - is_nsfl_content (boolean)
    - is_free (boolean)
    - has_copyright (boolean)
    - created_at
    - updated_at

13. **media**
    - id (primary key)
    - post_id (foreign key)
    - type (enum: 'image', 'video', 'gif')
    - src (string)
    - position (integer)
    - created_at
    - updated_at

14. **comments**
    - id (primary key)
    - post_id (foreign key)
    - user_id (foreign key)
    - parent_id (foreign key, nullable)
    - content (text)
    - created_at
    - updated_at

15. **comment_likes**
    - id (primary key)
    - comment_id (foreign key)
    - user_id (foreign key)
    - type (enum: 'like', 'dislike')
    - created_at
    - updated_at

16. **comment_reports**
    - id (primary key)
    - comment_id (foreign key)
    - user_id (foreign key)
    - reason (text)
    - status (enum: 'pending', 'reviewed', 'rejected')
    - created_at
    - updated_at

17. **notifications**
    - id (primary key)
    - user_id (foreign key)
    - type (enum: 'like', 'comment', 'follow', 'message')
    - sender_id (foreign key)
    - post_id (foreign key, nullable)
    - comment_id (foreign key, nullable)
    - read_at (timestamp, nullable)
    - created_at
    - updated_at

18. **messages**
    - id (primary key)
    - sender_id (foreign key)
    - recipient_id (foreign key)
    - content (text)
    - is_read (boolean)
    - created_at
    - updated_at

19. **tasks**
    - id (primary key)
    - title (string)
    - description (text)
    - reward (decimal)
    - created_at
    - updated_at

20. **user_tasks**
    - id (primary key)
    - user_id (foreign key)
    - task_id (foreign key)
    - completed (boolean)
    - completed_at (timestamp, nullable)
    - created_at
    - updated_at

## Авторизация и аутентификация

### JWT Токены

Рекомендуется использовать JWT для авторизации с следующими характеристиками:

1. **Access Token**:
   - Короткий срок жизни (например, 15-60 минут)
   - Содержит идентификатор пользователя и роли/разрешения

2. **Refresh Token**:
   - Длительный срок жизни (например, 7-30 дней)
   - Хранится в базе данных с привязкой к пользователю и устройству
   - Возможность отзыва всех активных сессий

### Безопасность учетных данных

1. Хеширование паролей с использованием bcrypt или Argon2
2. Проверка надежности пароля при регистрации
3. Защита от брутфорс-атак через ограничение количества попыток входа
4. Двухфакторная аутентификация (опционально)

## Обработка медиа-файлов

### Хранение и обработка изображений

1. **Хранение**:
   - Использование S3-совместимого хранилища или локального хранилища
   - CDN для быстрой доставки контента
   - Использование уникальных имен файлов для предотвращения конфликтов

2. **Обработка**:
   - Изменение размера изображений для создания миниатюр
   - Оптимизация изображений для уменьшения размера
   - Валидация типа файла и его содержимого
   - Лимиты на размер и количество загружаемых файлов

3. **Видео**:
   - Транскодирование видео в различные форматы и качество
   - Создание превью для видео

## Обеспечение производительности

### Кэширование

1. **Кэширование данных**:
   - Использование Redis/Memcached для кэширования
   - Кэширование популярных запросов (профили, посты, комментарии)
   - Инвалидация кэша при изменении данных

2. **N+1 проблема**:
   - Использование eager loading для связанных сущностей
   - Оптимизация запросов с помощью индексов

3. **Пагинация**:
   - Реализация курсорной пагинации для больших наборов данных
   - Ограничение размера страницы (по умолчанию 10-20 элементов)

## Реализация уведомлений

### Типы уведомлений

1. **Push-уведомления**:
   - Использование Firebase Cloud Messaging или OneSignal
   - Поддержка браузерных уведомлений

2. **Email-уведомления**:
   - Транзакционные email-сервисы (Sendgrid, Mailgun и т.д.)
   - Шаблоны для различных типов уведомлений

3. **Уведомления в реальном времени**:
   - WebSocket для мгновенной доставки
   - Fallback на polling для устаревших браузеров

### Обработка уведомлений

1. **Очередь уведомлений**:
   - Использование очередей сообщений (RabbitMQ, Redis Streams)
   - Асинхронная обработка для улучшения производительности

2. **Группировка уведомлений**:
   - Объединение схожих уведомлений для уменьшения шума
   - Агрегация уведомлений по типам

## Работа с сообщениями

### Архитектура чата

1. **WebSocket**:
   - Использование Socket.io или аналогичных библиотек
   - Fallback на HTTP Long Polling

2. **Хранение сообщений**:
   - Хранение сообщений в базе данных
   - Архивирование старых сообщений

3. **Функциональность**:
   - Индикатор "печатает..."
   - Статус прочтения сообщений
   - Уведомления о новых сообщениях

## Безопасность

### OWASP Top 10

1. **SQL Injection**:
   - Использование ORM или подготовленных запросов
   - Параметризованные запросы

2. **XSS (Cross-Site Scripting)**:
   - Санитизация входных данных
   - CSP (Content Security Policy)

3. **CSRF (Cross-Site Request Forgery)**:
   - CSRF-токены
   - Same-Site куки

4. **Контроль доступа**:
   - Проверка авторизации для всех запросов
   - Ограничение числа запросов (Rate Limiting)

5. **Безопасные заголовки**:
   - HTTPS everywhere
   - X-Content-Type-Options: nosniff
   - X-Frame-Options: DENY
   - X-XSS-Protection: 1; mode=block 