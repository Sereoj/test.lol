<?php

namespace App\Console\Commands;

use Exception;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Storage;

class TestS3Connection extends Command
{
    protected $signature = 'storage:test-s3';

    protected $description = 'Test S3 connection and configuration';

    public function handle(): int
    {
        $this->info('Testing S3 Connection...');
        $this->newLine();

        // Show configuration
        $this->info('Current Configuration:');
        $this->table(
            ['Parameter', 'Value'],
            [
                ['Filesystem Disk', config('filesystems.default')],
                ['S3 Endpoint', config('filesystems.disks.s3.endpoint')],
                ['S3 Bucket', config('filesystems.disks.s3.bucket')],
                ['S3 Region', config('filesystems.disks.s3.region')],
                ['S3 URL', config('filesystems.disks.s3.url')],
                ['Use Path Style', config('filesystems.disks.s3.use_path_style_endpoint') ? 'true' : 'false'],
                ['Access Key', substr(config('filesystems.disks.s3.key'), 0, 8) . '...'],
            ]
        );
        $this->newLine();

        // Test 1: Check if S3 disk exists
        $this->info('Test 1: Checking if S3 disk is configured...');
        try {
            $disk = Storage::disk('s3');
            $this->info('✓ S3 disk is configured');
        } catch (Exception $e) {
            $this->error('✗ S3 disk is not configured: ' . $e->getMessage());
            return Command::FAILURE;
        }

        // Test 2: Try to list files (should work even if bucket is empty)
        $this->info('Test 2: Attempting to list files in bucket...');
        try {
            $files = Storage::disk('s3')->files();
            $this->info('✓ Successfully connected to bucket');
            $this->info('  Files found: ' . count($files));
        } catch (Exception $e) {
            $this->error('✗ Failed to connect to bucket');
            $this->error('  Error: ' . $e->getMessage());

            if (str_contains($e->getMessage(), 'credentials')) {
                $this->warn('  Hint: Check your AWS_ACCESS_KEY_ID and AWS_SECRET_ACCESS_KEY');
            }
            if (str_contains($e->getMessage(), 'resolve')) {
                $this->warn('  Hint: Check your AWS_ENDPOINT URL');
            }

            return Command::FAILURE;
        }

        // Test 3: Try to write a test file
        $this->info('Test 3: Attempting to write a test file...');
        try {
            $testContent = 'Test file created at ' . now()->toDateTimeString();
            $testPath = 'test-' . time() . '.txt';

            $result = Storage::disk('s3')->put($testPath, $testContent);

            if ($result) {
                $this->info('✓ Successfully wrote test file: ' . $testPath);

                // Test 4: Try to read the file back
                $this->info('Test 4: Attempting to read the test file...');
                $content = Storage::disk('s3')->get($testPath);

                if ($content === $testContent) {
                    $this->info('✓ Successfully read test file');
                    $this->info('  Content matches: Yes');
                } else {
                    $this->warn('✗ Content mismatch');
                }

                // Test 5: Get URL
                $this->info('Test 5: Getting file URL...');
                $url = Storage::disk('s3')->url($testPath);
                $this->info('✓ File URL: ' . $url);

                // Test 6: Delete test file
                $this->info('Test 6: Cleaning up test file...');
                Storage::disk('s3')->delete($testPath);
                $this->info('✓ Test file deleted');

            } else {
                $this->error('✗ Failed to write test file (returned false)');
                return Command::FAILURE;
            }
        } catch (Exception $e) {
            $this->error('✗ Exception during file operations');
            $this->error('  Error: ' . $e->getMessage());
            $this->error('  Type: ' . get_class($e));

            if (method_exists($e, 'getAwsErrorMessage')) {
                $this->error('  AWS Error: ' . $e->getAwsErrorMessage());
            }

            return Command::FAILURE;
        }

        $this->newLine();
        $this->info('All tests passed! ✓');
        return Command::SUCCESS;
    }
}
