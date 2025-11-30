<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;

class GenerateRequestSchemas extends Command
{
    protected $signature = 'openapi:generate-request-schemas
                            {--path=app/Http/Requests : Path to scan for Request classes}
                            {--force : Overwrite existing schemas}';

    protected $description = 'Generate OpenAPI schemas for Request classes';

    public function handle()
    {
        $this->info('Generating OpenAPI schemas for Request classes...');

        $path = base_path($this->option('path'));

        if (!File::isDirectory($path)) {
            $this->error("Directory not found: {$path}");
            return 1;
        }

        $requestFiles = File::allFiles($path);
        $processed = 0;

        foreach ($requestFiles as $file) {
            if ($file->getExtension() !== 'php') {
                continue;
            }

            $className = $this->getClassNameFromFile($file->getPathname());

            if (!$className || !class_exists($className)) {
                continue;
            }

            if ($this->generateSchemaForRequest($className, $file->getPathname())) {
                $processed++;
                $this->line("  ✓ " . class_basename($className));
            }
        }

        $this->newLine();
        $this->info("✓ Generated schemas for {$processed} Request classes");

        return 0;
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
     * Генерирует схему для Request класса
     */
    protected function generateSchemaForRequest(string $className, string $filePath): bool
    {
        try {
            $reflection = new ReflectionClass($className);

            // Проверяем, есть ли уже схема
            $docComment = $reflection->getDocComment();
            if ($docComment && Str::contains($docComment, '@OA\Schema') && !$this->option('force')) {
                return false;
            }

            // Создаем экземпляр для получения правил валидации
            $instance = $reflection->newInstance();

            if (!method_exists($instance, 'rules')) {
                return false;
            }

            $rules = $instance->rules();

            if (empty($rules)) {
                return false;
            }

            // Генерируем схему
            $schemaName = str_replace('Request', '', class_basename($className));
            $schema = $this->generateSchema($schemaName, $rules);

            // Читаем файл
            $content = File::get($filePath);

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
     * Генерирует OpenAPI схему из правил валидации
     */
    protected function generateSchema(string $schemaName, array $rules): string
    {
        $schema = "/**\n";
        $schema .= " * @OA\\Schema(\n";
        $schema .= " *     schema=\"{$schemaName}Request\",\n";
        $schema .= " *     type=\"object\",\n";
        $schema .= " *     title=\"{$schemaName} Request\",\n";
        $schema .= " *     required={" . $this->getRequiredFields($rules) . "},\n";

        foreach ($rules as $field => $rule) {
            $property = $this->generateProperty($field, $rule);
            $schema .= $property;
        }

        $schema .= " * )\n";
        $schema .= " */";

        return $schema;
    }

    /**
     * Получает список обязательных полей
     */
    protected function getRequiredFields(array $rules): string
    {
        $required = [];

        foreach ($rules as $field => $rule) {
            $ruleString = $this->convertRuleToString($rule);

            if (Str::contains($ruleString, 'required')) {
                $required[] = "\"{$field}\"";
            }
        }

        return implode(', ', $required);
    }

    /**
     * Конвертирует правило в строку, обрабатывая объекты и массивы
     */
    protected function convertRuleToString($rule): string
    {
        if (is_string($rule)) {
            return $rule;
        }

        if (is_array($rule)) {
            $converted = [];
            foreach ($rule as $r) {
                if (is_string($r)) {
                    $converted[] = $r;
                } elseif (is_object($r)) {
                    // Для объектов правил берем имя класса
                    $converted[] = class_basename($r);
                }
            }
            return implode('|', $converted);
        }

        if (is_object($rule)) {
            return class_basename($rule);
        }

        return '';
    }

    /**
     * Генерирует Property для поля
     */
    protected function generateProperty(string $field, $rule): string
    {
        $ruleString = $this->convertRuleToString($rule);
        $rules = is_array($rule) ? $rule : explode('|', (string)$rule);

        // Определяем тип
        $type = 'string';
        $format = null;
        $nullable = false;
        $example = null;
        $description = ucfirst(str_replace('_', ' ', $field));

        foreach ($rules as $r) {
            // Пропускаем объекты правил валидации
            if (is_object($r)) {
                continue;
            }

            $r = trim((string)$r);

            if (Str::startsWith($r, 'integer') || $r === 'int') {
                $type = 'integer';
                $example = 1;
            } elseif (Str::startsWith($r, 'numeric') || Str::startsWith($r, 'decimal')) {
                $type = 'number';
                $example = 1.0;
            } elseif (Str::startsWith($r, 'boolean') || $r === 'bool') {
                $type = 'boolean';
                $example = true;
            } elseif (Str::startsWith($r, 'array')) {
                $type = 'array';
            } elseif ($r === 'email') {
                $format = 'email';
                $example = 'user@example.com';
            } elseif ($r === 'url') {
                $format = 'uri';
                $example = 'https://example.com';
            } elseif ($r === 'date') {
                $format = 'date';
                $example = '2024-01-01';
            } elseif ($r === 'nullable') {
                $nullable = true;
            } elseif (Str::startsWith($r, 'max:')) {
                $description .= ' (max: ' . Str::after($r, 'max:') . ')';
            } elseif (Str::startsWith($r, 'min:')) {
                $description .= ' (min: ' . Str::after($r, 'min:') . ')';
            }
        }

        // Генерируем example если не задан
        if ($example === null) {
            $example = $this->generateExample($field, $type);
        }

        $property = " *     @OA\\Property(\n";
        $property .= " *         property=\"{$field}\",\n";
        $property .= " *         type=\"{$type}\",\n";

        if ($format) {
            $property .= " *         format=\"{$format}\",\n";
        }

        if ($nullable) {
            $property .= " *         nullable=true,\n";
        }

        $property .= " *         description=\"{$description}\",\n";

        if ($type === 'array') {
            $property .= " *         @OA\\Items(type=\"string\")\n";
        } else {
            $exampleValue = is_string($example) ? "\"{$example}\"" : ($example === true ? 'true' : ($example === false ? 'false' : $example));
            $property .= " *         example={$exampleValue}\n";
        }

        $property .= " *     ),\n";

        return $property;
    }

    /**
     * Генерирует пример значения для поля
     */
    protected function generateExample(string $field, string $type): mixed
    {
        return match($type) {
            'integer' => 1,
            'number' => 1.0,
            'boolean' => true,
            'array' => [],
            default => 'Example ' . str_replace('_', ' ', $field),
        };
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
