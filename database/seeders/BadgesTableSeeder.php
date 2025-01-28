<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

class BadgesTableSeeder extends Seeder
{
    public function run(): void
    {

        /*  "options": {
                "availability": "open", // Доступно сразу: "open", достижение: "achievement", покупка: "purchase"
                 "requirements": {
                    "type": "likes", // Тип достижения: "likes", "comments", "uploads", "purchases"
                "value": 100     // Значение, которое нужно достичь
            },
            "price": 50 // Цена в токенах, если бейдж покупается
        }*/

        //DB::table('badges')->delete();

        $jsonFilePath = database_path('files/badges.json');

        if (! File::exists($jsonFilePath)) {
            $this->command->error('JSON file does not exist.');

            return;
        }

        $jsonContent = File::get($jsonFilePath);
        $badges = json_decode($jsonContent, true);

        foreach ($badges['badges'] as $badge) {
            DB::table('badges')->insert([
                'name' => json_encode($badge['name']),
                'color' => $badge['color'],
                'description' => json_encode($badge['description']),
                'options' => json_encode($badge['options']),
                'image' => $badge['image'],
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
