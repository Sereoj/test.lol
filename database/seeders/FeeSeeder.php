<?php

namespace Database\Seeders;

use App\Models\Fee;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class FeeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run()
    {
        // Очищаем таблицу перед заполнением (если необходимо)
        DB::table('fees')->truncate();

        // Вставка данных для различных типов комиссий, валют и сервисов
        DB::table('fees')->insert([
            // Комиссии для эквайринга (acquiring)
            [
                'type' => 'acquiring', // Комиссия для Tinkoff в RUB
                'gateway' => 'tinkoff',
                'fixed_amount' => 5.0000,
                'percentage' => 2.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'acquiring', // Комиссия для AnyPay в USD
                'gateway' => 'anypay',
                'fixed_amount' => 3.0000,
                'percentage' => 3.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'acquiring', // Комиссия для Enot в EUR
                'gateway' => 'enot',
                'fixed_amount' => 4.0000,
                'percentage' => 1.50,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'acquiring', // Комиссия для Selection в USD
                'gateway' => 'selection',
                'fixed_amount' => 2.0000,
                'percentage' => 4.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Комиссии для платформы (platform)
            [
                'type' => 'platform', // Платформенная комиссия в RUB
                'gateway' => null,
                'fixed_amount' => 10.0000,
                'percentage' => 5.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'platform', // Платформенная комиссия в USD
                'gateway' => null,
                'fixed_amount' => 1.0000,
                'percentage' => 6.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],

            // Комиссии для вывода средств (withdrawal)
            [
                'type' => 'withdrawal', // Комиссия для вывода в RUB
                'gateway' => null,
                'fixed_amount' => 1.0000,
                'percentage' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'withdrawal', // Комиссия для вывода в USD
                'gateway' => null,
                'fixed_amount' => 0.5000,
                'percentage' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
            [
                'type' => 'withdrawal', // Комиссия для вывода в EUR
                'gateway' => null,
                'fixed_amount' => 0.7500,
                'percentage' => 0.00,
                'created_at' => now(),
                'updated_at' => now(),
            ],
        ]);

        $this->command->info('Таблица fees успешно засеяна!');
    }
}
