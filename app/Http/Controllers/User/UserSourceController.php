<?php

namespace App\Http\Controllers\User;

use App\Http\Controllers\Controller;
use App\Http\Requests\Source\AddSourceRequest;
use App\Http\Requests\Source\RemoveSourceRequest;
use App\Services\UserSourceService;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Database\Eloquent\ModelNotFoundException;

class UserSourceController extends Controller
{
    protected UserSourceService $userSourceService;

    public function __construct(UserSourceService $userSourceService)
    {
        $this->userSourceService = $userSourceService;
    }

    /**
     * Добавить источник пользователю.
     */
    public function addSource(AddSourceRequest $request)
    {
        try {
            $user = Auth::user();

            // Добавляем источник
            $this->userSourceService->addSourceToUser($user->id, $request->input('source_id'));

            return response()->json(['message' => 'Source added successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Source not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while adding source: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Удалить источник у пользователя.
     */
    public function removeSource(RemoveSourceRequest $request)
    {
        try {
            $user = Auth::user();
            $this->userSourceService->removeSourceFromUser($user->id, $request->input('source_id'));

            return response()->json(['message' => 'Source removed successfully'], 200);
        } catch (ModelNotFoundException $e) {
            return response()->json(['message' => 'Source not found'], 404);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while removing source: ' . $e->getMessage()], 500);
        }
    }

    /**
     * Получить источники пользователя.
     */
    public function getUserSources()
    {
        try {
            $user = Auth::user();
            $cacheKey = "user_sources_{$user->id}";

            // Проверка кеша
            $sources = Cache::get($cacheKey);

            if (!$sources) {
                $sources = $this->userSourceService->getUserSources($user->id);

                // Кешируем источники на 10 минут
                Cache::put($cacheKey, $sources, now()->addMinutes(10));
            }

            return response()->json($sources, 200);
        } catch (\Exception $e) {
            return response()->json(['message' => 'An error occurred while retrieving sources: ' . $e->getMessage()], 500);
        }
    }
}
