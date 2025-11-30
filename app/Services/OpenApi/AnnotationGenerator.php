<?php

namespace App\Services\OpenApi;

use Illuminate\Support\Facades\File;
use Illuminate\Support\Str;
use ReflectionClass;
use ReflectionMethod;

class AnnotationGenerator
{
    protected array $httpMethods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE'];

    protected array $tagMapping = [
        'User' => 'Users',
        'Avatar' => 'Avatars',
        'Media' => 'Media',
        'Post' => 'Posts',
        'Comment' => 'Comments',
        'Notification' => 'Notifications',
        'Auth' => 'Authentication',
        'Badge' => 'Badges',
        'Challenge' => 'Challenges',
        'Tag' => 'Tags',
        'Category' => 'Categories',
    ];

    /**
     * Генерирует OpenAPI аннотацию для метода контроллера
     */
    public function generateMethodAnnotation(ReflectionMethod $method, string $route, string $httpMethod): string
    {
        $controllerName = $method->getDeclaringClass()->getShortName();
        $methodName = $method->getName();

        // Определяем тег на основе имени контроллера
        $tag = $this->getTagFromController($controllerName);

        // Генерируем summary и description
        $summary = $this->generateSummary($methodName, $controllerName);
        $description = $this->generateDescription($methodName, $controllerName);

        // Определяем нужна ли авторизация
        $security = $this->needsAuth($route) ? 'security={{"bearerAuth":{}}},' : '';

        $annotation = "    /**\n";
        $annotation .= "     * @OA\\{$httpMethod}(\n";
        $annotation .= "     *     path=\"{$route}\",\n";
        $annotation .= "     *     tags={\"{$tag}\"},\n";
        $annotation .= "     *     summary=\"{$summary}\",\n";
        $annotation .= "     *     description=\"{$description}\",\n";

        if ($security) {
            $annotation .= "     *     {$security}\n";
        }

        // Добавляем параметры пути
        $pathParams = $this->extractPathParameters($route);
        foreach ($pathParams as $param) {
            $annotation .= $this->generatePathParameter($param);
        }

        // Добавляем тело запроса для POST/PUT/PATCH
        if (in_array($httpMethod, ['Post', 'Put', 'Patch'])) {
            $annotation .= $this->generateRequestBody($method, $controllerName);
        }

        // Добавляем параметры запроса для GET
        if ($httpMethod === 'Get' && in_array($methodName, ['index', 'search'])) {
            $annotation .= $this->generateQueryParameters();
        }

        // Добавляем responses
        $annotation .= $this->generateResponses($httpMethod, $methodName, $controllerName);

        $annotation .= "     * )\n";
        $annotation .= "     */\n";

        return $annotation;
    }

    /**
     * Извлекает параметры из пути маршрута
     */
    protected function extractPathParameters(string $route): array
    {
        preg_match_all('/\{([^}]+)\}/', $route, $matches);
        return $matches[1] ?? [];
    }

    /**
     * Генерирует аннотацию для параметра пути
     */
    protected function generatePathParameter(string $param): string
    {
        $type = Str::contains($param, 'id') ? 'integer' : 'string';
        $description = ucfirst(str_replace('_', ' ', $param));

        return "     *     @OA\\Parameter(\n" .
               "     *         name=\"{$param}\",\n" .
               "     *         in=\"path\",\n" .
               "     *         required=true,\n" .
               "     *         description=\"{$description}\",\n" .
               "     *         @OA\\Schema(type=\"{$type}\")\n" .
               "     *     ),\n";
    }

    /**
     * Генерирует тело запроса
     */
    protected function generateRequestBody(ReflectionMethod $method, string $controllerName): string
    {
        $params = $method->getParameters();

        // Ищем Request класс в параметрах
        foreach ($params as $param) {
            $type = $param->getType();
            if ($type && !$type->isBuiltin()) {
                $className = $type->getName();
                if (Str::endsWith($className, 'Request')) {
                    $schemaName = str_replace('Request', '', class_basename($className));

                    return "     *     @OA\\RequestBody(\n" .
                           "     *         required=true,\n" .
                           "     *         @OA\\JsonContent(ref=\"#/components/schemas/{$schemaName}Request\")\n" .
                           "     *     ),\n";
                }
            }
        }

        // Генерируем базовое тело запроса
        return "     *     @OA\\RequestBody(\n" .
               "     *         required=true,\n" .
               "     *         @OA\\JsonContent(\n" .
               "     *             @OA\\Property(property=\"data\", type=\"object\")\n" .
               "     *         )\n" .
               "     *     ),\n";
    }

    /**
     * Генерирует параметры запроса для GET
     */
    protected function generateQueryParameters(): string
    {
        return "     *     @OA\\Parameter(\n" .
               "     *         name=\"page\",\n" .
               "     *         in=\"query\",\n" .
               "     *         description=\"Page number\",\n" .
               "     *         @OA\\Schema(type=\"integer\", example=1)\n" .
               "     *     ),\n" .
               "     *     @OA\\Parameter(\n" .
               "     *         name=\"per_page\",\n" .
               "     *         in=\"query\",\n" .
               "     *         description=\"Items per page\",\n" .
               "     *         @OA\\Schema(type=\"integer\", example=15)\n" .
               "     *     ),\n";
    }

    /**
     * Генерирует responses
     */
    protected function generateResponses(string $httpMethod, string $methodName, string $controllerName): string
    {
        $modelName = str_replace('Controller', '', $controllerName);

        $responses = "";

        // Success response
        if ($httpMethod === 'Get') {
            if (in_array($methodName, ['index', 'search'])) {
                // Список
                $responses .= "     *     @OA\\Response(\n" .
                            "     *         response=200,\n" .
                            "     *         description=\"Successful operation\",\n" .
                            "     *         @OA\\JsonContent(\n" .
                            "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                            "     *             @OA\\Property(\n" .
                            "     *                 property=\"data\",\n" .
                            "     *                 type=\"array\",\n" .
                            "     *                 @OA\\Items(ref=\"#/components/schemas/{$modelName}\")\n" .
                            "     *             )\n" .
                            "     *         )\n" .
                            "     *     ),\n";
            } else {
                // Один объект
                $responses .= "     *     @OA\\Response(\n" .
                            "     *         response=200,\n" .
                            "     *         description=\"Successful operation\",\n" .
                            "     *         @OA\\JsonContent(\n" .
                            "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                            "     *             @OA\\Property(property=\"data\", ref=\"#/components/schemas/{$modelName}\")\n" .
                            "     *         )\n" .
                            "     *     ),\n";
            }
        } elseif ($httpMethod === 'Post') {
            $responses .= "     *     @OA\\Response(\n" .
                        "     *         response=201,\n" .
                        "     *         description=\"Resource created successfully\",\n" .
                        "     *         @OA\\JsonContent(\n" .
                        "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                        "     *             @OA\\Property(property=\"data\", ref=\"#/components/schemas/{$modelName}\")\n" .
                        "     *         )\n" .
                        "     *     ),\n";
        } elseif ($httpMethod === 'Delete') {
            $responses .= "     *     @OA\\Response(\n" .
                        "     *         response=200,\n" .
                        "     *         description=\"Resource deleted successfully\",\n" .
                        "     *         @OA\\JsonContent(\n" .
                        "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                        "     *             @OA\\Property(\n" .
                        "     *                 property=\"data\",\n" .
                        "     *                 type=\"object\",\n" .
                        "     *                 @OA\\Property(property=\"message\", type=\"string\", example=\"Resource deleted successfully\")\n" .
                        "     *             )\n" .
                        "     *         )\n" .
                        "     *     ),\n";
        } else {
            $responses .= "     *     @OA\\Response(\n" .
                        "     *         response=200,\n" .
                        "     *         description=\"Successful operation\",\n" .
                        "     *         @OA\\JsonContent(\n" .
                        "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                        "     *             @OA\\Property(property=\"data\", ref=\"#/components/schemas/{$modelName}\")\n" .
                        "     *         )\n" .
                        "     *     ),\n";
        }

        // Error responses
        if (in_array($methodName, ['show', 'update', 'destroy'])) {
            $responses .= "     *     @OA\\Response(response=404, description=\"Resource not found\"),\n";
        }

        $responses .= "     *     @OA\\Response(response=500, description=\"Server error\")\n";

        return $responses;
    }

    /**
     * Генерирует summary
     */
    protected function generateSummary(string $methodName, string $controllerName): string
    {
        $resource = str_replace('Controller', '', $controllerName);
        $resource = Str::snake($resource);
        $resource = str_replace('_', ' ', $resource);

        $action = match($methodName) {
            'index' => 'Get all ' . Str::plural($resource),
            'show' => 'Get ' . $resource . ' by ID',
            'store' => 'Create new ' . $resource,
            'update' => 'Update ' . $resource,
            'destroy' => 'Delete ' . $resource,
            default => ucfirst($methodName) . ' ' . $resource,
        };

        return $action;
    }

    /**
     * Генерирует description
     */
    protected function generateDescription(string $methodName, string $controllerName): string
    {
        return $this->generateSummary($methodName, $controllerName);
    }

    /**
     * Определяет тег по имени контроллера
     */
    protected function getTagFromController(string $controllerName): string
    {
        foreach ($this->tagMapping as $key => $tag) {
            if (Str::contains($controllerName, $key)) {
                return $tag;
            }
        }

        $name = str_replace('Controller', '', $controllerName);
        return Str::plural($name);
    }

    /**
     * Проверяет нужна ли авторизация для маршрута
     */
    protected function needsAuth(string $route): bool
    {
        // Маршруты не требующие авторизации
        $publicRoutes = [
            '/api/v1/auth/login',
            '/api/v1/auth/register',
            '/api/v1/init',
            '/api/v1/languages',
        ];

        foreach ($publicRoutes as $publicRoute) {
            if (Str::startsWith($route, $publicRoute)) {
                return false;
            }
        }

        // По умолчанию требуется авторизация для /api/v1/
        return Str::startsWith($route, '/api/v1/');
    }
}
