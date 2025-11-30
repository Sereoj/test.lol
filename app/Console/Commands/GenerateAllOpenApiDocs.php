<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAllOpenApiDocs extends Command
{
    protected $signature = 'openapi:generate-all
                            {--force : Overwrite existing annotations}';

    protected $description = 'Generate all OpenAPI documentation (annotations + schemas + docs)';

    public function handle()
    {
        $this->info('========================================');
        $this->info('  OpenAPI Auto-Generation');
        $this->info('========================================');
        $this->newLine();

        // 1. Генерируем аннотации для контроллеров
        $this->info('Step 1/5: Generating controller annotations...');
        $this->call('openapi:generate-annotations', [
            '--force' => $this->option('force'),
        ]);

        $this->newLine();

        // 1.5. Исправляем форматирование аннотаций
        $this->info('Step 1.5/5: Fixing annotation formatting...');
        $this->call('openapi:fix-annotations');

        $this->newLine();

        // 2. Генерируем схемы для Request классов
        $this->info('Step 2/5: Generating Request schemas...');
        $this->call('openapi:generate-request-schemas', [
            '--force' => $this->option('force'),
        ]);

        $this->newLine();

        // 3. Генерируем схемы для Resource классов
        $this->info('Step 3/5: Generating Resource schemas...');
        $this->call('openapi:generate-resource-schemas', [
            '--force' => $this->option('force'),
        ]);

        $this->newLine();

        // 4. Генерируем финальную документацию
        $this->info('Step 4/5: Generating OpenAPI documentation...');
        $this->call('l5-swagger:generate');

        $this->newLine();
        $this->info('========================================');
        $this->info('✓ All OpenAPI documentation generated!');
        $this->info('========================================');
        $this->newLine();

        $this->info('Available endpoints:');
        $this->line('  - Swagger UI: /api/documentation');
        $this->line('  - JSON spec:  /api/v1/openapi.json');
        $this->line('  - YAML spec:  /api/v1/openapi.yaml');

        return 0;
    }
}
