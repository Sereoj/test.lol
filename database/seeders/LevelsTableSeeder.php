<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LevelsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFilePath = database_path('files/levels.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $levels = json_decode($jsonContent, true)['levels'];

        foreach ($levels as $level) {
            DB::table('levels')->insert([
                'name' => json_encode($level['name']),
                'experience_required' => $level['experience_required'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Levels table seeded successfully!');
    }
}
