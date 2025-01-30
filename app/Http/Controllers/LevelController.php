<?php

namespace App\Http\Controllers;

use App\Services\LevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LevelController extends Controller
{
    protected LevelService $levelService;

    public function __construct(LevelService $levelService)
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
    public function store(Request $request)
    {
        $request->validate([
            'name' => 'required|array',
            'name.ru' => 'required|string',
            'name.en' => 'required|string',
            'experience_required' => 'required|integer',
        ]);

        // Создаем новый уровень
        $level = $this->levelService->createLevel($request->name, $request->experience_required);

        // Очистка кеша после добавления нового уровня
        Cache::forget('levels_list');

        return response()->json($level, 201);
    }
}
