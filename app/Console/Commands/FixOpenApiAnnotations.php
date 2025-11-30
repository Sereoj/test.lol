<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class FixOpenApiAnnotations extends Command
{
    protected $signature = 'openapi:fix-annotations';

    protected $description = 'Fix malformed OpenAPI annotations (add line breaks after single-line comments)';

    public function handle()
    {
        $this->info('Fixing OpenAPI annotations...');

        $files = File::allFiles(app_path('Http/Controllers'));
        $fixed = 0;

        foreach ($files as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $content = File::get($file->getPathname());
            $originalContent = $content;

            // Исправляем все варианты слипшихся комментариев с docblocks
            // Паттерн 1: // комментарий    /**
            $content = preg_replace(
                '/\/\/([^\n]+)\s+\/\*\*/',
                "//\$1\n    /**",
                $content
            );

            // Паттерн 2: // комментарий/**
            $content = preg_replace(
                '/\/\/([^\n]+)\/\*\*/',
                "//\$1\n    /**",
                $content
            );

            // Паттерн 3: Множественные пробелы между комментарием и /** (более 10)
            $content = preg_replace(
                '/\/\/([^\n]+)(\s{10,})\/\*\*/',
                "//\$1\n    /**",
                $content
            );

            if ($content !== $originalContent) {
                File::put($file->getPathname(), $content);
                $fixed++;
                $this->line("  ✓ Fixed: " . $file->getRelativePathname());
            }
        }

        $this->newLine();
        $this->info("✓ Fixed {$fixed} files");

        return 0;
    }
}
