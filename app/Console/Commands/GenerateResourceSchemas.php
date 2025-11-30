<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class GenerateResourceSchemas extends Command
{
    protected $signature = 'openapi:generate-resource-schemas
                            {--path=app/Http/Resources : Path to scan for Resource classes}
                            {--force : Overwrite existing schemas}';

    protected $description = 'Generate OpenAPI schemas for Resource classes';

    public function handle()
    {
        $path = base_path($this->option('path'));

        if (!File::isDirectory($path)) {
            $this->error("Directory not found: {$path}");
            return Command::FAILURE;
        }

        $resourceFiles = File::allFiles($path);
        $processed = 0;
        $skipped = 0;

        foreach ($resourceFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file->getPathname());

            if (!$className || !class_exists($className)) {
                continue;
            }

            // Пропускаем коллекции
            if (Str::endsWith(class_basename($className), 'Collection')) {
                continue;
            }

            if ($this->generateSchemaForResource($className, $file->getPathname())) {
                $processed++;
                $this->line("  ✓ " . class_basename($className));
            } else {
                $skipped++;
            }
        }

        if ($processed > 0) {
            $this->info("✓ Generated schemas for {$processed} Resource classes");
        }
        if ($skipped > 0 && $this->option('verbose')) {
            $this->comment("  Skipped {$skipped} classes (already exist or no toArray)");
        }

        return Command::SUCCESS;
    }

    /**
     * Получает полное имя класса из файла
     */
    protected function getClassNameFromFile(string $filePath): ?string
    {
        $content = File::get($filePath);

        // Извлекаем namespace
        if (preg_match('/namespace\s+([^;]+);/', $content, $matches)) {
            $namespace = $matches[1];

            // Извлекаем имя класса
            if (preg_match('/class\s+(\w+)/', $content, $matches)) {
                return $namespace . '\\' . $matches[1];
            }
        }

        return null;
    }

    /**
     * Генерирует схему для Resource класса
     */
    protected function generateSchemaForResource(string $className, string $filePath): bool
    {
        try {
            $reflection = new ReflectionClass($className);

            // Проверяем, есть ли уже схема
            $docComment = $reflection->getDocComment();
            if ($docComment && Str::contains($docComment, '@OA\Schema') && !$this->option('force')) {
                return false;
            }

            // Читаем метод toArray
            if (!$reflection->hasMethod('toArray')) {
                return false;
            }

            $content = File::get($filePath);

            // Извлекаем код метода toArray
            $toArrayMethod = $this->extractToArrayMethod($content);

            if (!$toArrayMethod) {
                return false;
            }

            // Парсим возвращаемый массив
            $fields = $this->parseReturnArray($toArrayMethod);

            if (empty($fields)) {
                return false;
            }

            // Генерируем схему
            $schemaName = str_replace('Resource', '', class_basename($className));
            $schema = $this->generateSchema($schemaName, $fields);

            // КРИТИЧЕСКАЯ ПРОВЕРКА: убеждаемся, что файл имеет объявление класса
            if (!preg_match('/class\s+' . preg_quote(class_basename($className)) . '/s', $content)) {
                $this->warn("  ⚠ Skipping " . class_basename($className) . ": class declaration not found!");
                return false;
            }

            // Добавляем use statement
            if (!Str::contains($content, 'use OpenApi\Attributes as OA;')) {
                $content = $this->addUseStatement($content);
            }

            // Добавляем схему перед объявлением класса
            $pattern = '/(class\s+' . preg_quote(class_basename($className)) . ')/';

            if (preg_match($pattern, $content)) {
                if ($docComment) {
                    // Заменяем существующий docblock
                    $content = str_replace($docComment, $schema, $content);
                } else {
                    // Добавляем новый docblock
                    $content = preg_replace($pattern, $schema . "\n$1", $content);
                }

                // КРИТИЧЕСКАЯ ПРОВЕРКА: убеждаемся, что мы не удалили объявление класса
                if (!preg_match('/class\s+' . preg_quote(class_basename($className)) . '/s', $content)) {
                    $this->error("  ✗ ERROR: Class declaration would be deleted! Skipping " . class_basename($className));
                    return false;
                }

                File::put($filePath, $content);
                return true;
            }

            return false;

        } catch (\Exception $e) {
            $this->warn("Warning: Could not process " . class_basename($className) . ": " . $e->getMessage());
            return false;
        }
    }

    /**
     * Извлекает код метода toArray
     */
    protected function extractToArrayMethod(string $content): ?string
    {
        // Находим метод toArray
        if (preg_match('/public\s+function\s+toArray.*?\{(.*?)\n\s*\}/s', $content, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Парсит возвращаемый массив из метода toArray
     */
    protected function parseReturnArray(string $methodBody): array
    {
        $fields = [];

        // Ищем return [ ... ];
        if (preg_match('/return\s*\[(.*?)\];/s', $methodBody, $matches)) {
            $arrayContent = $matches[1];

            // Парсим каждое поле
            preg_match_all('/([\'\"])(.*?)\1\s*=>\s*(.+?),?$/m', $arrayContent, $fieldMatches, PREG_SET_ORDER);

            foreach ($fieldMatches as $match) {
                $fieldName = $match[2];
                $value = trim($match[3]);

                // Определяем тип по значению
                $type = $this->inferTypeFromValue($value);

                $fields[$fieldName] = [
                    'type' => $type,
                    'value' => $value,
                ];
            }
        }

        return $fields;
    }

    /**
     * Определяет тип по значению
     */
    protected function inferTypeFromValue(string $value): string
    {
        $value = trim($value);

        // Boolean
        if (in_array($value, ['true', 'false'])) {
            return 'boolean';
        }

        // Integer
        if (is_numeric($value) && !str_contains($value, '.')) {
            return 'integer';
        }

        // Number
        if (is_numeric($value)) {
            return 'number';
        }

        // Array/Collection
        if (Str::startsWith($value, '[') || Str::contains($value, '::collection')) {
            return 'array';
        }

        // Nested resource
        if (Str::contains($value, 'Resource')) {
            return 'object';
        }

        // Default
        return 'string';
    }

    /**
     * Генерирует OpenAPI схему
     */
    protected function generateSchema(string $schemaName, array $fields): string
    {
        $schema = "/**\n";
        $schema .= " * @OA\\Schema(\n";
        $schema .= " *     schema=\"{$schemaName}Resource\",\n";
        $schema .= " *     type=\"object\",\n";
        $schema .= " *     title=\"{$schemaName} Resource\",\n";

        foreach ($fields as $fieldName => $fieldData) {
            $type = $fieldData['type'];
            $description = ucfirst(str_replace('_', ' ', $fieldName));

            $schema .= " *     @OA\\Property(\n";
            $schema .= " *         property=\"{$fieldName}\",\n";
            $schema .= " *         type=\"{$type}\",\n";
            $schema .= " *         description=\"{$description}\"\n";

            if ($type === 'array') {
                $schema .= ",\n *         @OA\\Items(type=\"object\")\n";
            }

            $schema .= " *     ),\n";
        }

        // Удаляем последнюю запятую
        $schema = rtrim($schema, ",\n") . "\n";

        $schema .= " * )\n";
        $schema .= " */";

        return $schema;
    }

    /**
     * Добавляет use statement в файл
     */
    protected function addUseStatement(string $content): string
    {
        preg_match_all('/^use\s+[^;]+;$/m', $content, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches[0])) {
            $lastUse = end($matches[0]);
            $position = $lastUse[1] + strlen($lastUse[0]);

            $useStatement = "\nuse OpenApi\Attributes as OA;";
            return substr_replace($content, $useStatement, $position, 0);
        }

        return $content;
    }
}
