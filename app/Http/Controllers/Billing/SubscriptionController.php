<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;
use OpenApi\Attributes as OA;

// Контроллер для работы с подписками
class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_ACTIVE_SUBSCRIPTION = 'active_subscription_user_';

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    // Получение активной подписки пользователя   
    
    /**
     * @OA\Post(
     *     path="/api/v1/user/subscriptions/{subscriptionId}/extend",
     *     tags={"Subscriptions"},
     *     summary="ExtendSubscription subscription",
     *     description="ExtendSubscription subscription",
     *     security={{"bearerAuth":{}}},
     *     @OA\Parameter(
     *         name="subscriptionId",
     *         in="path",
     *         required=true,
     *         description="SubscriptionId",
     *         @OA\Schema(type="string")
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
public function extendSubscription(Request $request, int $subscriptionId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'duration' => 'required|integer',
            ]);
            
            $userId = Auth::id();
            
            $this->subscriptionService->extendSubscription($subscriptionId, $validated['duration']);
            
            $this->forgetCache(self::CACHE_KEY_ACTIVE_SUBSCRIPTION . $userId);
            
            Log::info('Subscription extended successfully', [
                'user_id' => $userId, 
                'subscription_id' => $subscriptionId,
                'added_duration' => $validated['duration']
            ]);
            
            return $this->successResponse(['message' => 'Subscription extended successfully']);
        } catch (ValidationException $e) {
            Log::warning('Validation error during subscription extension', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Error extending subscription: ' . $e->getMessage(), [
                'user_id' => Auth::id(),
                'subscription_id' => $subscriptionId
            ]);
            return $this->errorResponse('Error extending subscription: ' . $e->getMessage(), 500);
        }
    }
}
