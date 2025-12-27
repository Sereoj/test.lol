<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Redis;

class CheckProductionReadiness extends Command
{
    protected $signature = 'production:check';
    protected $description = 'Check if the application is ready for production deployment';

    private array $checks = [];
    private int $passedChecks = 0;
    private int $failedChecks = 0;

    public function handle(): int
    {
        $this->info('=================================================');
        $this->info('   Production Readiness Check for Wallone');
        $this->info('=================================================');
        $this->newLine();

        // Run all checks
        $this->checkEnvironment();
        $this->checkDebugMode();
        $this->checkAppKey();
        $this->checkDatabaseConnection();
        $this->checkRedisConnection();
        $this->checkStoragePermissions();
        $this->checkRequiredEnvVariables();
        $this->checkCacheConfiguration();
        $this->checkQueueConfiguration();
        $this->checkSessionConfiguration();

        // Display summary
        $this->newLine();
        $this->info('=================================================');
        $this->info('   Summary');
        $this->info('=================================================');
        $this->newLine();

        foreach ($this->checks as $check) {
            if ($check['status'] === 'pass') {
                $this->line("<fg=green>✓</> {$check['message']}");
            } else {
                $this->line("<fg=red>✗</> {$check['message']}");
                if (!empty($check['details'])) {
                    $this->line("  <fg=yellow>{$check['details']}</>");
                }
            }
        }

        $this->newLine();
        $totalChecks = $this->passedChecks + $this->failedChecks;
        $this->info("Total checks: {$totalChecks}");
        $this->info("<fg=green>Passed: {$this->passedChecks}</>");
        $this->info("<fg=red>Failed: {$this->failedChecks}</>");
        $this->newLine();

        if ($this->failedChecks === 0) {
            $this->info('<fg=green>✓ All checks passed! Application is ready for production.</>');
            return Command::SUCCESS;
        } else {
            $this->error('✗ Some checks failed. Please fix the issues before deploying to production.');
            return Command::FAILURE;
        }
    }

    private function pass(string $message): void
    {
        $this->checks[] = ['status' => 'pass', 'message' => $message, 'details' => ''];
        $this->passedChecks++;
    }

    private function fail(string $message, string $details = ''): void
    {
        $this->checks[] = ['status' => 'fail', 'message' => $message, 'details' => $details];
        $this->failedChecks++;
    }

    private function checkEnvironment(): void
    {
        $this->info('Checking environment configuration...');

        $env = config('app.env');
        if ($env === 'production') {
            $this->pass('Environment is set to production');
        } else {
            $this->fail(
                'Environment is not set to production',
                "Current: {$env}. Set APP_ENV=production in .env"
            );
        }
    }

    private function checkDebugMode(): void
    {
        $debug = config('app.debug');
        if ($debug === false) {
            $this->pass('Debug mode is disabled');
        } else {
            $this->fail(
                'Debug mode is enabled',
                'Set APP_DEBUG=false in .env for production'
            );
        }
    }

    private function checkAppKey(): void
    {
        $key = config('app.key');
        if (!empty($key)) {
            $this->pass('Application key is set');
        } else {
            $this->fail(
                'Application key is not set',
                'Run: php artisan key:generate'
            );
        }
    }

    private function checkDatabaseConnection(): void
    {
        $this->info('Checking database connection...');

        try {
            DB::connection()->getPdo();
            $dbName = DB::connection()->getDatabaseName();
            $this->pass("Database connection successful ({$dbName})");
        } catch (\Exception $e) {
            $this->fail(
                'Database connection failed',
                $e->getMessage()
            );
        }
    }

    private function checkRedisConnection(): void
    {
        $this->info('Checking Redis connection...');

        try {
            Redis::ping();
            $this->pass('Redis connection successful');
        } catch (\Exception $e) {
            $this->fail(
                'Redis connection failed',
                $e->getMessage()
            );
        }
    }

    private function checkStoragePermissions(): void
    {
        $this->info('Checking storage permissions...');

        $paths = [
            storage_path('app'),
            storage_path('framework'),
            storage_path('logs'),
            base_path('bootstrap/cache'),
        ];

        $allWritable = true;
        $nonWritablePaths = [];

        foreach ($paths as $path) {
            if (!is_writable($path)) {
                $allWritable = false;
                $nonWritablePaths[] = $path;
            }
        }

        if ($allWritable) {
            $this->pass('All storage directories are writable');
        } else {
            $this->fail(
                'Some storage directories are not writable',
                'Fix permissions: chmod -R 775 ' . implode(' ', $nonWritablePaths)
            );
        }
    }

    private function checkRequiredEnvVariables(): void
    {
        $this->info('Checking required environment variables...');

        $required = [
            'APP_NAME',
            'APP_ENV',
            'APP_KEY',
            'APP_URL',
            'DB_HOST',
            'DB_DATABASE',
            'DB_USERNAME',
            'DB_PASSWORD',
        ];

        $missing = [];
        foreach ($required as $var) {
            if (empty(env($var))) {
                $missing[] = $var;
            }
        }

        if (empty($missing)) {
            $this->pass('All required environment variables are set');
        } else {
            $this->fail(
                'Missing required environment variables',
                'Missing: ' . implode(', ', $missing)
            );
        }
    }

    private function checkCacheConfiguration(): void
    {
        $driver = config('cache.default');
        if ($driver === 'redis') {
            $this->pass("Cache driver is set to redis (recommended for production)");
        } else {
            $this->fail(
                "Cache driver is '{$driver}'",
                'Set CACHE_DRIVER=redis in .env for better performance'
            );
        }
    }

    private function checkQueueConfiguration(): void
    {
        $driver = config('queue.default');
        if ($driver === 'redis') {
            $this->pass("Queue driver is set to redis (recommended for production)");
        } elseif ($driver === 'sync') {
            $this->fail(
                "Queue driver is 'sync' (synchronous)",
                'Set QUEUE_CONNECTION=redis in .env for asynchronous job processing'
            );
        } else {
            $this->pass("Queue driver is set to {$driver}");
        }
    }

    private function checkSessionConfiguration(): void
    {
        $driver = config('session.driver');
        if ($driver === 'redis') {
            $this->pass("Session driver is set to redis (recommended for production)");
        } elseif ($driver === 'file') {
            $this->fail(
                "Session driver is 'file'",
                'Set SESSION_DRIVER=redis in .env for better performance and scalability'
            );
        } else {
            $this->pass("Session driver is set to {$driver}");
        }
    }
}
