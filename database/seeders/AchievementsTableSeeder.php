<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class AchievementsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFilePath = database_path('files/achievements.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }
        $jsonContent = File::get($jsonFilePath);
        $achievements = json_decode($jsonContent, true);

        foreach ($achievements as $achievement) {
            DB::table('achievements')->insert([
                'name' => json_encode($achievement['name']),
                'description' => json_encode($achievement['description']),
                'points' => $achievement['points'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Achievements table seeded successfully!');
    }
}
