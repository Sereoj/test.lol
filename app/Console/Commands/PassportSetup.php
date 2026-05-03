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

        // Очистка кеша конфигурации после обновления .env
        $this->call('config:clear');

        $this->newLine();
        $this->info('✅ Passport setup complete!');

        return self::SUCCESS;
    }

    private function setupPersonalAccessClient(): void
    {
        // Ищем клиент по типу, а не по ID из .env
        $existingClient = Client::where('personal_access_client', 1)->first();

        // Если force флаг установлен - пересоздаем клиента
        if ($existingClient && $this->option('force')) {
            $this->warn('Force flag detected, recreating Personal Access Client...');
            $existingClient->delete();
            $existingClient = null;
        }

        // Если клиент не найден - создаем новый
        if (!$existingClient) {
            $this->warn('Creating Personal Access Client...');

            $clientRepository = new ClientRepository();
            $client = $clientRepository->createPersonalAccessClient(
                null,
                'Laravel Personal Access Client',
                config('app.url')
            );

            // Обновляем .env файл и применяем в текущем процессе
            $this->updateEnvFile('PASSPORT_PERSONAL_ACCESS_CLIENT_ID', $client->id);
            $this->updateEnvFile('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET', $client->secret);
            $this->applyEnvToCurrentProcess('PASSPORT_PERSONAL_ACCESS_CLIENT_ID', $client->id);
            $this->applyEnvToCurrentProcess('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET', $client->secret);

            $this->info("✅ Personal Access Client created: {$client->id}");
        } else {
            // Клиент существует - синхронизируем .env с БД
            $envClientId = env('PASSPORT_PERSONAL_ACCESS_CLIENT_ID');

            if ($envClientId != $existingClient->id) {
                $this->warn("Syncing .env with database (DB: {$existingClient->id}, .env: {$envClientId})");
                $this->updateEnvFile('PASSPORT_PERSONAL_ACCESS_CLIENT_ID', $existingClient->id);
                $this->updateEnvFile('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET', $existingClient->secret);
                $this->applyEnvToCurrentProcess('PASSPORT_PERSONAL_ACCESS_CLIENT_ID', $existingClient->id);
                $this->applyEnvToCurrentProcess('PASSPORT_PERSONAL_ACCESS_CLIENT_SECRET', $existingClient->secret);
                $this->info("✅ .env synchronized with database");
            } else {
                $this->info("✅ Personal Access Client exists: {$existingClient->id}");
            }
        }
    }

    private function setupPasswordGrantClient(): void
    {
        // Ищем клиент по типу, а не по ID из .env
        $existingClient = Client::where('password_client', 1)->first();

        // Если force флаг установлен - пересоздаем клиента
        if ($existingClient && $this->option('force')) {
            $this->warn('Force flag detected, recreating Password Grant Client...');
            $existingClient->delete();
            $existingClient = null;
        }

        // Если клиент не найден - создаем новый
        if (!$existingClient) {
            $this->warn('Creating Password Grant Client...');

            $clientRepository = new ClientRepository();
            $client = $clientRepository->createPasswordGrantClient(
                null,
                'TestApp Password Grant Client',
                config('app.url')
            );

            // Обновляем .env файл и применяем в текущем процессе
            $this->updateEnvFile('PASSPORT_PASSWORD_CLIENT_ID', $client->id);
            $this->updateEnvFile('PASSPORT_PASSWORD_CLIENT_SECRET', $client->secret);
            $this->applyEnvToCurrentProcess('PASSPORT_PASSWORD_CLIENT_ID', $client->id);
            $this->applyEnvToCurrentProcess('PASSPORT_PASSWORD_CLIENT_SECRET', $client->secret);

            $this->info("✅ Password Grant Client created: {$client->id}");
        } else {
            // Клиент существует - синхронизируем .env с БД
            $envClientId = env('PASSPORT_PASSWORD_CLIENT_ID');

            if ($envClientId != $existingClient->id) {
                $this->warn("Syncing .env with database (DB: {$existingClient->id}, .env: {$envClientId})");
                $this->updateEnvFile('PASSPORT_PASSWORD_CLIENT_ID', $existingClient->id);
                $this->updateEnvFile('PASSPORT_PASSWORD_CLIENT_SECRET', $existingClient->secret);
                $this->applyEnvToCurrentProcess('PASSPORT_PASSWORD_CLIENT_ID', $existingClient->id);
                $this->applyEnvToCurrentProcess('PASSPORT_PASSWORD_CLIENT_SECRET', $existingClient->secret);
                $this->info("✅ .env synchronized with database");
            } else {
                $this->info("✅ Password Grant Client exists: {$existingClient->id}");
            }
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

    private function applyEnvToCurrentProcess(string $key, string $value): void
    {
        // Применяем изменения в текущем процессе
        putenv("{$key}={$value}");
        $_ENV[$key] = $value;
        $_SERVER[$key] = $value;
    }
}
