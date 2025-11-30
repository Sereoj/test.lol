<?php

namespace App\Services\OpenApi;

use Illuminate\Support\Str;
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
            $requestBody = $this->generateRequestBody($method);
            if ($requestBody) {
                $annotation .= $requestBody;
            }
        }

        // Добавляем параметры запроса для GET
        if ($httpMethod === 'Get' && in_array($methodName, ['index', 'search'])) {
            $annotation .= $this->generateQueryParameters();
        }

        // Добавляем responses
        $annotation .= $this->generateResponses($httpMethod, $methodName);

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
     * Генерирует тело запроса (только если найден кастомный FormRequest)
     */
    protected function generateRequestBody(ReflectionMethod $method): string
    {
        $params = $method->getParameters();

        // Ищем кастомный Request класс в параметрах
        foreach ($params as $param) {
            $type = $param->getType();

            // Пропускаем параметры без типа или с builtin типами
            if (!$type || $type->isBuiltin()) {
                continue;
            }

            $className = $type->getName();

            // Пропускаем базовый Laravel Request
            if ($className === 'Illuminate\\Http\\Request') {
                continue;
            }

            // Проверяем, что это кастомный FormRequest из App
            if (Str::endsWith($className, 'Request') && Str::startsWith($className, 'App\\Http\\Requests\\')) {
                $schemaName = str_replace('Request', '', class_basename($className));

                return "     *     @OA\\RequestBody(\n" .
                       "     *         required=true,\n" .
                       "     *         @OA\\JsonContent(ref=\"#/components/schemas/{$schemaName}Request\")\n" .
                       "     *     ),\n";
            }
        }

        // Если нет кастомного Request класса, не добавляем RequestBody
        return '';
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
     * Генерирует responses без ссылок на схемы (inline objects)
     * Это предотвращает ошибки "schema not found"
     */
    protected function generateResponses(string $httpMethod, string $methodName): string
    {
        $responses = "";

        // Success response
        if ($httpMethod === 'Get') {
            if (in_array($methodName, ['index', 'search'])) {
                // Список с пагинацией
                $responses .= "     *     @OA\\Response(\n" .
                            "     *         response=200,\n" .
                            "     *         description=\"Successful operation\",\n" .
                            "     *         @OA\\JsonContent(\n" .
                            "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                            "     *             @OA\\Property(\n" .
                            "     *                 property=\"data\",\n" .
                            "     *                 type=\"array\",\n" .
                            "     *                 @OA\\Items(type=\"object\")\n" .
                            "     *             ),\n" .
                            "     *             @OA\\Property(\n" .
                            "     *                 property=\"meta\",\n" .
                            "     *                 type=\"object\",\n" .
                            "     *                 @OA\\Property(property=\"current_page\", type=\"integer\", example=1),\n" .
                            "     *                 @OA\\Property(property=\"last_page\", type=\"integer\", example=10),\n" .
                            "     *                 @OA\\Property(property=\"per_page\", type=\"integer\", example=15),\n" .
                            "     *                 @OA\\Property(property=\"total\", type=\"integer\", example=150)\n" .
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
                            "     *             @OA\\Property(property=\"data\", type=\"object\")\n" .
                            "     *         )\n" .
                            "     *     ),\n";
            }
        } elseif ($httpMethod === 'Post') {
            $responses .= "     *     @OA\\Response(\n" .
                        "     *         response=201,\n" .
                        "     *         description=\"Resource created successfully\",\n" .
                        "     *         @OA\\JsonContent(\n" .
                        "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                        "     *             @OA\\Property(property=\"data\", type=\"object\"),\n" .
                        "     *             @OA\\Property(property=\"message\", type=\"string\", example=\"Resource created successfully\")\n" .
                        "     *         )\n" .
                        "     *     ),\n";
        } elseif ($httpMethod === 'Delete') {
            $responses .= "     *     @OA\\Response(\n" .
                        "     *         response=200,\n" .
                        "     *         description=\"Resource deleted successfully\",\n" .
                        "     *         @OA\\JsonContent(\n" .
                        "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                        "     *             @OA\\Property(property=\"message\", type=\"string\", example=\"Resource deleted successfully\")\n" .
                        "     *         )\n" .
                        "     *     ),\n";
        } else {
            // PUT/PATCH
            $responses .= "     *     @OA\\Response(\n" .
                        "     *         response=200,\n" .
                        "     *         description=\"Resource updated successfully\",\n" .
                        "     *         @OA\\JsonContent(\n" .
                        "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=true),\n" .
                        "     *             @OA\\Property(property=\"data\", type=\"object\"),\n" .
                        "     *             @OA\\Property(property=\"message\", type=\"string\", example=\"Resource updated successfully\")\n" .
                        "     *         )\n" .
                        "     *     ),\n";
        }

        // Error responses
        if (in_array($methodName, ['show', 'update', 'destroy'])) {
            $responses .= "     *     @OA\\Response(\n" .
                        "     *         response=404,\n" .
                        "     *         description=\"Resource not found\",\n" .
                        "     *         @OA\\JsonContent(\n" .
                        "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=false),\n" .
                        "     *             @OA\\Property(property=\"message\", type=\"string\", example=\"Resource not found\")\n" .
                        "     *         )\n" .
                        "     *     ),\n";
        }

        // Добавляем validation error для методов с RequestBody
        if (in_array($httpMethod, ['Post', 'Put', 'Patch'])) {
            $responses .= "     *     @OA\\Response(\n" .
                        "     *         response=422,\n" .
                        "     *         description=\"Validation error\",\n" .
                        "     *         @OA\\JsonContent(\n" .
                        "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=false),\n" .
                        "     *             @OA\\Property(property=\"message\", type=\"string\", example=\"Validation failed\"),\n" .
                        "     *             @OA\\Property(property=\"errors\", type=\"object\")\n" .
                        "     *         )\n" .
                        "     *     ),\n";
        }

        $responses .= "     *     @OA\\Response(\n" .
                    "     *         response=500,\n" .
                    "     *         description=\"Server error\",\n" .
                    "     *         @OA\\JsonContent(\n" .
                    "     *             @OA\\Property(property=\"success\", type=\"boolean\", example=false),\n" .
                    "     *             @OA\\Property(property=\"message\", type=\"string\", example=\"Internal server error\")\n" .
                    "     *         )\n" .
                    "     *     )\n";

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
            '/api/v1/auth/refresh',
            '/api/v1/auth/password/reset',
            '/api/v1/auth/password/forgot',
            '/api/v1/auth/verify',
            '/api/v1/init',
            '/api/v1/languages',
            '/api/v1/statuses',
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
