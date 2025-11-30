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
     *             @OA\Property(property="data", type="object"),
     *             @OA\Property(property="message", type="string", example="Resource created successfully")
     *         )
     *     ),
     *     @OA\Response(
     *         response=422,
     *         description="Validation error",
     *         @OA\JsonContent(
     *             @OA\Property(property="success", type="boolean", example=false),
     *             @OA\Property(property="message", type="string", example="Validation failed"),
     *             @OA\Property(property="errors", type="object")
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
