<?php

namespace App\Http\Controllers\Billing;

use App\Http\Controllers\Controller;
use App\Services\Billing\PurchaseService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Validation\ValidationException;
use Exception;

class PurchaseController extends Controller
{
    protected PurchaseService $purchaseService;
    
    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_USER_PURCHASES = 'user_purchases_';
    private const CACHE_KEY_POST_PURCHASES = 'post_purchases_';

    public function __construct(PurchaseService $purchaseService)
    {
        $this->purchaseService = $purchaseService;
    }

    // Метод для покупки поста
    public function purchasePost(Request $request, int $postId): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:0.01',
                'currency' => 'required|string|max:3',
            ]);

            $userId = Auth::id();

            $purchase = $this->purchaseService->purchasePost(
                $postId,
                $validated['amount'],
                $validated['currency']
            );

            // Очистка кеша покупок пользователя и поста
            $this->forgetCache(self::CACHE_KEY_USER_PURCHASES . $userId);
            $this->forgetCache(self::CACHE_KEY_POST_PURCHASES . $postId);

            Log::info('Post purchased successfully', [
                'user_id' => $userId,
                'post_id' => $postId,
                'amount' => $validated['amount'],
                'currency' => $validated['currency']
            ]);

            return $this->successResponse($purchase, 201);
        } catch (ValidationException $e) {
            Log::warning('Validation error during post purchase', ['errors' => $e->errors(), 'user_id' => Auth::id(), 'post_id' => $postId]);
            return $this->errorResponse($e->errors(), 422);
        } catch (Exception $e) {
            Log::error('Error purchasing post: ' . $e->getMessage(), ['user_id' => Auth::id(), 'post_id' => $postId]);
            return $this->errorResponse($e->getMessage(), 400);
        }
    }
}
