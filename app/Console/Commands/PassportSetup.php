<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Laravel\Passport\Client;
use Laravel\Passport\ClientRepository;
use Laravel\Passport\Passport;

class PassportSetup extends Command
{
    protected $signature = 'passport:setup {--force : Force recreation of clients}';

    protected $description = 'Setup Laravel Passport clients (Personal Access and Password Grant)';

    public function handle(): int
    {
        $this->info('🔐 Setting up Laravel Passport...');

        // Генерация ключей если их нет
        if (!file_exists(Passport::keyPath('oauth-private.key'))) {
            $this->warn('OAuth keys not found, generating...');
            $this->call('passport:keys', ['--force' => true]);
            $this->info('✅ OAuth keys generated');
        } else {
            $this->info('✅ OAuth keys already exist');
        }

        // Проверяем Personal Access Client
        $this->setupPersonalAccessClient();

        // Проверяем Password Grant Client
        $this->setupPasswordGrantClient();

        $this->newLine();
        $this->info('✅ Passport setup complete!');

        return self::SUCCESS;
    }

    private function setupPersonalAccessClient(): void
    {
        $clientId = env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID');
        $clientSecret = env('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET');

        // Проверяем существует ли клиент в БД
        $existingClient = null;
        if ($clientId) {
            $existingClient = Client::find($clientId);
        }

        // Если клиент не найден или force флаг установлен
        if (!$existingClient || $this->option('force')) {
            $this->warn('Creating Personal Access Client...');

            // Удаляем старый клиент если есть
            if ($existingClient && $this->option('force')) {
                $existingClient->delete();
            }

            // Создаем новый Personal Access Client
            $clientRepository = new ClientRepository();
            $client = $clientRepository->createPersonalAccessClient(
                null,
                'Laravel Personal Access Client',
                config('app.url')
            );

            // Обновляем .env файл
            $this->updateEnvFile('PASSPORT_PERSONAL_ACCESS_CLIENT_ID', $client->id);
            $this->updateEnvFile('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET', $client->secret);

            $this->info("✅ Personal Access Client created: {$client->id}");
            $this->warn('⚠️  Please restart your application to load new .env values');
        } else {
            $this->info("✅ Personal Access Client exists: {$existingClient->id}");
        }
    }

    private function setupPasswordGrantClient(): void
    {
        $clientId = env('PASSPORT_PASSWORD_CLIENT_ID');
        $clientSecret = env('PASSPORT_PASSWORD_CLIENT_SECRET');

        // Проверяем существует ли клиент в БД
        $existingClient = null;
        if ($clientId) {
            $existingClient = Client::find($clientId);
        }

        // Если клиент не найден или force флаг установлен
        if (!$existingClient || $this->option('force')) {
            $this->warn('Creating Password Grant Client...');

            // Удаляем старый клиент если есть
            if ($existingClient && $this->option('force')) {
                $existingClient->delete();
            }

            // Создаем новый Password Grant Client
            $clientRepository = new ClientRepository();
            $client = $clientRepository->createPasswordGrantClient(
                null,
                'Wallone Password Grant Client',
                config('app.url')
            );

            // Обновляем .env файл
            $this->updateEnvFile('PASSPORT_PASSWORD_CLIENT_ID', $client->id);
            $this->updateEnvFile('PASSPORT_PASSWORD_CLIENT_SECRET', $client->secret);

            $this->info("✅ Password Grant Client created: {$client->id}");
            $this->warn('⚠️  Please restart your application to load new .env values');
        } else {
            $this->info("✅ Password Grant Client exists: {$existingClient->id}");
        }
    }

    private function updateEnvFile(string $key, string $value): void
    {
        $envFile = base_path('.env');

        if (!file_exists($envFile)) {
            $this->error('.env file not found!');
            return;
        }

        $content = file_get_contents($envFile);

        // Проверяем существует ли ключ
        if (preg_match("/^{$key}=/m", $content)) {
            // Обновляем существующее значение
            $content = preg_replace(
                "/^{$key}=.*/m",
                "{$key}={$value}",
                $content
            );
        } else {
            // Добавляем новый ключ в конец файла
            $content .= "\n{$key}={$value}\n";
        }

        file_put_contents($envFile, $content);
        $this->line("Updated .env: {$key}");
    }
}
