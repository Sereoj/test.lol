<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class RolesTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     * php artisan db:seed --class=RolesTableSeeder --env=development
     * php artisan db:seed --class=RolesTableSeeder --env=production
     */
    public function run(): void
    {
        $roles = [
            ['name' => json_encode(['en' => 'Admin', 'ru' => 'Администратор']), 'type' => 'admin', 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'User', 'ru' => 'Пользователь']), 'type' => 'user', 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'Moderator', 'ru' => 'Модератор']), 'type' => 'moderator', 'created_at' => now(), 'updated_at' => now()],
        ];

        if (app()->environment('development')) {
            $roles[] = ['name' => json_encode(['en' => 'Developer', 'ru' => 'Разработчик']), 'type' => 'guest', 'created_at' => now(), 'updated_at' => now()];
        }

        DB::table('roles')->insert($roles);

        $this->command->info('Roles table seeded successfully!');
    }
}
