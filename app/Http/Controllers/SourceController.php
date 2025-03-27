<?php

namespace App\Http\Controllers;

use App\Http\Requests\Source\CreateSourceRequest;
use App\Http\Requests\Source\UpdateSourceRequest;
use App\Services\Content\SourceService;
use Exception;
use Illuminate\Support\Facades\Log;

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
     * Display a listing of the sources.
     *
     * @return \Illuminate\Http\JsonResponse
     */
    public function index()
    {
        try {
            $sources = $this->getFromCacheOrStore(self::CACHE_KEY_SOURCES, self::CACHE_MINUTES, function () {
                return $this->sourceService->getAll();
            });

            Log::info('Sources retrieved successfully');

            return $this->successResponse($sources);
        } catch (Exception $e) {
            Log::error('Error retrieving sources: '.$e->getMessage());

            return $this->errorResponse('Failed to retrieve sources. Please try again later.', 500);
        }
    }

    /**
     * Display the specified source.
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

            Log::info('Source retrieved successfully', ['id' => $id]);

            return $this->successResponse($source);
        } catch (Exception $e) {
            Log::error('Error retrieving source: '.$e->getMessage(), ['id' => $id]);

            return $this->errorResponse('Source not found', 404);
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
            $source = $this->sourceService->create($request->all());

            $this->forgetCache(self::CACHE_KEY_SOURCES);

            Log::info('Source created successfully', ['source' => $source]);

            return $this->successResponse($source, 201);
        } catch (Exception $e) {
            Log::error('Error creating source: '.$e->getMessage(), ['data' => $request->all()]);

            return $this->errorResponse('Failed to create source. Please try again later.', 500);
        }
    }

    /**
     * Update the specified source in storage.
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

            Log::info('Source updated successfully', ['id' => $id, 'data' => $request->only('name', 'iconUrl')]);

            return $this->successResponse($source);
        } catch (Exception $e) {
            Log::error('Error updating source: '.$e->getMessage(), ['id' => $id, 'data' => $request->only('name', 'iconUrl')]);

            return $this->errorResponse('Failed to update source. Please try again later.', 500);
        }
    }

    /**
     * Remove the specified source from storage.
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

            Log::info('Source deleted successfully', ['id' => $id]);

            return $this->successResponse(['message' => 'Source deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting source: '.$e->getMessage(), ['id' => $id]);

            return $this->errorResponse('Failed to delete source. Please try again later.', 500);
        }
    }
}
