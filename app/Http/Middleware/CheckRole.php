<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Проверяет, имеет ли пользователь доступ к запрашиваемому ресурсу на основе его роли.
     *
     * @param  \Illuminate\Http\Request  $request Объект запроса
     * @param  \Closure  $next Следующий middleware в цепочке
     * @param  string  ...$roles Список допустимых ролей для доступа
     * @return \Symfony\Component\HttpFoundation\Response Ответ приложения
     */
    public function handle(Request $request, Closure $next, ...$roles): Response
    {
        $user = Auth::user();
        $userRole = null;
        $userId = null;

        if ($user) {
            $userId = $user->id;
            if (isset($user->role->type)) {
                $userRole = $user->role->type;
            }
        }

        // Проверяем, что у пользователя есть необходимая роль
        if (!$user || !$userRole || !in_array($userRole, $roles)) {
            Log::warning('Access denied: User does not have required role', [
                'user_id' => $userId,
                'user_role' => $userRole,
                'required_roles' => $roles,
                'ip' => $request->ip(),
                'url' => $request->fullUrl(),
                'method' => $request->method()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'У вас нет прав доступа к этому ресурсу'
            ], 403);
        }

        Log::info('User role check passed', [
            'user_id' => $userId,
            'user_role' => $userRole,
            'required_roles' => $roles,
            'url' => $request->path()
        ]);

        return $next($request);
    }
}
