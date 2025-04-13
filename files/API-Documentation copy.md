# API Документация - WallOne

## Оглавление

1. [Введение](#введение)
2. [Общие принципы API](#общие-принципы-api)
3. [Авторизация и аутентификация](#авторизация-и-аутентификация)
4. [Эндпоинты пользователей](#эндпоинты-пользователей)
5. [Эндпоинты постов](#эндпоинты-постов)
6. [Эндпоинты комментариев](#эндпоинты-комментариев)
7. [Эндпоинты уведомлений](#эндпоинты-уведомлений)
8. [Эндпоинты сообщений](#эндпоинты-сообщений)
9. [Эндпоинты профиля](#эндпоинты-профиля)
10. [Эндпоинты челленджей](#эндпоинты-челленджей)
11. [Эндпоинты художников](#эндпоинты-художников)
12. [Эндпоинты поиска](#эндпоинты-поиска)
13. [Эндпоинты черновиков](#эндпоинты-черновиков)
14. [Эндпоинты настроек](#эндпоинты-настроек)
15. [Эндпоинты статических страниц](#эндпоинты-статических-страниц)
16. [Эндпоинты медиа файлов](#эндпоинты-медиа-файлов)
17. [Эндпоинты инициализации](#эндпоинты-инициализации)
18. [Эндпоинты категорий и тегов](#эндпоинты-категорий-и-тегов)
19. [Дополнительные эндпоинты сообщений](#дополнительные-эндпоинты-сообщений)
20. [Дополнительные эндпоинты уведомлений](#дополнительные-эндпоинты-уведомлений)

## Введение

Данный документ содержит подробное описание API-эндпоинтов для проекта WallOne. Эти эндпоинты необходимы для корректной работы фронтенд-части приложения.

Базовый URL API: `http://test.wallone.app/api/v1`

**Примечание:** В зависимости от конфигурации, некоторые эндпоинты могут иметь префикс `/api/` (например, `/api/posts` вместо `/posts`). В документации указаны пути без учета этого префикса, если не указано иное. В таких случаях обязательно обратите внимание на конкретные примеры использования в коде.

**Важно:** В текущей реализации проекта есть два типа эндпоинтов:
1. Основные API эндпоинты (без префикса `/api/`), которые обращаются напрямую к внешнему бэкенду.
2. Проксированные эндпоинты (с префиксом `/api/`), которые проходят через Nuxt сервер и затем перенаправляются на внешний бэкенд.

Следовательно, один и тот же функционал может быть доступен через разные пути в зависимости от контекста (например, `/posts` или `/api/posts`). Документация старается учитывать оба случая.

## Общие принципы API

### Формат ответов

Все API-ответы возвращаются в формате JSON со следующей структурой:

```json
{
  "success": true/false,
  "data": {...} или [...],
  "message": "Сообщение об успехе или ошибке",
  "errors": {...} // В случае ошибок валидации
}
```

### Коды ответов

- 200 OK - Запрос обработан успешно
- 201 Created - Ресурс успешно создан
- 400 Bad Request - Неверный запрос
- 401 Unauthorized - Не авторизован
- 403 Forbidden - Доступ запрещен
- 404 Not Found - Ресурс не найден
- 422 Unprocessable Entity - Ошибка валидации
- 429 Too Many Requests - Превышен лимит запросов
- 500 Server Error - Внутренняя ошибка сервера

### Пагинация

Для списков элементов используется пагинация со следующими параметрами:

```json
{
  "success": true,
  "data": [...],
  "pagination": {
    "total": 100,
    "per_page": 10,
    "current_page": 1,
    "last_page": 10,
    "from": 1,
    "to": 10
  }
}
```

## Авторизация и аутентификация

### Регистрация

- **URL**: `/auth/register`
- **Метод**: `POST`
- **Требуются данные**:
  ```json
  {
    "username": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string",
    "terms_accepted": true
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "user": {
        "id": 1,
        "username": "string",
        "email": "string",
        "verification": false,
        "avatar": {
          "path": "string",
          "thumbnail": "string"
        }
      },
      "token": {
        "access_token": "string",
        "refresh_token": "string",
        "expires_in": 3600
      }
    },
    "message": "Регистрация успешно завершена"
  }
  ```

### Вход

- **URL**: `/auth/login`
- **Метод**: `POST`
- **Требуются данные**:
  ```json
  {
    "email": "string",
    "password": "string",
    "remember_me": boolean
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "user": {
        "id": 1,
        "username": "string",
        "email": "string",
        "verification": false,
        "avatar": {
          "path": "string",
          "thumbnail": "string"
        },
        "wallet": {
          "balance": "100.00",
          "currency": "USD"
        }
      },
      "token": {
        "access_token": "string",
        "refresh_token": "string",
        "expires_in": 3600
      }
    },
    "message": "Вход выполнен успешно"
  }
  ```

### Восстановление пароля

- **URL**: `/auth/forgot-password`
- **Метод**: `POST`
- **Требуются данные**:
  ```json
  {
    "email": "string"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Инструкции по восстановлению пароля отправлены на ваш email"
  }
  ```

### Сброс пароля

- **URL**: `/auth/reset-password`
- **Метод**: `POST`
- **Требуются данные**:
  ```json
  {
    "token": "string",
    "email": "string",
    "password": "string",
    "password_confirmation": "string"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Пароль успешно изменен"
  }
  ```

### Выход

- **URL**: `/auth/logout`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Выход выполнен успешно"
  }
  ```

## Эндпоинты пользователей

### Получение профиля пользователя

- **URL**: `/users/{username}`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "username": "string",
      "display_name": "string",
      "bio": "string",
      "verification": false,
      "avatar": {
        "path": "string",
        "thumbnail": "string"
      },
      "cover": {
        "path": "string"
      },
      "stats": {
        "followers_count": 100,
        "following_count": 50,
        "posts_count": 25,
        "likes_received": 1000
      },
      "social_links": {
        "website": "string",
        "twitter": "string",
        "instagram": "string",
        "facebook": "string"
      },
      "is_following": false,
      "is_blocked": false,
      "created_at": "string"
    }
  }
  ```

### Получение информации о текущем пользователе

- **URL**: `/auth/me`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "username": "string",
      "email": "string",
      "verification": false,
      "avatar": {
        "path": "string",
        "thumbnail": "string"
      },
      "settings": {
        "notification_email": boolean,
        "notification_push": boolean
      }
    }
  }
  ```

### Подписка на пользователя

- **URL**: `/users/{username}/follow`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "is_following": true
    },
    "message": "Вы успешно подписались на пользователя"
  }
  ```

### Отписка от пользователя

- **URL**: `/users/{username}/unfollow`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "is_following": false
    },
    "message": "Вы успешно отписались от пользователя"
  }
  ```

## Эндпоинты постов

### Получение ленты постов

- **URL**: `/posts`
- **Метод**: `GET`
- **Параметры запроса**: 
  ```
  page: number
  per_page: number
  filter: 'latest' | 'trending' | 'following'
  category: string
  nsfw: boolean
  media_type: 'all' | 'image' | 'video' | 'gif'
  ```
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "title": "string",
        "description": "string",
        "media": [
          {
            "type": "image",
            "url": "string",
            "thumbnail": "string",
            "width": 1920,
            "height": 1080
          }
        ],
        "user": {
          "username": "string",
          "display_name": "string",
          "verification": false,
          "avatar": {
            "path": "string",
            "thumbnail": "string"
          }
        },
        "stats": {
          "likes_count": 100,
          "comments_count": 50,
          "views_count": 1000
        },
        "tags": ["string"],
        "is_nsfw": false,
        "is_liked": false,
        "created_at": "string"
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 10,
      "current_page": 1,
      "last_page": 10,
      "from": 1,
      "to": 10
    }
  }
  ```

### Создание поста

- **URL**: `/posts`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Content-Type**: `multipart/form-data`
- **Требуются данные**:
  ```
  title: string
  description: string (опционально)
  media[]: File[]
  tags[]: string[]
  is_nsfw: boolean
  category: string
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "title": "string",
      "description": "string",
      "media": [...],
      "user": {...},
      "stats": {...},
      "tags": ["string"],
      "is_nsfw": false,
      "created_at": "string"
    },
    "message": "Пост успешно создан"
  }
  ```

### Получение поста

- **URL**: `/posts/{id}`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "title": "string",
      "description": "string",
      "media": [...],
      "user": {...},
      "stats": {...},
      "tags": ["string"],
      "is_nsfw": false,
      "is_liked": false,
      "comments": [...],
      "created_at": "string"
    }
  }
  ```

## Эндпоинты челленджей

### Получение списка челленджей

- **URL**: `/challenges`
- **Метод**: `GET`
- **Параметры запроса**:
  - `per_page` (необязательно): количество элементов на странице
  - `status` (необязательно): фильтр по статусу (`active`, `completed`, `draft`, `cancelled`)
  - `search` (необязательно): поисковый запрос
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "title": "string",
        "description": "string",
        "cover": {
          "path": "string"
        },
        "prize_pool": {
          "amount": "1000.00",
          "currency": "USD"
        },
        "participants_count": 100,
        "start_date": "string",
        "end_date": "string",
        "status": "active",
        "is_participating": false
      }
    ]
  }
  ```

### Получение деталей челленджа

- **URL**: `/challenges/{id}`
- **Метод**: `GET`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "title": "string",
      "description": "string",
      "cover": {
        "path": "string"
      },
      "prize_pool": {
        "amount": "1000.00",
        "currency": "USD"
      },
      "participants_count": 100,
      "start_date": "string",
      "end_date": "string",
      "status": "active",
      "is_participating": false,
      "created_at": "string",
      "updated_at": "string"
    }
  }
  ```

### Создание челленджа

- **URL**: `/challenges`
- **Метод**: `POST`
- **Заголовки**: 
  ```
  Authorization: Bearer {access_token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Требуются данные**:
  ```json
  {
    "title": "string",
    "description": "string",
    "cover_path": "string",
    "prize_amount": "1000.00",
    "prize_currency": "USD",
    "start_date": "2024-05-01 00:00:00",
    "end_date": "2024-06-01 00:00:00",
    "status": "draft"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "title": "string",
      "description": "string",
      "cover": {
        "path": "string"
      },
      "prize_pool": {
        "amount": "1000.00",
        "currency": "USD"
      },
      "participants_count": 0,
      "start_date": "2024-05-01 00:00:00",
      "end_date": "2024-06-01 00:00:00",
      "status": "draft",
      "is_participating": false,
      "created_at": "string",
      "updated_at": "string"
    }
  }
  ```

### Обновление челленджа

- **URL**: `/challenges/{id}`
- **Метод**: `PUT`
- **Заголовки**: 
  ```
  Authorization: Bearer {access_token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Требуются данные**:
  ```json
  {
    "title": "string",
    "description": "string",
    "cover_path": "string",
    "prize_amount": "1000.00",
    "prize_currency": "USD",
    "start_date": "2024-05-01 00:00:00",
    "end_date": "2024-06-01 00:00:00",
    "status": "active"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "title": "string",
      "description": "string",
      "cover": {
        "path": "string"
      },
      "prize_pool": {
        "amount": "1000.00",
        "currency": "USD"
      },
      "participants_count": 0,
      "start_date": "2024-05-01 00:00:00",
      "end_date": "2024-06-01 00:00:00",
      "status": "active",
      "is_participating": false,
      "created_at": "string",
      "updated_at": "string"
    }
  }
  ```

### Удаление челленджа

- **URL**: `/challenges/{id}`
- **Метод**: `DELETE`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Челлендж успешно удален"
  }
  ```

### Получение активных челленджей

- **URL**: `/challenges/active`
- **Метод**: `GET`
- **Параметры запроса**:
  - `per_page` (необязательно): количество элементов на странице
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "title": "string",
        "description": "string",
        "cover": {
          "path": "string"
        },
        "prize_pool": {
          "amount": "1000.00",
          "currency": "USD"
        },
        "participants_count": 100,
        "start_date": "string",
        "end_date": "string",
        "status": "active",
        "is_participating": false
      }
    ]
  }
  ```

### Получение челленджей пользователя

- **URL**: `/challenges/user`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Параметры запроса**:
  - `per_page` (необязательно): количество элементов на странице
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "title": "string",
        "description": "string",
        "cover": {
          "path": "string"
        },
        "prize_pool": {
          "amount": "1000.00",
          "currency": "USD"
        },
        "participants_count": 100,
        "start_date": "string",
        "end_date": "string",
        "status": "active",
        "is_participating": true
      }
    ]
  }
  ```

### Присоединение к челленджу

- **URL**: `/challenges/{id}/join`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные** (необязательно):
  ```json
  {
    "submission_data": {
      "comment": "string",
      "additional_info": "string"
    }
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Вы успешно присоединились к челленджу"
  }
  ```

### Выход из челленджа

- **URL**: `/challenges/{id}/leave`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Вы успешно покинули челлендж"
  }
  ```

## Эндпоинты художников

### Получение списка художников

- **URL**: `/artists`
- **Метод**: `GET`
- **Параметры запроса**: 
  ```
  page: number
  per_page: number
  sort: 'popular' | 'new' | 'trending'
  category: string
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "username": "string",
        "display_name": "string",
        "avatar": {
          "path": "string",
          "thumbnail": "string"
        },
        "cover": {
          "path": "string"
        },
        "stats": {
          "followers_count": 100,
          "posts_count": 50,
          "likes_received": 1000
        },
        "featured_works": [
          {
            "id": 1,
            "thumbnail": "string"
          }
        ],
        "is_following": false,
        "verification": false
      }
    ],
    "pagination": {...}
  }
  ```

## Эндпоинты поиска

### Глобальный поиск

- **URL**: `/search`
- **Метод**: `GET`
- **Параметры запроса**: 
  ```
  q: string
  type: 'all' | 'posts' | 'users' | 'tags'
  page: number
  per_page: number
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "posts": {
        "items": [...],
        "pagination": {...}
      },
      "users": {
        "items": [...],
        "pagination": {...}
      },
      "tags": {
        "items": [...],
        "pagination": {...}
      }
    }
  }
  ```

### Поиск постов

- **URL**: `/search/posts`
- **Метод**: `GET`
- **Параметры запроса**: 
  ```
  query: string
  page: number
  per_page: number
  ```
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "title": "string",
        "slug": "string",
        "relevance_score": 10,
        "media": [...],
        "user": {
          "username": "string",
          "slug": "string",
          "verification": boolean,
          "avatar": {
            "path": "string"
          }
        },
        "created_at": "string"
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 10,
      "current_page": 1,
      "last_page": 10
    }
  }
  ```

### Поиск тегов

- **URL**: `/search/tags`
- **Метод**: `GET`
- **Параметры запроса**: 
  ```
  query: string
  page: number
  per_page: number
  ```
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "string",
        "name": "string",
        "posts_count": 100
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 10,
      "current_page": 1,
      "last_page": 10
    }
  }
  ```

### Поиск пользователей

- **URL**: `/search/users`
- **Метод**: `GET`
- **Параметры запроса**: 
  ```
  query: string
  page: number
  per_page: number
  ```
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "username": "string",
        "slug": "string",
        "verification": boolean,
        "avatar": {
          "path": "string"
        },
        "followers_count": 100
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 10,
      "current_page": 1,
      "last_page": 10
    }
  }
  ```

## Эндпоинты черновиков

### Получение списка черновиков

- **URL**: `/drafts`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": "string",
        "title": "string",
        "updated_at": "string"
      }
    ]
  }
  ```

### Получение черновика

- **URL**: `/drafts/{draft_id}`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": "string",
      "title": "string",
      "content": "string",
      "category_id": "string",
      "is_adult_content": boolean,
      "is_nsfl_content": boolean,
      "has_copyright": boolean,
      "is_free": boolean,
      "price": number,
      "tags_id": ["string"],
      "apps_id": ["string"],
      "media": ["string"],
      "media_files": [
        {
          "id": "string",
          "path": "string",
          "type": "string",
          "status": "string",
          "thumbnail": "string"
        }
      ],
      "updated_at": "string",
      "created_at": "string"
    }
  }
  ```

### Создание черновика

- **URL**: `/drafts`
- **Метод**: `POST`
- **Заголовки**: 
  ```
  Authorization: Bearer {access_token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Требуются данные**:
  ```json
  {
    "title": "string",
    "content": "string",
    "category_id": "string",
    "is_adult_content": boolean,
    "is_nsfl_content": boolean,
    "has_copyright": boolean,
    "is_free": boolean,
    "price": number,
    "tags_id": ["string"],
    "apps_id": ["string"],
    "media": ["string"]
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": "string"
    },
    "message": "Черновик успешно создан"
  }
  ```

### Обновление черновика

- **URL**: `/drafts/{draft_id}`
- **Метод**: `PUT`
- **Заголовки**: 
  ```
  Authorization: Bearer {access_token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Требуются данные**:
  ```json
  {
    "title": "string",
    "content": "string",
    "category_id": "string",
    "is_adult_content": boolean,
    "is_nsfl_content": boolean,
    "has_copyright": boolean,
    "is_free": boolean,
    "price": number,
    "tags_id": ["string"],
    "apps_id": ["string"],
    "media": ["string"]
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": "string"
    },
    "message": "Черновик успешно обновлен"
  }
  ```

### Удаление черновика

- **URL**: `/drafts/{draft_id}`
- **Метод**: `DELETE`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Черновик успешно удален"
  }
  ```

## Эндпоинты настроек

### Обновление профиля

- **URL**: `/user/profile`
- **Метод**: `PATCH`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Content-Type**: `multipart/form-data`
- **Требуются данные**:
  ```
  username: string (опционально)
  bio: string (опционально)
  website: string (опционально)
  avatar: File (опционально)
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "username": "string",
      "bio": "string",
      "website": "string",
      "avatar": {
        "path": "string"
      }
    },
    "message": "Профиль успешно обновлен"
  }
  ```

### Обновление аккаунта

- **URL**: `/user/account`
- **Метод**: `PATCH`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные**:
  ```json
  {
    "email": "string",
    "current_password": "string",
    "new_password": "string",
    "new_password_confirmation": "string"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Данные аккаунта успешно обновлены"
  }
  ```

### Обновление настроек

- **URL**: `/user/settings`
- **Метод**: `PATCH`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные**:
  ```json
  {
    "is_private": 0,
    "show_online_status": 1,
    "enable_two_factor": 0
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "is_private": 0,
      "show_online_status": 1,
      "enable_two_factor": 0
    },
    "message": "Настройки успешно обновлены"
  }
  ```

### Обновление настроек уведомлений

- **URL**: `/user/notification-settings`
- **Метод**: `PATCH`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные**:
  ```json
  {
    "push_enabled": true,
    "email_enabled": true,
    "likes_notifications": true,
    "comments_notifications": true,
    "followers_notifications": true,
    "messages_notifications": true
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "push_enabled": true,
      "email_enabled": true,
      "likes_notifications": true,
      "comments_notifications": true,
      "followers_notifications": true,
      "messages_notifications": true
    },
    "message": "Настройки уведомлений успешно обновлены"
  }
  ```

### Удаление аккаунта

- **URL**: `/user/account`
- **Метод**: `DELETE`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные**:
  ```json
  {
    "password": "string"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Аккаунт успешно удален"
  }
  ```

## Эндпоинты статических страниц

### Получение статической страницы

- **URL**: `/pages/{slug}`
- **Метод**: `GET`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "title": "string",
      "content": "string",
      "meta": {
        "description": "string",
        "keywords": "string"
      },
      "last_updated": "string"
    }
  }
  ```

## Эндпоинты комментариев

### Получение комментариев к посту

- **URL**: `/posts/{post_id}/comments`
- **Метод**: `GET`
- **Параметры запроса**: `page=1&per_page=10`
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "content": "string",
        "user": {
          "id": 1,
          "username": "string",
          "verification": false,
          "avatar": {
            "path": "string"
          }
        },
        "parent_id": null,
        "likes_count": 10,
        "created_at": "string",
        "updated_at": "string",
        "replies": [
          {
            "id": 2,
            "content": "string",
            "user": {...},
            "parent_id": 1,
            "likes_count": 5,
            "created_at": "string",
            "updated_at": "string"
          }
        ]
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 10,
      "current_page": 1,
      "last_page": 10
    }
  }
  ```

### Добавление комментария

- **URL**: `/posts/{post_id}/comments`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные**:
  ```json
  {
    "content": "string",
    "parent_id": null // опционально для ответов на комментарии
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "content": "string",
      "user": {...},
      "parent_id": null,
      "likes_count": 0,
      "created_at": "string",
      "updated_at": "string"
    },
    "message": "Комментарий успешно добавлен"
  }
  ```

### Редактирование комментария

- **URL**: `/posts/{post_id}/comments/{comment_id}`
- **Метод**: `PATCH`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные**:
  ```json
  {
    "content": "string"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "content": "string",
      "user": {...},
      "parent_id": null,
      "likes_count": 0,
      "created_at": "string",
      "updated_at": "string"
    },
    "message": "Комментарий успешно обновлен"
  }
  ```

### Удаление комментария

- **URL**: `/posts/{post_id}/comments/{comment_id}`
- **Метод**: `DELETE`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Комментарий успешно удален"
  }
  ```

### Оценка комментария (лайк/дизлайк)

- **URL**: `/posts/{post_id}/comments/{comment_id}/react`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные**:
  ```json
  {
    "type": "like" // или "dislike"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "likes_count": 11
    },
    "message": "Реакция успешно добавлена"
  }
  ```

### Жалоба на комментарий

- **URL**: `/posts/{post_id}/comments/{comment_id}/report`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Требуются данные**:
  ```json
  {
    "reason": "string"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Жалоба успешно отправлена"
  }
  ```

## Эндпоинты уведомлений

### Получение уведомлений

- **URL**: `/notifications`
- **Метод**: `GET`
- **Параметры запроса**: `page=1&per_page=10`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "type": "like",
        "data": {
          "user": {
            "id": 2,
            "username": "string",
            "avatar": {
              "path": "string"
            }
          },
          "post_id": 1
        },
        "read_at": null,
        "created_at": "string"
      }
    ],
    "pagination": {
      "total": 100,
      "per_page": 10,
      "current_page": 1,
      "last_page": 10
    }
  }
  ```

### Получение непрочитанных уведомлений

- **URL**: `/notifications/unread`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "count": 5,
      "notifications": [...]
    }
  }
  ```

### Отметка уведомления как прочитанного

- **URL**: `/notifications/{notification_id}/read`
- **Метод**: `PATCH`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Уведомление отмечено как прочитанное"
  }
  ```

### Отметка всех уведомлений как прочитанные

- **URL**: `/notifications/read-all`
- **Метод**: `PATCH`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Все уведомления отмечены как прочитанные"
  }
  ```

## Дополнительные эндпоинты сообщений

### Получение списка сообщений

- **URL**: `/api/messages`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "conversation_id": 1,
        "preview": "string",
        "read": false,
        "created_at": "string",
        "from": {
          "username": "string",
          "avatar": {
            "path": "string"
          }
        }
      }
    ]
  }
  ```

### Отметка сообщения как прочитанное

- **URL**: `/api/messages/{message_id}/read`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Сообщение отмечено как прочитанное"
  }
  ```

### Получение сообщений чата

- **URL**: `/api/messages/{conversation_id}`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "user": {
        "id": 2,
        "username": "string",
        "avatar": {
          "path": "string"
        },
        "is_online": true,
        "last_seen": "string"
      },
      "messages": [
        {
          "id": 1,
          "content": "string",
          "is_my_message": boolean,
          "created_at": "string",
          "is_read": boolean
        }
      ]
    }
  }
  ```

### Отправка сообщения в чат

- **URL**: `/api/messages/{conversation_id}/send`
- **Метод**: `POST`
- **Заголовки**: 
  ```
  Authorization: Bearer {access_token}
  Content-Type: application/json
  Accept: application/json
  ```
- **Требуются данные**:
  ```json
  {
    "content": "string"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "content": "string",
      "is_my_message": true,
      "created_at": "string",
      "is_read": false
    },
    "message": "Сообщение отправлено"
  }
  ```

### Отметка всех сообщений в чате как прочитанные

- **URL**: `/api/messages/{conversation_id}/read-all`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Все сообщения отмечены как прочитанные"
  }
  ```

## Дополнительные эндпоинты уведомлений

### Получение уведомлений

- **URL**: `/api/notifications`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "type": "post_like",
        "message": "string",
        "read": false,
        "created_at": "string",
        "data": {
          "post_id": 1,
          "comment_id": 1
        },
        "from": {
          "username": "string",
          "avatar": {
            "path": "string"
          }
        }
      }
    ]
  }
  ```

### Отметка уведомления как прочитанное

- **URL**: `/api/notifications/{notification_id}/read`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Уведомление отмечено как прочитанное"
  }
  ``` 