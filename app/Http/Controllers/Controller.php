<?php

namespace App\Http\Controllers;

use App\Traits\CacheableTrait;
use App\Traits\LoggableTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

// Базовый контроллер для всех контроллеров
class Controller extends BaseController
{
    use AuthorizesRequests,
        ValidatesRequests,
        LoggableTrait,
        CacheableTrait;

    /**
     * Успешный ответ
     *
     * @param mixed|null $data
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function successResponse(mixed $data = null, array $pagination = [], int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data,
            'pagination' => $pagination
        ], $statusCode, [], JSON_UNESCAPED_SLASHES);
    }

    /**
     * Ответ с ошибкой
     *
     * @param string $message
     * @param int $statusCode
     * @return \Illuminate\Http\JsonResponse
     */
    protected function errorResponse(string $message, int $statusCode = 400)
    {
        return response()->json([
            'success' => false,
            'data' => [
                'message' => $message,
            ]
        ], $statusCode);
    }
}
