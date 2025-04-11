<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Services\Locations\LocationService;
use Exception;
use Illuminate\Support\Facades\Log;

class UserLocationController extends Controller
{
    protected LocationService $locationService;

    private const CACHE_MINUTES = 60;
    private const CACHE_KEY_LOCATIONS_ALL = 'locations_all';
    private const CACHE_KEY_LOCATION = 'location_';

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Получить все местоположения.
     */
    public function index()
    {
        try {
            $locations = $this->getFromCacheOrStore(self::CACHE_KEY_LOCATIONS_ALL, self::CACHE_MINUTES, function () {
                return $this->locationService->getAllLocations();
            });

            Log::info('Locations retrieved successfully');

            return $this->successResponse($locations);
        } catch (Exception $e) {
            Log::error('Error retrieving locations: ' . $e->getMessage());
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Получить указанное местоположение.
     */
    public function show(int $id)
    {
        try {
            $cacheKey = self::CACHE_KEY_LOCATION . $id;
            $location = $this->getFromCacheOrStore($cacheKey, self::CACHE_MINUTES, function () use ($id) {
                return $this->locationService->getLocationById($id);
            });

            Log::info('Location retrieved successfully', ['id' => $id]);

            return $this->successResponse($location);
        } catch (Exception $e) {
            Log::error('Error retrieving location: ' . $e->getMessage(), ['id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Сохранить новое местоположение.
     */
    public function store(StoreLocationRequest $request)
    {
        try {
            $location = $this->locationService->storeLocation($request->all());
            Log::info('Location stored successfully', [
                'location_id' => $location->id,
                'data' => $request->all(),
                'timestamp' => now(),
            ]);

            $this->forgetCache(self::CACHE_KEY_LOCATIONS_ALL);

            return $this->successResponse(['message' => 'Location stored successfully', 'location' => $location], 201);
        } catch (Exception $e) {
            Log::error('Error storing location: ' . $e->getMessage(), ['data' => $request->all()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Обновить указанное местоположение.
     */
    public function update(StoreLocationRequest $request, int $id)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string',
            ]);

            $location = $this->locationService->updateLocation($id, $request->all());
            Log::info('Location updated successfully', ['id' => $id, 'data' => $request->all()]);

            $this->forgetCache([
                self::CACHE_KEY_LOCATION . $id,
                self::CACHE_KEY_LOCATIONS_ALL
            ]);

            return $this->successResponse(['message' => 'Location updated successfully', 'location' => $location]);
        } catch (Exception $e) {
            Log::error('Error updating location: ' . $e->getMessage(), ['id' => $id, 'data' => $request->all()]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }

    /**
     * Удалить указанное местоположение.
     */
    public function destroy(int $id)
    {
        try {
            $this->locationService->deleteLocation($id);
            Log::info('Location deleted successfully', ['id' => $id]);

            $this->forgetCache([
                self::CACHE_KEY_LOCATION . $id,
                self::CACHE_KEY_LOCATIONS_ALL
            ]);

            return $this->successResponse(['message' => 'Location deleted successfully']);
        } catch (Exception $e) {
            Log::error('Error deleting location: ' . $e->getMessage(), ['id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
