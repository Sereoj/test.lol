# API Типы данных - WallOne

## Оглавление

1. [Общие типы](#общие-типы)
2. [Пользователи](#пользователи)
3. [Посты](#посты)
4. [Комментарии](#комментарии)
5. [Уведомления](#уведомления)
6. [Сообщения](#сообщения)
7. [Настройки](#настройки)

## Общие типы

### ApiResponse

Общий формат ответа API:

```typescript
interface ApiResponse<T> {
  success: boolean;
  data?: T;
  message?: string;
  errors?: Record<string, string[]>;
  pagination?: {
    total: number;
    per_page: number;
    current_page: number;
    last_page: number;
  };
}
```

## Пользователи

### User

Основная модель пользователя:

```typescript
interface User {
  id: number;
  username: string;
  email?: string; // только для авторизованного пользователя
  slug: string;
  verification: boolean;
  avatar: {
    path: string;
  };
  wallet?: {
    balance: string;
  };
}
```

### UserProfile

Расширенная информация о пользователе, используемая на странице профиля:

```typescript
interface UserProfile {
  id: number;
  username: string;
  slug: string;
  description: string;
  verification: boolean;
  avatars: {
    path: string;
  }[];
  specializations: {
    id: number;
    name: {
      ru: string;
      en: string;
    };
  }[];
  badges: Badge[];
  followers_count: number;
  following_count: number;
  experience: number;
  language: string;
  online: {
    is_online: boolean;
    last_activity: string;
  };
  user_setting?: UserSetting;
  additional_data?: {
    balance: {
      amount: string;
      currency: string;
    }[];
    tasks: Task[];
  };
}
```

### UserRelationship

Информация о взаимосвязях между пользователями:

```typescript
interface UserRelationship {
  is_following: boolean;
  is_followed_by: boolean;
}
```

### Badge

Модель значка/награды пользователя:

```typescript
interface Badge {
  id: number;
  name: {
    ru: string;
    en: string;
  };
  description: {
    ru: string;
    en: string;
  };
  icon: string;
}
```

### Task

Модель задания:

```typescript
interface Task {
  id: number;
  title: string;
  description: string;
  reward: string;
  pivot: {
    completed: boolean;
  };
}
```

### Token

Модель токена авторизации:

```typescript
interface Token {
  access_token: string;
  refresh_token: string;
  expires_in: number;
}
```

### AuthData

Данные после авторизации:

```typescript
interface AuthData {
  user: User;
  token: Token;
}
```

### ProfileApiResponse

Ответ API при запросе профиля:

```typescript
interface ProfileApiResponse extends ApiResponse<{
  user: UserProfile;
  relationship: UserRelationship;
  is_my_profile: boolean;
}> {}
```

## Посты

### Post

Модель поста:

```typescript
interface Post {
  id?: number;
  title: string;
  slug: string;
  content?: string;
  media: {
    type: 'group';
    group: {
      type: 'image' | 'video' | 'gif';
      src: string;
    }[];
  }[];
  user: {
    username: string;
    slug: string;
    verification: boolean;
    avatar: {
      path: string;
    };
  };
  is_adult_content: boolean;
  is_nsfl_content: boolean;
  is_free: boolean;
  has_copyright: boolean;
  created_at?: string;
  updated_at?: string;
}
```

### MediaItem

Отдельный медиа-элемент:

```typescript
interface MediaItem {
  type: 'image' | 'video' | 'gif';
  src: string;
}
```

### MediaGroup

Группа медиа-элементов:

```typescript
interface MediaGroup {
  type: 'group';
  group: MediaItem[];
}
```

### PostsResponse

Ответ API при запросе постов:

```typescript
interface PostsResponse extends ApiResponse<Post[]> {}
```

## Комментарии

### Comment

Модель комментария:

```typescript
interface Comment {
  id: number;
  content: string;
  user: {
    id: number;
    username: string;
    verification: boolean;
    avatar: {
      path: string;
    };
  };
  parent_id: number | null;
  likes_count: number;
  created_at: string;
  updated_at: string;
  replies?: Comment[];
}
```

### CommentUser

Информация о пользователе в комментарии:

```typescript
interface CommentUser {
  id: number;
  username: string;
  verification: boolean;
  avatar: {
    path: string;
  };
}
```

### CommentsResponse

Ответ API при запросе комментариев:

```typescript
interface CommentsResponse extends ApiResponse<Comment[]> {}
```

## Уведомления

### Notification

Модель уведомления:

```typescript
interface Notification {
  id: number;
  type: 'like' | 'comment' | 'follow' | 'message';
  data: {
    user: {
      id: number;
      username: string;
      avatar: {
        path: string;
      };
    };
    post_id?: number;
    comment_id?: number;
  };
  read_at: string | null;
  created_at: string;
}
```

### NotificationOptions

Параметры уведомления:

```typescript
interface NotificationOptions {
  actions?: {
    label: string;
    onClick: () => void;
  }[];
}
```

### NotificationsResponse

Ответ API при запросе уведомлений:

```typescript
interface NotificationsResponse extends ApiResponse<Notification[]> {}
```

### UnreadNotificationsResponse

Ответ API при запросе непрочитанных уведомлений:

```typescript
interface UnreadNotificationsResponse extends ApiResponse<{
  count: number;
  notifications: Notification[];
}> {}
```

## Сообщения

### Message

Модель сообщения:

```typescript
interface Message {
  id: number;
  sender_id: number;
  recipient_id: number;
  content: string;
  is_read: boolean;
  created_at: string;
}
```

### Chat

Модель чата:

```typescript
interface Chat {
  id: number;
  user: {
    id: number;
    username: string;
    verification: boolean;
    avatar: {
      path: string;
    };
    online: {
      is_online: boolean;
      last_activity: string;
    };
  };
  last_message: {
    content: string;
    created_at: string;
    is_read: boolean;
  };
  unread_count: number;
}
```

### MessagesResponse

Ответ API при запросе сообщений:

```typescript
interface MessagesResponse extends ApiResponse<{
  user: {
    id: number;
    username: string;
    verification: boolean;
    avatar: {
      path: string;
    };
    online: {
      is_online: boolean;
      last_activity: string;
    };
  };
  messages: Message[];
}> {}
```

### ChatsResponse

Ответ API при запросе списка чатов:

```typescript
interface ChatsResponse extends ApiResponse<Chat[]> {}
```

## Настройки

### UserSetting

Настройки пользователя:

```typescript
interface UserSetting {
  is_private: number; // 0 или 1
  show_online_status: number; // 0 или 1
  enable_two_factor: number; // 0 или 1
}
```

### NotificationSetting

Настройки уведомлений:

```typescript
interface NotificationSetting {
  push_enabled: boolean;
  email_enabled: boolean;
  likes_notifications: boolean;
  comments_notifications: boolean;
  followers_notifications: boolean;
  messages_notifications: boolean;
}
```

### ProfileUpdateData

Данные для обновления профиля:

```typescript
interface ProfileUpdateData {
  username?: string;
  bio?: string;
  website?: string;
  avatar?: File;
}
```

### AccountUpdateData

Данные для обновления аккаунта:

```typescript
interface AccountUpdateData {
  email?: string;
  current_password?: string;
  new_password?: string;
  new_password_confirmation?: string;
} 