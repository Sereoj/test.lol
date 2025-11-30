<?php

return [
    'default' => 'default',
    'documentations' => [
        'default' => [
            'api' => [
                'title' => 'API Documentation',
            ],
            'routes' => [
                /*
                 * Route для доступа к сгенерированной документации OpenAPI
                */
                'api' => 'api/documentation',
            ],
            'paths' => [
                /*
                 * Базовый путь для аннотаций (обязательный параметр)
                 */
                'base' => base_path(),

                /*
                 * Путь для генерации файла swagger.json
                 */
                'docs_json' => 'api-docs.json',
                'docs_yaml' => 'api-docs.yaml',

                /*
                 * Файл с базовыми аннотациями
                 */
                'format_to_use_for_docs' => env('L5_FORMAT_TO_USE_FOR_DOCS', 'json'),

                /*
                 * Абсолютный путь к директории, где будет храниться документация
                 */
                'docs' => storage_path('api-docs'),

                /*
                 * Папки для сканирования аннотаций
                 */
                'annotations' => [
                    base_path('app/Http/Controllers'),
                    base_path('app/Models'),
                    base_path('app/Http/Requests'),
                    base_path('app/Http/Resources'),
                ],
            ],
        ],
    ],

    'defaults' => [
        'routes' => [
            /*
             * Middleware для роута документации
             */
            'middleware' => [
                'api',
            ],
        ],

        'paths' => [
            /*
             * Исключить папки из сканирования
             */
            'excludes' => [],
        ],

        /*
         * Настройки Swagger UI
         */
        'swagger_ui_settings' => [
            'persist_authorization' => true,
            'display_operation_id' => false,
        ],

        /*
         * Константы, которые можно использовать в аннотациях
         */
        'constants' => [
            'L5_SWAGGER_CONST_HOST' => env('L5_SWAGGER_CONST_HOST', 'http://localhost'),
        ],
    ],
];
