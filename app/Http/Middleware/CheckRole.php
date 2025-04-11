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
     * Проверяет, имеет ли аутентифицированный пользователь указанную роль.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $role
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $role)
    {
        if (!Auth::check()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized',
            ], 401);
        }

        $user = Auth::user();
        
        // Проверка на роль администратора
        // Этот метод нужно реализовать в модели User
        if (!$user->hasRole($role)) {
            return response()->json([
                'success' => false,
                'message' => 'Access denied. You do not have the required permissions.',
            ], 403);
        }

        return $next($request);
    }
}
