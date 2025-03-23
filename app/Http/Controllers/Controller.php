<?php

namespace App\Http\Controllers;

use App\Traits\CacheableTrait;
use App\Traits\LoggableTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;

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
    protected function successResponse(mixed $data = null, int $statusCode = 200)
    {
        return response()->json([
            'success' => true,
            'data' => $data
        ], $statusCode);
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
            'message' => $message
        ], $statusCode);
    }
}
