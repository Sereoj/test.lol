# Документация по маршрутам API

## Общая структура API

API приложения имеет четкую структуру с версионированием и разделением по уровням доступа. Все маршруты имеют префикс `/api`, который устанавливается автоматически через `RouteServiceProvider`.

### Версионирование

API использует версионирование в URL-адресах, что позволяет поддерживать разные версии API одновременно. Текущая версия API: `v1`.

Пример URL:
```
https://domain.com/api/v1/posts
```

### Разделение на группы доступа

Все маршруты API разделены на три основные группы:

1. **Гостевые маршруты** (`guest.php`) - доступны без авторизации
2. **Маршруты авторизованных пользователей** (`auth.php`) - требуют авторизации пользователя
3. **Административные маршруты** (`admin.php`) - требуют авторизации пользователя с ролью администратора

## Гостевые маршруты (Guest Routes)

Эти маршруты доступны без авторизации.

### Аутентификация

| Метод | URL                            | Контроллер                                      | Название маршрута       | Описание                                |
|-------|--------------------------------|------------------------------------------------|------------------------|----------------------------------------|
| GET   | `/auth/redirect/{provider}`    | `SocialiteController@redirectToProvider`       | `auth.redirect`        | Перенаправление на провайдер OAuth      |
| GET   | `/auth/callback/{provider}`    | `SocialiteController@handleProviderCallback`   | `auth.callback`        | Обработка ответа от провайдера OAuth    |
| POST  | `/auth/register`               | `AuthController@register`                      | `register.public`      | Регистрация нового пользователя         |
| POST  | `/auth/login`                  | `AuthController@login`                         | `login.public`         | Авторизация пользователя               |
| POST  | `/refresh-token`               | `AuthController@refreshToken`                  | `refresh-token`        | Обновление токена доступа              |
| POST  | `/send-verification-code`      | `EmailVerificationController@sendVerificationCode` | `send.verification.code` | Отправка кода верификации          |
| POST  | `/verify-email`                | `EmailVerificationController@verifyEmail`      | `verify.email`         | Подтверждение email                    |
| POST  | `/password/reset/email`        | `PasswordResetController@sendPasswordResetEmail` | `password.reset.email` | Отправка email для сброса пароля     |
| POST  | `/password/reset`              | `PasswordResetController@resetPassword`        | `password.reset`       | Сброс пароля                           |

### Общедоступные маршруты

| Метод | URL                         | Контроллер                           | Название маршрута         | Описание                                 |
|-------|-----------------------------|-----------------------------------------|--------------------------|------------------------------------------|
| GET   | `/init`                     | `InitController@init`                   | `init.public`            | Инициализация приложения                |
| POST  | `/language`                 | `UserLanguageController@setLanguage`    | `set.language`           | Установка языка                          |
| GET   | `/search`                   | `PostSearchController@search`           | `posts.search.public`    | Поиск постов                             |
| GET   | `/search/suggest`           | `PostSearchController@suggest`          | `posts.suggest.public`   | Получение поисковых подсказок           |
| GET   | `/posts`                    | `PostController@index`                  | `posts.index.public`     | Получение списка публичных постов       |
| GET   | `/posts/{id}`               | `PostController@show`                   | `posts.show.public`      | Получение конкретного публичного поста   |
| GET   | `/posts/{post_id}/comments` | `CommentController@index`               | `comments.index.public`  | Получение комментариев к посту          |
| GET   | `/profile/{slug}`           | `UserProfileController@show`            | `profile.show.public`    | Просмотр публичного профиля пользователя |

## Маршруты авторизованных пользователей (Auth Routes)

Эти маршруты требуют авторизации через `auth:api` middleware.

### Аутентификация

| Метод | URL                     | Контроллер                    | Название маршрута | Описание                                |
|-------|-------------------------|---------------------------------|-------------------|----------------------------------------|
| POST  | `/auth/logout`          | `AuthController@logout`         | `logout`          | Выход из системы                       |
| GET   | `/auth/me`              | `AuthController@user`           | `auth.me`         | Получение данных текущего пользователя |
| POST  | `/auth/step/one`        | `StepController@one`            | `step.one`        | Первый шаг регистрации                 |
| POST  | `/auth/step/two`        | `StepController@two`            | `step.two`        | Второй шаг регистрации                 |
| POST  | `/auth/step/three`      | `StepController@three`          | `step.three`      | Третий шаг регистрации                 |

### Управление профилем

| Метод  | URL                            | Контроллер                                | Название маршрута                 | Описание                              |
|--------|--------------------------------|-------------------------------------------|-----------------------------------|---------------------------------------|
| GET    | `/user`                        | `AuthController@user`                     | `user.user`                       | Получение данных пользователя          |
| GET    | `/profile/{slug}`              | `UserProfileController@show`              | `profile.show`                    | Просмотр профиля                       |
| PATCH  | `/profile`                     | `UserProfileController@update`            | `profile.update`                  | Обновление профиля                     |
| PATCH  | `/user/account`                | `UserAccountController@update`            | `user.account.update`             | Обновление аккаунта                    |
| DELETE | `/user/account`                | `UserAccountController@destroy`           | `user.account.destroy`            | Удаление аккаунта                      |
| PATCH  | `/user/settings`               | `UserSettingsController@update`           | `user.settings.update`            | Обновление настроек пользователя       |
| PATCH  | `/user/notification-settings`  | `UserNotificationSettingsController@update` | `user.notification_settings.update` | Обновление настроек уведомлений     |

### Управление аватарами и медиа

| Метод  | URL                     | Контроллер                     | Название маршрута  | Описание                      |
|--------|-------------------------|--------------------------------|--------------------|-------------------------------|
| POST   | `/avatars`              | `AvatarController@uploadAvatar`| `avatars.upload`   | Загрузка аватара              |
| GET    | `/avatars`              | `AvatarController@getUserAvatars` | `avatars.get`  | Получение аватаров пользователя |
| DELETE | `/avatars/{avatarId}`   | `AvatarController@deleteAvatar`| `avatars.delete`  | Удаление аватара              |
| POST   | `/media`                | `MediaController@store`        | `media.store`      | Загрузка медиафайла           |
| GET    | `/media/{id}`           | `MediaController@show`         | `media.show`       | Получение медиафайла          |
| PUT    | `/media/{id}`           | `MediaController@update`       | `media.update`     | Обновление медиафайла         |
| DELETE | `/media/{id}`           | `MediaController@destroy`      | `media.destroy`    | Удаление медиафайла           |

### Посты и комментарии

| Метод  | URL                                  | Контроллер                         | Название маршрута      | Описание                        |
|--------|--------------------------------------|------------------------------------|-----------------------|--------------------------------|
| GET    | `/posts`                             | `PostController@index`             | `posts.index`         | Получение списка постов         |
| POST   | `/posts`                             | `PostController@store`             | `posts.store`         | Создание поста                  |
| GET    | `/posts/{id}`                        | `PostController@show`              | `posts.show`          | Получение поста                 |
| PUT    | `/posts/{id}`                        | `PostController@update`            | `posts.update`        | Обновление поста                |
| DELETE | `/posts/{id}`                        | `PostController@destroy`           | `posts.destroy`       | Удаление поста                  |
| POST   | `/posts/{id}/like`                   | `PostController@toggleLike`        | `posts.like`          | Лайк/дизлайк поста              |
| POST   | `/posts/{id}/repost`                 | `PostController@repost`            | `posts.repost`        | Репост                          |
| GET    | `/posts/{id}/download`               | `PostController@download`          | `post.download`       | Скачивание поста                |
| GET    | `/posts/statistics/{post}/summary`   | `PostStatisticController@getPostStatistics` | `posts.statistics.post` | Статистика по посту  |
| GET    | `/posts/statistics/summary`          | `PostStatisticController@summary`  | `posts.statistics.summary` | Сводная статистика      |
| GET    | `/posts/statistics/recent`           | `PostStatisticController@recent`   | `posts.statistics.recent` | Недавняя статистика       |
| GET    | `/posts/{post_id}/comments`          | `CommentController@index`          | `comments.index`      | Получение комментариев          |
| POST   | `/posts/{post_id}/comments`          | `CommentController@store`          | `comments.store`      | Создание комментария            |
| GET    | `/posts/{post_id}/comments/{id}`     | `CommentController@show`           | `comments.show`       | Получение комментария           |
| PATCH  | `/posts/{post_id}/comments/{id}`     | `CommentController@update`         | `comments.update`     | Обновление комментария          |
| DELETE | `/posts/{post_id}/comments/{id}`     | `CommentController@destroy`        | `comments.destroy`    | Удаление комментария            |
| POST   | `/posts/{post_id}/comments/{commentId}/react` | `CommentController@react` | `comments.react`      | Реакция на комментарий          |
| POST   | `/posts/{post_id}/comments/{commentId}/report` | `CommentController@report` | `comments.report`   | Жалоба на комментарий           |
| POST   | `/posts/{post_id}/comments/{commentId}/repost` | `CommentController@repost` | `comments.repost`   | Репост комментария              |

### Уведомления и сообщения

| Метод  | URL                             | Контроллер                          | Название маршрута        | Описание                         |
|--------|----------------------------------|-------------------------------------|--------------------------|---------------------------------|
| GET    | `/notifications`                 | `NotificationController@index`       | `notifications.index`    | Получение всех уведомлений       |
| GET    | `/notifications/unread`          | `NotificationController@unread`      | `notifications.unread`   | Получение непрочитанных уведомлений |
| PATCH  | `/notifications/{notification_id}/read` | `NotificationController@markAsRead` | `notifications.mark_read` | Отметка уведомления как прочитанного |
| PATCH  | `/notifications/read-all`        | `NotificationController@markAllAsRead` | `notifications.mark_all_read` | Отметка всех уведомлений как прочитанных |
| GET    | `/messages/chats`                | `MessageController@getChats`         | `messages.chats`         | Получение списка чатов           |
| GET    | `/messages/{user_id}`            | `MessageController@getMessages`      | `messages.get`           | Получение сообщений с пользователем |
| POST   | `/messages/{user_id}`            | `MessageController@sendMessage`      | `messages.send`          | Отправка сообщения пользователю |
| PATCH  | `/messages/{user_id}/read`       | `MessageController@markAsRead`       | `messages.mark_read`     | Отметка сообщений как прочитанных |

### Теги и категории

| Метод | URL                  | Контроллер               | Название маршрута  | Описание                     |
|-------|----------------------|--------------------------|--------------------|-----------------------------|
| GET   | `/tags`              | `TagController@index`     | `tags.index`       | Получение списка тегов      |
| GET   | `/tags/{tag}`        | `TagController@show`      | `tags.show`        | Получение информации о теге |
| POST  | `/tags`              | `TagController@store`     | `tags.store`       | Создание тега              |
| PUT   | `/tags/{tag}`        | `TagController@update`    | `tags.update`      | Обновление тега            |
| GET   | `/categories`        | `CategoryController@index` | `categories.index` | Получение списка категорий |

### Платежи и баланс

| Метод | URL                                   | Контроллер                          | Название маршрута | Описание                       |
|-------|---------------------------------------|-------------------------------------|------------------|--------------------------------|
| GET   | `/user/balance`                       | `BalanceController@getBalance`      | -                | Получение баланса пользователя |
| POST  | `/user/balance/topup`                 | `BalanceController@topUpBalance`    | -                | Пополнение баланса            |
| POST  | `/user/balance/withdraw`              | `BalanceController@withdrawBalance` | -                | Вывод средств                 |
| POST  | `/user/balance/transfer`              | `BalanceController@transferBalance` | -                | Перевод средств               |
| GET   | `/user/transactions`                  | `TransactionController@getTransactions` | -          | Получение транзакций          |
| POST  | `/user/posts/{postId}/purchase`       | `PurchaseController@purchasePost`   | -                | Покупка поста                 |
| POST  | `/user/subscriptions`                 | `SubscriptionController@createSubscription` | -     | Создание подписки            |
| GET   | `/user/subscriptions/active`          | `SubscriptionController@getActiveSubscription` | -  | Получение активной подписки  |
| POST  | `/user/subscriptions/{subscriptionId}/extend` | `SubscriptionController@extendSubscription` | - | Продление подписки        |

### Другие функции пользователя

| Метод  | URL                               | Контроллер                                | Название маршрута       | Описание                        |
|--------|-----------------------------------|-------------------------------------------|------------------------|--------------------------------|
| POST   | `/user/follow/{userId}`           | `UserFollowController@follow`             | `follow.user`          | Подписка на пользователя        |
| DELETE | `/user/follow/{userId}`           | `UserFollowController@unfollow`           | `unfollow.user`        | Отписка от пользователя         |
| GET    | `/user/follow/followers`          | `UserFollowController@followers`          | `followers`            | Получение подписчиков           |
| GET    | `/user/follow/following`          | `UserFollowController@following`          | `following`            | Получение подписок              |
| POST   | `/user/employment-status/assign`  | `UserEmploymentStatusController@assignEmploymentStatus` | `employment.status.assign` | Назначение статуса занятости |
| DELETE | `/user/employment-status/remove`  | `UserEmploymentStatusController@removeEmploymentStatus` | `employment.status.remove` | Удаление статуса занятости |
| GET    | `/user/sources`                   | `UserSourceController@getUserSources`     | `user.sources.get`     | Получение источников пользователя |
| GET    | `/user/skills`                    | `UserSkillController@getUserSkills`       | `user.skills.get`      | Получение навыков пользователя  |

## Административные маршруты (Admin Routes)

Все эти маршруты требуют авторизации через `auth:api` middleware и наличия роли администратора через `role:admin` middleware.

### Дашборд и аналитика

| Метод | URL                      | Контроллер                    | Название маршрута        | Описание                          |
|-------|--------------------------|-------------------------------|-------------------------|----------------------------------|
| GET   | `/admin/dashboard`       | `DashboardController@index`    | `admin.dashboard`       | Дашборд администратора           |
| GET   | `/admin/analytics/users` | `AnalyticsController@users`    | `admin.analytics.users` | Аналитика по пользователям       |
| GET   | `/admin/analytics/posts` | `AnalyticsController@posts`    | `admin.analytics.posts` | Аналитика по постам              |
| GET   | `/admin/analytics/revenue` | `AnalyticsController@revenue` | `admin.analytics.revenue` | Аналитика по доходам           |
| GET   | `/admin/analytics/engagement` | `AnalyticsController@engagement` | `admin.analytics.engagement` | Аналитика взаимодействия |

### Управление пользователями

| Метод  | URL                          | Контроллер                     | Название маршрута      | Описание                       |
|--------|------------------------------|--------------------------------|-----------------------|--------------------------------|
| GET    | `/admin/users`               | `UserController@index`          | `admin.users.index`    | Список пользователей           |
| GET    | `/admin/users/{id}`          | `UserController@show`           | `admin.users.show`     | Просмотр пользователя          |
| POST   | `/admin/users`               | `UserController@store`          | `admin.users.store`    | Создание пользователя          |
| PATCH  | `/admin/users/{id}`          | `UserController@update`         | `admin.users.update`   | Обновление пользователя        |
| DELETE | `/admin/users/{id}`          | `UserController@destroy`        | `admin.users.destroy`  | Удаление пользователя          |
| PATCH  | `/admin/users/{id}/ban`      | `UserController@ban`            | `admin.users.ban`      | Бан пользователя               |
| PATCH  | `/admin/users/{id}/unban`    | `UserController@unban`          | `admin.users.unban`    | Разбан пользователя            |
| PATCH  | `/admin/users/{id}/verify`   | `UserController@verify`         | `admin.users.verify`   | Верификация пользователя       |
| PATCH  | `/admin/users/{id}/unverify` | `UserController@unverify`       | `admin.users.unverify` | Отмена верификации пользователя |

### Управление ролями

| Метод  | URL                                       | Контроллер                | Название маршрута       | Описание                         |
|--------|-------------------------------------------|---------------------------|------------------------|----------------------------------|
| GET    | `/admin/roles`                            | `RoleController@index`     | `admin.roles.index`    | Список ролей                     |
| GET    | `/admin/roles/{id}`                       | `RoleController@show`      | `admin.roles.show`     | Просмотр роли                    |
| POST   | `/admin/roles`                            | `RoleController@store`     | `admin.roles.store`    | Создание роли                    |
| PATCH  | `/admin/roles/{id}`                       | `RoleController@update`    | `admin.roles.update`   | Обновление роли                  |
| DELETE | `/admin/roles/{id}`                       | `RoleController@destroy`   | `admin.roles.destroy`  | Удаление роли                    |
| POST   | `/admin/roles/{role_id}/assign/{user_id}` | `RoleController@assignRole` | `admin.roles.assign`  | Назначение роли пользователю     |
| DELETE | `/admin/roles/{role_id}/remove/{user_id}` | `RoleController@removeRole` | `admin.roles.remove`  | Удаление роли у пользователя     |

### Управление контентом

| Метод  | URL                             | Контроллер                   | Название маршрута         | Описание                        |
|--------|--------------------------------|------------------------------|--------------------------|--------------------------------|
| GET    | `/admin/posts`                 | `PostController@index`        | `admin.posts.index`      | Список постов                   |
| GET    | `/admin/posts/{id}`            | `PostController@show`         | `admin.posts.show`       | Просмотр поста                  |
| POST   | `/admin/posts`                 | `PostController@store`        | `admin.posts.store`      | Создание поста                  |
| PATCH  | `/admin/posts/{id}`            | `PostController@update`       | `admin.posts.update`     | Обновление поста                |
| DELETE | `/admin/posts/{id}`            | `PostController@destroy`      | `admin.posts.destroy`    | Удаление поста                  |
| PATCH  | `/admin/posts/{id}/approve`    | `PostController@approve`      | `admin.posts.approve`    | Одобрение поста                 |
| PATCH  | `/admin/posts/{id}/reject`     | `PostController@reject`       | `admin.posts.reject`     | Отклонение поста                |
| PATCH  | `/admin/posts/{id}/feature`    | `PostController@feature`      | `admin.posts.feature`    | Выделение поста                 |
| PATCH  | `/admin/posts/{id}/unfeature`  | `PostController@unfeature`    | `admin.posts.unfeature`  | Снятие выделения поста          |
| GET    | `/admin/media`                 | `MediaController@index`        | `admin.media.index`      | Список медиафайлов              |
| GET    | `/admin/media/{id}`            | `MediaController@show`         | `admin.media.show`       | Просмотр медиафайла             |
| DELETE | `/admin/media/{id}`            | `MediaController@destroy`      | `admin.media.destroy`    | Удаление медиафайла             |

### Управление категориями и тегами

| Метод  | URL                          | Контроллер                  | Название маршрута          | Описание                       |
|--------|------------------------------|-----------------------------|-----------------------------|--------------------------------|
| GET    | `/admin/categories`          | `CategoryController@index`   | `admin.categories.index`    | Список категорий               |
| GET    | `/admin/categories/{id}`     | `CategoryController@show`    | `admin.categories.show`     | Просмотр категории             |
| POST   | `/admin/categories`          | `CategoryController@store`   | `admin.categories.store`    | Создание категории             |
| PATCH  | `/admin/categories/{id}`     | `CategoryController@update`  | `admin.categories.update`   | Обновление категории           |
| DELETE | `/admin/categories/{id}`     | `CategoryController@destroy` | `admin.categories.destroy`  | Удаление категории             |
| GET    | `/admin/tags`                | `TagController@index`         | `admin.tags.index`          | Список тегов                   |
| GET    | `/admin/tags/{id}`           | `TagController@show`          | `admin.tags.show`           | Просмотр тега                  |
| POST   | `/admin/tags`                | `TagController@store`         | `admin.tags.store`          | Создание тега                  |
| PATCH  | `/admin/tags/{id}`           | `TagController@update`        | `admin.tags.update`         | Обновление тега                |
| DELETE | `/admin/tags/{id}`           | `TagController@destroy`       | `admin.tags.destroy`        | Удаление тега                  |

### Системные настройки и модерация

| Метод  | URL                                | Контроллер                             | Название маршрута                | Описание                             |
|--------|------------------------------------|-----------------------------------------|----------------------------------|--------------------------------------|
| GET    | `/admin/settings`                  | `SettingsController@index`              | `admin.settings.index`           | Получение системных настроек        |
| PATCH  | `/admin/settings`                  | `SettingsController@update`             | `admin.settings.update`          | Обновление системных настроек       |
| POST   | `/admin/settings/cache/clear`      | `SettingsController@clearCache`         | `admin.settings.clear-cache`     | Очистка кэша                        |
| POST   | `/admin/settings/maintenance/{status}` | `SettingsController@setMaintenanceMode` | `admin.settings.maintenance`  | Управление режимом обслуживания     |
| GET    | `/admin/reports/users`             | `UserReportController@index`            | `admin.reports.users.index`      | Список жалоб на пользователей       |
| GET    | `/admin/reports/users/{id}`        | `UserReportController@show`             | `admin.reports.users.show`       | Просмотр жалобы на пользователя     |
| PATCH  | `/admin/reports/users/{id}/resolve` | `UserReportController@resolve`         | `admin.reports.users.resolve`    | Разрешение жалобы на пользователя   |
| PATCH  | `/admin/reports/users/{id}/dismiss` | `UserReportController@dismiss`         | `admin.reports.users.dismiss`    | Отклонение жалобы на пользователя   |
| GET    | `/admin/reports/content`           | `ContentReportController@index`          | `admin.reports.content.index`    | Список жалоб на контент             |
| GET    | `/admin/reports/content/{id}`      | `ContentReportController@show`           | `admin.reports.content.show`     | Просмотр жалобы на контент          |
| PATCH  | `/admin/reports/content/{id}/resolve` | `ContentReportController@resolve`     | `admin.reports.content.resolve`  | Разрешение жалобы на контент        |
| PATCH  | `/admin/reports/content/{id}/dismiss` | `ContentReportController@dismiss`     | `admin.reports.content.dismiss`  | Отклонение жалобы на контент        |

## Обработка ошибок

При обращении к несуществующему маршруту API возвращается JSON-ответ с кодом 404 и информацией об ошибке:

```json
{
    "success": false,
    "error": "Route not found or method not supported",
    "supported_methods": ["GET", "HEAD", "POST", "PUT", "DELETE", "OPTIONS", "PATCH"],
    "message": "The request to route {uri} with method {method} does not exist."
}
``` 