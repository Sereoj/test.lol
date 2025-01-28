<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class EmploymentStatusesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFilePath = database_path('files/employment_statuses.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $employment_statuses = json_decode($jsonContent, true);

        foreach ($employment_statuses as $employment_status) {
            DB::table('employment_statuses')->insert([
                'name' => json_encode($employment_status['name']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
