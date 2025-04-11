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
10. [Эндпоинты настроек](#эндпоинты-настроек)

## Введение

Данный документ содержит подробное описание API-эндпоинтов для проекта WallOne. Эти эндпоинты необходимы для корректной работы фронтенд-части приложения.

Базовый URL API: `http://test/api/`

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
    "last_page": 10
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
    "password_confirmation": "string"
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
          "path": "string"
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
    "password": "string"
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
          "path": "string"
        },
        "wallet": {
          "balance": "100.00"
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

### Обновление токена

- **URL**: `/auth/refresh`
- **Метод**: `POST`
- **Требуются данные**:
  ```json
  {
    "refresh_token": "string"
  }
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "access_token": "string",
      "refresh_token": "string",
      "expires_in": 3600
    },
    "message": "Токен обновлен успешно"
  }
  ```

### Получение информации о пользователе

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
        "path": "string"
      },
      "wallet": {
        "balance": "100.00"
      }
    }
  }
  ```

## Эндпоинты пользователей

### Подписка на пользователя

- **URL**: `/user/follow/{user_id}`
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

- **URL**: `/user/follow/{user_id}`
- **Метод**: `DELETE`
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

### Получение списка подписчиков

- **URL**: `/user/{user_id}/followers`
- **Метод**: `GET`
- **Параметры запроса**: `page=1&per_page=10`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "username": "string",
        "verification": false,
        "avatar": {
          "path": "string"
        }
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

### Получение списка подписок

- **URL**: `/user/{user_id}/following`
- **Метод**: `GET`
- **Параметры запроса**: `page=1&per_page=10`
- **Ответ**: Аналогично списку подписчиков

## Эндпоинты постов

### Получение ленты постов

- **URL**: `/posts`
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
        "title": "string",
        "slug": "string",
        "content": "string",
        "media": [
          {
            "type": "group",
            "group": [
              {
                "type": "image",
                "src": "string"
              }
            ]
          }
        ],
        "user": {
          "username": "string",
          "slug": "string",
          "verification": false,
          "avatar": {
            "path": "string"
          }
        },
        "is_adult_content": false,
        "is_nsfl_content": false,
        "is_free": true,
        "has_copyright": false,
        "created_at": "string",
        "updated_at": "string"
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

### Получение постов пользователя

- **URL**: `/user/{user_id}/posts`
- **Метод**: `GET`
- **Параметры запроса**: `page=1&per_page=10`
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**: Аналогично ленте постов

### Создание поста

- **URL**: `/user/posts`
- **Метод**: `POST`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Content-Type**: `multipart/form-data`
- **Требуются данные**:
  ```
  content: string
  media[]: File (изображения/видео)
  is_adult_content: boolean (опционально)
  is_nsfl_content: boolean (опционально)
  has_copyright: boolean (опционально)
  ```
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "title": "string",
      "slug": "string",
      "content": "string",
      "media": [...],
      "user": {...},
      "is_adult_content": false,
      "is_nsfl_content": false,
      "is_free": true,
      "has_copyright": false,
      "created_at": "string",
      "updated_at": "string"
    },
    "message": "Пост успешно создан"
  }
  ```

### Получение отдельного поста

- **URL**: `/posts/{post_id}`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "id": 1,
      "title": "string",
      "slug": "string",
      "content": "string",
      "media": [...],
      "user": {...},
      "is_adult_content": false,
      "is_nsfl_content": false,
      "is_free": true,
      "has_copyright": false,
      "created_at": "string",
      "updated_at": "string"
    }
  }
  ```

### Удаление поста

- **URL**: `/posts/{post_id}`
- **Метод**: `DELETE`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Пост успешно удален"
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

## Эндпоинты сообщений

### Получение списка чатов

- **URL**: `/messages/chats`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": [
      {
        "id": 1,
        "user": {
          "id": 2,
          "username": "string",
          "verification": false,
          "avatar": {
            "path": "string"
          },
          "online": {
            "is_online": true,
            "last_activity": "string"
          }
        },
        "last_message": {
          "content": "string",
          "created_at": "string",
          "is_read": false
        },
        "unread_count": 5
      }
    ]
  }
  ```

### Получение сообщений чата

- **URL**: `/messages/{user_id}`
- **Метод**: `GET`
- **Параметры запроса**: `page=1&per_page=20`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "user": {
        "id": 2,
        "username": "string",
        "verification": false,
        "avatar": {
          "path": "string"
        },
        "online": {
          "is_online": true,
          "last_activity": "string"
        }
      },
      "messages": [
        {
          "id": 1,
          "sender_id": 1,
          "recipient_id": 2,
          "content": "string",
          "is_read": true,
          "created_at": "string"
        }
      ]
    },
    "pagination": {
      "total": 100,
      "per_page": 20,
      "current_page": 1,
      "last_page": 5
    }
  }
  ```

### Отправка сообщения

- **URL**: `/messages/{user_id}`
- **Метод**: `POST`
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
      "sender_id": 1,
      "recipient_id": 2,
      "content": "string",
      "is_read": false,
      "created_at": "string"
    },
    "message": "Сообщение отправлено"
  }
  ```

### Отметка сообщений как прочитанные

- **URL**: `/messages/{user_id}/read`
- **Метод**: `PATCH`
- **Заголовки**: `Authorization: Bearer {access_token}`
- **Ответ**:
  ```json
  {
    "success": true,
    "message": "Сообщения отмечены как прочитанные"
  }
  ```

## Эндпоинты профиля

### Получение профиля пользователя

- **URL**: `/profile/{slug}`
- **Метод**: `GET`
- **Заголовки**: `Authorization: Bearer {access_token}` (опционально)
- **Ответ**:
  ```json
  {
    "success": true,
    "data": {
      "user": {
        "id": 1,
        "username": "string",
        "slug": "string",
        "description": "string",
        "verification": false,
        "avatars": [
          {
            "path": "string"
          }
        ],
        "specializations": [
          {
            "id": 1,
            "name": {
              "ru": "string",
              "en": "string"
            }
          }
        ],
        "badges": [
          {
            "id": 1,
            "name": {
              "ru": "string",
              "en": "string"
            },
            "description": {
              "ru": "string",
              "en": "string"
            },
            "icon": "string"
          }
        ],
        "followers_count": 100,
        "following_count": 50,
        "experience": 1000,
        "language": "ru",
        "online": {
          "is_online": true,
          "last_activity": "string"
        },
        "user_setting": {
          "is_private": 0,
          "show_online_status": 1,
          "enable_two_factor": 0
        },
        "additional_data": {
          "balance": [
            {
              "amount": "100.00",
              "currency": "RUB"
            }
          ],
          "tasks": [
            {
              "id": 1,
              "title": "string",
              "description": "string",
              "reward": "10.00",
              "pivot": {
                "completed": true
              }
            }
          ]
        }
      },
      "relationship": {
        "is_following": false,
        "is_followed_by": false
      },
      "is_my_profile": false
    }
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