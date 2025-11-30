<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class GenerateSwaggerDocs extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'swagger:generate';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate Swagger/OpenAPI documentation';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Generating Swagger documentation...');

        $this->call('l5-swagger:generate');

        $this->info('Swagger documentation generated successfully!');
        $this->info('Available at: /api/v1/openapi.json');

        return 0;
    }
}
