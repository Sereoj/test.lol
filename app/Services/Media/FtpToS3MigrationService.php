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
        $this->logInfo('Начало миграции с FTP на S3', ['dry_run' => $dryRun]);

        try {
            // Migrate media files
            $this->migrateMediaFiles($dryRun);

            // Migrate avatar files
            $this->migrateAvatarFiles($dryRun);

            // Migrate user cover images
            $this->migrateUserCoverFiles($dryRun);

            $this->logInfo('Миграция успешно завершена');
            return $this->getStatistics();
        } catch (\Exception $e) {
            $this->logError('Ошибка миграции', ['error' => $e->getMessage()], $e);
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
            ->lockForUpdate()
            ->get();

        $this->logInfo("Найдено {$mediaFiles->count()} медиафайлов для миграции");

        foreach ($mediaFiles as $media) {
            // Skip if already a full URL
            if ($this->isExternalUrl($media->file_path)) {
                $this->logInfo("Пропуск внешнего URL", [
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
                        DB::transaction(function () use ($media) {
                            $media->disk = 's3';
                            $media->save();
                        });
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
            ->lockForUpdate()
            ->get();

        $this->logInfo("Найдено {$avatars->count()} аватаров для миграции");

        foreach ($avatars as $avatar) {
            // Skip if already a full URL
            if ($this->isExternalUrl($avatar->path)) {
                $this->logInfo("Пропуск внешнего URL", [
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
                        DB::transaction(function () use ($avatar) {
                            $avatar->disk = 's3';
                            $avatar->save();
                        });
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
            ->lockForUpdate()
            ->get();

        $this->logInfo("Найдено {$users->count()} обложек пользователей для миграции");

        foreach ($users as $user) {
            // Skip if already a full URL
            if ($this->isExternalUrl($user->cover)) {
                $this->logInfo("Пропуск внешнего URL", [
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
                        DB::transaction(function () use ($user) {
                            $user->disk = 's3';
                            $user->save();
                        });
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
                $this->logWarning("Файл не найден на {$sourceDisk}", [
                    'file_path' => $filePath,
                    'identifier' => $identifier
                ]);
                $this->failedFiles++;
                $this->errors[] = [
                    'file' => $filePath,
                    'identifier' => $identifier,
                    'error' => 'Файл не найден на исходном диске'
                ];
                return;
            }

            // Check if file already exists on target disk
            if (Storage::disk($targetDisk)->exists($filePath)) {
                $this->logInfo("Файл уже существует на {$targetDisk}, копирование пропущено", [
                    'file_path' => $filePath,
                    'identifier' => $identifier
                ]);
            } else {
                // Special handling: Check if path is a directory (Laravel Storage bug with nested FTP paths)
                // This happens when files were uploaded using putFile() which creates hashed names
                $actualFilePath = $filePath;
                $fileContent = null;

                // Try to read as file first
                $fileContent = Storage::disk($sourceDisk)->get($filePath);

                // If failed (returns null), try native FTP fallback
                if ($fileContent === null || $fileContent === false) {
                    $this->logInfo("Storage API вернул null, попытка через нативный FTP", [
                        'file_path' => $filePath,
                        'identifier' => $identifier
                    ]);

                    $fileContent = $this->readFileViaFtp($filePath);

                    if ($fileContent !== null) {
                        $this->logInfo("Файл успешно прочитан через нативный FTP", [
                            'file_path' => $filePath,
                            'identifier' => $identifier,
                            'size' => strlen($fileContent)
                        ]);
                    }
                }

                // Check if file content was successfully read
                if ($fileContent === null || $fileContent === false) {
                    throw new \Exception("Не удалось прочитать содержимое файла с {$sourceDisk}");
                }

                // Write to target using the ORIGINAL path from database (not the hashed one)
                Storage::disk($targetDisk)->put($filePath, $fileContent);

                $this->logInfo("Файл успешно скопирован", [
                    'file_path' => $filePath,
                    'actual_source' => $actualFilePath,
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

            $this->logError("Не удалось мигрировать файл", [
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
     * Read file using native FTP connection (fallback for problematic paths)
     */
    private function readFileViaFtp(string $filePath): ?string
    {
        $host = config('filesystems.disks.ftp.host');
        $username = config('filesystems.disks.ftp.username');
        $password = config('filesystems.disks.ftp.password');
        $port = config('filesystems.disks.ftp.port', 21);
        $root = config('filesystems.disks.ftp.root', '/');

        $conn = @ftp_connect($host, $port, 10);
        if (!$conn) {
            return null;
        }

        if (!@ftp_login($conn, $username, $password)) {
            ftp_close($conn);
            return null;
        }

        ftp_pasv($conn, true);

        // Build full path
        $fullPath = rtrim($root, '/') . '/' . ltrim($filePath, '/');

        // Check if this is a directory
        $list = @ftp_nlist($conn, $fullPath);
        if ($list !== false && count($list) > 0) {
            // It's a directory, find the actual file inside
            foreach ($list as $item) {
                if (basename($item) !== '.' && basename($item) !== '..') {
                    $fullPath = $item;
                    break;
                }
            }
        }

        // Download to temp file
        $tempFile = tmpfile();
        $meta = stream_get_meta_data($tempFile);
        $tempPath = $meta['uri'];

        $success = @ftp_get($conn, $tempPath, $fullPath, FTP_BINARY);
        ftp_close($conn);

        if (!$success) {
            fclose($tempFile);
            return null;
        }

        $content = file_get_contents($tempPath);
        fclose($tempFile);

        return $content ?: null;
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
