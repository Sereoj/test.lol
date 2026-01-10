<?php

namespace Database\Seeders;

use App\Models\Billing\MediaPurchase;
use App\Models\Media\Media;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class MediaPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Создаем 15 медиа файлов с исходниками
        Media::factory()
            ->count(15)
            ->withSource()
            ->create();

        // Создаем 5 медиа файлов с конкретной ценой исходника
        Media::factory()
            ->count(5)
            ->withSourcePrice(50.00)
            ->create();

        // Создаем 30 покупок медиа в разных статусах
        MediaPurchase::factory()
            ->count(20)
            ->completed()
            ->create();

        MediaPurchase::factory()
            ->count(5)
            ->pending()
            ->create();

        MediaPurchase::factory()
            ->count(3)
            ->succeeded()
            ->create();

        MediaPurchase::factory()
            ->count(2)
            ->failed()
            ->create();
    }
}
