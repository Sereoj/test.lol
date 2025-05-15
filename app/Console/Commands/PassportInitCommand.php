<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\File;

class PassportInitCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'passport:init';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Инициализация Laravel Passport (генерация ключей и клиентов)';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Начинаем инициализацию Laravel Passport...');

        // Проверяем существование директории storage
        if (!File::exists(storage_path())) {
            $this->error('Директория storage не существует');
            File::makeDirectory(storage_path(), 0775, true);
            $this->info('Директория storage создана');
        }

        // Генерируем ключи для Laravel Passport
        $this->info('Запускаем passport:keys --force...');
        try {
            Artisan::call('passport:keys', ['--force' => true]);
            $this->info('Ключи Passport успешно сгенерированы');
        } catch (\Exception $e) {
            $this->error('Ошибка при генерации ключей: ' . $e->getMessage());
            return 1;
        }

        // Проверяем, созданы ли ключи
        if (!File::exists(storage_path('oauth-private.key')) || !File::exists(storage_path('oauth-public.key'))) {
            $this->error('Ключи не были созданы');
            return 1;
        }

        // Запускаем passport:install
        $this->info('Запускаем passport:install --uuids...');
        try {
            Artisan::call('passport:install', ['--uuids' => true]);
            $this->info('Passport успешно установлен');
        } catch (\Exception $e) {
            $this->error('Ошибка при установке Passport: ' . $e->getMessage());
            // Продолжаем выполнение, так как ключи уже созданы
        }

        // Запускаем сидер
        $this->info('Запускаем сидер PassportClientsSeeder...');
        try {
            Artisan::call('db:seed', ['--class' => 'Database\\Seeders\\PassportClientsSeeder']);
            $this->info('Сидер PassportClientsSeeder успешно выполнен');
        } catch (\Exception $e) {
            $this->error('Ошибка при запуске сидера: ' . $e->getMessage());
        }

        // Устанавливаем правильные права
        $this->info('Устанавливаем права на директорию storage...');
        chmod(storage_path(), 0775);
        chmod(storage_path('oauth-private.key'), 0660);
        chmod(storage_path('oauth-public.key'), 0660);

        $this->info('Инициализация Laravel Passport завершена успешно');
        return 0;
    }
} 