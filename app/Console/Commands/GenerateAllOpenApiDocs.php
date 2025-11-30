<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateAllOpenApiDocs extends Command
{
    protected $signature = 'openapi:generate
                            {--force : Overwrite existing annotations}
                            {--skip-requests : Skip Request schema generation}
                            {--skip-resources : Skip Resource schema generation}
                            {--skip-annotations : Skip controller annotation generation}
                            {--only-swagger : Only generate Swagger documentation from existing annotations}';

    protected $description = 'Generate OpenAPI documentation (annotations + schemas + docs)';

    public function handle()
    {
        $startTime = microtime(true);

        $this->showHeader();

        $steps = $this->getStepsToExecute();
        $currentStep = 1;
        $totalSteps = count($steps);

        foreach ($steps as $step) {
            $this->executeStep($step, $currentStep, $totalSteps);
            $currentStep++;
        }

        $this->showFooter($startTime);

        return Command::SUCCESS;
    }

    protected function showHeader(): void
    {
        $this->newLine();
        $this->info('╔════════════════════════════════════════╗');
        $this->info('║   OpenAPI Documentation Generator     ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();
    }

    protected function getStepsToExecute(): array
    {
        if ($this->option('only-swagger')) {
            return ['swagger'];
        }

        $steps = [];

        if (!$this->option('skip-annotations')) {
            $steps[] = 'annotations';
        }

        if (!$this->option('skip-requests')) {
            $steps[] = 'requests';
        }

        if (!$this->option('skip-resources')) {
            $steps[] = 'resources';
        }

        $steps[] = 'swagger';

        return $steps;
    }

    protected function executeStep(string $step, int $current, int $total): void
    {
        $this->info("Step {$current}/{$total}: " . $this->getStepDescription($step));

        match ($step) {
            'annotations' => $this->call('openapi:generate-annotations', [
                '--force' => $this->option('force'),
            ]),
            'requests' => $this->call('openapi:generate-request-schemas', [
                '--force' => $this->option('force'),
            ]),
            'resources' => $this->call('openapi:generate-resource-schemas', [
                '--force' => $this->option('force'),
            ]),
            'swagger' => $this->call('l5-swagger:generate'),
            default => 0,
        };

        $this->newLine();
    }

    protected function getStepDescription(string $step): string
    {
        return match ($step) {
            'annotations' => 'Generating controller annotations',
            'requests' => 'Generating Request schemas',
            'resources' => 'Generating Resource schemas',
            'swagger' => 'Generating Swagger documentation',
            default => 'Unknown step',
        };
    }

    protected function showFooter(float $startTime): void
    {
        $duration = round(microtime(true) - $startTime, 2);

        $this->info('╔════════════════════════════════════════╗');
        $this->info('║  ✓ Documentation Generated!           ║');
        $this->info('╚════════════════════════════════════════╝');
        $this->newLine();

        $this->line("⏱  Completed in {$duration}s");
        $this->newLine();

        $this->info('📚 Available endpoints:');
        $this->line('  • Swagger UI:  ' . url('/api/documentation'));
        $this->line('  • JSON spec:   ' . storage_path('api-docs/api-docs.json'));
        $this->line('  • YAML spec:   ' . storage_path('api-docs/api-docs.yaml'));
        $this->newLine();

        $this->comment('💡 Quick commands:');
        $this->line('  • Full regeneration:    php artisan openapi:generate --force');
        $this->line('  • Only Swagger:         php artisan openapi:generate --only-swagger');
        $this->line('  • Skip resources:       php artisan openapi:generate --skip-resources');
        $this->line('  • Skip requests:        php artisan openapi:generate --skip-requests');
        $this->line('  • Skip annotations:     php artisan openapi:generate --skip-annotations');
        $this->newLine();
    }
}
