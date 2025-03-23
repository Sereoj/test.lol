<?php

namespace App\Services\Acquiring;

/**
 * Сервис платежной системы Selection
 */
class SelectionService extends AcquiringBaseService
{
    /**
     * Название платежной системы
     *
     * @var string
     */
    protected string $client = 'selection';
}
