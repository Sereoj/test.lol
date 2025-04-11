# Документация по middleware API

## Общая информация

Middleware в Laravel предоставляют удобный механизм для фильтрации HTTP-запросов, поступающих в приложение. В API используются различные middleware для обеспечения безопасности, авторизации и других функций.

## Основные middleware для API

### auth:api

Middleware `auth:api` используется для защиты маршрутов, требующих авторизации пользователя. Он проверяет наличие и валидность токена доступа в запросе.

**Применение:**
```php
Route::middleware('auth:api')->group(function () {
    // Маршруты, требующие авторизации
});
```

**Поведение:**
- Проверяет валидность токена доступа OAuth (Passport)
- В случае отсутствия или недействительности токена возвращает ответ с кодом 401 (Unauthorized)
- В случае успешной авторизации делает доступным объект авторизованного пользователя через `Auth::user()`

### role

Middleware `role` используется для проверки, имеет ли авторизованный пользователь определенную роль. Это позволяет ограничить доступ к определенным маршрутам только для пользователей с определенными ролями.

**Определение middleware:**
```php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        
        if (!$user->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have the required permissions.',
            ], 403);
        }

        return $next($request);
    }
}
```

**Применение:**
```php
Route::middleware(['auth:api', 'role:admin'])->group(function () {
    // Маршруты, требующие роли администратора
});
```

**Поведение:**
- Сначала проверяет авторизацию пользователя
- Затем проверяет, имеет ли пользователь указанную роль через метод `hasRole()` модели User
- В случае отсутствия требуемой роли возвращает ответ с кодом 403 (Forbidden)
- В случае успешной проверки пропускает запрос дальше

### guest

Middleware `guest` используется для маршрутов, которые должны быть доступны только неавторизованным пользователям, например страницы входа и регистрации.

**Применение:**
```php
Route::middleware('guest')->group(function () {
    // Маршруты только для гостей
});
```

**Поведение:**
- Проверяет, что пользователь не авторизован
- Если пользователь авторизован, то он будет перенаправлен на указанный в конфигурации URL

### EnsureJson

Middleware `EnsureJson` используется для того, чтобы убедиться, что все запросы и ответы API используют формат JSON.

**Применение:**
Этот middleware регистрируется глобально в `app/Http/Kernel.php` и применяется ко всем запросам API:

```php
protected $middleware = [
    // ...
    EnsureJson::class,
];
```

**Поведение:**
- Добавляет заголовок `Accept: application/json` к запросу
- Устанавливает заголовок `Content-Type: application/json` для ответа
- Гарантирует, что все ответы будут возвращаться в формате JSON

### TrackUserActivity

Middleware `TrackUserActivity` используется для отслеживания активности пользователей в API. Он обновляет статус "онлайн" пользователя и время последней активности.

**Применение:**
Этот middleware регистрируется в группе `api` в `app/Http/Kernel.php`:

```php
protected $middlewareGroups = [
    'api' => [
        // ...
        \App\Http\Middleware\TrackUserActivity::class,
    ],
];
```

**Поведение:**
- Для авторизованных пользователей обновляет время последней активности
- Устанавливает статус "онлайн"
- Не влияет на неавторизованных пользователей

## Регистрация middleware

Все middleware регистрируются в файле `app/Http/Kernel.php` и могут быть применены к маршрутам глобально, в группах или индивидуально.

```php
protected $middlewareAliases = [
    'auth' => \App\Http\Middleware\Authenticate::class,
    'auth.basic' => \Illuminate\Auth\Middleware\AuthenticateWithBasicAuth::class,
    'auth.session' => \Illuminate\Session\Middleware\AuthenticateSession::class,
    'cache.headers' => \Illuminate\Http\Middleware\SetCacheHeaders::class,
    'can' => \Illuminate\Auth\Middleware\Authorize::class,
    'guest' => \App\Http\Middleware\RedirectIfAuthenticated::class,
    'password.confirm' => \Illuminate\Auth\Middleware\RequirePassword::class,
    'precognitive' => \Illuminate\Foundation\Http\Middleware\HandlePrecognitiveRequests::class,
    'signed' => \App\Http\Middleware\ValidateSignature::class,
    'throttle' => \Illuminate\Routing\Middleware\ThrottleRequests::class,
    'verified' => \Illuminate\Auth\Middleware\EnsureEmailIsVerified::class,
    'role' => \App\Http\Middleware\CheckRole::class,
];
``` 