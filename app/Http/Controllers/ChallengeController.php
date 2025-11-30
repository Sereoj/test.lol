<?php

namespace App\Http\Controllers;

use App\Http\Requests\Challenge\ChallengeRequest;
use App\Http\Requests\Challenge\ParticipateRequest;
use App\Http\Resources\ChallengeResource;
use App\Services\ChallengeService;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use OpenApi\Attributes as OA;

// Контроллер для работы с челленджами
class ChallengeController extends Controller
{
    protected ChallengeService $challengeService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_CHALLENGES = 'challenges';
    private const CACHE_KEY_CHALLENGE = 'challenge_';
    private const CACHE_KEY_ACTIVE_CHALLENGES = 'active_challenges';
    private const CACHE_KEY_USER_CHALLENGES = 'user_challenges_';

                                        /**
     * @OA\Post(
     *     path="/api/v1/challenges/{id}/leave",
     *     tags={"Challenges"},
     *     summary="Leave challenge",
     *     description="Leave challenge",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="id",
     *         in="path",
     *         required=true,
     *         description="Id",
     *         @OA\Schema(type="integer")
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
public function leave(int $id): JsonResponse
    {
        try {
            $userId = Auth::id();
            $this->challengeService->removeParticipant($id, $userId);

            $this->forgetCache(self::CACHE_KEY_CHALLENGE . $id);
            $this->forgetCache(self::CACHE_KEY_USER_CHALLENGES . $userId . '_10');

            Log::info('Пользователь покинул челлендж', ['user_id' => $userId, 'challenge_id' => $id]);

            return $this->successResponse(['message' => 'Вы успешно покинули челлендж']);
        } catch (Exception $e) {
            Log::error('Ошибка при выходе из челленджа: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'challenge_id' => $id
            ]);

            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
