<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreLevelRequest;
use App\Services\Users\UserLevelService;
use Exception;
use Illuminate\Support\Facades\Log;

// Контроллер для работы с уровнями пользователей
class UserLevelController extends Controller
{
    protected UserLevelService $levelService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_LEVELS_LIST = 'levels_list';

    public function __construct(UserLevelService $levelService)
    {
        $this->levelService = $levelService;
    }

    /**
     * Получить все уровни.
     */
    public function index()
    {
        try {
            $levels = $this->getFromCacheOrStore(self::CACHE_KEY_LEVELS_LIST, self::CACHE_MINUTES, function () {
                return $this->levelService->getAll();
            });

            Log::info('Уровни успешно получены');

            return $this->successResponse($levels);
        } catch (Exception $e) {
            Log::error('Ошибка при получении уровней: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Создать новый уровень.
     */
    public function store(StoreLevelRequest $request)
    {
        try {
            $level = $this->levelService->createLevel($request->name, $request->experience_required);

            Log::info('Уровень успешно создан', ['level_id' => $level->id]);

            $this->forgetCache(self::CACHE_KEY_LEVELS_LIST);

            return $this->successResponse($level, 201);
        } catch (Exception $e) {
            Log::error('Ошибка при создании уровня: ' . $e->getMessage(), ['data' => $request->all()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
