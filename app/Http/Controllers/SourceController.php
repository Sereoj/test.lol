<?php

namespace App\Http\Controllers;

use App\Http\Requests\Source\CreateSourceRequest;
use App\Http\Requests\Source\UpdateSourceRequest;
use App\Services\SourceService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class SourceController extends Controller
{
    protected SourceService $sourceService;

    public function __construct(SourceService $sourceService)
    {
        $this->sourceService = $sourceService;
    }

    /**
     * Display a listing of the sources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            // Попытка получить данные из кеша
            $sources = Cache::get('sources');

            // Если кеш пуст, то извлекаем данные из базы и сохраняем их в кеш
            if (!$sources) {
                $sources = $this->sourceService->getAllSources();
                Cache::put('sources', $sources, now()->addMinutes(10)); // Кешируем на 10 минут
                Log::info('Sources retrieved from database and cached');
            } else {
                Log::info('Sources retrieved from cache');
            }

            return response()->json($sources, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving sources: '.$e->getMessage());
            return response()->json(['message' => 'Failed to retrieve sources. Please try again later.'], 500);
        }
    }

    /**
     * Display the specified source.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function show(int $id)
    {
        try {
            // Попытка получить данные из кеша для конкретного источника
            $cacheKey = 'source_' . $id;
            $source = Cache::get($cacheKey);

            if (!$source) {
                // Если данных нет в кеше, извлекаем из базы и кешируем
                $source = $this->sourceService->getSourceById($id);
                Cache::put($cacheKey, $source, now()->addMinutes(10)); // Кешируем на 10 минут
                Log::info('Source retrieved from database and cached', ['id' => $id]);
            } else {
                Log::info('Source retrieved from cache', ['id' => $id]);
            }

            return response()->json($source, 200);
        } catch (Exception $e) {
            Log::error('Error retrieving source: '.$e->getMessage(), ['id' => $id]);
            return response()->json(['message' => 'Source not found'], 404);
        }
    }

    /**
     * Store a newly created source in storage.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function store(CreateSourceRequest $request)
    {
        try {
            $source = $this->sourceService->createSource($request->all());

            // Очистить кеш, так как данные изменились
            Cache::forget('sources');

            Log::info('Source created successfully', ['source' => $source]);
            return response()->json($source, 201);
        } catch (Exception $e) {
            Log::error('Error creating source: '.$e->getMessage(), ['data' => $request->all()]);
            return response()->json(['message' => 'Failed to create source. Please try again later.'], 500);
        }
    }

    /**
     * Update the specified source in storage.
     *
     * @param  UpdateSourceRequest  $request
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function update(UpdateSourceRequest $request, int $id)
    {
        try {
            $source = $this->sourceService->updateSource($id, $request->only('name', 'iconUrl'));

            // Очистить кеш для обновленного источника
            Cache::forget('source_' . $id);

            Log::info('Source updated successfully', ['id' => $id, 'data' => $request->only('name', 'iconUrl')]);
            return response()->json($source, 200);
        } catch (Exception $e) {
            Log::error('Error updating source: '.$e->getMessage(), ['id' => $id, 'data' => $request->only('name', 'iconUrl')]);
            return response()->json(['message' => 'Failed to update source. Please try again later.'], 500);
        }
    }

    /**
     * Remove the specified source from storage.
     *
     * @param  int  $id
     * @return \Illuminate\Http\JsonResponse
     */
    public function destroy(int $id)
    {
        try {
            $this->sourceService->deleteSource($id);

            // Очистить кеш для удаленного источника и список источников
            Cache::forget('source_' . $id);
            Cache::forget('sources');

            Log::info('Source deleted successfully', ['id' => $id]);
            return response()->json(['message' => 'Source deleted successfully'], 200);
        } catch (Exception $e) {
            Log::error('Error deleting source: '.$e->getMessage(), ['id' => $id]);
            return response()->json(['message' => 'Failed to delete source. Please try again later.'], 500);
        }
    }
}
