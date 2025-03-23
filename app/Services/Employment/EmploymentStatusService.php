<?php

namespace App\Services\Employment;

use App\Models\Employment\EmploymentStatus;
use App\Services\Base\SimpleService;
use Exception;

/**
 * Сервис для работы со статусами занятости
 */
class EmploymentStatusService extends SimpleService
{
    /**
     * Префикс кеша
     *
     * @var string
     */
    protected string $cachePrefix = 'employment_status';
    
    /**
     * Время хранения кеша в минутах
     *
     * @var int
     */
    protected int $defaultCacheMinutes = 60;
    
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('EmploymentStatusService');
    }
    
    /**
     * Получить все статусы занятости
     *
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAllEmploymentStatuses()
    {
        $cacheKey = $this->buildCacheKey('all');
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () {
            $this->logInfo('Получение всех статусов занятости');
            
            try {
                $statuses = EmploymentStatus::all();
                
                $this->logInfo('Получены статусы занятости', [
                    'count' => $statuses->count()
                ]);
                
                return $statuses;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении статусов занятости', [], $e);
                return collect();
            }
        });
    }

    /**
     * Получить статус занятости по ID
     *
     * @param int $id
     * @return EmploymentStatus|null
     */
    public function getEmploymentStatusById($id)
    {
        $cacheKey = $this->buildCacheKey('status', [$id]);
        
        return $this->getFromCacheOrStore($cacheKey, $this->defaultCacheMinutes, function () use ($id) {
            $this->logInfo('Получение статуса занятости по ID', ['id' => $id]);
            
            try {
                $status = EmploymentStatus::find($id);
                
                if (!$status) {
                    $this->logWarning('Статус занятости не найден', ['id' => $id]);
                    return null;
                }
                
                $this->logInfo('Получен статус занятости', ['id' => $id]);
                
                return $status;
            } catch (Exception $e) {
                $this->logError('Ошибка при получении статуса занятости', ['id' => $id], $e);
                return null;
            }
        });
    }

    /**
     * Создать статус занятости
     *
     * @param array $data
     * @return EmploymentStatus|null
     */
    public function createEmploymentStatus(array $data)
    {
        $this->logInfo('Создание статуса занятости', $data);
        
        return $this->transaction(function () use ($data) {
            try {
                $status = EmploymentStatus::create($data);
                
                $this->logInfo('Создан статус занятости', [
                    'id' => $status->id,
                    'name' => $status->name
                ]);
                
                // Очистка кеша всех статусов
                $this->forgetCache($this->buildCacheKey('all'));
                
                return $status;
            } catch (Exception $e) {
                $this->logError('Ошибка при создании статуса занятости', $data, $e);
                return null;
            }
        });
    }

    /**
     * Обновить статус занятости
     *
     * @param int $id
     * @param array $data
     * @return EmploymentStatus|null
     */
    public function updateEmploymentStatus($id, array $data)
    {
        $this->logInfo('Обновление статуса занятости', ['id' => $id, 'data' => $data]);
        
        return $this->transaction(function () use ($id, $data) {
            try {
                $status = EmploymentStatus::find($id);
                
                if (!$status) {
                    $this->logWarning('Статус занятости не найден при обновлении', ['id' => $id]);
                    return null;
                }
                
                $status->update($data);
                
                $this->logInfo('Обновлен статус занятости', [
                    'id' => $status->id,
                    'name' => $status->name
                ]);
                
                // Очистка кеша
                $this->forgetCache([
                    $this->buildCacheKey('all'),
                    $this->buildCacheKey('status', [$id])
                ]);
                
                return $status;
            } catch (Exception $e) {
                $this->logError('Ошибка при обновлении статуса занятости', ['id' => $id], $e);
                return null;
            }
        });
    }

    /**
     * Удалить статус занятости
     *
     * @param int $id
     * @return bool
     */
    public function deleteEmploymentStatus($id)
    {
        $this->logInfo('Удаление статуса занятости', ['id' => $id]);
        
        return $this->transaction(function () use ($id) {
            try {
                $status = EmploymentStatus::find($id);
                
                if (!$status) {
                    $this->logWarning('Статус занятости не найден при удалении', ['id' => $id]);
                    return false;
                }
                
                $result = $status->delete();
                
                if ($result) {
                    $this->logInfo('Удален статус занятости', ['id' => $id]);
                    
                    // Очистка кеша
                    $this->forgetCache([
                        $this->buildCacheKey('all'),
                        $this->buildCacheKey('status', [$id])
                    ]);
                }
                
                return $result;
            } catch (Exception $e) {
                $this->logError('Ошибка при удалении статуса занятости', ['id' => $id], $e);
                return false;
            }
        });
    }
}
