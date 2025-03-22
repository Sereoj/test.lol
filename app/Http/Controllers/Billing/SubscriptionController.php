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

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_ACTIVE_SUBSCRIPTION = 'active_subscription_user_';

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function getActiveSubscription(Request $request): JsonResponse
    {
        try {
            $userId = Auth::id();
            $cacheKey = self::CACHE_KEY_ACTIVE_SUBSCRIPTION . $userId;
            
            $this->subscriptionService->checkAndUpdateSubscriptionStatus();
            
            $subscription = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () {
                return $this->subscriptionService->getActiveSubscription();
            });
            
            Log::info('Active subscription retrieved successfully', ['user_id' => $userId]);
            
            return $this->successResponse($subscription);
        } catch (Exception $e) {
            Log::error('Error retrieving active subscription: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('Error retrieving active subscription: ' . $e->getMessage(), 500);
        }
    }

    public function createSubscription(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'plan' => 'required|string',
                'amount' => 'required|numeric',
                'currency' => 'required|string|max:3',
                'duration' => 'required|integer',
            ]);
            
            $userId = Auth::id();
            
            $subscription = $this->subscriptionService->createSubscription(
                $validated['plan'],
                $validated['amount'],
                $validated['currency'],
                $validated['duration']
            );
            
            $this->forgetCache(self::CACHE_KEY_ACTIVE_SUBSCRIPTION . $userId);
            
            Log::info('Subscription created successfully', [
                'user_id' => $userId,
                'plan' => $validated['plan'],
                'duration' => $validated['duration']
            ]);
            
            return $this->successResponse($subscription, 201);
        } catch (ValidationException $e) {
            Log::warning('Validation error during subscription creation', ['errors' => $e->errors(), 'user_id' => Auth::id()]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Error creating subscription: ' . $e->getMessage(), ['user_id' => Auth::id()]);
            return $this->errorResponse('Error creating subscription: ' . $e->getMessage(), 500);
        }
    }

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
