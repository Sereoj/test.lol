<?php

namespace Database\Seeders;

use App\Models\Challenge;
use App\Models\ChallengePrize;
use Illuminate\Database\Seeder;

class ChallengePrizeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Получаем все активные и завершенные челленджи
        $challenges = Challenge::whereIn('status', ['active', 'completed', 'voting'])->get();

        foreach ($challenges as $challenge) {
            // Для каждого челленджа создаем призы для топ-3 мест
            ChallengePrize::factory()->create([
                'challenge_id' => $challenge->id,
                'place' => 1,
                'percentage' => 50.00,
                'amount' => $challenge->prize_amount * 0.50,
            ]);

            ChallengePrize::factory()->create([
                'challenge_id' => $challenge->id,
                'place' => 2,
                'percentage' => 30.00,
                'amount' => $challenge->prize_amount * 0.30,
            ]);

            ChallengePrize::factory()->create([
                'challenge_id' => $challenge->id,
                'place' => 3,
                'percentage' => 20.00,
                'amount' => $challenge->prize_amount * 0.20,
            ]);
        }

        $this->command->info('Challenge prizes created successfully!');
    }
}
