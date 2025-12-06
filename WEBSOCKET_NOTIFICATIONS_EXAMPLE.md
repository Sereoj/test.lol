# Пример использования WebSocket уведомлений на фронтенде

## Настройка Laravel Echo и Pusher

### 1. Установка зависимостей

```bash
npm install --save laravel-echo pusher-js
```

### 2. Настройка Echo (например, в main.js или app.js)

```javascript
import Echo from 'laravel-echo';
import Pusher from 'pusher-js';

window.Pusher = Pusher;

window.Echo = new Echo({
    broadcaster: 'reverb',
    key: import.meta.env.VITE_REVERB_APP_KEY,
    wsHost: import.meta.env.VITE_REVERB_HOST,
    wsPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    wssPort: import.meta.env.VITE_REVERB_PORT ?? 443,
    forceTLS: (import.meta.env.VITE_REVERB_SCHEME ?? 'https') === 'https',
    enabledTransports: ['ws', 'wss'],
    authEndpoint: 'http://test/broadcasting/auth', // URL вашего API для авторизации
    auth: {
        headers: {
            Authorization: `Bearer ${localStorage.getItem('token')}` // Ваш токен авторизации
        }
    }
});
```

## Подключение к каналу уведомлений

### Пример на Vue 3 Composition API

```vue
<template>
  <div class="notifications">
    <h2>Уведомления</h2>
    <div v-if="notifications.length === 0">
      Нет новых уведомлений
    </div>
    <div v-for="notification in notifications" :key="notification.id" class="notification-item">
      <h3>{{ notification.title }}</h3>
      <p>{{ notification.message }}</p>
      <small>{{ notification.type }}</small>
      <small>{{ new Date(notification.created_at).toLocaleString() }}</small>
    </div>
  </div>
</template>

<script setup>
import { ref, onMounted, onUnmounted } from 'vue';

const notifications = ref([]);
const userId = ref(1); // ID текущего пользователя

let echoChannel = null;

onMounted(() => {
  // Подключаемся к приватному каналу уведомлений пользователя
  echoChannel = window.Echo.private(`notifications.${userId.value}`)
    .listen('.notification.sent', (event) => {
      console.log('Получено новое уведомление:', event);

      // Добавляем уведомление в начало списка
      notifications.value.unshift(event.notification);

      // Можно показать toast/snackbar уведомление
      showToast(event.notification);
    });
});

onUnmounted(() => {
  // Отключаемся от канала при размонтировании компонента
  if (echoChannel) {
    window.Echo.leave(`notifications.${userId.value}`);
  }
});

function showToast(notification) {
  // Пример показа toast уведомления (зависит от вашей UI библиотеки)
  console.log(`Toast: ${notification.title} - ${notification.message}`);
}
</script>

<style scoped>
.notification-item {
  border: 1px solid #ddd;
  padding: 10px;
  margin: 10px 0;
  border-radius: 5px;
}
</style>
```

### Пример на обычном JavaScript

```javascript
// Получаем ID текущего пользователя
const userId = 1; // Замените на реальный ID

// Подключаемся к приватному каналу уведомлений
window.Echo.private(`notifications.${userId}`)
    .listen('.notification.sent', (event) => {
        console.log('Получено новое уведомление:', event);

        const notification = event.notification;

        // Обработка уведомления
        displayNotification(notification);
    });

function displayNotification(notification) {
    console.log('Тип:', notification.type);
    console.log('Заголовок:', notification.title);
    console.log('Сообщение:', notification.message);
    console.log('Данные:', notification.data);
    console.log('Время:', notification.created_at);

    // Показываем браузерное уведомление
    if ('Notification' in window && Notification.permission === 'granted') {
        new Notification(notification.title, {
            body: notification.message,
            icon: '/path/to/icon.png'
        });
    }
}

// Запрос разрешения на показ браузерных уведомлений
if ('Notification' in window && Notification.permission === 'default') {
    Notification.requestPermission();
}
```

## Отправка уведомления через API

### Пример запроса

```javascript
async function sendNotification(userId, notificationData) {
    const response = await fetch('http://test/api/v1/notifications/send', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'Authorization': `Bearer ${localStorage.getItem('token')}`,
            'Accept': 'application/json'
        },
        body: JSON.stringify({
            user_id: userId,
            type: notificationData.type,
            title: notificationData.title,
            message: notificationData.message,
            data: notificationData.data || {}
        })
    });

    const result = await response.json();
    console.log('Уведомление отправлено:', result);
    return result;
}

// Пример использования
sendNotification(2, {
    type: 'new_follower',
    title: 'Новый подписчик',
    message: 'У вас новый подписчик!',
    data: {
        follower_id: 1,
        follower_name: 'John Doe'
    }
});
```

## Структура данных уведомления

```json
{
  "id": "unique_id",
  "type": "new_follower",
  "title": "Новый подписчик",
  "message": "У вас новый подписчик!",
  "data": {
    "follower_id": 1,
    "follower_name": "John Doe"
  },
  "created_at": "2025-12-06T12:00:00.000000Z"
}
```

## Типы уведомлений (примеры)

- `new_follower` - Новый подписчик
- `new_like` - Новый лайк
- `new_comment` - Новый комментарий
- `new_message` - Новое сообщение
- `post_published` - Пост опубликован
- `achievement_unlocked` - Достижение разблокировано

## Запуск Laravel Reverb сервера

Убедитесь, что Laravel Reverb сервер запущен:

```bash
php artisan reverb:start
```

Или в фоновом режиме:

```bash
php artisan reverb:start --host=0.0.0.0 --port=8080
```

## Важные замечания

1. Убедитесь, что в `.env` настроены правильные параметры Reverb:
   - `REVERB_APP_ID`
   - `REVERB_APP_KEY`
   - `REVERB_APP_SECRET`
   - `REVERB_HOST`
   - `REVERB_PORT`

2. Пользователь должен быть авторизован для подключения к приватному каналу

3. Token авторизации должен быть действительным и передаваться в headers при подключении к Echo

4. Для production окружения рекомендуется использовать SSL (wss://)
