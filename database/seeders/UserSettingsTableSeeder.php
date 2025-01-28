<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class UserSettingsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $settings = [
            [
                'is_online' => true,
                'is_preferences_feed' => true,
                'preferences_feed' => 'popularity',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'is_online' => false,
                'is_preferences_feed' => true,
                'preferences_feed' => 'downloads',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'is_online' => true,
                'is_preferences_feed' => false,
                'preferences_feed' => 'likes',
                'created_at' => now(), 'updated_at' => now(),
            ],
            [
                'is_online' => false,
                'is_preferences_feed' => false,
                'preferences_feed' => 'default',
                'created_at' => now(), 'updated_at' => now(),
            ],
        ];

        DB::table('user_settings')->insert($settings);
    }
}
