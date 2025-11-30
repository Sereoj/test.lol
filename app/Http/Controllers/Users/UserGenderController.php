<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use OpenApi\Attributes as OA;

// Контроллер для работы с полом пользователя
class UserGenderController extends Controller
{                    /**
     * @OA\Get(
     *     path="/api/v1/gender",
     *     tags={"Users"},
     *     summary="Get all user genders",
     *     description="Get all user genders",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="page",
     *         in="query",
     *         description="Page number",
     *         @OA\Schema(type="integer", example=1)
     *     ),
     *     @OA\Parameter(
     *         name="per_page",
     *         in="query",
     *         description="Items per page",
     *         @OA\Schema(type="integer", example=15)
     *     ),
     *     @OA\Response(
     *         response=200,
     *         description="Successful operation",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(
     *                 property="data",
     *                 type="array",
     *                 @OA\Items(type="object")
     *             ),
     *             @OA\Property(
     *                 property="meta",
     *                 type="object",
     *                 @OA\Property(property="current_page", type="integer", example=1),
     *                 @OA\Property(property="last_page", type="integer", example=10),
     *                 @OA\Property(property="per_page", type="integer", example=15),
     *                 @OA\Property(property="total", type="integer", example=150)
     *             )
     *         )
     *     ),
     *     @OA\Response(
     *         response=500,
     *         description="Server error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Internal server error")
     *         )
     *     )
     * )
     */
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
