<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SkillsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        $jsonFilePath = database_path('files/skills.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $data = json_decode($jsonContent, true);

        foreach ($data as $item) {
            DB::table('skills')->insert([
                'name' => json_encode($item['name']),
                'color' => $item['color'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Skills table seeded successfully!');
    }
}
