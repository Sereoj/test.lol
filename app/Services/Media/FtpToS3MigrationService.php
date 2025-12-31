<?php

namespace App\Services\Media;

use App\Models\Media\Avatar;
use App\Models\Media\Media;
use App\Models\Users\User;
use App\Traits\LoggableTrait;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class FtpToS3MigrationService
{
    use LoggableTrait;

    private int $totalFiles = 0;
    private int $migratedFiles = 0;
    private int $failedFiles = 0;
    private array $errors = [];

    /**
     * Migrate all media files from FTP to S3
     *
     * @param bool $dryRun If true, only simulate migration without actual changes
     * @return array Migration statistics
     */
    public function migrate(bool $dryRun = false): array
    {
        $this->logInfo('Starting FTP to S3 migration', ['dry_run' => $dryRun]);

        DB::beginTransaction();

        try {
            // Migrate media files
            $this->migrateMediaFiles($dryRun);

            // Migrate avatar files
            $this->migrateAvatarFiles($dryRun);

            // Migrate user cover images
            $this->migrateUserCoverFiles($dryRun);

            if ($dryRun) {
                DB::rollBack();
                $this->logInfo('Dry run completed - no changes committed');
            } else {
                DB::commit();
                $this->logInfo('Migration completed successfully');
            }

            return $this->getStatistics();
        } catch (\Exception $e) {
            DB::rollBack();
            $this->logError('Migration failed', ['error' => $e->getMessage()], $e);
            throw $e;
        }
    }

    /**
     * Migrate files from media table
     */
    private function migrateMediaFiles(bool $dryRun): void
    {
        $mediaFiles = Media::where('disk', 'ftp')
            ->whereNotNull('file_path')
            ->where('file_path', '!=', '')
            ->get();

        $this->logInfo("Found {$mediaFiles->count()} media files to migrate");

        foreach ($mediaFiles as $media) {
            // Skip if already a full URL
            if ($this->isExternalUrl($media->file_path)) {
                $this->logInfo("Skipping external URL", [
                    'file_path' => $media->file_path,
                    'identifier' => "media ID {$media->id}"
                ]);
                continue;
            }

            $this->migrateFile(
                $media->file_path,
                'ftp',
                's3',
                function () use ($media, $dryRun) {
                    if (!$dryRun) {
                        $media->disk = 's3';
                        $media->save();
                    }
                },
                "media ID {$media->id}"
            );
        }
    }

    /**
     * Migrate files from avatars table
     */
    private function migrateAvatarFiles(bool $dryRun): void
    {
        $avatars = Avatar::where('disk', 'ftp')
            ->whereNotNull('path')
            ->where('path', '!=', '')
            ->get();

        $this->logInfo("Found {$avatars->count()} avatar files to migrate");

        foreach ($avatars as $avatar) {
            // Skip if already a full URL
            if ($this->isExternalUrl($avatar->path)) {
                $this->logInfo("Skipping external URL", [
                    'file_path' => $avatar->path,
                    'identifier' => "avatar ID {$avatar->id}"
                ]);
                continue;
            }

            $this->migrateFile(
                $avatar->path,
                'ftp',
                's3',
                function () use ($avatar, $dryRun) {
                    if (!$dryRun) {
                        $avatar->disk = 's3';
                        $avatar->save();
                    }
                },
                "avatar ID {$avatar->id}"
            );
        }
    }

    /**
     * Migrate user cover images
     */
    private function migrateUserCoverFiles(bool $dryRun): void
    {
        $users = User::whereNotNull('cover')
            ->where('cover', '!=', '')
            ->where('disk', 'ftp')
            ->get();

        $this->logInfo("Found {$users->count()} user cover images to migrate");

        foreach ($users as $user) {
            // Skip if already a full URL
            if ($this->isExternalUrl($user->cover)) {
                $this->logInfo("Skipping external URL", [
                    'file_path' => $user->cover,
                    'identifier' => "user ID {$user->id} cover"
                ]);
                continue;
            }

            $this->migrateFile(
                $user->cover,
                'ftp',
                's3',
                function () use ($user, $dryRun) {
                    if (!$dryRun) {
                        $user->disk = 's3';
                        $user->save();
                    }
                },
                "user ID {$user->id} cover"
            );
        }
    }

    /**
     * Migrate a single file from FTP to S3
     *
     * @param string $filePath Relative path to file
     * @param string $sourceDisk Source disk name
     * @param string $targetDisk Target disk name
     * @param callable $onSuccess Callback to execute on successful migration
     * @param string $identifier File identifier for logging
     */
    private function migrateFile(
        string $filePath,
        string $sourceDisk,
        string $targetDisk,
        callable $onSuccess,
        string $identifier
    ): void {
        $this->totalFiles++;

        try {
            // Check if file exists on source disk
            if (!Storage::disk($sourceDisk)->exists($filePath)) {
                $this->logWarning("File not found on {$sourceDisk}", [
                    'file_path' => $filePath,
                    'identifier' => $identifier
                ]);
                $this->failedFiles++;
                $this->errors[] = [
                    'file' => $filePath,
                    'identifier' => $identifier,
                    'error' => 'File not found on source disk'
                ];
                return;
            }

            // Check if file already exists on target disk
            if (Storage::disk($targetDisk)->exists($filePath)) {
                $this->logInfo("File already exists on {$targetDisk}, skipping copy", [
                    'file_path' => $filePath,
                    'identifier' => $identifier
                ]);
            } else {
                // Read file from source
                $fileContent = Storage::disk($sourceDisk)->get($filePath);

                // Write to target
                Storage::disk($targetDisk)->put($filePath, $fileContent);

                $this->logInfo("File copied successfully", [
                    'file_path' => $filePath,
                    'identifier' => $identifier,
                    'from' => $sourceDisk,
                    'to' => $targetDisk
                ]);
            }

            // Execute success callback (update database)
            $onSuccess();

            $this->migratedFiles++;
        } catch (\Exception $e) {
            $this->failedFiles++;
            $this->errors[] = [
                'file' => $filePath,
                'identifier' => $identifier,
                'error' => $e->getMessage()
            ];

            $this->logError("Failed to migrate file", [
                'file_path' => $filePath,
                'identifier' => $identifier,
                'error' => $e->getMessage()
            ], $e);
        }
    }

    /**
     * Check if path is an external URL
     */
    private function isExternalUrl(string $path): bool
    {
        return str_starts_with($path, 'http://') || str_starts_with($path, 'https://');
    }

    /**
     * Get migration statistics
     */
    public function getStatistics(): array
    {
        return [
            'total_files' => $this->totalFiles,
            'migrated_files' => $this->migratedFiles,
            'failed_files' => $this->failedFiles,
            'errors' => $this->errors,
            'success_rate' => $this->totalFiles > 0
                ? round(($this->migratedFiles / $this->totalFiles) * 100, 2)
                : 0
        ];
    }
}
