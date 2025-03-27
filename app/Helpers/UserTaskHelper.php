<?php

namespace App\Helpers;

class TaskHelper
{
    public static function calculatePeriodEnd($task, $periodStart)
    {
        switch ($task->period) {
            case 'day':
                return $periodStart->addDay();
            case 'week':
                return $periodStart->addWeek();
            case 'month':
                return $periodStart->addMonth();
            case 'half_year':
                return $periodStart->addMonths(6);
            case 'year':
                return $periodStart->addYear();
            default:
                return null;
        }
    }

    public function isPeriodExpired($task, $periodStart, $periodEnd)
    {
        if (! $periodEnd) {
            return false;
        }

        return now()->isAfter($periodEnd);
    }
}
