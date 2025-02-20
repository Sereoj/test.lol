<?php

namespace App\Http\Controllers;

use App\Http\Requests\Location\StoreLocationRequest;
use App\Services\Locations\LocationService;
use Exception;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class UserLocationController extends Controller
{
    protected LocationService $locationService;

    public function __construct(LocationService $locationService)
    {
        $this->locationService = $locationService;
    }

    /**
     * Обработчик ошибок и логирование
     */
    protected function handleError(Exception $e, string $message)
    {
        Log::error($message.': '.$e->getMessage());

        return response()->json(['message' => $e->getMessage()], 500);
    }

    /**
     * Получить все местоположения.
     */
    public function index()
    {
        try {
            $cacheKey = 'locations_all';
            $locations = Cache::remember($cacheKey, now()->addMinutes(60), function () {
                return $this->locationService->getAllLocations();
            });

            Log::info('Locations retrieved successfully from cache');

            return response()->json($locations, 200);
        } catch (Exception $e) {
            return $this->handleError($e, 'Error retrieving locations');
        }
    }

    /**
     * Получить указанное местоположение.
     */
    public function show(int $id)
    {
        try {
            $cacheKey = 'location_'.$id;
            $location = Cache::remember($cacheKey, now()->addMinutes(60), function () use ($id) {
                return $this->locationService->getLocationById($id);
            });

            Log::info('Location retrieved successfully from cache', ['id' => $id]);

            return response()->json($location, 200);
        } catch (Exception $e) {
            return $this->handleError($e, 'Error retrieving location');
        }
    }

    /**
     * Сохранить новое местоположение.
     */
    public function store(StoreLocationRequest $request)
    {
        try {
            // Сохранение нового местоположения
            $location = $this->locationService->storeLocation($request->all());
            Log::info('Location stored successfully', [
                'location_id' => $location->id,
                'data' => $request->all(),
                'timestamp' => now(),
            ]);

            // Очистка кеша после создания нового местоположения
            Cache::forget('locations_all');

            return response()->json(['message' => 'Location stored successfully', 'location' => $location], 201);
        } catch (Exception $e) {
            return $this->handleError($e, 'Error storing location');
        }
    }

    /**
     * Обновить указанное местоположение.
     */
    public function update(StoreLocationRequest $request, int $id)
    {
        try {
            // Валидация запроса
            $request->validate([
                'name' => 'required|string|max:255',
                'address' => 'required|string',
            ]);

            // Обновление местоположения
            $location = $this->locationService->updateLocation($id, $request->all());
            Log::info('Location updated successfully', ['id' => $id, 'data' => $request->all()]);

            // Очистка кеша после обновления местоположения
            Cache::forget('location_'.$id);
            Cache::forget('locations_all');

            return response()->json(['message' => 'Location updated successfully', 'location' => $location], 200);
        } catch (Exception $e) {
            return $this->handleError($e, 'Error updating location');
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

            // Очистка кеша после удаления местоположения
            Cache::forget('location_'.$id);
            Cache::forget('locations_all');

            return response()->json(['message' => 'Location deleted successfully'], 200);
        } catch (Exception $e) {
            return $this->handleError($e, 'Error deleting location');
        }
    }
}
