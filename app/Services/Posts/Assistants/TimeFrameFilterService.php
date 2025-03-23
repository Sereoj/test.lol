<?php

namespace App\Services\Posts\Assistants;

use App\Services\Base\SimpleService;
use Exception;

/**
 * Сервис для фильтрации постов по временному диапазону
 */
class TimeFrameFilterService extends SimpleService
{
    /**
     * Конструктор
     */
    public function __construct()
    {
        parent::__construct();
        $this->setLogPrefix('TimeFrameFilterService');
    }
    
    /**
     * Применение фильтрации по временному диапазону
     *
     * @param mixed $query
     * @param string|null $timeFrame
     * @return void
     */
    public function apply($query, ?string $timeFrame)
    {
        if (!$timeFrame) {
            $this->logInfo('Фильтрация по временному диапазону не применена (не указан период)');
            return;
        }
        
        $this->logInfo('Применение фильтрации по временному диапазону', [
            'time_frame' => $timeFrame
        ]);
        
        try {
            $timeFrameMap = [
                'week' => now()->subWeek(),
                'month' => now()->subMonth(),
                'year' => now()->subYear(),
            ];

            if (isset($timeFrameMap[$timeFrame])) {
                $query->where('posts.created_at', '>=', $timeFrameMap[$timeFrame]);
                $this->logInfo('Применен фильтр по временному диапазону', [
                    'time_frame' => $timeFrame,
                    'date_from' => $timeFrameMap[$timeFrame]->toDateTimeString()
                ]);
            } else {
                $this->logWarning('Неизвестный временной диапазон, фильтрация не применена', [
                    'time_frame' => $timeFrame,
                    'available_time_frames' => array_keys($timeFrameMap)
                ]);
            }
        } catch (Exception $e) {
            $this->logError('Ошибка при применении фильтрации по временному диапазону', [
                'time_frame' => $timeFrame
            ], $e);
        }
    }
}
