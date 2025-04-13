<?php

namespace App\Console\Commands;

use Database\Seeders\DevelopmentSeeder;
use Illuminate\Console\Command;

class SeedDevelopmentData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'dev:seed {--force : Force the operation to run in production}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Seed the database with mock data for development';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        if (!app()->environment('local', 'development') && !$this->option('force')) {
            $this->error('This command can only be run in the local or development environment');
            $this->comment('If you want to run it in production anyway, use the --force flag');
            return 1;
        }

        if (app()->environment('production') && $this->option('force')) {
            if (!$this->confirm('Are you sure you want to seed mock data in the PRODUCTION environment?')) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        $this->info('Starting to seed mock data for development...');
        
        // Запускаем сидер
        $this->call('db:seed', [
            '--class' => DevelopmentSeeder::class,
        ]);
        
        $this->info('Mock data seeded successfully!');
        
        return 0;
    }
} 