<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;

class UserAccountController extends Controller
{
    /**
     * Обновление аккаунта пользователя
     */
    public function update(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'sometimes|email|unique:users,email,' . Auth::id(),
            'current_password' => 'required_with:new_password|string',
            'new_password' => 'sometimes|string|min:8|confirmed',
            'new_password_confirmation' => 'required_with:new_password|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Проверка текущего пароля
        if ($request->has('new_password') && !Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Текущий пароль неверен'
            ], 422);
        }
        
        // Обновление данных
        if ($request->has('email')) {
            $user->email = $request->email;
        }
        
        if ($request->has('new_password')) {
            $user->password = Hash::make($request->new_password);
        }
        
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'Данные аккаунта успешно обновлены'
        ]);
    }

    /**
     * Удаление аккаунта пользователя
     */
    public function destroy(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'password' => 'required|string'
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors()
            ], 422);
        }

        $user = Auth::user();
        
        // Проверка пароля
        if (!Hash::check($request->password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'Неверный пароль'
            ], 422);
        }
        
        // Удаление пользователя (или мягкое удаление)
        $user->delete();

        return response()->json([
            'success' => true,
            'message' => 'Аккаунт успешно удален'
        ]);
    }
} 