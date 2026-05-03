<?php

namespace App\Http\Controllers;

use App\Http\Requests\Source\CreateSourceRequest;
use App\Http\Requests\Source\UpdateSourceRequest;
use App\Services\Content\SourceService;
use Exception;
use Illuminate\Support\Facades\Log;

// Контроллер для работы с источниками
class SourceController extends Controller
{
    protected SourceService $sourceService;

    private const CACHE_MINUTES = 10;
    private const CACHE_KEY_SOURCES = 'sources';
    private const CACHE_KEY_SOURCE = 'source_';

    public function __construct(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;
    }

    /**
     * Получение списка всех источников
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $sources = $this->getFromCacheOrStore(self::CACHE_KEY_SOURCES, self::CACHE_MINUTES, function () {
                return $this->sourceService->getAll();
            });

            Log::info('Источники успешно получены');

            return $this->successResponse($sources);
        } catch (Exception $e) {
            Log::error('Ошибка при получении источников: '.$e->getMessage());

            return $this->errorResponse('Failed to retrieve sources. Please try again later.', 500);
        }
    }

    /**
     * Получение конкретного источника
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            $cacheKey = self::CACHE_KEY_SOURCE . $id;
            $source = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                return $this->sourceService->getById($id);
            });

            Log::info('Источник успешно получен', ['id' => $id]);

            return $this->successResponse($source);
        } catch (Exception $e) {
            Log::error('Ошибка при получении источника: '.$e->getMessage(), ['id' => $id]);

            return $this->errorResponse('Source not found', 404);
        }
    }

    /**
     * Создание нового источника
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateSourceRequest $request)
    {
        try {
            $source = $this->sourceService->create($request->all());

            $this->forgetCache(self::CACHE_KEY_SOURCES);

            Log::info('Источник успешно создан', ['source' => $source]);

            return $this->successResponse($source, [], 201);
        } catch (Exception $e) {
            Log::error('Ошибка при создании источника: '.$e->getMessage(), ['data' => $request->all()]);

            return $this->errorResponse('Failed to create source. Please try again later.', 500);
        }
    }

    /**
     * Обновление конкретного источника
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateSourceRequest $request, int $id)
    {
        try {
            $source = $this->sourceService->update($id, $request->only('name', 'iconUrl'));

            $this->forgetCache([
                self::CACHE_KEY_SOURCE . $id,
                self::CACHE_KEY_SOURCES
            ]);

            Log::info('Источник успешно обновлен', ['id' => $id, 'data' => $request->only('name', 'iconUrl')]);

            return $this->successResponse($source);
        } catch (Exception $e) {
            Log::error('Ошибка при обновлении источника: '.$e->getMessage(), ['id' => $id, 'data' => $request->only('name', 'iconUrl')]);

            return $this->errorResponse('Failed to update source. Please try again later.', 500);
        }
    }

    /**
     * Удаление конкретного источника
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->sourceService->delete($id);

            $this->forgetCache([
                self::CACHE_KEY_SOURCE . $id,
                self::CACHE_KEY_SOURCES
            ]);

            Log::info('Источник успешно удален', ['id' => $id]);

            return $this->successResponse(['message' => 'Source deleted successfully']);
        } catch (Exception $e) {
            Log::error('Ошибка при удалении источника: '.$e->getMessage(), ['id' => $id]);

            return $this->errorResponse('Failed to delete source. Please try again later.', 500);
        }
    }
}
