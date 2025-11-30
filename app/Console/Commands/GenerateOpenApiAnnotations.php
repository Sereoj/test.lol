<?php

namespace App\Console\Commands;

use App\Services\OpenApi\AnnotationGenerator;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class GenerateOpenApiAnnotations extends Command
{
    protected $signature = 'openapi:generate-annotations
                            {--controller= : Specific controller to generate annotations for}
                            {--force : Overwrite existing annotations}';

    protected $description = 'Generate OpenAPI annotations for controllers based on routes';

    protected AnnotationGenerator $generator;

    public function __construct(AnnotationGenerator $generator)
    {
        parent::__construct();
        $this->generator = $generator;
    }

    public function handle()
    {
        $this->info('Generating OpenAPI annotations...');

        $routes = $this->getApiRoutes();
        $controllerMethods = $this->groupRoutesByController($routes);

        $processedControllers = 0;
        $processedMethods = 0;

        foreach ($controllerMethods as $controllerClass => $methods) {
            if ($this->option('controller') && !Str::contains($controllerClass, $this->option('controller'))) {
                continue;
            }

            $this->info("Processing controller: " . class_basename($controllerClass));

            foreach ($methods as $methodData) {
                if ($this->addAnnotationToMethod($controllerClass, $methodData)) {
                    $processedMethods++;
                    $this->line("  ✓ {$methodData['method']} - {$methodData['http_method']} {$methodData['uri']}");
                }
            }

            $processedControllers++;
        }

        if ($processedMethods > 0) {
            $this->info("✓ Processed {$processedControllers} controllers, {$processedMethods} methods");
        } else {
            $this->comment("  No new annotations generated (use --force to overwrite)");
        }

        return Command::SUCCESS;
    }

    /**
     * Получает все API маршруты
     */
    protected function getApiRoutes(): array
    {
        $routes = [];

        foreach (Route::getRoutes() as $route) {
            $uri = $route->uri();

            // Только API маршруты
            if (!Str::startsWith($uri, 'api/')) {
                continue;
            }

            $action = $route->getAction();

            if (!isset($action['controller'])) {
                continue;
            }

            [$controller, $method] = explode('@', $action['controller']);

            $routes[] = [
                'uri' => '/' . $uri,
                'method' => $method,
                'controller' => $controller,
                'http_methods' => $route->methods(),
            ];
        }

        return $routes;
    }

    /**
     * Группирует маршруты по контроллерам
     */
    protected function groupRoutesByController(array $routes): array
    {
        $grouped = [];

        foreach ($routes as $route) {
            $controller = $route['controller'];
            $method = $route['method'];

            // Пропускаем некоторые методы
            if (in_array($method, ['__construct', '__invoke'])) {
                continue;
            }

            // Определяем HTTP метод
            $httpMethods = array_diff($route['http_methods'], ['HEAD']);
            $httpMethod = reset($httpMethods);

            if (!$httpMethod || $httpMethod === 'HEAD') {
                continue;
            }

            $grouped[$controller][] = [
                'method' => $method,
                'uri' => $route['uri'],
                'http_method' => $httpMethod,
            ];
        }

        return $grouped;
    }

    /**
     * Добавляет аннотацию к методу контроллера
     */
    protected function addAnnotationToMethod(string $controllerClass, array $methodData): bool
    {
        try {
            $reflection = new ReflectionClass($controllerClass);

            // Проверяем, существует ли метод
            if (!$reflection->hasMethod($methodData['method'])) {
                return false;
            }

            $method = $reflection->getMethod($methodData['method']);

            // Проверяем, есть ли уже аннотация
            $docComment = $method->getDocComment();
            if ($docComment && Str::contains($docComment, '@OA\\') && !$this->option('force')) {
                return false; // Уже есть аннотация
            }

            // Получаем путь к файлу
            $filePath = $reflection->getFileName();

            if (!$filePath || !File::exists($filePath)) {
                return false;
            }

            // Читаем файл
            $content = File::get($filePath);

            // КРИТИЧЕСКАЯ ПРОВЕРКА: убеждаемся, что файл имеет объявление класса
            // Ищем class с учетом возможного docblock перед ним
            if (!preg_match('/class\s+' . preg_quote($reflection->getShortName()) . '\s+extends/s', $content)) {
                $this->error("  ✗ Skipping {$reflection->getShortName()}: class declaration not found!");
                return false;
            }

            // Генерируем аннотацию
            $httpMethodFormatted = ucfirst(strtolower($methodData['http_method']));
            $annotation = $this->generator->generateMethodAnnotation(
                $method,
                $methodData['uri'],
                $httpMethodFormatted
            );

            // Находим метод в файле и добавляем аннотацию
            $methodName = $methodData['method'];

            // Паттерн для поиска метода с учетом однострочных комментариев перед ним
            // ВАЖНО: Паттерн НЕ должен матчить объявление класса
            $pattern = '/((?:\/\/[^\n]*\n\s*)*)(\/\*\*.*?\*\/\s*)?(\s*)(public|protected|private)\s+function\s+' . preg_quote($methodName) . '\s*\(/s';

            if (preg_match($pattern, $content, $matches, PREG_OFFSET_CAPTURE)) {
                $offset = $matches[0][1];
                $singleLineComments = $matches[1][0] ?? '';  // Однострочные комментарии
                $existingDocBlock = $matches[2][0] ?? '';    // Существующий docblock
                $indent = $matches[3][0];                     // Отступы

                $fullMatch = $matches[0][0];

                // Если уже есть docblock, заменяем его
                if (!empty($existingDocBlock)) {
                    // Сохраняем однострочные комментарии и заменяем только docblock
                    $newContent = $singleLineComments;
                    // Всегда добавляем перенос после однострочного комментария, если он есть
                    if (!empty(trim($singleLineComments))) {
                        $newContent .= "\n";
                    }
                    $newContent .= $annotation;
                    $replacement = $newContent . substr($fullMatch, strlen($singleLineComments) + strlen($existingDocBlock));
                    $content = substr_replace($content, $replacement, $offset, strlen($fullMatch));
                } else {
                    // Добавляем новый docblock после однострочных комментариев
                    $newContent = $singleLineComments;
                    // Всегда добавляем перенос после однострочного комментария, если он есть
                    if (!empty(trim($singleLineComments))) {
                        $newContent .= "\n";
                    }
                    $newContent .= $annotation;

                    // Находим где начинается функция
                    $afterComments = substr($fullMatch, strlen($singleLineComments));
                    $replacement = $newContent . $afterComments;
                    $content = substr_replace($content, $replacement, $offset, strlen($fullMatch));
                }

                // КРИТИЧЕСКАЯ ПРОВЕРКА: убеждаемся, что мы не удалили объявление класса
                // Ищем class с учетом возможного docblock перед ним
                if (!preg_match('/class\s+' . preg_quote($reflection->getShortName()) . '\s+extends/s', $content)) {
                    $this->error("  ✗ ERROR: Class declaration would be deleted! Skipping {$reflection->getShortName()}::{$methodName}");
                    return false;
                }

                // Сохраняем файл
                File::put($filePath, $content);

                // Добавляем use statement если его нет
                $this->addUseStatement($filePath);

                return true;
            }

            return false;

        } catch (\ReflectionException $e) {
            // Метод не существует - это нормально, пропускаем
            return false;
        } catch (\Exception $e) {
            $this->warn("Warning: {$controllerClass}::{$methodData['method']}: " . $e->getMessage());
            return false;
        }
    }

    /**
     * Добавляет use OpenApi\Attributes в файл
     */
    protected function addUseStatement(string $filePath): void
    {
        $content = File::get($filePath);

        if (Str::contains($content, 'use OpenApi\Attributes as OA;')) {
            return;
        }

        // Находим последний use statement
        preg_match_all('/^use\s+[^;]+;$/m', $content, $matches, PREG_OFFSET_CAPTURE);

        if (!empty($matches[0])) {
            $lastUse = end($matches[0]);
            $position = $lastUse[1] + strlen($lastUse[0]);

            $useStatement = "\nuse OpenApi\Attributes as OA;";
            $content = substr_replace($content, $useStatement, $position, 0);

            File::put($filePath, $content);
        }
    }
}
