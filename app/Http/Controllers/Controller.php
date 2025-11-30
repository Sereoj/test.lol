<?php

namespace App\Http\Controllers;

use App\Traits\CacheableTrait;
use App\Traits\LoggableTrait;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Foundation\Validation\ValidatesRequests;
use Illuminate\Routing\Controller as BaseController;
use OpenApi\Attributes as OA;

/**
 * @OA\Info(
 *     version="1.0.0",
 *     title="API Documentation",
 *     description="API documentation for the application",
 *     @OA\Contact(
 *         email="support@example.com"
 *     ),
 *     @OA\License(
 *         name="MIT",
 *         url="https://opensource.org/licenses/MIT"
 *     )
 * )
 *
 * @OA\Server(
 *     url=L5_SWAGGER_CONST_HOST,
 *     description="API Server"
 * )
 *
 * @OA\SecurityScheme(
 *     securityScheme="bearerAuth",
 *     type="http",
 *     scheme="bearer",
 *     bearerFormat="JWT",
 *     description="Enter your Bearer token in the format: Bearer {token}"
 * )
 *
 * @OA\Tag(
 *     name="Authentication",
 *     description="Authentication endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Users",
 *     description="User management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Media",
 *     description="Media management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Avatars",
 *     description="Avatar management endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Notifications",
 *     description="Notification endpoints"
 * )
 *
 * @OA\Tag(
 *     name="Posts",
 *     description="Post management endpoints"
 * )
 */
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
            'data' => [
                'message' => $message,
            ]
        ], $statusCode);
    }
}
