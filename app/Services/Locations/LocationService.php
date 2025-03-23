<?php

namespace App\Services\Locations;

use App\Models\Locations\Location;
use App\Services\Base\SimpleService;
use Exception;

class LocationService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'location';

    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 180; // Данные о местоположениях редко меняются

    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('LocationService');
    }

    /**
     * Получить все местоположения
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllLocations()
    {
        $cacheKey = $this->buildCacheKey('all_locations');
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo("Получение всех местоположений");
            return Location::all();
        });
    }

    /**
     * Получить местоположение по ID
     *
     * @param int $id ID местоположения
     * @return Location
     * @throws Exception
     */
    public function getLocationById(int $id)
    {
        $cacheKey = $this->buildCacheKey('location', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo("Получение местоположения по ID", ['location_id' => $id]);
            
            try {
                return Location::findOrFail($id);
            } catch (Exception $e) {
                $this->logWarning("Местоположение не найдено", ['location_id' => $id]);
                throw new Exception("Location with ID {$id} not found");
            }
        });
    }

    /**
     * Создать местоположение
     *
     * @param array $data Данные местоположения
     * @return Location
     */
    public function storeLocation(array $data)
    {
        $this->logInfo("Создание нового местоположения", [
            'name' => $data['name'] ?? 'not set',
            'country' => $data['country'] ?? 'not set'
        ]);
        
        return $this->transaction(function () use ($data) {
            try {
                $location = Location::create($data);
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('all_locations'));
                
                $this->logInfo("Местоположение успешно создано", ['location_id' => $location->id]);
                
                return $location;
            } catch (Exception $e) {
                $this->logError("Ошибка при создании местоположения", ['data' => $data], $e);
                throw new Exception("Error creating location: " . $e->getMessage());
            }
        });
    }

    /**
     * Обновить местоположение
     *
     * @param int $id ID местоположения
     * @param array $data Данные для обновления
     * @return Location
     * @throws Exception
     */
    public function updateLocation(int $id, array $data)
    {
        $this->logInfo("Обновление местоположения", ['location_id' => $id]);
        
        return $this->transaction(function () use ($id, $data) {
            try {
                $location = Location::findOrFail($id);
                $location->update($data);
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('location', [$id]));
                $this->forgetCache($this->buildCacheKey('all_locations'));
                
                $this->logInfo("Местоположение успешно обновлено", ['location_id' => $id]);
                
                return $location;
            } catch (Exception $e) {
                $this->logError("Ошибка при обновлении местоположения", ['location_id' => $id], $e);
                throw new Exception("Error updating location: " . $e->getMessage());
            }
        });
    }

    /**
     * Удалить местоположение
     *
     * @param int $id ID местоположения
     * @return bool
     * @throws Exception
     */
    public function deleteLocation(int $id)
    {
        $this->logInfo("Удаление местоположения", ['location_id' => $id]);
        
        return $this->transaction(function () use ($id) {
            try {
                $location = Location::findOrFail($id);
                $location->delete();
                
                // Сбрасываем кеш
                $this->forgetCache($this->buildCacheKey('location', [$id]));
                $this->forgetCache($this->buildCacheKey('all_locations'));
                
                $this->logInfo("Местоположение успешно удалено", ['location_id' => $id]);
                
                return true;
            } catch (Exception $e) {
                $this->logError("Ошибка при удалении местоположения", ['location_id' => $id], $e);
                throw new Exception("Error deleting location: " . $e->getMessage());
            }
        });
    }
    
    /**
     * Найти местоположения по названию
     *
     * @param string $query Поисковый запрос
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function searchLocations(string $query)
    {
        $cacheKey = $this->buildCacheKey('search_locations', [$query]);
        
        return $this->getFromCacheOrStore($cacheKey, 60, function () use ($query) {
            $this->logInfo("Поиск местоположений", ['query' => $query]);
            
            return Location::where('name', 'like', "%{$query}%")
                ->orWhere('country', 'like', "%{$query}%")
                ->orWhere('city', 'like', "%{$query}%")
                ->get();
        });
    }
    
    /**
     * Получить местоположения по стране
     *
     * @param string $country Название страны
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getLocationsByCountry(string $country)
    {
        $cacheKey = $this->buildCacheKey('country_locations', [$country]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($country) {
            $this->logInfo("Получение местоположений по стране", ['country' => $country]);
            
            return Location::where('country', $country)->get();
        });
    }
}
