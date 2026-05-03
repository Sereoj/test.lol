<?php

namespace App\Jobs;

use App\Models\Billing\Transaction;
use App\Models\Users\User;
use App\Notifications\TransactionNotification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

class SendNotificationJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected User $user;

    protected Transaction $transaction;

    public function __construct(User $user, Transaction $transaction)
    {
        $this->user = $user;
        $this->transaction = $transaction;
        
        // Отправлять после успешной транзакции
        $this->afterCommit();
    }

    public function handle()
    {
        $this->user->notify(new TransactionNotification($this->transaction));
    }
    
    public function failed(\Throwable $exception)
    {
        \Log::error('Failed to send notification', [
            'user_id' => $this->user->id,
            'transaction_id' => $this->transaction->id,
            'error' => $exception->getMessage()
        ]);
    }
}
