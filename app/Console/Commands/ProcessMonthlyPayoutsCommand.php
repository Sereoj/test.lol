<?php

namespace App\Console\Commands;

use App\Models\Users\UserBalance;
use App\Services\Billing\BalanceService;
use Illuminate\Console\Command;

class ProcessMonthlyPayoutsCommand extends Command
{
    protected $signature = 'payouts:process';

    protected $description = 'Process monthly payouts to sellers';

    protected $balanceService;

    public function __construct(BalanceService $balanceService)
    {
        parent::__construct();
        $this->balanceService = $balanceService;
    }

    public function handle()
    {
        $sellers = UserBalance::where('pending_balance', '>', 0)->get();

        foreach ($sellers as $seller) {
            try {
                $this->balanceService->payoutToSeller($seller->user_id);
                $this->info("Payout processed for user ID {$seller->user_id}");
            } catch (\Exception $e) {
                $this->error("Failed to process payout for user ID {$seller->user_id}: {$e->getMessage()}");
            }
        }
    }
}
