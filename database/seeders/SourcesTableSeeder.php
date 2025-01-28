<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class SourcesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFilePath = database_path('files/sources.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $data = json_decode($jsonContent, true);

        foreach ($data as $item) {
            DB::table('sources')->insert([
                'name' => json_encode($item['name']),
                'iconUrl' => $item['iconUrl'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
