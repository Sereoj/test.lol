<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;

class UserGenderController extends Controller
{
    public function index()
    {
        $this->successResponse([
            [
                'label' => 'Мужской',
                'value' => 'male',
            ],
            [
                'label' => 'Женский',
                'value' => 'female',
            ],
            [
                'label' => 'Другой',
                'value' => 'other',
            ],
        ]);
    }
}
