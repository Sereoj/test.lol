<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLevelRequest;
use App\Services\Users\UserLevelService;
use Illuminate\Support\Facades\Cache;

class UserLevelController extends Controller
{
    protected UserLevelService $levelService;

    public function __construct(UserLevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    /**
     * Получить все уровни.
     */
    public function index()
    {
        // Кешируем список уровней
        $cacheKey = 'levels_list';
        if (Cache::has($cacheKey)) {
            // Возвращаем кешированные данные
            return response()->json(Cache::get($cacheKey));
        }

        // Если данных нет в кеше, загружаем их из базы данных
        $levels = $this->levelService->getAllLevels();

        // Кешируем результат на 60 минут
        Cache::put($cacheKey, $levels, now()->addMinutes(60));

        return response()->json($levels);
    }

    /**
     * Создать новый уровень.
     */
    public function store(StoreLevelRequest $request)
    {
        // Создаем новый уровень
        $level = $this->levelService->createLevel($request->name, $request->experience_required);

        // Очистка кеша после добавления нового уровня
        Cache::forget('levels_list');

        return response()->json($level, 201);
    }
}
