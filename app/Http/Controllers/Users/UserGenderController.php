<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

// Контроллер для работы с полом пользователя
class UserGenderController extends Controller
{
    public function index()
    {
        $this->successResponse([
            [
                'id' => 0,
                'name' => [
                    'ru' => 'Не указано',
                    'en' => 'Not specified'
                ],
                'code' => 'unknown',
            ],
            [
                'id' => 1,
                'name' => [
                    'ru' => 'Мужской',
                    'en' => 'Male'
                ],
                'code' => 'male',
            ],
            [
                'id' => 2,
                'name' => [
                    'ru' => 'Женский',
                    'en' => 'Female'
                ],
                'code' => 'female',
            ],
            [
                'id' => 3,
                'name' => [
                    'ru' => 'Другой',
                    'en' => 'Other'
                ],
                'code' => 'other',
            ],
        ]);
    }
}
