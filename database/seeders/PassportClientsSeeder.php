<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Laravel\Passport\ClientRepository;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PassportClientsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Проверяем наличие ключей OAuth и создаем их при необходимости
        if (!File::exists(storage_path('oauth-private.key')) || !File::exists(storage_path('oauth-public.key'))) {
            $this->command->info('Генерация ключей Passport...');
            Artisan::call('passport:keys', ['--force' => true]);
            $this->command->info('Ключи Passport сгенерированы.');
        }

        $clientRepository = app(ClientRepository::class);

        // Проверяем существование клиентов перед созданием
        try {
            $personalClient = $clientRepository->createPersonalAccessClient(
                1,
                'Personal Access Client',
                env('APP_URL'),
            );

            $passwordClient = $clientRepository->createPasswordGrantClient(
                1,
                'Password Grant Client',
                env('APP_URL'),
            );

            if ($personalClient) {
                $secret = $personalClient->secret;
                $this->command->info('Personal Access Client secret: '.$secret);
            } else {
                $this->command->error('Failed to create personal access client.');
            }

            if ($passwordClient) {
                $secret = $passwordClient->secret;
                $this->command->info('Password Grant Client secret: '.$secret);
            } else {
                $this->command->error('Failed to create password grant client.');
            }
        } catch (\Exception $e) {
            $this->command->error('Ошибка при создании клиентов Passport: '.$e->getMessage());
        }
    }
}
