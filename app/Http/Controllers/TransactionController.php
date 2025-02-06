<?php

namespace App\Http\Controllers;

use App\Services\Transactions\TransactionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class TransactionController extends Controller
{
    protected TransactionService $transactionService;

    public function __construct(TransactionService $transactionService)
    {
        $this->transactionService = $transactionService;
    }

    public function getTransactions(Request $request): JsonResponse
    {
        $transactions = $this->transactionService->getUserTransactions($request->user()->id);

        return response()->json($transactions);
    }
}
