<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|---------------------------------------------------------------------------
| API Routes
|---------------------------------------------------------------------------
| Основной файл маршрутов API, делегирующий запросы в подфайлы по версиям.
| Все маршруты будут иметь префикс 'api' благодаря RouteServiceProvider.
|
*/

// Версия 1 API (текущая)
Route::prefix('v1')->group(function () {
    // Маршруты для администраторов
    require __DIR__.'/api/v1/admin.php';

    // Маршруты, требующие авторизации (auth)
    require __DIR__.'/api/v1/auth.php';

    // Маршруты, не требующие авторизации (guest)
    require __DIR__.'/api/v1/guest.php';
});

// Общий обработчик 404 для неизвестных маршрутов API
Route::fallback(function (Request $request) {
    $method = $request->getMethod();
    $uri = $request->getRequestUri();

    return response()->json([
        'success' => false,
        'error' => 'Route not found or method not supported',
        'supported_methods' => ['GET', 'HEAD', 'POST', 'PUT', 'DELETE', 'OPTIONS', 'PATCH'],
        'message' => "The request to route {$uri} with method {$method} does not exist.",
    ], 404);
})->name('fallback');
