<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\SetLanguageRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

// Контроллер для работы с языком пользователя
class UserLanguageController extends Controller
{
    // Получение списка языков
    public function index()
    {
        return $this->successResponse([
            'languages' => [
                [
                    'label' => 'Русский',
                    'value' => 'ru'
                ],
                [
                    'label' => 'English',
                    'value' => 'en'
                ],
            ],
        ]);
    }
    // Смена языка пользователя
    public function switchLanguage(SetLanguageRequest $request)
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $user->language = $request->input('language');
                $user->save();

                Log::info('Язык пользователя успешно обновлен', [
                    'user_id' => $user->id,
                    'language' => $request->input('language')
                ]);
            } else {
                session(['language' => $request->input('language')]);

                Log::info('Язык сессии успешно обновлен', [
                    'session_id' => session()->getId(),
                    'language' => $request->input('language')
                ]);
            }

            return $this->successResponse(['message' => 'Language updated successfully']);
        } catch (Exception $e) {
            Log::error('Ошибка при обновлении языка: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'language' => $request->input('language')
            ]);
            return $this->errorResponse('An error occurred while updating the language: ' . $e->getMessage(), 500);
        }
    }
}
