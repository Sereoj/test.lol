<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UsersTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $users = [
            [
                'username' => 'admin',
                'slug' => 'admin',
                'description' => 'Administrator',
                'email' => 'admin@example.com',
                'email_verified_at' => now(),
                'verification' => true,
                'experience' => 1000,
                'gender' => 'male',
                'language' => 'en',
                'age' => 30,
                'password' => Hash::make('securepassword'),
                'role_id' => 1, // Пример ID роли
                'usingApps_id' => 1, // Пример ID приложения
                'userSettings_id' => 1, // Пример ID настроек пользователя
                'status_id' => 1, // Пример ID статуса
                'location_id' => 1, // Пример ID локации
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'username' => 'user1',
                'slug' => 'user1',
                'description' => 'Regular user',
                'email' => 'user1@example.com',
                'email_verified_at' => null,
                'verification' => false,
                'experience' => 0,
                'gender' => 'female',
                'language' => 'en',
                'age' => 25,
                'password' => Hash::make('password'),
                'role_id' => 2,
                'usingApps_id' => 2,
                'userSettings_id' => 2,
                'status_id' => 2,
                'location_id' => 2,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ];

        if (app()->environment('development')) {
            $users[] = [
                'username' => 'dev_user',
                'slug' => 'dev-user',
                'description' => 'Development user',
                'email' => 'dev_user@example.com',
                'email_verified_at' => now(),
                'verification' => true,
                'experience' => 100,
                'gender' => 'other',
                'age' => 22,
                'password' => Hash::make('devpassword'),
                'role_id' => 3,
                'usingApps_id' => 3,
                'userSettings_id' => 3,
                'status_id' => 3,
                'location_id' => 3,
                'created_at' => now(),
                'updated_at' => now(),
            ];
        }

        DB::table('users')->insert($users);
    }
}
