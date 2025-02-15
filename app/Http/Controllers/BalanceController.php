<?php

namespace App\Http\Controllers;

use App\Services\Billing\BalanceService;
use App\Services\Billing\PaymentGatewayService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

class BalanceController extends Controller
{
    protected BalanceService $balanceService;

    protected PaymentGatewayService $paymentGatewayService;

    public function __construct(BalanceService $balanceService, PaymentGatewayService $paymentGatewayService)
    {
        $this->balanceService = $balanceService;
        $this->paymentGatewayService = $paymentGatewayService;
    }

    public function getBalance(Request $request): JsonResponse
    {
        $currency = $request->input('currency');

        if (! $currency || ! is_string($currency) || strlen($currency) !== 3) {
            return response()->json(['error' => 'Некорректная или отсутствующая валюта'], 400);
        }

        $balance = $this->balanceService->getUserBalance(Auth::id(), strtoupper($currency));

        if (isset($balance['error'])) {
            return response()->json(['error' => $balance['error']], 404);
        }

        return response()->json(['balance' => $balance]);
    }

    public function topUpBalance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|size:3',
                'gateway' => 'required|string',
            ]);

            $topup = $this->balanceService->topUpBalance(
                $validated['amount'],
                strtoupper($validated['currency']),
                $validated['gateway']
            );

            return response()->json(['success' => true, 'topup' => $topup]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function transferBalance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'recipient_id' => 'required|exists:users,id|different:user_id',
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|size:3',
            ]);

            $transfer = $this->balanceService->transferBalance(
                Auth::id(),
                $validated['recipient_id'],
                $validated['amount'],
                strtoupper($validated['currency'])
            );

            return response()->json(['success' => true, 'transfer' => $transfer]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }

    public function withdrawBalance(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'amount' => 'required|numeric|min:1',
                'currency' => 'required|string|size:3',
            ]);

            $withdrawal = $this->balanceService->withdrawBalance(
                $validated['amount'],
                strtoupper($validated['currency'])
            );

            return response()->json(['success' => true, 'withdrawal' => $withdrawal]);
        } catch (ValidationException $e) {
            return response()->json(['error' => $e->errors()], 422);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 400);
        }
    }
}
