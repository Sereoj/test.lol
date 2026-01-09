<?php

namespace App\Repositories;

use App\Models\ChallengePrize;
use Illuminate\Database\Eloquent\Collection;

class ChallengePrizeRepository
{
    /**
     * Получить призы челленджа.
     *
     * @param int $challengeId
     * @return Collection
     */
    public function getPrizesByChallenge(int $challengeId): Collection
    {
        return ChallengePrize::where('challenge_id', $challengeId)
            ->orderBy('place')
            ->get();
    }

    /**
     * Создать приз.
     *
     * @param array $data
     * @return ChallengePrize
     */
    public function createPrize(array $data): ChallengePrize
    {
        return ChallengePrize::create($data);
    }

    /**
     * Обновить суммы призов на основе процентов.
     *
     * @param int $challengeId
     * @param float $netPrizeAmount
     * @return void
     */
    public function recalculatePrizeAmounts(int $challengeId, float $netPrizeAmount): void
    {
        $prizes = $this->getPrizesByChallenge($challengeId);

        foreach ($prizes as $prize) {
            $prize->update([
                'amount' => ($netPrizeAmount * $prize->percentage) / 100,
            ]);
        }
    }

    /**
     * Валидация распределения призов (сумма процентов = 100).
     *
     * @param array $prizes
     * @return bool
     */
    public function validatePrizeDistribution(array $prizes): bool
    {
        $totalPercentage = array_sum(array_column($prizes, 'percentage'));
        return abs($totalPercentage - 100) < 0.01; // Допуск на погрешность float
    }
}
