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
        try {
            $users = $this->userService->getAllUsers();

            return response()->json($users);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching users.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

    /**
     * Display the specified user.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show($id)
    {
        try {
            $user = $this->userService->findUserById($id);

            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            return response()->json($user);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while fetching the user.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

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

    public function update(Request $request, $id)
    {
        $user = User::find($id);
        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'email' => 'sometimes|required|string|email|max:255|unique:users,email,'.$id,
            'password' => 'sometimes|required|string|min:8',
        ]);

        $user->update([
            'name' => $request->name ?? $user->name,
            'email' => $request->email ?? $user->email,
            'password' => $request->password ? PasswordUtil::hash($request->password) : $user->password,
        ]);

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

            if (! $user) {
                return response()->json(['message' => 'User not found'], 404);
            }

            $this->userService->deleteUser($user);

            return response()->json(['message' => 'User deleted successfully']);
        } catch (Exception $e) {
            return response()->json([
                'error' => 'An error occurred while deleting the user.',
                'message' => $e->getMessage(),
            ], 500);
        }
    }

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

        return response()->json($updatedUser);
    }
}
