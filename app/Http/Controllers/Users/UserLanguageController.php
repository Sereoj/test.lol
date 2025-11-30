<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Language\SetLanguageRequest;
use Exception;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с языком пользователя
class UserLanguageController extends Controller
{
    // Получение списка языков   
    /**
     * @OA\Get(
     *     path="/api/v1/languages",
     *     tags={"Users"},
     *     summary="Get all user languages",
     *     description="Get all user languages",
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
     *                 @OA\Items(ref="#/components/schemas/UserLanguage")
     *             )
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

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
    /**
     * @OA\Post(
     *     path="/api/v1/languages",
     *     tags={"Users"},
     *     summary="SwitchLanguage user language",
     *     description="SwitchLanguage user language",
     *     @OA\RequestBody(
     *         required=true,
     *         @OA\JsonContent(ref="#/components/schemas/SetLanguageRequest")
     *     ),
     *     @OA\Response(
     *         response=201,
     *         description="Resource created successfully",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=true),
     *             @OA\Property(property="data", ref="#/components/schemas/UserLanguage")
     *         )
     *     ),
     *     @OA\Response(response=500, description="Server error")
     * )
     */

    public function switchLanguage(SetLanguageRequest $request)
    {
        try {
            if (Auth::check()) {
                $user = Auth::user();
                $user->language = $request->input('language');
                $user->save();

                Log::info('User language updated successfully', [
                    'user_id' => $user->id,
                    'language' => $request->input('language')
                ]);
            } else {
                session(['language' => $request->input('language')]);

                Log::info('Session language updated successfully', [
                    'session_id' => session()->getId(),
                    'language' => $request->input('language')
                ]);
            }

            return $this->successResponse(['message' => 'Language updated successfully']);
        } catch (Exception $e) {
            Log::error('Error updating language: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'language' => $request->input('language')
            ]);
            return $this->errorResponse('An error occurred while updating the language: ' . $e->getMessage(), 500);
        }
    }
}
