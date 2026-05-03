<?php

namespace App\Http\Controllers\Users;

use App\Http\Controllers\Controller;
use App\Http\Requests\Location\StoreLocationRequest;
use App\Http\Resources\LocationResource;
use App\Services\Locations\LocationService;
use Exception;
use Illuminate\Support\Facades\Log;

// Контроллер для работы с местоположениями пользователей
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
                return LocationResource::collection($this->locationService->getAllLocations());
            });

            Log::info('Местоположения успешно получены');

            return $this->successResponse($locations);
        } catch (Exception $e) {
            Log::error('Ошибка при получении местоположений: ' . $e->getMessage());
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

            Log::info('Местоположение успешно получено', ['id' => $id]);

            return $this->successResponse($location);
        } catch (Exception $e) {
            Log::error('Ошибка при получении местоположения: ' . $e->getMessage(), ['id' => $id]);
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
            Log::info('Местоположение успешно сохранено', [
                'location_id' => $location->id,
                'data' => $request->all(),
                'timestamp' => now(),
            ]);

            $this->forgetCache(self::CACHE_KEY_LOCATIONS_ALL);

            return $this->successResponse(['message' => 'Location stored successfully', 'location' => $location], 201);
        } catch (Exception $e) {
            Log::error('Ошибка при сохранении местоположения: ' . $e->getMessage(), ['data' => $request->all()]);
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
            Log::info('Местоположение успешно обновлено', ['id' => $id, 'data' => $request->all()]);

            $this->forgetCache([
                self::CACHE_KEY_LOCATION . $id,
                self::CACHE_KEY_LOCATIONS_ALL
            ]);

            return $this->successResponse(['message' => 'Location updated successfully', 'location' => $location]);
        } catch (Exception $e) {
            Log::error('Ошибка при обновлении местоположения: ' . $e->getMessage(), ['id' => $id, 'data' => $request->all()]);
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
            Log::info('Местоположение успешно удалено', ['id' => $id]);

            $this->forgetCache([
                self::CACHE_KEY_LOCATION . $id,
                self::CACHE_KEY_LOCATIONS_ALL
            ]);

            return $this->successResponse(['message' => 'Location deleted successfully']);
        } catch (Exception $e) {
            Log::error('Ошибка при удалении местоположения: ' . $e->getMessage(), ['id' => $id]);
            return $this->errorResponse($e->getMessage(), 500);
        }
    }
}
