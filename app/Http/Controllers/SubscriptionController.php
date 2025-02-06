<?php

namespace App\Http\Controllers;

use App\Services\SubscriptionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class SubscriptionController extends Controller
{
    protected SubscriptionService $subscriptionService;

    public function __construct(SubscriptionService $subscriptionService)
    {
        $this->subscriptionService = $subscriptionService;
    }

    public function getActiveSubscription(Request $request): JsonResponse
    {
        $this->subscriptionService->checkAndUpdateSubscriptionStatus();
        $subscription = $this->subscriptionService->getActiveSubscription();

        return response()->json($subscription);
    }

    public function createSubscription(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'plan' => 'required|string',
            'amount' => 'required|numeric',
            'currency' => 'required|string|max:3',
            'duration' => 'required|integer',
        ]);

        $subscription = $this->subscriptionService->createSubscription(
            $validated['plan'],
            $validated['amount'],
            $validated['currency'],
            $validated['duration']
        );

        return response()->json($subscription, 201);
    }

    public function extendSubscription(Request $request, int $subscriptionId): JsonResponse
    {
        $validated = $request->validate([
            'duration' => 'required|integer',
        ]);

        $this->subscriptionService->extendSubscription($subscriptionId, $validated['duration']);

        return response()->json(['success' => true]);
    }
}
