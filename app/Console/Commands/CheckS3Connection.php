<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class CheckS3Connection extends Command
{
    protected $signature = 'storage:check-s3';

    protected $description = 'Check S3 storage connection and configuration';

    public function handle(): int
    {
        $this->info('🔍 Checking S3 configuration...');

        // Проверяем переменные окружения
        $requiredEnvVars = [
            'AWS_ACCESS_KEY_ID',
            'AWS_SECRET_ACCESS_KEY',
            'AWS_DEFAULT_REGION',
            'AWS_BUCKET',
            'AWS_ENDPOINT',
        ];

        $missingVars = [];
        foreach ($requiredEnvVars as $var) {
            if (empty(env($var))) {
                $missingVars[] = $var;
            }
        }

        if (!empty($missingVars)) {
            $this->error('❌ Missing required environment variables:');
            foreach ($missingVars as $var) {
                $this->line("   - {$var}");
            }
            return self::FAILURE;
        }

        $this->info('✅ All required environment variables are set');

        // Выводим конфигурацию
        $this->newLine();
        $this->info('📋 S3 Configuration:');
        $this->line('   Endpoint: ' . env('AWS_ENDPOINT'));
        $this->line('   Region: ' . env('AWS_DEFAULT_REGION'));
        $this->line('   Bucket: ' . env('AWS_BUCKET'));
        $this->line('   Access Key: ' . substr(env('AWS_ACCESS_KEY_ID'), 0, 8) . '...');

        // Проверяем подключение
        $this->newLine();
        $this->info('⏳ Testing S3 connection...');

        try {
            $disk = Storage::disk('s3');

            // Пытаемся создать тестовый файл
            $testFileName = '.test-connection-' . time() . '.txt';
            $testContent = 'S3 connection test - ' . now()->toDateTimeString();

            $this->line("   Creating test file: {$testFileName}");
            $disk->put($testFileName, $testContent);

            // Проверяем существование файла
            if (!$disk->exists($testFileName)) {
                throw new \Exception('Test file was not created');
            }

            $this->line('   ✅ Test file created successfully');

            // Читаем содержимое
            $readContent = $disk->get($testFileName);
            if ($readContent !== $testContent) {
                throw new \Exception('Test file content mismatch');
            }

            $this->line('   ✅ Test file read successfully');

            // Получаем URL
            $url = $disk->url($testFileName);
            $this->line("   📎 File URL: {$url}");

            // Удаляем тестовый файл
            $disk->delete($testFileName);
            $this->line('   ✅ Test file deleted successfully');

            $this->newLine();
            $this->info('✅ S3 connection is working correctly!');

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->newLine();
            $this->error('❌ S3 connection failed!');
            $this->error('Error: ' . $e->getMessage());

            // Дополнительная диагностика
            $this->newLine();
            $this->warn('💡 Troubleshooting tips:');
            $this->line('   1. Verify AWS credentials are correct');
            $this->line('   2. Check if bucket exists and is accessible');
            $this->line('   3. Verify endpoint URL is correct');
            $this->line('   4. Check network connectivity to S3 endpoint');
            $this->line('   5. Ensure bucket permissions allow read/write operations');

            return self::FAILURE;
        }
    }
}
