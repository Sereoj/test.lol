<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppsTableSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $apps = [
            ['name' => json_encode(['en' => 'Photoshop', 'ru' => 'Фотошоп']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'Illustrator', 'ru' => 'Иллюстратор']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'CorelDRAW', 'ru' => 'КорелДРАВ']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'GIMP', 'ru' => 'ГИМП']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'Inkscape', 'ru' => 'Инкскейп']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'Sketch', 'ru' => 'Скетч']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'Affinity Designer', 'ru' => 'Аффинити Дизайнер']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'Krita', 'ru' => 'Крита']), 'created_at' => now(), 'updated_at' => now()],
            ['name' => json_encode(['en' => 'Blender', 'ru' => 'Блендер']), 'created_at' => now(), 'updated_at' => now()],
        ];

        DB::table('apps')->insert($apps);

        $this->command->info('Apps table seeded successfully!');
    }
}
