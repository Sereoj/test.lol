<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class LocationsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFilePath = database_path('files/locations.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $locations = json_decode($jsonContent, true);

        foreach ($locations as $location) {
            DB::table('locations')->insert([
                'name' => json_encode($location['name']),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Locations table seeded successfully!');
    }
}
