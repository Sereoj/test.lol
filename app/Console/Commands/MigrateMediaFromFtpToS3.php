<?php

namespace App\Console\Commands;

use App\Services\Media\FtpToS3MigrationService;
use Illuminate\Console\Command;

class MigrateMediaFromFtpToS3 extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'media:migrate-ftp-to-s3
                            {--dry-run : Run migration simulation without making actual changes}
                            {--force : Skip confirmation prompt}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Migrate all media files from FTP storage to S3 storage';

    private FtpToS3MigrationService $migrationService;

    /**
     * Create a new command instance.
     */
    public function __construct(FtpToS3MigrationService $migrationService)
    {
        parent::__construct();
        $this->migrationService = $migrationService;
    }

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $dryRun = $this->option('dry-run');
        $force = $this->option('force');

        // Show warning
        $this->warn('==============================================');
        $this->warn('  FTP to S3 Media Migration');
        $this->warn('==============================================');
        $this->newLine();

        if ($dryRun) {
            $this->info('Running in DRY RUN mode - no actual changes will be made');
        } else {
            $this->warn('This will migrate all media files from FTP to S3 storage.');
            $this->warn('Database records will be updated to use S3 disk.');
        }

        $this->newLine();

        // Confirmation
        if (!$force && !$dryRun) {
            if (!$this->confirm('Do you want to continue?', false)) {
                $this->info('Migration cancelled.');
                return self::FAILURE;
            }
        }

        $this->newLine();
        $this->info('Starting migration...');
        $this->newLine();

        // Start progress
        $bar = $this->output->createProgressBar();
        $bar->start();

        try {
            // Run migration
            $statistics = $this->migrationService->migrate($dryRun);

            $bar->finish();
            $this->newLine(2);

            // Display results
            $this->displayResults($statistics, $dryRun);

            return $statistics['failed_files'] > 0 ? self::FAILURE : self::SUCCESS;
        } catch (\Exception $e) {
            $bar->finish();
            $this->newLine(2);

            $this->error('Migration failed with error:');
            $this->error($e->getMessage());
            $this->newLine();

            if ($this->output->isVerbose()) {
                $this->error($e->getTraceAsString());
            }

            return self::FAILURE;
        }
    }

    /**
     * Display migration results
     */
    private function displayResults(array $statistics, bool $dryRun): void
    {
        $this->info('==============================================');
        $this->info('  Migration ' . ($dryRun ? 'Simulation ' : '') . 'Results');
        $this->info('==============================================');
        $this->newLine();

        $this->table(
            ['Metric', 'Value'],
            [
                ['Total Files', $statistics['total_files']],
                ['Migrated Successfully', $statistics['migrated_files']],
                ['Failed', $statistics['failed_files']],
                ['Success Rate', $statistics['success_rate'] . '%'],
            ]
        );

        if ($statistics['failed_files'] > 0) {
            $this->newLine();
            $this->error('Failed Files:');
            $this->newLine();

            $errorRows = array_map(function ($error) {
                return [
                    $error['identifier'] ?? 'N/A',
                    $error['file'],
                    $error['error']
                ];
            }, $statistics['errors']);

            $this->table(
                ['Identifier', 'File Path', 'Error'],
                $errorRows
            );
        }

        $this->newLine();

        if ($dryRun) {
            $this->info('This was a DRY RUN - no actual changes were made.');
            $this->info('Run without --dry-run flag to perform actual migration.');
        } else {
            if ($statistics['failed_files'] === 0) {
                $this->info('✓ All files migrated successfully!');
            } else {
                $this->warn("⚠ Migration completed with {$statistics['failed_files']} errors.");
                $this->warn('Check the error log for details.');
            }
        }

        $this->newLine();
    }
}
