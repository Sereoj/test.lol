<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class StatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFilePath = database_path('files/statuses.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $specializations = json_decode($jsonContent, true);

        foreach ($specializations as $specialization) {
            DB::table('statuses')->insert([
                'name' => json_encode($specialization['name']),
                'emoji' => $specialization['emoji'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
