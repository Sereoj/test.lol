<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SpecializationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFilePath = database_path('files/specializations.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $specializations = json_decode($jsonContent, true);

        foreach ($specializations as $specialization) {
            DB::table('specializations')->insert([
                'name' => json_encode($specialization['name']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Specializations table seeded successfully!');
    }
}
