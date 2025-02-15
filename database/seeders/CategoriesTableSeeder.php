<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class CategoriesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $jsonFilePath = database_path('files/categories.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $categories = json_decode($jsonContent, true);

        foreach ($categories as $categoryData) {
            DB::table('categories')->insert([
                'meta' => json_encode($categoryData['meta']),
                'name' => json_encode($categoryData['name']),
                'slug' => $categoryData['slug'],
                'description' => $categoryData['description'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->command->info('Categories table seeded successfully!');
    }
}
