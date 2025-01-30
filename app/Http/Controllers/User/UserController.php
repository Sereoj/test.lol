<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\RegisterRequest;
use App\Models\User;
use App\Services\AuthService;
use App\Services\UserService;
use App\Utils\PasswordUtil;
use Exception;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class UserController extends Controller
{
    protected UserService $userService;

    protected AuthService $authService;

    public function __construct(UserService $userService, AuthService $authService)
    {
        $this->userService = $userService;
        $this->authService = $authService;
    }

    /**
     * Display a listing of the users.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        // Попытка получить данные из кеша
        $users = Cache::get('users');

        // Если кеш пуст, извлекаем данные из базы и сохраняем их в кеш
        if (!$users) {
            try {
                $users = $this->userService->getAllUsers();
                Cache::put('users', $users, now()->addMinutes(10)); // Кешируем на 10 минут
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'An error occurred while fetching users.',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json($users);
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        // Попытка получить данные из кеша
        $cacheKey = 'user_' . $id;
        $user = Cache::get($cacheKey);

        if (!$user) {
            try {
                $user = $this->userService->findUserById($id);

                if (!$user) {
                    return response()->json(['message' => 'User not found'], 404);
                }

                Cache::put($cacheKey, $user, now()->addMinutes(10)); // Кешируем на 10 минут
            } catch (Exception $e) {
                return response()->json([
                    'error' => 'An error occurred while fetching the user.',
                    'message' => $e->getMessage(),
                ], 500);
            }
        }

        return response()->json($user);
    }

    /**
     * Store a newly created user in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(RegisterRequest $request)
    {
        try {
            $userData = $request->validated();
            $user = $this->userService->createUser($userData);

            return $this->authService->register($user, false);
        } catch (Exception $e) {
            return response()->json([
                'message' => 'User registration failed',
                'error' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Update the specified user in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (!$user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,' . $id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? PasswordUtil::hash($request->password) : $user->password,
        ]);

        // Очистка кеша после обновления данных пользователя
        Cache::forget('user_' . $id);

        return response()->json($user, 200);
    }

    /**
     * Remove the specified user from storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $user = $this->userService->findUserById($id);

            if (!$user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $this->userService->deleteUser($user);

            // Очистка кеша после удаления пользователя
            Cache::forget('user_' . $id);
            Cache::forget('users');

            return response()->json(['message' => 'User deleted successfully']);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the user.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Change the role of a user.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  int  $userId
     * @return \Illuminate\Http\JsonResponse
     */
    public function changeRole(Request $request, $userId)
    {
        // Валидация данных
        $data = $request->validate([
            'role_id' => 'required|exists:roles,id',
        ]);

        // Получаем пользователя
        $user = User::findOrFail($userId);

        // Используем сервис для изменения роли
        $updatedUser = $this->userService->changeUserRole($user, $data['role_id']);

        // Очистка кеша после изменения роли пользователя
        Cache::forget('user_' . $userId);
        Cache::forget('users');

        return response()->json($updatedUser);
    }
}
